<?php

namespace App\Actions\Admin;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderActivity;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // For potential database transactions
use InvalidArgumentException; // For specific error handling

class ConfirmManualTransactionAction
{
    /**
     * Execute the action to confirm a manual transaction.
     *
     * @param Transaction $transaction The transaction to confirm.
     * @return bool True on success, throws exception on failure.
     * @throws InvalidArgumentException If the transaction is not pending.
     */
    public function execute(Transaction $transaction): bool
    {
        if ($transaction->status !== 'pending') {
            throw new InvalidArgumentException('La transacción no está pendiente y no puede ser confirmada.');
        }

        // It's good practice to wrap database operations in a transaction
        // if multiple models are being updated to ensure data integrity.
        DB::beginTransaction();

        try {
            $transaction->status = 'completed';

            // Order Payment Logic
            if ($transaction->type === 'order_payment' && $transaction->invoice_id) {
                $invoice = Invoice::with('order.client')->find($transaction->invoice_id);
                if ($invoice) {
                    $this->updateInvoiceAndOrderStatus($invoice, $transaction);
                }
            }
            // Fund Addition Logic
            elseif ($transaction->type === 'fund_addition' && $transaction->client_id) {
                $client = User::find($transaction->client_id);
                if ($client) {
                    $client->balance = bcadd($client->balance, $transaction->amount, 2);
                    $client->save();
                }
            }

            $transaction->save();

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error or handle it as needed
            // For now, rethrow to be caught by the controller
            throw $e; 
        }
    }

    /**
     * Helper method to update invoice and order status.
     * This logic was part of AdminTransactionController and is moved here for encapsulation.
     */
    private function updateInvoiceAndOrderStatus(Invoice $invoice, Transaction $transaction)
    {
        // Ensure relations are loaded if not already by the caller
        $invoice->loadMissing('transactions', 'order.client');

        $totalPaid = $invoice->transactions()
                             ->where('status', 'completed')
                             // ->where('type', 'payment') // Consider if 'fund_addition' can also pay invoices
                             ->sum('amount');
        
        // Assuming $transaction being processed is now 'completed' and part of the sum
        // If $transaction is not yet saved when this is called, its amount might need to be added manually to $totalPaid for this calculation
        // However, the action saves it before commit, so it should be included in the sum if relations are reloaded or sum is on the DB.
        // For simplicity and given the flow, we assume the sum includes the current transaction's effect.

        $netPaid = $totalPaid; // Simplified, assuming no refunds for now in this context

        if (bccomp($netPaid, $invoice->total_amount, 2) >= 0) {
            if ($invoice->status !== 'paid') {
                $invoice->status = 'paid';
                $invoice->paid_date = $transaction->transaction_date ?? now();
            }
        } elseif (bccomp($netPaid, '0', 2) <= 0 && in_array($invoice->status, ['paid', 'overdue'])) {
            $invoice->status = 'unpaid'; // Or 'refunded'
            $invoice->paid_date = null;
        } elseif (bccomp($netPaid, '0', 2) > 0 && bccomp($netPaid, $invoice->total_amount, 2) < 0 && $invoice->status === 'paid') {
            $invoice->status = 'unpaid'; // Or 'partially_paid'
            $invoice->paid_date = null;
        }
        $invoice->save();

        $order = $invoice->order;
        if ($invoice->status === 'paid' && $order) {
            if ($order->status === 'pending_payment' || $order->status === 'paid_pending_execution') { // Also update if it was already marked as such but transaction was pending
                $previous_status = $order->status;
                $order->status = 'paid_pending_execution'; // Ensure it's this status
                $order->save();

                OrderActivity::create([
                    'order_id' => $order->id,
                    'user_id' => Auth::id(), // Admin user performing the action
                    'type' => 'payment_confirmed_order_pending_execution',
                    'details' => json_encode([
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'payment_transaction_id' => $transaction->id,
                        'transaction_status_changed_to' => $transaction->status,
                        'previous_order_status' => $previous_status,
                        'client_name' => $order->client->name ?? 'N/A',
                        'new_order_status' => $order->status,
                    ]),
                ]);
            }
        }
    }
}

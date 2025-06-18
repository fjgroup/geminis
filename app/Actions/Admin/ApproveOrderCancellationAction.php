<?php

namespace App\Actions\Admin;

use App\Models\Order;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\OrderActivity;
use App\Models\User; // Needed for updating client balance
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Keep Log for potential direct use in action if needed, though controller handles it too
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Exception; // Import base Exception

class ApproveOrderCancellationAction
{
    /**
     * Execute the action to approve an order cancellation request.
     *
     * @param  Order  $order
     * @return void
     * @throws Exception If any error occurs during the process.
     */
    public function execute(Order $order): void
    {
        DB::beginTransaction();

        try {
            $previousStatus = $order->status;

            // This check is also in the controller, but can be an assertion here too.
            if ($previousStatus !== 'cancellation_requested_by_client') {
                throw new Exception("Order is not in 'cancellation_requested_by_client' status. Current status: {$previousStatus}");
            }

            // 1. Update Order Status
            $order->status = 'cancelled';
            $order->save();

            $creditedAmount = 0; // Initialize credited amount for logging

            // 2. Update associated Invoice status
            if ($order->invoice) {
                $invoice = $order->invoice;
                if (in_array($invoice->status, ['paid', 'overdue'])) { // Only if it was actually paid
                    $invoice->status = 'refunded';
                    $invoice->save();

                    $creditedAmount = $invoice->total_amount; // Set credited amount

                    // 3. Create Financial Transaction for the credit issued to client
                    Transaction::create([
                        'invoice_id' => $invoice->id,
                        'client_id' => $order->client_id,
                        'reseller_id' => $order->reseller_id,
                        'gateway_slug' => 'internal_credit',
                        'gateway_transaction_id' => 'CREDIT-' . strtoupper(Str::random(10)),
                        'type' => 'credit_added',
                        'amount' => $creditedAmount,
                        'currency_code' => $invoice->currency_code,
                        'status' => 'completed',
                        'description' => 'Credit issued for cancelled Order #' . $order->order_number . ' / Invoice #' . $invoice->invoice_number,
                        'transaction_date' => Carbon::now(),
                    ]);

                    // 3.5. Update Client's Balance
                    $client = $order->client; // Assumes 'client' relationship is available
                    if ($client && $creditedAmount > 0) {
                        $client->increment('balance', $creditedAmount);
                    } else if (!$client) {
                        Log::warning("Client not found for order ID: {$order->id} during credit approval in Action.");
                        // Depending on business rules, this might be a critical error.
                    }
                } else {
                    // If invoice wasn't 'paid' or 'overdue', just mark invoice as cancelled.
                    $invoice->status = 'cancelled';
                    $invoice->save();
                }
            }

            // 4. Create OrderActivity Log
            // Re-fetch client for the most up-to-date balance for logging, if necessary.
            // If client was fetched above and incremented, its model instance might have the new balance.
            // For simplicity, we'll assume $order->client->balance (if reloaded or if increment updates instance) is current.
            // Or, calculate new balance: $newBalance = $originalBalance + $creditedAmount;
            $updatedClientForLog = User::find($order->client_id); // Re-fetch for accurate balance

            OrderActivity::create([
                'order_id' => $order->id,
                'user_id' => Auth::id(), // Admin performing the action
                'type' => 'cancellation_approved_credit_issued',
                'details' => [
                    'previous_status' => $previousStatus,
                    'new_order_status' => 'cancelled',
                    'invoice_id' => $order->invoice_id,
                    'invoice_status_updated_to' => $order->invoice ? $order->invoice->status : null,
                    'credited_amount' => $creditedAmount,
                    'client_new_balance' => $updatedClientForLog ? $updatedClientForLog->balance : null, // Use re-fetched client's balance
                ]
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e; // Re-throw to be handled by the controller
        }
    }
}

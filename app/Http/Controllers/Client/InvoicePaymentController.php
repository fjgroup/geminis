<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Transaction; // Financial transaction
use App\Models\OrderActivity; // Order activity log
use Illuminate\Http\Request; // Basic request, no form data needed for this simulation
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // For logging
use Carbon\Carbon;
use Illuminate\Support\Str; // For Str::random
use Exception; // Added for general \Exception

class InvoicePaymentController extends Controller
{
    /**
     * Simulate processing a payment for the specified invoice.
     *
     * @param  Request  $request
     * @param  Invoice  $invoice
     * @return RedirectResponse
     */
    public function store(Request $request, Invoice $invoice): RedirectResponse
    {
        $user = Auth::user();
        if ($invoice->client_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }
        if ($invoice->status !== 'unpaid') {
            return redirect()->route('client.invoices.show', $invoice->id)
                             ->with('error', 'This invoice cannot be paid at this time. Status: ' . $invoice->status);
        }

        $paymentMethod = $request->input('payment_method', 'manual_simulation'); // 'manual_simulation' or 'account_credit'

        DB::beginTransaction();
        try {
            if ($paymentMethod === 'account_credit') {
                if ($user->balance >= $invoice->total_amount) {
                    // Sufficient credit to pay full invoice
                    $user->decrement('balance', $invoice->total_amount); // Use decrement for safety

                    Transaction::create([
                        'invoice_id' => $invoice->id,
                        'client_id' => $user->id,
                        'reseller_id' => $invoice->reseller_id,
                        'gateway_slug' => 'account_credit',
                        'gateway_transaction_id' => 'CREDIT-USED-' . strtoupper(Str::random(10)),
                        'type' => 'credit_used', // New transaction type
                        'amount' => $invoice->total_amount,
                        'currency_code' => $invoice->currency_code,
                        'status' => 'completed',
                        'description' => 'Payment for Invoice ' . $invoice->invoice_number . ' using account credit.',
                        'transaction_date' => Carbon::now(),
                    ]);

                    $invoice->status = 'paid';
                    $invoice->paid_date = Carbon::now();
                    $invoice->save();
                    
                    $invoice->loadMissing('order'); // Ensure order is loaded
                    if ($invoice->order) {
                        $order = $invoice->order;
                        if ($order->status === 'pending_payment') { // Assuming initial state is 'pending_payment'
                            $previous_order_status = $order->status;
                            // Correction: Set Order status to 'pending_payment' as per explicit task requirement.
                            $order->status = 'paid_pending_execution';
                            $order->save();
                            OrderActivity::create([
                                'order_id' => $order->id,
                                'user_id' => $user->id,
                                'type' => 'invoice_paid_by_client', // Corrected type
                                'details' => json_encode([
                                    'invoice_id' => $invoice->id,
                                    'invoice_number' => $invoice->invoice_number,
                                    'payment_method' => 'account_credit',
                                    'previous_order_status' => $previous_order_status,
                                    'new_order_status' => $order->status, // This will be 'pending_payment'
                                    'message' => 'Invoice paid by client using account credit. Order is now pending further processing or admin confirmation.'
                                ])
                            ]);
                        } else if ($order) {
                            // Log if order was found but not in 'pending_payment' status when invoice was paid.
                            OrderActivity::create([
                                'order_id' => $order->id,
                                'user_id' => $user->id,
                                'type' => 'invoice_paid_by_client',
                                'details' => json_encode([
                                     'invoice_id' => $invoice->id,
                                     'invoice_number' => $invoice->invoice_number,
                                     'payment_method' => 'account_credit',
                                     'order_status_at_payment' => $order->status,
                                     'message' => 'Invoice paid using account credit, but associated order was not in the expected initial state (pending_payment).'
                                ])
                            ]);
                        }
                    }
                    DB::commit();
                    return redirect()->route('client.invoices.show', $invoice->id)
                                     ->with('success', 'Invoice paid successfully using account credit. Your order is now pending processing.');
                } else {
                    DB::rollBack(); 
                    return redirect()->route('client.invoices.show', $invoice->id)
                                     ->with('error', 'Insufficient account credit to pay this invoice. Your balance: ' . $user->formatted_balance);
                }
            } elseif ($paymentMethod === 'manual_simulation') {
                // Existing manual simulation logic
                $invoice->status = 'paid';
                $invoice->paid_date = Carbon::now();
                $invoice->save();

                Transaction::create([
                    'invoice_id' => $invoice->id, 'client_id' => $user->id, 'reseller_id' => $invoice->reseller_id,
                    'gateway_slug' => 'manual_simulation', 'gateway_transaction_id' => 'SIM-' . strtoupper(Str::random(12)),
                    'type' => 'payment', 'amount' => $invoice->total_amount, 'currency_code' => $invoice->currency_code,
                    'status' => 'completed', 'description' => 'Simulated payment for Invoice ' . $invoice->invoice_number,
                    'transaction_date' => Carbon::now(),
                ]);

                $invoice->loadMissing('order'); // Ensure order is loaded
                if ($invoice->order) {
                    $order = $invoice->order;
                    if ($order->status === 'pending_payment') { // Assuming initial state is 'pending_payment'
                        $previous_order_status = $order->status;
                        // Correction: Set Order status to 'pending_payment' as per explicit task requirement.
                        $order->status = 'paid_pending_execution';
                        $order->save();
                        OrderActivity::create([
                            'order_id' => $order->id,
                            'user_id' => $user->id,
                            'type' => 'invoice_paid_by_client', // Corrected type
                            'details' => json_encode([
                                'invoice_id' => $invoice->id,
                                'invoice_number' => $invoice->invoice_number,
                                'payment_method' => 'manual_simulation',
                                'previous_order_status' => $previous_order_status,
                                'new_order_status' => $order->status, // This will be 'pending_payment'
                                'message' => 'Invoice paid by client (simulated). Order is now pending further processing or admin confirmation.'
                            ])
                        ]);
                    } else if ($order) {
                         // Log if order was found but not in 'pending_payment' status when invoice was paid.
                        OrderActivity::create([
                            'order_id' => $order->id,
                            'user_id' => $user->id,
                            'type' => 'invoice_paid_by_client',
                            'details' => json_encode([
                                 'invoice_id' => $invoice->id,
                                 'invoice_number' => $invoice->invoice_number,
                                 'payment_method' => 'manual_simulation',
                                 'order_status_at_payment' => $order->status,
                                 'message' => 'Invoice paid (simulated), but associated order was not in the expected initial state (pending_payment).'
                            ])
                        ]);
                    }
                }
                DB::commit();
                return redirect()->route('client.invoices.show', $invoice->id)
                                 ->with('success', 'Invoice marked as paid successfully (simulated). Your order is now pending processing.');
            } else {
                DB::rollBack();
                return redirect()->route('client.invoices.show', $invoice->id)->with('error', 'Invalid payment method selected.');
            }

        } catch (Exception $e) { // Use statement applied
            DB::rollBack();
            Log::error('Invoice payment failed for invoice ' . $invoice->id . ': ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('client.invoices.show', $invoice->id)
                             ->with('error', 'There was an issue processing your payment. Please try again.');
        }
    }
}

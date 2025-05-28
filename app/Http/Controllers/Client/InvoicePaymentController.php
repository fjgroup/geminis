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
        // Authorization: Ensure the invoice belongs to the authenticated client
        // and can be paid (e.g., status is 'unpaid').
        if ($invoice->client_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        if ($invoice->status !== 'unpaid') {
            return redirect()->route('client.invoices.show', $invoice->id)
                             ->with('error', 'This invoice cannot be paid at this time. Status: ' . $invoice->status);
        }

        DB::beginTransaction();
        try {
            // 1. Update Invoice
            $invoice->status = 'paid';
            $invoice->paid_date = Carbon::now();
            $invoice->save();

            // 2. Create Financial Transaction
            Transaction::create([
                'invoice_id' => $invoice->id,
                'client_id' => $invoice->client_id,
                'reseller_id' => $invoice->reseller_id, // Carry over from invoice
                'gateway_slug' => 'manual_simulation', // Or 'client_marked_paid'
                'gateway_transaction_id' => 'SIM-' . strtoupper(Str::random(12)),
                'type' => 'payment',
                'amount' => $invoice->total_amount,
                'currency_code' => $invoice->currency_code,
                'status' => 'completed', // Simulated payment is instantly completed
                'fees_amount' => 0, // No fees for simulation
                'description' => 'Simulated payment for Invoice ' . $invoice->invoice_number,
                'transaction_date' => Carbon::now(),
            ]);

            // 3. Update associated Order status (if exists)
            // Ensure 'order' relationship is loaded if not automatically by route model binding enhancements
            $invoice->loadMissing('order'); 
            if ($invoice->order) { 
                $order = $invoice->order;
                // Ensure order status allows for this transition
                if ($order->status === 'pending_payment') {
                     // IMPORTANT: 'paid_pending_execution' must be a valid ENUM value in the 'orders' table 'status' column.
                     // If not, this will cause a database error.
                     $order->status = 'paid_pending_execution'; 
                     $order->save();

                     // 4. Log Order Activity for order status update
                     OrderActivity::create([
                        'order_id' => $order->id,
                        'user_id' => Auth::id(), // Client initiated this via payment
                        'type' => 'invoice_paid_by_client', // Or a more specific "order_payment_confirmed"
                        'details' => [
                            'invoice_id' => $invoice->id,
                            'invoice_number' => $invoice->invoice_number,
                            'new_order_status' => $order->status,
                        ]
                    ]);
                }
            } else {
                 // If it's a manual invoice without a direct order, log activity against invoice if needed
                 // Or a general payment activity type. For now, focusing on order-linked invoices.
            }


            DB::commit();

            return redirect()->route('client.invoices.show', $invoice->id)
                             ->with('success', 'Invoice marked as paid successfully. Your order will now be processed.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Simulated payment failed for invoice ' . $invoice->id . ': ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('client.invoices.show', $invoice->id)
                             ->with('error', 'There was an issue processing the simulated payment. Please try again.');
        }
    }
}

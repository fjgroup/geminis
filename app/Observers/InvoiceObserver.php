<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderActivity;

class InvoiceObserver
{
    /**
     * Handle the Invoice "updating" event.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return void
     */
    public function updating(Invoice $invoice)
    {
        // Check if the status attribute is being changed to 'paid' and was not 'paid' before.
        if ($invoice->isDirty('status') && $invoice->status === 'paid' && $invoice->getOriginal('status') !== 'paid') {
            // Retrieve the associated Order using the order() relationship.
            $order = $invoice->order;

            // If an Order exists and its status is currently 'pending_payment' or 'paid_pending_execution':
            if ($order && in_array($order->status, ['pending_payment', 'paid_pending_execution'])) {
                $original_order_status = $order->status;

                // Update the Order's status to 'pending_provisioning'.
                $order->status = 'pending_provisioning';
                // Save the Order.
                $order->save();

                // Create an OrderActivity record.
                OrderActivity::create([
                    'order_id' => $order->id,
                    'user_id' => null, // For now, using null as it's an automated process.
                    'type' => 'order_status_auto_updated_to_pending_provisioning',
                    'details' => json_encode([
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'previous_order_status' => $original_order_status,
                        'new_order_status' => 'pending_provisioning',
                    ]),
                ]);
            }
        }
    }
}

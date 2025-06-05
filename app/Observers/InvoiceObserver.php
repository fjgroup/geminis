<?php

namespace App\Observers;

use App\Models\Invoice;
// use App\Models\Order; // Removed
// use App\Models\OrderActivity; // Removed
use App\Jobs\ProvisionClientServiceJob;
// use App\Models\InvoiceItem; // Not strictly needed if using $invoice->items

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
        // Check if the status attribute is being changed to 'paid' or 'pending_activation'
        if ($invoice->isDirty('status') &&
            in_array($invoice->status, ['paid', 'pending_activation'])) {

            // Load necessary relationships if not already loaded.
            // Ensure productType and clientService are loaded for each item.
            $invoice->loadMissing(['items.product.productType', 'items.clientService']);

            foreach ($invoice->items as $invoiceItem) {
                // Check if the product type associated with this item is meant to create a service instance
                if ($invoiceItem->product &&
                    $invoiceItem->product->productType &&
                    $invoiceItem->product->productType->creates_service_instance) {

                    // Check if a client service does not exist for this item,
                    // or if it exists and its status is 'pending' (or another initial state you define)
                    if (!$invoiceItem->clientService || $invoiceItem->clientService->status === 'pending') {
                        // Dispatch the job to provision this specific invoice item
                        ProvisionClientServiceJob::dispatch($invoiceItem);

                        // Optional: Log or add an internal note to the invoice item or invoice itself
                        // Log::info("Dispatched ProvisionClientServiceJob for InvoiceItem ID: {$invoiceItem->id}");
                    }
                }
            }
        }
    }
}

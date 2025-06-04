<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ClientService; // Keep for type hinting if any method still uses it, though direct creation is removed
use App\Models\OrderActivity;
// ProductType is not directly instantiated but accessed via $orderItem->product->productType
// use App\Models\ProductType;
use Carbon\Carbon; // Keep if any date logic remains or is planned, though direct use is removed
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Keep for potential future use or consistency
use App\Jobs\ProvisionClientServiceJob; // Added import

class OrderObserver
{
    /**
     * Handle the Order "updating" event.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function updating(Order $order)
    {
        // Check if the status attribute is being changed to 'pending_provisioning'
        if ($order->isDirty('status') && $order->status === 'pending_provisioning') {
            // Load necessary relationships for the order
            $order->loadMissing('items.product.productType', 'items.productPricing.billingCycle', 'client');

            foreach ($order->items as $orderItem) {
                // Check if the product type creates a service instance and if a service hasn't been created yet
                if (
                    $orderItem->product &&
                    $orderItem->product->productType &&
                    $orderItem->product->productType->creates_service_instance &&
                    is_null($orderItem->client_service_id)
                    // Add more checks if needed, e.g., ensure the product is not already provisioned via another mechanism
                ) {
                    // Dispatch the job to handle provisioning
                    ProvisionClientServiceJob::dispatch($orderItem);
                    Log::info("OrderObserver: ProvisionClientServiceJob dispatched for OrderItem ID: {$orderItem->id} (Order ID {$order->id}).");

                    // Create an OrderActivity record indicating the job was queued
                    OrderActivity::create([
                        'order_id' => $order->id,
                        'user_id' => null, // System action
                        'type' => 'service_provisioning_queued', // New type
                        'details' => json_encode([
                            'order_item_id' => $orderItem->id,
                            'product_name' => $orderItem->product->name, // Assumes product relationship is loaded via loadMissing
                            'domain' => $orderItem->domain_name,
                        ]),
                    ]);
                    // Note: The ClientService itself is not created here anymore.
                    // The OrderItem->client_service_id will be updated by the job.
                }
            }
        }
    }
}

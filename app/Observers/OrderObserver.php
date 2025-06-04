<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ClientService;
use App\Models\OrderActivity;
// ProductType is not directly instantiated but accessed via $orderItem->product->productType
// use App\Models\ProductType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // For potential transaction control if needed, though Eloquent handles saves individually

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
                ) {
                    // Determine registration_date
                    $registrationDate = Carbon::now();
                    $nextDueDate = $registrationDate->copy();

                    // Calculate next_due_date based on billing cycle
                    $billingCycle = $orderItem->productPricing->billingCycle ?? null;

                    if ($billingCycle) {
                        switch ($billingCycle->type) {
                            case 'day':
                                $nextDueDate->addDays($billingCycle->multiplier);
                                break;
                            case 'month':
                                $nextDueDate->addMonthsNoOverflow($billingCycle->multiplier);
                                break;
                            case 'year':
                                $nextDueDate->addYearsNoOverflow($billingCycle->multiplier);
                                break;
                            default:
                                Log::warning("OrderObserver: Unknown billing cycle type '{$billingCycle->type}' for ProductPricing ID: {$orderItem->product_pricing_id}. Defaulting next due date to 1 month.");
                                $nextDueDate->addMonth();
                        }
                    } else {
                        Log::warning("OrderObserver: BillingCycle not found for ProductPricing ID: {$orderItem->product_pricing_id}. Defaulting next due date to 1 month.");
                        $nextDueDate->addMonth();
                    }

                    // Create a new ClientService
                    $newClientService = new ClientService([
                        'client_id' => $order->client_id,
                        'reseller_id' => $order->client->reseller_id, // Assumes client relationship and reseller_id on client are loaded
                        'order_id' => $order->id,
                        'product_id' => $orderItem->product_id,
                        'product_pricing_id' => $orderItem->product_pricing_id,
                        'billing_cycle_id' => $billingCycle->id ?? null,
                        'domain_name' => $orderItem->domain_name,
                        'status' => 'pending_configuration', // The new status
                        'registration_date' => $registrationDate->toDateString(),
                        'next_due_date' => $nextDueDate->toDateString(),
                        'billing_amount' => $orderItem->unit_price, // Assuming unit_price is the recurring amount
                        'notes' => "Servicio auto-creado desde Pedido #" . $order->order_number,
                    ]);
                    $newClientService->save();

                    // Update the order item with the new client_service_id
                    $orderItem->client_service_id = $newClientService->id;
                    $orderItem->save();

                    // Create an OrderActivity record
                    OrderActivity::create([
                        'order_id' => $order->id,
                        'user_id' => null, // System action
                        'type' => 'service_shell_auto_created',
                        'details' => json_encode([
                            'client_service_id' => $newClientService->id,
                            'order_item_id' => $orderItem->id,
                            'product_name' => $orderItem->product->name, // Assumes product relationship is loaded
                            'domain' => $orderItem->domain_name,
                        ]),
                    ]);

                    Log::info("OrderObserver: ClientService ID {$newClientService->id} created for OrderItem ID {$orderItem->id} (Order ID {$order->id}). Status set to pending_configuration.");
                }
            }
        }
    }
}

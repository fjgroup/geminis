<?php

namespace App\Observers;

use App\Models\ClientService;
use App\Models\Order;
use App\Models\OrderActivity;
use Illuminate\Support\Facades\Log; // For logging
use Illuminate\Support\Facades\Auth; // To potentially get authenticated user for OrderActivity

class ClientServiceObserver
{
    /**
     * Handle the ClientService "updating" event.
     *
     * @param  \App\Models\ClientService  $clientService
     * @return void
     */
    public function updating(ClientService $clientService)
    {
        // Check if the status attribute is being changed to 'active' and was not 'active' before.
        if ($clientService->isDirty('status') && $clientService->status === 'active' && $clientService->getOriginal('status') !== 'active') {
            // If an order_id is associated with this client service
            if ($clientService->order_id) {
                $order = Order::find($clientService->order_id);

                // If an Order exists and its status is not already 'active' or 'completed'
                if ($order && !in_array($order->status, ['active', 'completed'])) {
                    $original_order_status = $order->status;

                    // Update the Order's status to 'active'.
                    $order->status = 'active';
                    $order->save();

                    // Create an OrderActivity record.
                    // Regarding user_id: If this is always a system-driven change due to service activation,
                    // null is appropriate. If an admin manually activates a service, Auth::id() might be considered,
                    // but the trigger here is the service model update, so null seems more consistent for automation.
                    OrderActivity::create([
                        'order_id' => $order->id,
                        'user_id' => null, // System action as it's a consequence of service activation.
                        'type' => 'order_auto_activated_post_service_config',
                        'details' => json_encode([
                            'client_service_id' => $clientService->id,
                            'previous_order_status' => $original_order_status,
                            'new_order_status' => 'active',
                            'service_domain' => $clientService->domain_name,
                        ]),
                    ]);

                    Log::info("ClientServiceObserver: Order ID {$order->id} status updated to 'active' due to ClientService ID {$clientService->id} activation.");
                } elseif ($order) {
                    Log::info("ClientServiceObserver: Order ID {$order->id} not updated. Current status: {$order->status}. (ClientService ID {$clientService->id})");
                } else {
                    Log::warning("ClientServiceObserver: Order not found with ID {$clientService->order_id} for ClientService ID {$clientService->id}.");
                }
            }
        }
    }
}

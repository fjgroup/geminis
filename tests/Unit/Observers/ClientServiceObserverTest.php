<?php

namespace Tests\Unit\Observers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\ClientService;
use App\Models\OrderActivity;
use App\Models\Product; // For ClientService factory if it needs it indirectly
use App\Models\ProductPricing; // For ClientService factory
use App\Models\BillingCycle; // For ClientService factory
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClientServiceObserverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Observers are typically registered by AppServiceProvider
    }

    /** @test */
    public function order_status_updates_to_active_when_client_service_becomes_active()
    {
        // Arrange
        $client = User::factory()->create();
        $order = Order::factory()->for($client)->create(['status' => 'pending_provisioning']);
        // ClientService factory by default might create its own order, product etc.
        // So we override to link it to our specific order.
        $clientService = ClientService::factory()->create([
            'client_id' => $client->id,
            'order_id' => $order->id,
            'status' => 'pending_configuration',
        ]);

        $originalOrderStatus = $order->status;

        // Act
        $clientService->status = 'active';
        $clientService->save(); // Triggers ClientServiceObserver@updating

        // Assert
        $order->refresh();
        $this->assertEquals('active', $order->status);

        $activity = OrderActivity::where('order_id', $order->id)
            ->where('type', 'order_auto_activated_post_service_config')
            ->latest('id')
            ->first();

        $this->assertNotNull($activity, "OrderActivity for auto-activation was not logged.");
        $details = json_decode($activity->details, true);
        $this->assertEquals($clientService->id, $details['client_service_id']);
        $this->assertEquals($originalOrderStatus, $details['previous_order_status']);
        $this->assertEquals('active', $details['new_order_status']);
        $this->assertEquals($clientService->domain_name, $details['service_domain'] ?? null);
    }

    /** @test */
    public function order_status_does_not_change_if_order_is_already_completed()
    {
        // Arrange
        $client = User::factory()->create();
        $order = Order::factory()->for($client)->create(['status' => 'completed']);
        $clientService = ClientService::factory()->create([
            'client_id' => $client->id,
            'order_id' => $order->id,
            'status' => 'pending_configuration',
        ]);

        // Act
        $clientService->status = 'active';
        $clientService->save();

        // Assert
        $order->refresh();
        $this->assertEquals('completed', $order->status, "Order status should remain completed.");

        $activity = OrderActivity::where('order_id', $order->id)
            ->where('type', 'order_auto_activated_post_service_config')
            ->first();
        $this->assertNull($activity, "No new auto-activation activity should be logged.");
    }

    /** @test */
    public function order_status_does_not_change_if_order_is_already_active()
    {
        // Arrange
        $client = User::factory()->create();
        $order = Order::factory()->for($client)->create(['status' => 'active']); // Already active
        $clientService = ClientService::factory()->create([
            'client_id' => $client->id,
            'order_id' => $order->id,
            'status' => 'pending_configuration',
        ]);

        // Act
        $clientService->status = 'active'; // Service becomes active
        $clientService->save();

        // Assert
        $order->refresh();
        $this->assertEquals('active', $order->status, "Order status should remain active.");

        $activity = OrderActivity::where('order_id', $order->id)
            ->where('type', 'order_auto_activated_post_service_config')
            ->first();
        // The observer logic is: if ($order && !in_array($order->status, ['active', 'completed']))
        // So if order is already active, it won't log. This is correct.
        $this->assertNull($activity, "No new auto-activation activity should be logged if order already active.");
    }


    /** @test */
    public function order_status_does_not_change_if_client_service_has_no_order_id()
    {
        // Arrange
        $clientService = ClientService::factory()->create([
            'order_id' => null, // No associated order
            'status' => 'pending_configuration',
        ]);

        // Act
        $clientService->status = 'active';
        $clientService->save();

        // Assert
        // Primarily assert no error occurs and no OrderActivity is mistakenly created.
        $activity = OrderActivity::where('type', 'order_auto_activated_post_service_config')
            // A bit tricky to query for a null order_id in details if it's not set.
            // So, just check if any such activity was created recently tied to this service.
            ->whereJsonContains('details->client_service_id', $clientService->id)
            ->first();
        $this->assertNull($activity, "No OrderActivity should be logged for a service without an order.");
        // And check that no orders were accidentally updated (there shouldn't be any to update)
        $this->assertEquals(0, Order::where('status', 'active')->count());
    }

    /** @test */
    public function observer_does_not_act_if_client_service_status_changes_but_not_to_active()
    {
        // Arrange
        $client = User::factory()->create();
        $order = Order::factory()->for($client)->create(['status' => 'pending_provisioning']);
        $clientService = ClientService::factory()->create([
            'client_id' => $client->id,
            'order_id' => $order->id,
            'status' => 'pending_configuration',
        ]);

        // Act
        $clientService->status = 'suspended'; // Change to something other than 'active'
        $clientService->save();

        // Assert
        $order->refresh();
        $this->assertEquals('pending_provisioning', $order->status, "Order status should not change.");

        $activity = OrderActivity::where('order_id', $order->id)
            ->where('type', 'order_auto_activated_post_service_config')
            ->first();
        $this->assertNull($activity, "No auto-activation activity should be logged.");
    }

    /** @test */
    public function observer_does_not_act_if_client_service_is_resaved_as_active()
    {
        // Arrange
        $client = User::factory()->create();
        $order = Order::factory()->for($client)->create(['status' => 'pending_provisioning']);
        $clientService = ClientService::factory()->create([
            'client_id' => $client->id,
            'order_id' => $order->id,
            'status' => 'pending_configuration',
        ]);

        // First activation
        $clientService->status = 'active';
        $clientService->save();
        $order->refresh();
        $this->assertEquals('active', $order->status, "Order should be active after first save.");
        $this->assertEquals(1, OrderActivity::where('type', 'order_auto_activated_post_service_config')->count());

        // Act
        // Save the ClientService again, still 'active'
        $clientService->notes = 'Updated service notes.';
        $clientService->save();

        // Assert
        $order->refresh();
        $this->assertEquals('active', $order->status, "Order status should remain active.");

        $activities = OrderActivity::where('order_id', $order->id)
            ->where('type', 'order_auto_activated_post_service_config')
            ->get();
        $this->assertCount(1, $activities, "Only one auto-activation activity should exist.");
    }
}

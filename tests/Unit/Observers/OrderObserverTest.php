<?php

namespace Tests\Unit\Observers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderActivity;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\ProductPricing;
use App\Models\BillingCycle;
use App\Models\ClientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class OrderObserverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Observers are typically registered by AppServiceProvider, so no manual registration needed here usually.
    }

    /** @test */
    public function service_is_created_when_order_becomes_pending_provisioning_for_relevant_product_type()
    {
        // Arrange
        $client = User::factory()->create();
        $billingCycle = BillingCycle::factory()->create(['type' => 'month', 'multiplier' => 1]); // e.g., Monthly
        $productType = ProductType::factory()->create(['creates_service_instance' => true]);
        $product = Product::factory()->for($productType)->create();
        $productPricing = ProductPricing::factory()->for($product)->for($billingCycle)->create();

        $order = Order::factory()->for($client)->create(['status' => 'pending_payment']);
        $orderItem = OrderItem::factory()
            ->for($order)
            ->for($product)
            ->for($productPricing)
            ->create(['client_service_id' => null]);

        // Act
        $order->status = 'pending_provisioning';
        $order->save(); // Triggers OrderObserver@updating

        // Assert
        $orderItem->refresh();
        $this->assertNotNull($orderItem->client_service_id, "Client service ID should be populated.");

        $clientService = ClientService::find($orderItem->client_service_id);
        $this->assertNotNull($clientService, "ClientService record should be created.");

        $this->assertEquals('pending_configuration', $clientService->status);
        $this->assertEquals($orderItem->product_id, $clientService->product_id);
        $this->assertEquals($order->id, $clientService->order_id);
        $this->assertEquals($order->client_id, $clientService->client_id);
        $this->assertEquals(Carbon::now()->toDateString(), $clientService->registration_date);

        $expectedNextDueDate = Carbon::now()->addMonthsNoOverflow($billingCycle->multiplier)->toDateString();
        $this->assertEquals($expectedNextDueDate, $clientService->next_due_date);

        $activity = OrderActivity::where('order_id', $order->id)
            ->where('type', 'service_shell_auto_created')
            ->whereJsonContains('details->order_item_id', $orderItem->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($activity, "OrderActivity for service shell creation was not logged.");
        $details = json_decode($activity->details, true);
        $this->assertEquals($clientService->id, $details['client_service_id']);
        $this->assertEquals($product->name, $details['product_name']);
    }

    /** @test */
    public function service_is_not_created_when_product_type_does_not_require_it()
    {
        // Arrange
        $client = User::factory()->create();
        $billingCycle = BillingCycle::factory()->create();
        $productType = ProductType::factory()->create(['creates_service_instance' => false]); // Key difference
        $product = Product::factory()->for($productType)->create();
        $productPricing = ProductPricing::factory()->for($product)->for($billingCycle)->create();

        $order = Order::factory()->for($client)->create(['status' => 'pending_payment']);
        $orderItem = OrderItem::factory()
            ->for($order)
            ->for($product)
            ->for($productPricing)
            ->create(['client_service_id' => null]);

        // Act
        $order->status = 'pending_provisioning';
        $order->save();

        // Assert
        $orderItem->refresh();
        $this->assertNull($orderItem->client_service_id, "Client service ID should remain null.");

        $this->assertEquals(0, ClientService::where('order_id', $order->id)->where('product_id', $product->id)->count(), "No ClientService should be created.");

        $activity = OrderActivity::where('order_id', $order->id)
            ->where('type', 'service_shell_auto_created')
            ->whereJsonContains('details->order_item_id', $orderItem->id)
            ->first();
        $this->assertNull($activity, "OrderActivity for service shell creation should not be logged.");
    }

    /** @test */
    public function service_is_not_created_if_already_linked_to_order_item()
    {
        // Arrange
        $client = User::factory()->create();
        $billingCycle = BillingCycle::factory()->create();
        $productType = ProductType::factory()->create(['creates_service_instance' => true]);
        $product = Product::factory()->for($productType)->create();
        $productPricing = ProductPricing::factory()->for($product)->for($billingCycle)->create();

        $order = Order::factory()->for($client)->create(['status' => 'pending_payment']);

        $existingClientService = ClientService::factory()->create([
            'client_id' => $client->id,
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_pricing_id' => $productPricing->id,
        ]);
        $orderItem = OrderItem::factory()
            ->for($order)
            ->for($product)
            ->for($productPricing)
            ->create(['client_service_id' => $existingClientService->id]); // Pre-linked

        // Act
        $order->status = 'pending_provisioning';
        $order->save();

        // Assert
        $orderItem->refresh();
        $this->assertEquals($existingClientService->id, $orderItem->client_service_id, "Client service ID should not change.");

        $serviceCount = ClientService::where('order_id', $order->id)->where('product_id', $product->id)->count();
        $this->assertEquals(1, $serviceCount, "No new ClientService should be created; count should remain 1.");

        $activity = OrderActivity::where('order_id', $order->id)
            ->where('type', 'service_shell_auto_created')
            ->whereJsonContains('details->client_service_id', $existingClientService->id + 1) // Check for a *new* service ID
            ->first();
        $this->assertNull($activity, "No new OrderActivity for service shell creation should be logged.");
    }

    /** @test */
    public function observer_handles_order_with_multiple_items_correctly()
    {
        // Arrange
        $client = User::factory()->create();
        $order = Order::factory()->for($client)->create(['status' => 'pending_payment']);

        // Item 1: Requires service instance
        $billingCycle1 = BillingCycle::factory()->create();
        $productType1 = ProductType::factory()->create(['creates_service_instance' => true]);
        $product1 = Product::factory()->for($productType1)->create();
        $productPricing1 = ProductPricing::factory()->for($product1)->for($billingCycle1)->create();
        $orderItem1 = OrderItem::factory()
            ->for($order)
            ->for($product1)
            ->for($productPricing1)
            ->create(['client_service_id' => null]);

        // Item 2: Does NOT require service instance
        $billingCycle2 = BillingCycle::factory()->create();
        $productType2 = ProductType::factory()->create(['creates_service_instance' => false]);
        $product2 = Product::factory()->for($productType2)->create();
        $productPricing2 = ProductPricing::factory()->for($product2)->for($billingCycle2)->create();
        $orderItem2 = OrderItem::factory()
            ->for($order)
            ->for($product2)
            ->for($productPricing2)
            ->create(['client_service_id' => null]);

        // Act
        $order->status = 'pending_provisioning';
        $order->save();

        // Assert for Item 1
        $orderItem1->refresh();
        $this->assertNotNull($orderItem1->client_service_id, "ClientService ID should be set for item 1.");
        $clientService1 = ClientService::find($orderItem1->client_service_id);
        $this->assertNotNull($clientService1);
        $this->assertEquals('pending_configuration', $clientService1->status);
        $activity1 = OrderActivity::where('order_id', $order->id)
            ->where('type', 'service_shell_auto_created')
            ->whereJsonContains('details->order_item_id', $orderItem1->id)
            ->first();
        $this->assertNotNull($activity1, "Activity for item 1 not logged.");

        // Assert for Item 2
        $orderItem2->refresh();
        $this->assertNull($orderItem2->client_service_id, "ClientService ID should remain null for item 2.");
        $activity2 = OrderActivity::where('order_id', $order->id)
            ->where('type', 'service_shell_auto_created')
            ->whereJsonContains('details->order_item_id', $orderItem2->id)
            ->first();
        $this->assertNull($activity2, "Activity for item 2 should not be logged.");
    }

    /** @test */
    public function observer_does_not_act_if_order_status_changes_but_not_to_pending_provisioning()
    {
        // Arrange
        $client = User::factory()->create();
        $billingCycle = BillingCycle::factory()->create();
        $productType = ProductType::factory()->create(['creates_service_instance' => true]);
        $product = Product::factory()->for($productType)->create();
        $productPricing = ProductPricing::factory()->for($product)->for($billingCycle)->create();

        $order = Order::factory()->for($client)->create(['status' => 'pending_payment']);
        $orderItem = OrderItem::factory()
            ->for($order)
            ->for($product)
            ->for($productPricing)
            ->create(['client_service_id' => null]);

        // Act
        $order->status = 'active'; // Any status other than 'pending_provisioning'
        $order->save();

        // Assert
        $orderItem->refresh();
        $this->assertNull($orderItem->client_service_id);
        $this->assertEquals(0, ClientService::count());
        $this->assertEquals(0, OrderActivity::where('type', 'service_shell_auto_created')->count());
    }

    /** @test */
    public function observer_does_not_create_service_if_order_is_saved_again_in_pending_provisioning_status()
    {
        // Arrange
        $client = User::factory()->create();
        $billingCycle = BillingCycle::factory()->create();
        $productType = ProductType::factory()->create(['creates_service_instance' => true]);
        $product = Product::factory()->for($productType)->create();
        $productPricing = ProductPricing::factory()->for($product)->for($billingCycle)->create();
        $order = Order::factory()->for($client)->create(['status' => 'pending_payment']);
        $orderItem = OrderItem::factory()->for($order)->for($product)->for($productPricing)->create();

        // First transition to pending_provisioning
        $order->status = 'pending_provisioning';
        $order->save();

        $orderItem->refresh();
        $firstClientServiceId = $orderItem->client_service_id;
        $this->assertNotNull($firstClientServiceId);
        $this->assertEquals(1, ClientService::count());
        $this->assertEquals(1, OrderActivity::where('type', 'service_shell_auto_created')->count());

        // Act: Save the order again while it's still 'pending_provisioning'
        // (e.g., admin adds a note to the order, observer's "updating" will run)
        $order->notes = 'Admin added a note.';
        $order->save();

        // Assert
        $orderItem->refresh();
        $this->assertEquals($firstClientServiceId, $orderItem->client_service_id, "ClientService ID should not change.");
        $this->assertEquals(1, ClientService::count(), "No new ClientService should be created.");
        $this->assertEquals(1, OrderActivity::where('type', 'service_shell_auto_created')->count(), "No new OrderActivity should be logged.");
    }
}

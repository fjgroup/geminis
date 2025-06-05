<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\BillingCycle;
use App\Models\ProductPricing;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\OrderItem;
use App\Models\ClientService;
use App\Models\OrderActivity;
use App\Models\Transaction;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Event;
use App\Jobs\ProvisionClientServiceJob;

class EndToEndOrderProvisioningTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_full_order_to_service_activation_flow()
    {
        // 0. Ensure events are listened to (usually true by default in tests unless explicitly disabled)
        // Event::fake(); // If you want to assert events, but here we check outcomes.

        // 1. Arrange - User and Product Setup
        $client = User::factory()->create(['role' => 'client']);
        $billingCycle = BillingCycle::factory()->create(['type' => 'month', 'multiplier' => 1]);
        $productType = ProductType::factory()->create(['creates_service_instance' => true, 'requires_domain' => true]);
        $product = Product::factory()->for($productType)->create();
        $productPricing = ProductPricing::factory()->for($product)->for($billingCycle)->create();

        // 2. Act - Client Places Order
        $this->actingAs($client);

        $orderData = [
            'billing_cycle_id' => $productPricing->id, // This should be product_pricing_id based on PlaceOrderRequest
            'quantity' => 1,
            'domainNames' => ['testdomain.com'],
            // Configurable options would go here if the product had them
        ];

        // Assuming PlaceOrderRequest expects 'billing_cycle_id' as the ID of ProductPricing
        // If it expects billing_cycle_id (from BillingCycle model), adjust accordingly.
        // Based on previous subtasks, PlaceOrderRequest uses $request->input('billing_cycle_id') which is ProductPricing ID.

        $response = $this->post(route('client.order.placeOrder', ['product' => $product->id]), $orderData);

        // 3. Assert - Order and Invoice Creation
        $this->assertDatabaseCount('orders', 1);
        $order = Order::latest('id')->first();
        $response->assertRedirect(route('client.orders.show', $order->id)); // Or wherever it redirects

        $this->assertDatabaseCount('invoices', 1);
        $invoice = Invoice::latest('id')->first();

        $this->assertEquals('pending_payment', $order->status);
        $this->assertEquals('unpaid', $invoice->status);
        $this->assertEquals($invoice->id, $order->invoice_id);
        $this->assertEquals($productPricing->price * $orderData['quantity'], $order->total_amount);

        // 4. Arrange - Simulate Payment (Admin Confirmation of Manual Payment)
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $paymentMethod = PaymentMethod::firstOrCreate(
            ['slug' => 'manual_transfer'],
            ['name' => 'Manual Bank Transfer', 'type' => 'offline', 'is_active' => true]
        );

        $pendingTransaction = Transaction::create([
            'invoice_id' => $invoice->id,
            'client_id' => $client->id,
            'payment_method_id' => $paymentMethod->id,
            'gateway_slug' => $paymentMethod->slug,
            'type' => 'payment',
            'amount' => $invoice->total_amount,
            'currency_code' => $invoice->currency_code,
            'status' => 'pending',
            'transaction_date' => now(),
            'description' => 'Test manual payment confirmation'
        ]);

        // 5. Act - Admin Confirms Payment
        $response = $this->post(route('admin.transactions.confirm', $pendingTransaction->id));

        // 6. Assert - Payment Confirmation and Order Status Update (by InvoiceObserver)
        $response->assertRedirect(route('admin.transactions.index')); // Or specific transaction show page
        $response->assertSessionHas('success');

        $invoice->refresh();
        $order->refresh();

        $this->assertEquals('paid', $invoice->status);
        $this->assertEquals('pending_provisioning', $order->status, "Order status should be pending_provisioning after invoice is paid.");

        $this->assertDatabaseHas('order_activities', [
            'order_id' => $order->id,
            'type' => 'order_status_auto_updated_to_pending_provisioning'
        ]);

        // 7. Act & Assert - Job Dispatch and Execution (by OrderObserver and then manually for test)
        Queue::fake();

        // The OrderObserver should have dispatched the job when $order->status became 'pending_provisioning'.
        // We need to ensure this happened.
        // The $order->save() inside InvoiceObserver that changes order status would trigger OrderObserver.

        $orderItem = $order->items()->first(); // Assuming one item for simplicity
        $this->assertNotNull($orderItem, "Order should have items.");

        Queue::assertPushed(ProvisionClientServiceJob::class, function ($job) use ($orderItem) {
            return $job->orderItem->id === $orderItem->id;
        });

        // Execute the job manually
        // Need to load relations the job expects if they weren't loaded when $orderItem was passed to constructor
        // The job now reloads the OrderItem with necessary relations.
        $jobInstance = new ProvisionClientServiceJob($orderItem);
        $jobInstance->handle();

        // 8. Assert - Service Activation
        $orderItem->refresh();
        $order->refresh(); // Refresh order again as ClientServiceObserver might have changed its status

        $this->assertNotNull($orderItem->client_service_id, "ClientService ID on OrderItem should be populated by the job.");
        $clientService = ClientService::find($orderItem->client_service_id);

        $this->assertNotNull($clientService, "ClientService should have been created by the job.");
        $this->assertEquals('active', $clientService->status, "ClientService status should be active after job execution.");
        $this->assertNotEmpty($clientService->username);
        $this->assertNotEmpty($clientService->password_encrypted);

        // Assert Order status updated by ClientServiceObserver
        $this->assertEquals('active', $order->status, "Order status should be active after service activation.");

        // Assert OrderActivity for provisioning queued (by OrderObserver)
        $this->assertDatabaseHas('order_activities', [
            'order_id' => $order->id,
            'type' => 'service_provisioning_queued',
            // 'details' => json_encode(['order_item_id' => $orderItem->id, ...]) // More specific if needed
        ]);

        // Assert OrderActivity for order auto-activation (by ClientServiceObserver)
        $this->assertDatabaseHas('order_activities', [
            'order_id' => $order->id,
            'type' => 'order_auto_activated_post_service_config',
            // 'details' => json_encode(['client_service_id' => $clientService->id, ...]) // More specific if needed
        ]);

        // 9. (Optional) Act - Client Views Service
        $this->actingAs($client);
        $response = $this->get(route('client.services.index'));
        $response->assertStatus(200);
        $response->assertSeeText('Activo'); // Friendly status for 'active'
        if ($clientService->domain_name) {
            $response->assertSeeText($clientService->domain_name);
        }
    }
}

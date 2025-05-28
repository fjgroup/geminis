<?php

namespace Tests\Feature\Client;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\BillingCycle;
use App\Models\OrderItem; // Import OrderItem
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

class ViewOrdersPageTest extends TestCase
{
    use RefreshDatabase;

    private User $client;
    private ProductPricing $productPricing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = User::factory()->create([
            'role' => 'client',
            'password' => Hash::make('password'),
        ]);

        $product = Product::factory()->create();
        $billingCycle = BillingCycle::factory()->create();
        $this->productPricing = ProductPricing::factory()->create([
            'product_id' => $product->id,
            'billing_cycle_id' => $billingCycle->id,
        ]);
    }

    private function createOrderWithInvoice(array $orderAttributes, array $invoiceAttributes = []): Order
    {
        $order = Order::factory()->create(array_merge([
            'client_id' => $this->client->id,
            'product_pricing_id' => $this->productPricing->id,
            'billing_cycle_id' => $this->productPricing->billing_cycle_id,
            'total_amount' => $this->productPricing->price,
            'currency_code' => $this->productPricing->currency_code,
        ], $orderAttributes));

        // Create OrderItem for the order
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $this->productPricing->product_id,
            'product_pricing_id' => $this->productPricing->id,
            'item_type' => 'product',
            'description' => $this->productPricing->product->name,
            'quantity' => 1,
            'unit_price' => $this->productPricing->price,
            'total_price' => $this->productPricing->price,
        ]);
        
        $invoice = Invoice::factory()->create(array_merge([
            'client_id' => $this->client->id,
            'order_id' => $order->id,
            'total_amount' => $order->total_amount,
            'currency_code' => $order->currency_code,
        ], $invoiceAttributes));

        $order->update(['invoice_id' => $invoice->id]);
        return $order->fresh(); // Re-fetch to get updated relations
    }

    /** @test */
    public function client_can_view_their_orders_with_various_statuses()
    {
        $orderPendingPayment = $this->createOrderWithInvoice(
            ['status' => 'pending_payment', 'order_date' => Carbon::now()->subDays(5)],
            ['status' => 'unpaid']
        );
        $orderPaidPendingExecution = $this->createOrderWithInvoice(
            ['status' => 'paid_pending_execution', 'order_date' => Carbon::now()->subDays(4)],
            ['status' => 'paid']
        );
        $orderPendingProvisioning = $this->createOrderWithInvoice(
            ['status' => 'pending_provisioning', 'order_date' => Carbon::now()->subDays(3)],
            ['status' => 'paid']
        );
        $orderActive = $this->createOrderWithInvoice(
            ['status' => 'active', 'order_date' => Carbon::now()->subDays(2)],
            ['status' => 'paid']
        );
        $orderCancelled = $this->createOrderWithInvoice(
            ['status' => 'cancelled', 'order_date' => Carbon::now()->subDays(1)],
            ['status' => 'cancelled']
        );
        $orderCancellationRequested = $this->createOrderWithInvoice(
            ['status' => 'cancellation_requested_by_client', 'order_date' => Carbon::now()],
            ['status' => 'paid']
        );
        
        // Create an order for another client to ensure it's not visible
        $otherClient = User::factory()->create(['role' => 'client']);
        Order::factory()->create([
            'client_id' => $otherClient->id,
            'product_pricing_id' => $this->productPricing->id,
            'billing_cycle_id' => $this->productPricing->billing_cycle_id,
            'status' => 'active',
        ]);


        $this->actingAs($this->client);

        $response = $this->get(route('client.orders.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Client/Orders/Index')
            ->has('orders.data', 6) // Expecting 6 orders for the logged-in client
            ->where('orders.data.0.status', 'cancellation_requested_by_client') // Most recent
            ->where('orders.data.1.status', 'cancelled')
            ->where('orders.data.2.status', 'active')
            ->where('orders.data.3.status', 'pending_provisioning')
            ->where('orders.data.4.status', 'paid_pending_execution')
            ->where('orders.data.5.status', 'pending_payment')
            // Check if productPricing and billingCycle are loaded
            ->has('orders.data.0.product_pricing.billing_cycle')
            ->has('orders.data.0.items')
        );
    }

    /** @test */
    public function client_sees_empty_state_when_no_orders_exist()
    {
        $this->actingAs($this->client);

        $response = $this->get(route('client.orders.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Client/Orders/Index')
            ->has('orders.data', 0)
        );
    }

    /** @test */
    public function orders_are_paginated_on_my_orders_page()
    {
        // Controller paginates by 10
        for ($i = 0; $i < 12; $i++) {
            $this->createOrderWithInvoice(
                ['status' => 'pending_payment', 'order_date' => Carbon::now()->subMinutes($i)],
                ['status' => 'unpaid']
            );
        }

        $this->actingAs($this->client);
        $response = $this->get(route('client.orders.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Client/Orders/Index')
            ->has('orders.data', 10)
            ->has('orders.links')
            ->where('orders.total', 12)
            ->where('orders.current_page', 1)
        );
    }
}

<?php

namespace Tests\Feature\Client;

use App\Models\User;
use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\OrderActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

class ClientOrderPlacementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $client;
    private Product $product;
    private ProductPricing $productPricing;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a client user
        $this->client = User::factory()->create(['role' => 'client']); // Assuming role is set on User model or factory

        // Create a product
        $this->product = Product::factory()->create();

        // Create product pricing
        $this->productPricing = ProductPricing::factory()->for($this->product)->create([
            'price' => 100.00,
            'currency_code' => 'USD',
            // billing_cycle_id is usually handled by its own factory or set directly
        ]);
    }

    public function test_client_can_view_order_form(): void
    {
        $this->actingAs($this->client);

        $response = $this->get(route('client.order.showOrderForm', ['product' => $this->product->id]));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Client/Orders/OrderForm')
            ->has('product', fn (Assert $prop) => $prop
                ->where('id', $this->product->id)
                ->etc()
            )
        );
    }

    public function test_client_can_place_order_and_invoice_is_generated(): void
    {
        $this->actingAs($this->client);

        $orderData = [
            'billing_cycle_id' => $this->productPricing->id, // This is actually product_pricing_id
            'quantity' => 2,
            'notes_to_client' => 'Test order notes',
        ];

        $response = $this->post(route('client.order.placeOrder', ['product' => $this->product->id]), $orderData);
        
        $this->assertDatabaseHas('orders', [
            'client_id' => $this->client->id,
            'product_id' => null, // product_id is on order_items, not orders table
            'status' => 'pending_payment',
            'total_amount' => $this->productPricing->price * $orderData['quantity'],
            'currency_code' => $this->productPricing->currency_code,
            'notes_to_client' => $orderData['notes_to_client'],
        ]);

        $createdOrder = Order::where('client_id', $this->client->id)->latest()->first();
        $this->assertNotNull($createdOrder);
        
        $response->assertRedirect(route('client.orders.show', ['order' => $createdOrder->id]));


        $this->assertDatabaseHas('order_items', [
            'order_id' => $createdOrder->id,
            'product_id' => $this->product->id,
            'product_pricing_id' => $this->productPricing->id,
            'quantity' => $orderData['quantity'],
            'unit_price' => $this->productPricing->price,
            'total_price' => $this->productPricing->price * $orderData['quantity'],
        ]);

        $this->assertDatabaseHas('invoices', [
            'client_id' => $this->client->id,
            'order_id' => null, // invoice_id is on orders table
            'status' => 'unpaid',
            'total_amount' => $createdOrder->total_amount,
            'currency_code' => $createdOrder->currency_code,
        ]);
        
        $createdInvoice = Invoice::where('client_id', $this->client->id)->latest()->first();
        $this->assertNotNull($createdInvoice);
        $this->assertEquals($createdOrder->invoice_id, $createdInvoice->id);


        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $createdInvoice->id,
            // 'product_id' => $this->product->id, // product_id is not directly on invoice_items
            'quantity' => $orderData['quantity'],
            'unit_price' => $this->productPricing->price,
            'total_price' => $this->productPricing->price * $orderData['quantity'],
        ]);

        $this->assertDatabaseHas('order_activities', [
            'order_id' => $createdOrder->id,
            'user_id' => $this->client->id,
            'type' => 'order_placed',
        ]);
    }
}

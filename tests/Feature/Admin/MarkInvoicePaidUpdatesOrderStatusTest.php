<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Transaction;
use App\Models\OrderActivity;
use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\BillingCycle;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MarkInvoicePaidUpdatesOrderStatusTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'admin',
            'password' => Hash::make('password'),
        ], $attributes));
    }

    private function createClient(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'client',
            'password' => Hash::make('password'),
        ], $attributes));
    }

    private function createProductAndPricing(array $productAttributes = [], array $pricingAttributes = []): ProductPricing
    {
        $product = Product::factory()->create($productAttributes);
        $billingCycle = BillingCycle::factory()->create(); // Ensure this exists
        return ProductPricing::factory()->create(array_merge([
            'product_id' => $product->id,
            'billing_cycle_id' => $billingCycle->id,
            'currency_code' => 'USD',
            'price' => 100.00, // Default price
        ], $pricingAttributes));
    }

    private function createOrder(User $client, ProductPricing $productPricing, array $attributes = []): Order
    {
        $order = Order::factory()->create(array_merge([
            'client_id' => $client->id,
            'product_pricing_id' => $productPricing->id,
            'billing_cycle_id' => $productPricing->billing_cycle_id,
            'status' => 'pending_payment', // Default status
            'total_amount' => $productPricing->price,
            'currency_code' => $productPricing->currency_code,
            'order_date' => Carbon::now(),
            'order_number' => 'ORD-' . strtoupper(Str::random(10)),
        ], $attributes));

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $productPricing->product_id,
            'product_pricing_id' => $productPricing->id,
            'item_type' => 'product',
            'description' => $productPricing->product->name,
            'quantity' => 1,
            'unit_price' => $productPricing->price,
            'total_price' => $productPricing->price,
            'billing_cycle_id' => $productPricing->billing_cycle_id,
        ]);
        return $order;
    }

    private function createInvoice(Order $order, array $attributes = []): Invoice
    {
        $invoice = Invoice::factory()->create(array_merge([
            'client_id' => $order->client_id,
            'order_id' => $order->id,
            'status' => 'unpaid', // Default status
            'total_amount' => $order->total_amount,
            'currency_code' => $order->currency_code,
            'issue_date' => Carbon::now(),
            'due_date' => Carbon::now()->addDays(15),
            'invoice_number' => 'INV-' . strtoupper(Str::random(10)),
        ], $attributes));

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'order_item_id' => $order->items->first()->id,
            'description' => $order->items->first()->description,
            'quantity' => 1,
            'unit_price' => $order->items->first()->unit_price,
            'total_price' => $order->items->first()->total_price,
        ]);
        
        // Link invoice to order if not already linked by factory
        $order->update(['invoice_id' => $invoice->id]);

        return $invoice;
    }

    /** @test */
    public function admin_marking_invoice_paid_updates_order_status()
    {
        $admin = $this->createAdmin();
        $client = $this->createClient();
        $productPricing = $this->createProductAndPricing(['price' => 100.00]);
        $order = $this->createOrder($client, $productPricing, ['status' => 'pending_payment', 'total_amount' => 100.00]);
        $invoice = $this->createInvoice($order, ['status' => 'unpaid', 'total_amount' => 100.00]);

        $this->actingAs($admin);

        $transactionData = [
            'amount' => 100.00,
            'transaction_date' => Carbon::now()->toDateString(),
            'gateway_slug' => 'manual_payment',
            'type' => 'payment',
            'status' => 'completed',
            'description' => 'Manual payment by admin.',
            // 'client_id' and 'reseller_id' are set by controller from invoice
        ];
        
        // The route is POST /admin/invoices/{invoice}/transactions
        $response = $this->post(route('admin.invoices.transactions.store', $invoice), $transactionData);

        $response->assertRedirect(route('admin.invoices.show', $invoice->id));
        $response->assertSessionHas('success', 'Payment registered successfully.');

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'paid_pending_execution',
        ]);

        $this->assertDatabaseHas('order_activities', [
            'order_id' => $order->id,
            'user_id' => $admin->id, // Admin user initiated this
            'type' => 'payment_confirmed_order_pending_execution',
        ]);
    }

    /** @test */
    public function admin_marking_invoice_paid_does_not_update_order_if_order_not_pending_payment()
    {
        $admin = $this->createAdmin();
        $client = $this->createClient();
        $productPricing = $this->createProductAndPricing(['price' => 100.00]);
        $originalOrderStatus = 'active'; // Example: order already active
        $order = $this->createOrder($client, $productPricing, ['status' => $originalOrderStatus, 'total_amount' => 100.00]);
        $invoice = $this->createInvoice($order, ['status' => 'unpaid', 'total_amount' => 100.00]);

        $this->actingAs($admin);

        $transactionData = [
            'amount' => 100.00,
            'transaction_date' => Carbon::now()->toDateString(),
            'gateway_slug' => 'manual_payment',
            'type' => 'payment',
            'status' => 'completed',
            'description' => 'Manual payment by admin for an active order.',
        ];

        $response = $this->post(route('admin.invoices.transactions.store', $invoice), $transactionData);

        $response->assertRedirect(route('admin.invoices.show', $invoice->id));
        $response->assertSessionHas('success'); // Payment is still registered

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => $originalOrderStatus, // Status should NOT change
        ]);

        $this->assertDatabaseMissing('order_activities', [
            'order_id' => $order->id,
            'user_id' => $admin->id,
            'type' => 'payment_confirmed_order_pending_execution',
        ]);
    }

    /** @test */
    public function admin_adding_partial_payment_does_not_update_order_status()
    {
        $admin = $this->createAdmin();
        $client = $this->createClient();
        $productPricing = $this->createProductAndPricing(['price' => 100.00]);
        $order = $this->createOrder($client, $productPricing, ['status' => 'pending_payment', 'total_amount' => 100.00]);
        $invoice = $this->createInvoice($order, ['status' => 'unpaid', 'total_amount' => 100.00]);

        $this->actingAs($admin);

        $transactionData = [
            'amount' => 50.00, // Partial payment
            'transaction_date' => Carbon::now()->toDateString(),
            'gateway_slug' => 'manual_payment',
            'type' => 'payment',
            'status' => 'completed',
            'description' => 'Partial manual payment by admin.',
        ];

        $response = $this->post(route('admin.invoices.transactions.store', $invoice), $transactionData);

        $response->assertRedirect(route('admin.invoices.show', $invoice->id));
        $response->assertSessionHas('success');

        // Based on current Admin/TransactionController logic, invoice status becomes 'paid' 
        // only if netPaid >= total_amount.
        // So, with a partial payment, it should remain 'unpaid' (or 'partially_paid' if such status exists and logic handles it).
        // The current logic would keep it 'unpaid' as there's no 'partially_paid' status.
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'unpaid', 
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'pending_payment', // Should remain unchanged
        ]);

        $this->assertDatabaseMissing('order_activities', [
            'order_id' => $order->id,
            'type' => 'payment_confirmed_order_pending_execution',
        ]);
    }
}

<?php

namespace Tests\Feature\Client;

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

class PayInvoiceWithBalanceTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'client',
            'password' => Hash::make('password'),
        ], $attributes));
    }

    private function createProductAndPricing(array $productAttributes = [], array $pricingAttributes = []): ProductPricing
    {
        $product = Product::factory()->create($productAttributes);
        $billingCycle = BillingCycle::factory()->create();
        return ProductPricing::factory()->create(array_merge([
            'product_id' => $product->id,
            'billing_cycle_id' => $billingCycle->id,
            'currency_code' => 'USD',
            'price' => 50.00,
        ], $pricingAttributes));
    }

    private function createOrder(User $client, ProductPricing $productPricing, array $attributes = []): Order
    {
        $order = Order::factory()->create(array_merge([
            'client_id' => $client->id,
            'product_pricing_id' => $productPricing->id,
            'billing_cycle_id' => $productPricing->billing_cycle_id,
            'status' => 'pending_payment',
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
            'status' => 'unpaid',
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
        
        // Link invoice to order
        $order->update(['invoice_id' => $invoice->id]);

        return $invoice;
    }

    /** @test */
    public function client_can_pay_unpaid_invoice_with_sufficient_balance()
    {
        $client = $this->createUser(['balance' => 100.00]);
        $productPricing = $this->createProductAndPricing(['price' => 50.00]);
        $order = $this->createOrder($client, $productPricing);
        $invoice = $this->createInvoice($order, ['total_amount' => 50.00, 'status' => 'unpaid']);

        $this->actingAs($client);

        $response = $this->post(route('client.invoices.payWithBalance', $invoice));

        $response->assertRedirect(route('client.invoices.show', $invoice));
        $response->assertSessionHas('success', 'Invoice paid successfully using your account balance.');

        $this->assertDatabaseHas('users', [
            'id' => $client->id,
            'balance' => 50.00, // 100 - 50
        ]);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'paid',
        ]);
        $this->assertNotNull($invoice->fresh()->paid_date);

        $this->assertDatabaseHas('transactions', [
            'client_id' => $client->id,
            'invoice_id' => $invoice->id,
            'gateway_slug' => 'balance',
            'type' => 'payment',
            'amount' => 50.00,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'paid_pending_execution',
        ]);

        $this->assertDatabaseHas('order_activities', [
            'order_id' => $order->id,
            'user_id' => $client->id,
            'type' => 'payment_confirmed_order_pending_execution',
        ]);
    }

    /** @test */
    public function client_cannot_pay_invoice_with_insufficient_balance()
    {
        $client = $this->createUser(['balance' => 30.00]);
        $productPricing = $this->createProductAndPricing(['price' => 50.00]);
        // Order creation is not strictly necessary if we only test invoice payment,
        // but creating it and the invoice makes the setup more complete.
        $order = $this->createOrder($client, $productPricing, ['status' => 'pending_payment', 'total_amount' => 50.00]);
        $invoice = $this->createInvoice($order, ['total_amount' => 50.00, 'status' => 'unpaid']);
        
        $initialOrderCount = Transaction::count();

        $this->actingAs($client);

        $response = $this->post(route('client.invoices.payWithBalance', $invoice));

        $response->assertRedirect(route('client.invoices.show', $invoice->id));
        $response->assertSessionHas('error', 'Insufficient balance to pay this invoice.');

        $this->assertDatabaseHas('users', [
            'id' => $client->id,
            'balance' => 30.00, // Unchanged
        ]);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'unpaid', // Unchanged
        ]);
        
        $this->assertEquals($initialOrderCount, Transaction::count(), "No new transaction should be created.");

        $this->assertDatabaseHas('orders', [ // Order status should remain unchanged
            'id' => $order->id,
            'status' => 'pending_payment',
        ]);
    }

    /** @test */
    public function client_cannot_pay_already_paid_invoice_with_balance()
    {
        $client = $this->createUser(['balance' => 100.00]);
        $productPricing = $this->createProductAndPricing(['price' => 50.00]);
        $order = $this->createOrder($client, $productPricing, ['status' => 'paid_pending_execution']);
        $invoice = $this->createInvoice($order, ['total_amount' => 50.00, 'status' => 'paid', 'paid_date' => Carbon::now()]);

        $this->actingAs($client);

        $response = $this->post(route('client.invoices.payWithBalance', $invoice));

        // The policy should prevent this, typically resulting in a 403 or a redirect with error.
        // Based on current InvoicePolicy for payWithBalance, it checks status === 'unpaid'.
        // If policy fails, controller might redirect with error. Let's assume policy leads to 403.
        // If the controller's explicit check kicks in first, it's a redirect.
        // The controller has: if ($invoice->status !== 'unpaid') { return redirect()->route('client.invoices.show', $invoice->id)->with('error', 'This invoice is not awaiting payment.'); }
        // So, it will be a redirect with an error.
        $response->assertRedirect(route('client.invoices.show', $invoice->id));
        $response->assertSessionHas('error', 'This invoice is not awaiting payment.');


        $this->assertDatabaseHas('users', [
            'id' => $client->id,
            'balance' => 100.00, // Unchanged
        ]);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'paid', // Unchanged
        ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_pay_with_balance()
    {
        $client = $this->createUser(['balance' => 100.00]);
        $productPricing = $this->createProductAndPricing();
        $order = $this->createOrder($client, $productPricing);
        $invoice = $this->createInvoice($order);

        $response = $this->post(route('client.invoices.payWithBalance', $invoice));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function client_cannot_pay_another_clients_invoice_with_balance()
    {
        $clientA = $this->createUser(['balance' => 100.00, 'name' => 'Client A']);
        $clientB = $this->createUser(['name' => 'Client B']);
        
        $productPricing = $this->createProductAndPricing();
        $orderB = $this->createOrder($clientB, $productPricing);
        $invoiceB = $this->createInvoice($orderB);

        $this->actingAs($clientA);

        $response = $this->post(route('client.invoices.payWithBalance', $invoiceB));

        // Based on current InvoicePolicy for payWithBalance, it checks $user->id === $invoice->client_id.
        // This should result in a 403 Forbidden response.
        $response->assertStatus(403);

        $this->assertDatabaseHas('users', [ // Client A balance unchanged
            'id' => $clientA->id,
            'balance' => 100.00,
        ]);
        $this->assertDatabaseHas('invoices', [ // Invoice B status unchanged
            'id' => $invoiceB->id,
            'status' => 'unpaid',
        ]);
    }
}

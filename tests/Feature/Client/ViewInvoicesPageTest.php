<?php

namespace Tests\Feature\Client;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\BillingCycle;
use App\Models\InvoiceItem; // Import InvoiceItem
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

class ViewInvoicesPageTest extends TestCase
{
    use RefreshDatabase;

    private User $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = User::factory()->create([
            'role' => 'client',
            'password' => Hash::make('password'),
        ]);
    }

    private function createInvoice(array $invoiceAttributes): Invoice
    {
        // Simplified Order and Product setup for Invoice context
        $product = Product::factory()->create();
        $billingCycle = BillingCycle::factory()->create();
        $productPricing = ProductPricing::factory()->create([
            'product_id' => $product->id,
            'billing_cycle_id' => $billingCycle->id,
        ]);
        $order = Order::factory()->create([
            'client_id' => $this->client->id,
            'product_pricing_id' => $productPricing->id,
            'billing_cycle_id' => $productPricing->billing_cycle_id,
            'total_amount' => $invoiceAttributes['total_amount'] ?? 100.00,
        ]);
        
        $invoice = Invoice::factory()->create(array_merge([
            'client_id' => $this->client->id,
            'order_id' => $order->id, // Link to an order
            'currency_code' => 'USD',
            'issue_date' => Carbon::now()->subDays(10),
            'due_date' => Carbon::now()->addDays(5),
        ], $invoiceAttributes));

        // Add at least one InvoiceItem for the 'items' relationship if needed by controller/view
        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'description' => 'Test Item',
            'quantity' => 1,
            'unit_price' => $invoice->total_amount,
            'total_price' => $invoice->total_amount,
        ]);
        
        return $invoice;
    }

    /** @test */
    public function client_can_view_their_invoices_with_various_statuses_and_correct_totals()
    {
        $invoiceUnpaid = $this->createInvoice(['status' => 'unpaid', 'total_amount' => 50.00, 'invoice_number' => 'INV-001']);
        $invoicePaid = $this->createInvoice(['status' => 'paid', 'total_amount' => 75.50, 'invoice_number' => 'INV-002']);
        $invoiceCancelled = $this->createInvoice(['status' => 'cancelled', 'total_amount' => 30.25, 'invoice_number' => 'INV-003']);
        $invoiceRefunded = $this->createInvoice(['status' => 'refunded', 'total_amount' => 100.00, 'invoice_number' => 'INV-004']);
        $invoiceOverdue = $this->createInvoice(['status' => 'overdue', 'total_amount' => 25.00, 'invoice_number' => 'INV-005', 'due_date' => Carbon::now()->subDay()]);

        // Create an invoice for another client to ensure it's not visible
        $otherClient = User::factory()->create(['role' => 'client']);
        Invoice::factory()->create([
            'client_id' => $otherClient->id,
            'status' => 'paid',
            'total_amount' => 99.00,
        ]);

        $this->actingAs($this->client);

        $response = $this->get(route('client.invoices.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Client/Invoices/Index')
            ->has('invoices.data', 5) // Expecting 5 invoices for the logged-in client
            ->where('invoices.data.0.status', 'overdue') // Assuming default order is by latest, or needs specific ordering in controller
            ->where('invoices.data.0.total_amount', 25.00)
            ->where('invoices.data.1.status', 'refunded')
            ->where('invoices.data.1.total_amount', 100.00)
            ->where('invoices.data.2.status', 'cancelled')
            ->where('invoices.data.2.total_amount', 30.25)
            ->where('invoices.data.3.status', 'paid')
            ->where('invoices.data.3.total_amount', 75.50)
            ->where('invoices.data.4.status', 'unpaid')
            ->where('invoices.data.4.total_amount', 50.00)
            // Check if items relationship is loaded (as per controller `->with('items')`)
            ->has('invoices.data.0.items')
        );
    }

    /** @test */
    public function client_sees_empty_state_when_no_invoices_exist()
    {
        $this->actingAs($this->client);

        $response = $this->get(route('client.invoices.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Client/Invoices/Index')
            ->has('invoices.data', 0)
        );
    }

    /** @test */
    public function invoices_are_paginated_on_my_invoices_page()
    {
        // Controller paginates by 10
        for ($i = 0; $i < 12; $i++) {
            $this->createInvoice(['status' => 'unpaid', 'total_amount' => 10.00 + $i]);
        }

        $this->actingAs($this->client);
        $response = $this->get(route('client.invoices.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Client/Invoices/Index')
            ->has('invoices.data', 10)
            ->has('invoices.links')
            ->where('invoices.total', 12)
            ->where('invoices.current_page', 1)
        );
    }
}

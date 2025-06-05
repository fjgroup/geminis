<?php

namespace Tests\Feature\Client;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\ProductPricing;
use App\Models\BillingCycle;
use App\Models\ClientService;
use App\Models\Transaction;

class PayInvoiceWithBalanceTest extends TestCase
{
    use RefreshDatabase;

    private function createProductAndPricing(string $typeSlug, bool $isDomainProductType = false, float $price = 10.00, bool $createsServiceInstance = true): ProductPricing
    {
        // Asumiendo que ProductTypeFactory puede tomar 'is_domain_product' y 'creates_service_instance'
        $productType = ProductType::factory()->create([
            'slug' => $typeSlug,
            'name' => ucfirst($typeSlug),
            'creates_service_instance' => $createsServiceInstance,
            'is_domain_product' => $isDomainProductType
        ]);
        $product = Product::factory()->create(['product_type_id' => $productType->id, 'name' => ucfirst($typeSlug) . ' Product']);
        $billingCycle = BillingCycle::factory()->create(['type' => 'month', 'multiplier' => 1, 'name' => 'Monthly']);
        return ProductPricing::factory()->create([
            'product_id' => $product->id,
            'billing_cycle_id' => $billingCycle->id,
            'price' => $price,
            'setup_fee' => 0,
            'currency_code' => 'USD',
        ]);
    }

    public function test_client_can_pay_invoice_with_sufficient_balance_activating_non_domain_and_pending_domain(): void
    {
        // 1. Setup
        $client = User::factory()->create(['balance' => 100.00, 'role' => 'client']);

        $hostingPricing = $this->createProductAndPricing('web-hosting', false, 25.00);
        $domainPricing = $this->createProductAndPricing('domain-registration', true, 15.00);

        $invoice = Invoice::factory()->create([
            'client_id' => $client->id,
            'status' => 'unpaid',
            'total_amount' => 40.00,
            'subtotal' => 40.00,
            'currency_code' => 'USD',
            'requested_date' => now(),
        ]);

        // Usar create directamente si InvoiceItemFactory no está configurada para todos los campos nuevos.
        $hostingItem = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_id' => $hostingPricing->product_id,
            'product_pricing_id' => $hostingPricing->id,
            'quantity' => 1,
            'unit_price' => 25.00,
            'total_price' => 25.00,
            'description' => 'Web Hosting Service',
            'item_type' => 'new_service',
            'taxable' => false, // Añadir campos que InvoiceItem espera
        ]);

        $domainItem = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_id' => $domainPricing->product_id,
            'product_pricing_id' => $domainPricing->id,
            'quantity' => 1,
            'unit_price' => 15.00,
            'total_price' => 15.00,
            'description' => 'Domain Registration',
            'item_type' => 'new_service',
            'domain_name' => 'testdomain.com',
            'taxable' => false, // Añadir campos que InvoiceItem espera
        ]);

        // 2. Action
        $this->actingAs($client);
        $response = $this->post(route('client.invoices.payWithBalance', $invoice));

        // 3. Assertions
        $response->assertRedirect(route('client.invoices.show', $invoice->id));
        $response->assertSessionHas('success');

        $this->assertEquals(60.00, $client->fresh()->balance); // 100 - 40

        $this->assertDatabaseHas('transactions', [
            'invoice_id' => $invoice->id,
            'client_id' => $client->id,
            'gateway_slug' => 'balance',
            'amount' => 40.00,
            'status' => 'completed',
        ]);

        $updatedInvoice = $invoice->fresh();
        $this->assertEquals('pending_activation', $updatedInvoice->status); // Porque el dominio queda pendiente
        $this->assertNotNull($updatedInvoice->paid_date);

        // Hosting Service Assertions
        $hostingService = ClientService::where('product_id', $hostingPricing->product_id)->first();
        $this->assertNotNull($hostingService, "Hosting ClientService no fue creado.");
        $this->assertEquals('active', $hostingService->status);
        $this->assertEquals($client->id, $hostingService->client_id);
        $this->assertEquals($hostingService->id, InvoiceItem::find($hostingItem->id)->client_service_id);


        // Domain Service Assertions
        $domainService = ClientService::where('product_id', $domainPricing->product_id)->first();
        $this->assertNotNull($domainService, "Domain ClientService no fue creado.");
        $this->assertEquals('pending', $domainService->status);
        $this->assertEquals($client->id, $domainService->client_id);
        $this->assertEquals('testdomain.com', $domainService->domain_name);
        $this->assertEquals($domainService->id, InvoiceItem::find($domainItem->id)->client_service_id);
    }

    public function test_client_cannot_pay_invoice_with_insufficient_balance(): void
    {
        $client = User::factory()->create(['balance' => 10.00, 'role' => 'client']);
        $invoice = Invoice::factory()->create([
            'client_id' => $client->id,
            'status' => 'unpaid',
            'total_amount' => 50.00,
        ]);

        $this->actingAs($client);
        $response = $this->post(route('client.invoices.payWithBalance', $invoice));

        $response->assertRedirect(route('client.invoices.show', $invoice->id));
        $response->assertSessionHas('error', 'Insufficient balance to pay this invoice.');
        $this->assertEquals(10.00, $client->fresh()->balance);
        $this->assertEquals('unpaid', $invoice->fresh()->status);
    }

    public function test_client_cannot_pay_already_paid_invoice(): void
    {
        $client = User::factory()->create(['balance' => 100.00, 'role' => 'client']);
        $invoice = Invoice::factory()->paid()->create([
            'client_id' => $client->id,
            'total_amount' => 50.00,
        ]);

        $this->actingAs($client);
        $response = $this->post(route('client.invoices.payWithBalance', $invoice));

        $response->assertRedirect(route('client.invoices.show', $invoice->id));
        $response->assertSessionHas('error', 'This invoice is not awaiting payment.');
        $this->assertEquals(100.00, $client->fresh()->balance);
    }
}

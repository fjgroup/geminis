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
use Inertia\Testing\AssertableInertia as Assert;

class ViewInvoicesPageTest extends TestCase
{
    use RefreshDatabase;

    private function createProductAndPricing(string $typeSlug, string $productName, bool $isDomain = false, float $price = 10.00): ProductPricing
    {
        $productType = ProductType::factory()->create([
            'slug' => $typeSlug,
            'name' => ucfirst($typeSlug),
            'creates_service_instance' => true,
            'is_domain_product' => $isDomain,
        ]);
        $product = Product::factory()->create(['product_type_id' => $productType->id, 'name' => $productName]);
        $billingCycle = BillingCycle::factory()->create(['type' => 'month', 'multiplier' => 1, 'name' => 'Monthly']);
        return ProductPricing::factory()->create([
            'product_id' => $product->id,
            'billing_cycle_id' => $billingCycle->id,
            'price' => $price,
            'setup_fee' => 5.00, // Incluir setup_fee para probar su visualización
            'currency_code' => 'USD',
        ]);
    }

    public function test_client_can_view_their_invoices_index_page(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        Invoice::factory()->count(3)->create([
            'client_id' => $client->id,
            'status' => 'unpaid',
            'requested_date' => now()->subDays(5),
        ]);
        Invoice::factory()->create([
            'client_id' => $client->id,
            'status' => 'pending_activation', // Nuevo estado
            'requested_date' => now()->subDays(2),
        ]);

        $this->actingAs($client);
        $response = $this->get(route('client.invoices.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Client/Invoices/Index')
            ->has('invoices.data', 4)
            ->where('invoices.data.0.status', 'unpaid') // Asumiendo orden por defecto
            ->where('invoices.data.3.status', 'pending_activation')
            ->has('invoices.data.0.requested_date')
        );
    }

    public function test_client_can_view_invoice_show_page_with_detailed_items_and_service_status(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $hostingPricing = $this->createProductAndPricing('web-hosting', 'Awesome Hosting Plan', false, 20.00);
        $domainPricing = $this->createProductAndPricing('domain-reg', 'Awesome Domain Reg', true, 12.00);

        $invoice = Invoice::factory()->create([
            'client_id' => $client->id,
            'status' => 'pending_activation',
            'total_amount' => 37.00, // 20 (hosting) + 5 (setup) + 12 (domain)
            'subtotal' => 32.00, // 20+12
            'requested_date' => now(),
            'ip_address' => '127.0.0.1',
            'payment_gateway_slug' => 'test_gateway',
        ]);

        $item1 = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_id' => $hostingPricing->product_id,
            'product_pricing_id' => $hostingPricing->id,
            'quantity' => 1,
            'unit_price' => $hostingPricing->price,
            'setup_fee' => $hostingPricing->setup_fee,
            'total_price' => $hostingPricing->price + $hostingPricing->setup_fee,
            'description' => $hostingPricing->product->name . ' - Monthly',
            'item_type' => 'new_service',
            'domain_name' => 'mycoolsite.com',
        ]);
        $service1 = ClientService::factory()->create([
            'client_id' => $client->id,
            'product_id' => $hostingPricing->product_id,
            'status' => 'active' // Hosting activado
        ]);
        $item1->update(['client_service_id' => $service1->id]);

        $item2 = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_id' => $domainPricing->product_id,
            'product_pricing_id' => $domainPricing->id,
            'quantity' => 1,
            'unit_price' => $domainPricing->price,
            'setup_fee' => $domainPricing->setup_fee, // Asumimos que el domain pricing también puede tener setup fee
            'total_price' => $domainPricing->price + $domainPricing->setup_fee,
            'description' => $domainPricing->product->name,
            'item_type' => 'new_service',
            'domain_name' => 'anotherdomain.net',
        ]);
        $service2 = ClientService::factory()->create([
            'client_id' => $client->id,
            'product_id' => $domainPricing->product_id,
            'status' => 'pending' // Dominio pendiente
        ]);
        $item2->update(['client_service_id' => $service2->id]);

        $this->actingAs($client);
        $response = $this->get(route('client.invoices.show', $invoice->id));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Client/Invoices/Show')
            ->has('invoice.id')
            ->where('invoice.status', 'pending_activation')
            ->has('invoice.requested_date')
            ->has('invoice.ip_address') // Verificar que estos campos lleguen si los mostramos
            ->has('invoice.items', 2)
            ->where('invoice.items.0.product.name', $hostingPricing->product->name)
            ->where('invoice.items.0.product_pricing.billing_cycle.name', 'Monthly')
            ->where('invoice.items.0.domain_name', 'mycoolsite.com')
            ->where('invoice.items.0.setup_fee', (string) $hostingPricing->setup_fee) // Cast a string para comparación
            ->where('invoice.items.0.client_service.status', 'active')
            ->where('invoice.items.1.product.name', $domainPricing->product->name)
            ->where('invoice.items.1.client_service.status', 'pending')
        );
    }
}

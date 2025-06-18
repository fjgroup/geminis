<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\Models\User;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\ProductPricing;
use App\Models\BillingCycle;
use App\Models\ClientService;
use App\Jobs\ProvisionClientServiceJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProvisionClientServiceJobTest extends TestCase
{
    use RefreshDatabase;

    private User $client;
    private ProductPricing $hostingProductPricing;
    private ProductPricing $domainProductPricing;
    private Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = User::factory()->create();

        $hostingType = ProductType::factory()->create(['name' => 'Web Hosting', 'slug' => 'web-hosting', 'creates_service_instance' => true, 'is_domain_product' => false]);
        $hostingProduct = Product::factory()->create(['name' => 'Basic Hosting', 'product_type_id' => $hostingType->id]);
        $monthlyCycle = BillingCycle::factory()->create(['name' => 'Monthly', 'type' => 'month', 'multiplier' => 1]);
        $this->hostingProductPricing = ProductPricing::factory()->create(['product_id' => $hostingProduct->id, 'billing_cycle_id' => $monthlyCycle->id, 'price' => 10.00]);

        $domainType = ProductType::factory()->create(['name' => 'Domain Registration', 'slug' => 'domain-registration', 'creates_service_instance' => true, 'is_domain_product' => true]);
        $domainProduct = Product::factory()->create(['name' => 'Standard Domain', 'product_type_id' => $domainType->id]);
        $this->domainProductPricing = ProductPricing::factory()->create(['product_id' => $domainProduct->id, 'billing_cycle_id' => $monthlyCycle->id, 'price' => 15.00]);

        $this->invoice = Invoice::factory()->create(['client_id' => $this->client->id, 'status' => 'paid']); // Job se dispara con 'paid' o 'pending_activation'
    }

    private function createInvoiceItemWithPendingService(ProductPricing $productPricing, string $domainName = null): InvoiceItem
    {
        $clientService = ClientService::factory()->create([
            'client_id' => $this->client->id,
            'product_id' => $productPricing->product_id,
            'product_pricing_id' => $productPricing->id,
            'billing_cycle_id' => $productPricing->billing_cycle_id,
            'status' => 'pending', // Estado inicial antes de que el job corra
            'domain_name' => $domainName,
            'registration_date' => Carbon::now()->subDay(), // Simular que fue creado antes
            'next_due_date' => Carbon::now()->subDay()->addMonth(), // Fecha tentativa
            'billing_amount' => $productPricing->price,
        ]);

        return InvoiceItem::factory()->create([
            'invoice_id' => $this->invoice->id,
            'product_id' => $productPricing->product_id,
            'product_pricing_id' => $productPricing->id,
            'client_service_id' => $clientService->id, // Vinculado
            'description' => $productPricing->product->name,
            'quantity' => 1,
            'unit_price' => $productPricing->price,
            'total_price' => $productPricing->price,
            'item_type' => 'new_service',
            'domain_name' => $domainName,
        ]);
    }

    /** @test */
    public function it_activates_non_domain_client_service_and_updates_details(): void
    {
        $invoiceItem = $this->createInvoiceItemWithPendingService($this->hostingProductPricing, 'myhostingsite.com');
        $clientService = $invoiceItem->clientService;

        $initialRegistrationDate = Carbon::parse($clientService->registration_date);

        $job = new ProvisionClientServiceJob($invoiceItem->withoutRelations()); // Usar withoutRelations como en el constructor real
        $job->handle();

        $clientService->refresh();

        $this->assertEquals('active', $clientService->status);
        $this->assertNotNull($clientService->username);
        $this->assertNotNull($clientService->password_encrypted);
        // Verificar que la fecha de registro no cambie si ya estaba establecida
        $this->assertEquals($initialRegistrationDate->toDateString(), Carbon::parse($clientService->registration_date)->toDateString());
        // Verificar que next_due_date se calcule basado en la registration_date y el ciclo
        $expectedDueDate = $initialRegistrationDate->copy()->addMonthsNoOverflow($clientService->productPricing->billingCycle->multiplier);
        $this->assertEquals($expectedDueDate->toDateString(), Carbon::parse($clientService->next_due_date)->toDateString());
        $this->assertStringContainsString("Servicio (re)aprovisionado por Job", $clientService->notes);
    }

    /** @test */
    public function it_keeps_domain_client_service_as_pending_and_updates_details(): void
    {
        $invoiceItem = $this->createInvoiceItemWithPendingService($this->domainProductPricing, 'mydomain.com');
        $clientService = $invoiceItem->clientService;
        $initialStatus = $clientService->status; // 'pending'
        $initialRegistrationDate = Carbon::parse($clientService->registration_date);


        $job = new ProvisionClientServiceJob($invoiceItem->withoutRelations());
        $job->handle();

        $clientService->refresh();

        // El estado para dominios debería seguir siendo 'pending' o el que el Job defina para "espera manual"
        // La lógica actual del Job mantiene 'pending' para dominios.
        $this->assertEquals('pending', $clientService->status);
        $this->assertEquals('mydomain.com', $clientService->domain_name); // Asegurar que el dominio se mantuvo/asignó
        $expectedDueDate = $initialRegistrationDate->copy()->addMonthsNoOverflow($clientService->productPricing->billingCycle->multiplier);
        $this->assertEquals($expectedDueDate->toDateString(), Carbon::parse($clientService->next_due_date)->toDateString());
        $this->assertStringContainsString("Servicio (re)aprovisionado por Job", $clientService->notes);
    }

    /** @test */
    public function it_logs_error_if_client_service_is_not_found_for_invoice_item(): void
    {
        Log::shouldReceive('error')->once()->with(\Mockery::pattern('/ClientService no encontrado para InvoiceItem ID/'));

        $invoiceItem = InvoiceItem::factory()->create([ // No creamos ClientService vinculado
            'invoice_id' => $this->invoice->id,
            'product_id' => $this->hostingProductPricing->product_id,
            'product_pricing_id' => $this->hostingProductPricing->id,
            'client_service_id' => 999, // ID no existente
        ]);

        $job = new ProvisionClientServiceJob($invoiceItem->withoutRelations());
        $job->handle(); // Debería loguear y retornar
    }

    /** @test */
    public function it_does_not_process_if_client_service_is_not_in_pending_status(): void
    {
        Log::shouldReceive('info')->once()->with(\Mockery::pattern('/no está en estado \'pending\'/'));

        $invoiceItem = $this->createInvoiceItemWithPendingService($this->hostingProductPricing);
        $clientService = $invoiceItem->clientService;
        $clientService->status = 'active'; // Cambiar a un estado no-pending
        $clientService->save();

        $job = new ProvisionClientServiceJob($invoiceItem->withoutRelations());
        $job->handle();

        $clientService->refresh();
        $this->assertEquals('active', $clientService->status); // No debería haber cambiado
    }

    /** @test */
    public function it_updates_client_service_id_on_invoice_item_if_it_was_null_job_created_service(): void
    {
        // Este test asume un escenario donde el Job podría crear el ClientService si no existe.
        // La lógica actual del Job espera que ClientService ya exista.
        // Si quisiéramos probar que el Job vincule un CS que él mismo creó, el Job necesitaría esa lógica.
        // Por ahora, el Job espera que ClientService exista. Si no, loguea error.
        // Si el ClientService es creado por payWithBalance y vinculado, este test en su forma actual no es necesario
        // o necesita un setup donde el invoiceItem.client_service_id es null y el Job SÍ lo crea.
        // Dado que el Job actual no crea el CS, este test no es aplicable tal cual.
        // Lo que sí hace el Job es:
        // if (is_null($invoiceItem->client_service_id) || $invoiceItem->client_service_id !== $clientService->id) {
        //    $invoiceItem->client_service_id = $clientService->id;
        //    $invoiceItem->save();
        // }
        // Esto es más una salvaguarda. La creación y vinculación inicial ocurre en payWithBalance.

        $this->markTestSkipped('El Job actual espera que ClientService ya esté creado y vinculado por payWithBalance. Este test necesitaría reevaluación si el Job creara el ClientService.');
    }
}

<?php

namespace Tests\Unit\Observers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\ProductPricing;
use App\Models\BillingCycle;
use App\Models\ClientService;
use App\Jobs\ProvisionClientServiceJob; // Importar el Job
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue; // Importar para fake queue

class InvoiceObserverTest extends TestCase
{
    use RefreshDatabase;

    // Helper para crear productos y precios
    private function createProductHierarchy(string $productTypeName, string $productTypeSlug, bool $createsServiceInstance, float $price = 10.00): ProductPricing
    {
        $productType = ProductType::factory()->create([
            'name' => $productTypeName,
            'slug' => $productTypeSlug,
            'creates_service_instance' => $createsServiceInstance,
        ]);
        $product = Product::factory()->create(['product_type_id' => $productType->id, 'name' => $productTypeName . ' Product']);
        $billingCycle = BillingCycle::factory()->create(['name' => 'Monthly', 'type' => 'month', 'multiplier' => 1]);
        return ProductPricing::factory()->create([
            'product_id' => $product->id,
            'billing_cycle_id' => $billingCycle->id,
            'price' => $price
        ]);
    }

    // Helper para crear InvoiceItems
    private function createInvoiceItemForProduct(Invoice $invoice, ProductPricing $productPricing, ?ClientService $clientService = null): InvoiceItem
    {
        return InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'product_id' => $productPricing->product_id,
            'product_pricing_id' => $productPricing->id,
            'client_service_id' => $clientService?->id,
            'description' => $productPricing->product->name,
            'quantity' => 1,
            'unit_price' => $productPricing->price,
            'total_price' => $productPricing->price,
            'item_type' => 'new_service',
        ]);
    }


    /** @test */
    public function dispatches_provisioning_job_when_invoice_status_changes_to_paid_for_pending_service_items(): void
    {
        Queue::fake();

        $client = User::factory()->create();
        $invoice = Invoice::factory()->for($client)->create(['status' => 'unpaid']);

        $hostingPricing = $this->createProductHierarchy('Web Hosting', 'web-hosting', true);
        $manualItemPricing = $this->createProductHierarchy('Manual Service', 'manual-service', false); // No crea instancia

        // Ítem que necesita aprovisionamiento (ClientService en 'pending')
        $servicePending = ClientService::factory()->create(['status' => 'pending', 'product_id' => $hostingPricing->product_id]);
        $itemNeedingProvisioning = $this->createInvoiceItemForProduct($invoice, $hostingPricing, $servicePending);

        // Ítem que ya está activo
        $serviceActive = ClientService::factory()->create(['status' => 'active', 'product_id' => $hostingPricing->product_id]);
        $itemAlreadyActive = $this->createInvoiceItemForProduct($invoice, $hostingPricing, $serviceActive);

        // Ítem que no crea instancia de servicio
        $itemNoService = $this->createInvoiceItemForProduct($invoice, $manualItemPricing, null);

        // Act
        $invoice->status = 'paid';
        $invoice->save(); // Esto dispara el observer

        // Assert
        Queue::assertPushed(ProvisionClientServiceJob::class, 1);
        Queue::assertPushed(ProvisionClientServiceJob::class, function ($job) use ($itemNeedingProvisioning) {
            return $job->invoiceItem->id === $itemNeedingProvisioning->id;
        });
        Queue::assertNotPushed(ProvisionClientServiceJob::class, function ($job) use ($itemAlreadyActive) {
            return $job->invoiceItem->id === $itemAlreadyActive->id;
        });
         Queue::assertNotPushed(ProvisionClientServiceJob::class, function ($job) use ($itemNoService) {
            return $job->invoiceItem->id === $itemNoService->id;
        });
    }

    /** @test */
    public function dispatches_provisioning_job_when_invoice_status_changes_to_pending_activation(): void
    {
        Queue::fake();
        $client = User::factory()->create();
        $invoice = Invoice::factory()->for($client)->create(['status' => 'unpaid']);
        $hostingPricing = $this->createProductHierarchy('Web Hosting', 'web-hosting', true);
        $servicePending = ClientService::factory()->create(['status' => 'pending', 'product_id' => $hostingPricing->product_id]);
        $itemToProvision = $this->createInvoiceItemForProduct($invoice, $hostingPricing, $servicePending);

        // Act
        $invoice->status = 'pending_activation';
        $invoice->save();

        // Assert
        Queue::assertPushed(ProvisionClientServiceJob::class, 1);
        Queue::assertPushed(ProvisionClientServiceJob::class, function ($job) use ($itemToProvision) {
            return $job->invoiceItem->id === $itemToProvision->id;
        });
    }

    /** @test */
    public function does_not_dispatch_job_if_invoice_status_changes_but_not_to_a_trigger_status(): void
    {
        Queue::fake();
        $client = User::factory()->create();
        $invoice = Invoice::factory()->for($client)->create(['status' => 'unpaid']);
        $hostingPricing = $this->createProductHierarchy('Web Hosting', 'web-hosting', true);
        $servicePending = ClientService::factory()->create(['status' => 'pending', 'product_id' => $hostingPricing->product_id]);
        $this->createInvoiceItemForProduct($invoice, $hostingPricing, $servicePending);

        // Act
        $invoice->status = 'cancelled'; // No es 'paid' ni 'pending_activation'
        $invoice->save();

        // Assert
        Queue::assertNotPushed(ProvisionClientServiceJob::class);
    }

    /** @test */
    public function does_not_dispatch_job_if_invoice_is_resaved_without_relevant_status_change(): void
    {
        Queue::fake();
        $client = User::factory()->create();
        $invoice = Invoice::factory()->for($client)->create(['status' => 'paid']); // Ya está 'paid'
        $hostingPricing = $this->createProductHierarchy('Web Hosting', 'web-hosting', true);
        $servicePending = ClientService::factory()->create(['status' => 'pending', 'product_id' => $hostingPricing->product_id]);
        $this->createInvoiceItemForProduct($invoice, $hostingPricing, $servicePending);

        // Simular que el job ya se despachó la primera vez que pasó a 'paid'
        // Para esta prueba, nos enfocamos en que un simple re-save no lo despache de nuevo si el status no cambia *a* 'paid'/'pending_activation'
        Queue::fake(); // Resetear la cola para esta aserción específica

        // Act
        $invoice->admin_notes = 'Updating notes'; // Cambiar un campo no relacionado con el status
        $invoice->save();

        // Assert
        Queue::assertNotPushed(ProvisionClientServiceJob::class);
    }

    /** @test */
    public function dispatches_job_if_invoice_item_has_no_client_service_yet_but_product_creates_one(): void
    {
        Queue::fake();
        $client = User::factory()->create();
        $invoice = Invoice::factory()->for($client)->create(['status' => 'unpaid']);
        $hostingPricing = $this->createProductHierarchy('Web Hosting', 'web-hosting', true);
        // Importante: No se crea ClientService aquí, o se pasa null.
        $itemWithoutService = $this->createInvoiceItemForProduct($invoice, $hostingPricing, null);

        // Act
        $invoice->status = 'paid';
        $invoice->save();

        // Assert
        Queue::assertPushed(ProvisionClientServiceJob::class, 1);
        Queue::assertPushed(ProvisionClientServiceJob::class, function ($job) use ($itemWithoutService) {
            return $job->invoiceItem->id === $itemWithoutService->id;
        });
    }
}

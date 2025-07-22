<?php

namespace Tests\Unit\Services;

use App\Models\ClientService;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Services\InvoiceValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceValidationServiceTest extends TestCase
{
    use RefreshDatabase;

    private InvoiceValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InvoiceValidationService();
    }

    /** @test */
    public function it_allows_cancellation_of_unpaid_new_service_invoice()
    {
        $client = User::factory()->create(['role' => 'client']);
        $invoice = Invoice::factory()->create([
            'client_id' => $client->id,
            'status' => 'unpaid'
        ]);

        $clientService = ClientService::factory()->create([
            'client_id' => $client->id,
            'status' => 'pending'
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'client_service_id' => $clientService->id,
            'item_type' => 'new_service'
        ]);

        $result = $this->service->canInvoiceBeCancelledAsNewService($invoice);

        $this->assertTrue($result['can_cancel']);
        $this->assertEmpty($result['reasons']);
    }

    /** @test */
    public function it_prevents_cancellation_of_paid_invoice()
    {
        $client = User::factory()->create(['role' => 'client']);
        $invoice = Invoice::factory()->create([
            'client_id' => $client->id,
            'status' => 'paid'
        ]);

        $result = $this->service->canInvoiceBeCancelledAsNewService($invoice);

        $this->assertFalse($result['can_cancel']);
        $this->assertContains('Solo se pueden cancelar facturas no pagadas', $result['reasons']);
    }

    /** @test */
    public function it_prevents_cancellation_when_service_is_active()
    {
        $client = User::factory()->create(['role' => 'client']);
        $invoice = Invoice::factory()->create([
            'client_id' => $client->id,
            'status' => 'unpaid'
        ]);

        $clientService = ClientService::factory()->create([
            'client_id' => $client->id,
            'status' => 'active'
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'client_service_id' => $clientService->id,
            'item_type' => 'new_service'
        ]);

        $result = $this->service->canInvoiceBeCancelledAsNewService($invoice);

        $this->assertFalse($result['can_cancel']);
        $this->assertNotEmpty($result['reasons']);
    }

    /** @test */
    public function it_validates_invoice_can_be_paid()
    {
        $client = User::factory()->create(['role' => 'client']);
        $invoice = Invoice::factory()->create([
            'client_id' => $client->id,
            'status' => 'unpaid',
            'total_amount' => 100.00
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 100.00
        ]);

        $result = $this->service->canInvoiceBePaid($invoice);

        $this->assertTrue($result['can_pay']);
    }

    /** @test */
    public function it_prevents_payment_of_zero_amount_invoice()
    {
        $client = User::factory()->create(['role' => 'client']);
        $invoice = Invoice::factory()->create([
            'client_id' => $client->id,
            'status' => 'unpaid',
            'total_amount' => 0.00
        ]);

        $result = $this->service->canInvoiceBePaid($invoice);

        $this->assertFalse($result['can_pay']);
        $this->assertContains('El monto de la factura debe ser mayor a cero', $result['reasons']);
    }

    /** @test */
    public function it_validates_invoice_data()
    {
        $validData = [
            'client_id' => 1,
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'subtotal' => 100.00,
            'total_amount' => 100.00,
            'currency_code' => 'USD'
        ];

        $result = $this->service->validateInvoiceData($validData);

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    /** @test */
    public function it_rejects_invalid_invoice_data()
    {
        $invalidData = [
            'client_id' => null,
            'issue_date' => null,
            'due_date' => null,
            'subtotal' => -10,
            'total_amount' => 0,
            'currency_code' => null
        ];

        $result = $this->service->validateInvoiceData($invalidData);

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
        $this->assertArrayHasKey('client_id', $result['errors']);
        $this->assertArrayHasKey('total_amount', $result['errors']);
    }

    /** @test */
    public function it_checks_if_invoice_can_be_edited()
    {
        $client = User::factory()->create(['role' => 'client']);
        $invoice = Invoice::factory()->create([
            'client_id' => $client->id,
            'status' => 'unpaid'
        ]);

        $result = $this->service->canInvoiceBeEdited($invoice);

        $this->assertTrue($result['can_edit']);
        $this->assertEmpty($result['reasons']);
    }

    /** @test */
    public function it_prevents_editing_paid_invoice()
    {
        $client = User::factory()->create(['role' => 'client']);
        $invoice = Invoice::factory()->create([
            'client_id' => $client->id,
            'status' => 'paid'
        ]);

        $result = $this->service->canInvoiceBeEdited($invoice);

        $this->assertFalse($result['can_edit']);
        $this->assertContains('No se pueden editar facturas pagadas', $result['reasons']);
    }

    /** @test */
    public function it_validates_invoice_integrity()
    {
        $client = User::factory()->create(['role' => 'client']);
        $invoice = Invoice::factory()->create([
            'client_id' => $client->id,
            'subtotal' => 100.00,
            'tax1_rate' => 10.00,
            'tax1_amount' => 10.00,
            'tax2_amount' => 0.00,
            'total_amount' => 110.00
        ]);

        // Crear items que sumen el subtotal
        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 100.00
        ]);

        $result = $this->service->validateInvoiceIntegrity($invoice);

        $this->assertTrue($result['is_valid']);
        $this->assertEmpty($result['issues']);
    }

    /** @test */
    public function it_detects_integrity_issues()
    {
        $client = User::factory()->create(['role' => 'client']);
        $invoice = Invoice::factory()->create([
            'client_id' => $client->id,
            'subtotal' => 100.00,
            'tax1_rate' => 10.00,
            'tax1_amount' => 10.00,
            'tax2_amount' => 0.00,
            'total_amount' => 150.00 // Total incorrecto
        ]);

        // Crear items que sumen el subtotal
        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 100.00
        ]);

        $result = $this->service->validateInvoiceIntegrity($invoice);

        $this->assertFalse($result['is_valid']);
        $this->assertNotEmpty($result['issues']);
    }

    /** @test */
    public function it_gets_complete_validation_summary()
    {
        $client = User::factory()->create(['role' => 'client']);
        $invoice = Invoice::factory()->create([
            'client_id' => $client->id,
            'status' => 'unpaid',
            'total_amount' => 100.00,
            'subtotal' => 100.00,
            'tax1_amount' => 0.00,
            'tax2_amount' => 0.00
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 100.00,
            'item_type' => 'new_service'
        ]);

        $summary = $this->service->getCompleteValidationSummary($invoice);

        $this->assertArrayHasKey('can_be_paid', $summary);
        $this->assertArrayHasKey('can_be_cancelled', $summary);
        $this->assertArrayHasKey('can_be_edited', $summary);
        $this->assertArrayHasKey('integrity_check', $summary);
    }
}

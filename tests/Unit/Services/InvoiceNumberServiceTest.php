<?php

namespace Tests\Unit\Services;

use App\Models\Invoice;
use App\Services\InvoiceNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceNumberServiceTest extends TestCase
{
    use RefreshDatabase;

    private InvoiceNumberService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InvoiceNumberService();
    }

    /** @test */
    public function it_generates_first_invoice_number_for_new_day()
    {
        $invoiceNumber = $this->service->generateNextInvoiceNumber();
        
        $expectedPrefix = now()->format('Ymd') . '-';
        $this->assertStringStartsWith($expectedPrefix, $invoiceNumber);
        $this->assertStringEndsWith('0001', $invoiceNumber);
    }

    /** @test */
    public function it_generates_sequential_invoice_numbers()
    {
        // Crear primera factura
        Invoice::factory()->create([
            'invoice_number' => now()->format('Ymd') . '-0001'
        ]);

        $invoiceNumber = $this->service->generateNextInvoiceNumber();
        
        $expectedNumber = now()->format('Ymd') . '-0002';
        $this->assertEquals($expectedNumber, $invoiceNumber);
    }

    /** @test */
    public function it_handles_custom_prefix()
    {
        $customPrefix = 'TEST-';
        $invoiceNumber = $this->service->generateNextInvoiceNumber($customPrefix);
        
        $this->assertStringStartsWith($customPrefix, $invoiceNumber);
        $this->assertStringEndsWith('0001', $invoiceNumber);
    }

    /** @test */
    public function it_validates_correct_invoice_number_format()
    {
        $validNumber = '20250122-0001';
        $this->assertTrue($this->service->validateInvoiceNumberFormat($validNumber));
    }

    /** @test */
    public function it_validates_fallback_invoice_number_format()
    {
        $fallbackNumber = 'FALLBACK-1642857600';
        $this->assertTrue($this->service->validateInvoiceNumberFormat($fallbackNumber));
    }

    /** @test */
    public function it_rejects_invalid_invoice_number_format()
    {
        $invalidNumber = 'INVALID-FORMAT';
        $this->assertFalse($this->service->validateInvoiceNumberFormat($invalidNumber));
    }

    /** @test */
    public function it_parses_standard_invoice_number()
    {
        $invoiceNumber = '20250122-0001';
        $parsed = $this->service->parseInvoiceNumber($invoiceNumber);
        
        $this->assertEquals('standard', $parsed['type']);
        $this->assertEquals(1, $parsed['sequence']);
        $this->assertEquals('2025-01-22', $parsed['formatted_date']);
    }

    /** @test */
    public function it_parses_fallback_invoice_number()
    {
        $invoiceNumber = 'FALLBACK-1642857600';
        $parsed = $this->service->parseInvoiceNumber($invoiceNumber);
        
        $this->assertEquals('fallback', $parsed['type']);
        $this->assertEquals('1642857600', $parsed['timestamp']);
    }

    /** @test */
    public function it_gets_invoice_number_stats()
    {
        $today = now()->format('Y-m-d');
        $prefix = now()->format('Ymd') . '-';
        
        // Crear algunas facturas
        Invoice::factory()->count(3)->create([
            'invoice_number' => $prefix . '0001'
        ]);

        $stats = $this->service->getInvoiceNumberStats($today);
        
        $this->assertEquals($today, $stats['date']);
        $this->assertEquals($prefix, $stats['prefix']);
        $this->assertEquals(3, $stats['total_invoices']);
    }

    /** @test */
    public function it_checks_if_invoice_number_exists()
    {
        $invoiceNumber = '20250122-0001';
        
        // Inicialmente no existe
        $this->assertFalse($this->service->invoiceNumberExists($invoiceNumber));
        
        // Crear factura
        Invoice::factory()->create(['invoice_number' => $invoiceNumber]);
        
        // Ahora existe
        $this->assertTrue($this->service->invoiceNumberExists($invoiceNumber));
    }

    /** @test */
    public function it_generates_multiple_consecutive_numbers()
    {
        $count = 5;
        $numbers = $this->service->generateMultipleInvoiceNumbers($count);
        
        $this->assertCount($count, $numbers);
        
        $prefix = now()->format('Ymd') . '-';
        for ($i = 0; $i < $count; $i++) {
            $expectedNumber = $prefix . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
            $this->assertEquals($expectedNumber, $numbers[$i]);
        }
    }

    /** @test */
    public function it_throws_exception_for_invalid_count()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->generateMultipleInvoiceNumbers(0);
    }

    /** @test */
    public function it_throws_exception_for_too_many_numbers()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->generateMultipleInvoiceNumbers(101);
    }

    /** @test */
    public function it_handles_corrupted_invoice_number_format()
    {
        $prefix = now()->format('Ymd') . '-';
        
        // Crear factura con formato corrupto
        Invoice::factory()->create([
            'invoice_number' => $prefix . 'CORRUPTED'
        ]);

        $invoiceNumber = $this->service->generateNextInvoiceNumber();
        
        // Debería usar fallback y generar número basado en conteo
        $this->assertStringStartsWith($prefix, $invoiceNumber);
    }

    /** @test */
    public function it_generates_fallback_number_on_exception()
    {
        // Simular error usando un mock
        $mockService = $this->createPartialMock(InvoiceNumberService::class, ['getNextSequentialNumber']);
        $mockService->method('getNextSequentialNumber')->willThrowException(new \Exception('Test error'));
        
        $invoiceNumber = $mockService->generateNextInvoiceNumber();
        
        $this->assertStringStartsWith('FALLBACK-', $invoiceNumber);
    }

    /** @test */
    public function it_reserves_invoice_numbers()
    {
        $count = 3;
        $reservedNumbers = $this->service->reserveInvoiceNumbers($count);
        
        $this->assertCount($count, $reservedNumbers);
        $this->assertIsArray($reservedNumbers);
    }

    /** @test */
    public function it_handles_different_date_prefixes()
    {
        $customDate = '2025-01-01';
        $stats = $this->service->getInvoiceNumberStats($customDate);
        
        $this->assertEquals($customDate, $stats['date']);
        $this->assertEquals('20250101-', $stats['prefix']);
    }

    /** @test */
    public function it_handles_stats_error_gracefully()
    {
        $invalidDate = 'invalid-date';
        $stats = $this->service->getInvoiceNumberStats($invalidDate);
        
        $this->assertArrayHasKey('error', $stats);
    }
}

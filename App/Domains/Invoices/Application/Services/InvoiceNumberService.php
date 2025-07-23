<?php

namespace App\Domains\Invoices\Application\Services;

use App\Domains\Invoices\Infrastructure\Persistence\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Servicio especializado para generación de números de factura
 * 
 * Aplica Single Responsibility Principle - solo genera números de factura únicos
 * Ubicado en Application layer según arquitectura hexagonal
 */
class InvoiceNumberService
{
    /**
     * Generar número de factura único
     */
    public function generateInvoiceNumber(array $options = []): string
    {
        $format = $options['format'] ?? config('invoicing.number_format', 'INV-YYYY-NNNNNN');
        $prefix = $options['prefix'] ?? config('invoicing.number_prefix', 'INV');
        $year = $options['year'] ?? Carbon::now()->year;
        $resetYearly = $options['reset_yearly'] ?? true;

        try {
            // Generar número basado en el formato
            switch ($format) {
                case 'INV-YYYY-NNNNNN':
                    return $this->generateYearlySequentialNumber($prefix, $year);
                
                case 'INV-NNNNNN':
                    return $this->generateSequentialNumber($prefix);
                
                case 'YYYY-MM-NNNN':
                    return $this->generateMonthlySequentialNumber($year);
                
                case 'YYYYMMDD-NNN':
                    return $this->generateDailySequentialNumber();
                
                default:
                    return $this->generateYearlySequentialNumber($prefix, $year);
            }

        } catch (\Exception $e) {
            Log::error('Error generando número de factura', [
                'format' => $format,
                'error' => $e->getMessage()
            ]);

            // Fallback a formato simple con timestamp
            return $this->generateFallbackNumber($prefix);
        }
    }

    /**
     * Generar número secuencial anual (INV-2024-000001)
     */
    private function generateYearlySequentialNumber(string $prefix, int $year): string
    {
        $pattern = "{$prefix}-{$year}-%";
        
        $lastInvoice = Invoice::where('invoice_number', 'like', $pattern)
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            // Extraer el número secuencial del último número de factura
            $lastNumber = (int) substr($lastInvoice->invoice_number, -6);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%s-%d-%06d', $prefix, $year, $nextNumber);
    }

    /**
     * Generar número secuencial simple (INV-000001)
     */
    private function generateSequentialNumber(string $prefix): string
    {
        $pattern = "{$prefix}-%";
        
        $lastInvoice = Invoice::where('invoice_number', 'like', $pattern)
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            // Extraer el número secuencial
            $parts = explode('-', $lastInvoice->invoice_number);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%s-%06d', $prefix, $nextNumber);
    }

    /**
     * Generar número secuencial mensual (2024-01-0001)
     */
    private function generateMonthlySequentialNumber(int $year): string
    {
        $month = Carbon::now()->month;
        $pattern = sprintf('%d-%02d-%%', $year, $month);
        
        $lastInvoice = Invoice::where('invoice_number', 'like', $pattern)
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%d-%02d-%04d', $year, $month, $nextNumber);
    }

    /**
     * Generar número secuencial diario (20240123-001)
     */
    private function generateDailySequentialNumber(): string
    {
        $date = Carbon::now()->format('Ymd');
        $pattern = "{$date}-%";
        
        $lastInvoice = Invoice::where('invoice_number', 'like', $pattern)
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%s-%03d', $date, $nextNumber);
    }

    /**
     * Generar número de fallback con timestamp
     */
    private function generateFallbackNumber(string $prefix): string
    {
        $timestamp = Carbon::now()->format('YmdHis');
        return "{$prefix}-{$timestamp}";
    }

    /**
     * Validar formato de número de factura
     */
    public function validateInvoiceNumberFormat(string $invoiceNumber, string $format = null): bool
    {
        $format = $format ?? config('invoicing.number_format', 'INV-YYYY-NNNNNN');

        switch ($format) {
            case 'INV-YYYY-NNNNNN':
                return preg_match('/^[A-Z]+-\d{4}-\d{6}$/', $invoiceNumber);
            
            case 'INV-NNNNNN':
                return preg_match('/^[A-Z]+-\d{6}$/', $invoiceNumber);
            
            case 'YYYY-MM-NNNN':
                return preg_match('/^\d{4}-\d{2}-\d{4}$/', $invoiceNumber);
            
            case 'YYYYMMDD-NNN':
                return preg_match('/^\d{8}-\d{3}$/', $invoiceNumber);
            
            default:
                return true; // Permitir cualquier formato si no se reconoce
        }
    }

    /**
     * Verificar si un número de factura es único
     */
    public function isInvoiceNumberUnique(string $invoiceNumber, int $excludeInvoiceId = null): bool
    {
        $query = Invoice::where('invoice_number', $invoiceNumber);
        
        if ($excludeInvoiceId) {
            $query->where('id', '!=', $excludeInvoiceId);
        }

        return !$query->exists();
    }

    /**
     * Generar número de factura personalizado
     */
    public function generateCustomInvoiceNumber(array $components): string
    {
        $parts = [];

        // Agregar prefijo si se especifica
        if (isset($components['prefix'])) {
            $parts[] = $components['prefix'];
        }

        // Agregar año si se especifica
        if (isset($components['year'])) {
            $parts[] = $components['year'];
        }

        // Agregar mes si se especifica
        if (isset($components['month'])) {
            $parts[] = sprintf('%02d', $components['month']);
        }

        // Agregar día si se especifica
        if (isset($components['day'])) {
            $parts[] = sprintf('%02d', $components['day']);
        }

        // Agregar número secuencial
        if (isset($components['sequence'])) {
            $digits = $components['sequence_digits'] ?? 6;
            $parts[] = sprintf('%0' . $digits . 'd', $components['sequence']);
        }

        // Agregar sufijo si se especifica
        if (isset($components['suffix'])) {
            $parts[] = $components['suffix'];
        }

        $separator = $components['separator'] ?? '-';
        return implode($separator, $parts);
    }

    /**
     * Obtener próximo número secuencial para un patrón
     */
    public function getNextSequentialNumber(string $pattern): int
    {
        $lastInvoice = Invoice::where('invoice_number', 'like', $pattern)
            ->orderBy('invoice_number', 'desc')
            ->first();

        if (!$lastInvoice) {
            return 1;
        }

        // Extraer el número secuencial del final del número de factura
        if (preg_match('/(\d+)$/', $lastInvoice->invoice_number, $matches)) {
            return (int) $matches[1] + 1;
        }

        return 1;
    }

    /**
     * Obtener estadísticas de numeración
     */
    public function getNumberingStats(): array
    {
        try {
            $currentYear = Carbon::now()->year;
            $currentMonth = Carbon::now()->month;

            $totalInvoices = Invoice::count();
            $invoicesThisYear = Invoice::whereYear('created_at', $currentYear)->count();
            $invoicesThisMonth = Invoice::whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $currentMonth)
                ->count();

            $lastInvoiceNumber = Invoice::orderBy('created_at', 'desc')->value('invoice_number');

            return [
                'total_invoices' => $totalInvoices,
                'invoices_this_year' => $invoicesThisYear,
                'invoices_this_month' => $invoicesThisMonth,
                'last_invoice_number' => $lastInvoiceNumber,
                'current_year' => $currentYear,
                'current_month' => $currentMonth,
            ];

        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas de numeración', [
                'error' => $e->getMessage()
            ]);

            return [
                'total_invoices' => 0,
                'invoices_this_year' => 0,
                'invoices_this_month' => 0,
                'last_invoice_number' => null,
                'current_year' => Carbon::now()->year,
                'current_month' => Carbon::now()->month,
            ];
        }
    }

    /**
     * Regenerar número de factura si hay conflicto
     */
    public function regenerateIfConflict(string $invoiceNumber, int $excludeInvoiceId = null): string
    {
        if ($this->isInvoiceNumberUnique($invoiceNumber, $excludeInvoiceId)) {
            return $invoiceNumber;
        }

        // Si hay conflicto, generar uno nuevo
        Log::warning('Conflicto de número de factura detectado', [
            'original_number' => $invoiceNumber,
            'exclude_id' => $excludeInvoiceId
        ]);

        return $this->generateInvoiceNumber();
    }

    /**
     * Formatear número de factura para visualización
     */
    public function formatForDisplay(string $invoiceNumber): string
    {
        // Agregar formato visual si es necesario
        // Por ejemplo, agregar espacios o guiones para mejor legibilidad
        return $invoiceNumber;
    }

    /**
     * Extraer componentes de un número de factura
     */
    public function parseInvoiceNumber(string $invoiceNumber): array
    {
        $components = [];

        // Intentar extraer componentes comunes
        if (preg_match('/^([A-Z]+)-(\d{4})-(\d+)$/', $invoiceNumber, $matches)) {
            $components = [
                'prefix' => $matches[1],
                'year' => (int) $matches[2],
                'sequence' => (int) $matches[3],
                'format' => 'INV-YYYY-NNNNNN'
            ];
        } elseif (preg_match('/^([A-Z]+)-(\d+)$/', $invoiceNumber, $matches)) {
            $components = [
                'prefix' => $matches[1],
                'sequence' => (int) $matches[2],
                'format' => 'INV-NNNNNN'
            ];
        } elseif (preg_match('/^(\d{4})-(\d{2})-(\d+)$/', $invoiceNumber, $matches)) {
            $components = [
                'year' => (int) $matches[1],
                'month' => (int) $matches[2],
                'sequence' => (int) $matches[3],
                'format' => 'YYYY-MM-NNNN'
            ];
        }

        return $components;
    }
}

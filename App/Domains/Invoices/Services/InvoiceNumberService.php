<?php

namespace App\Domains\Invoices\Services;

use App\Domains\Invoices\Models\Invoice;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para la generación de números de factura
 * 
 * Extrae la lógica de generación de números de factura del modelo Invoice
 */
class InvoiceNumberService
{
    /**
     * Generar el siguiente número de factura
     * 
     * @param string|null $prefix Prefijo personalizado (opcional)
     * @return string
     */
    public function generateNextInvoiceNumber(?string $prefix = null): string
    {
        try {
            // Usar prefijo personalizado o generar uno basado en la fecha
            $prefix = $prefix ?? $this->generateDatePrefix();
            
            // Obtener el siguiente número secuencial
            $nextNumber = $this->getNextSequentialNumber($prefix);
            
            // Formatear el número final
            return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            
        } catch (\Exception $e) {
            Log::error('Error generando número de factura', [
                'error' => $e->getMessage(),
                'prefix' => $prefix
            ]);
            
            // Fallback a un número básico
            return $this->generateFallbackNumber();
        }
    }

    /**
     * Generar prefijo basado en la fecha actual
     * 
     * @return string
     */
    private function generateDatePrefix(): string
    {
        return now()->format('Ymd') . '-';
    }

    /**
     * Obtener el siguiente número secuencial para un prefijo dado
     * 
     * @param string $prefix
     * @return int
     */
    private function getNextSequentialNumber(string $prefix): int
    {
        $latestInvoice = Invoice::where('invoice_number', 'LIKE', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if (!$latestInvoice) {
            return 1; // Primera factura con este prefijo
        }

        // Extraer la parte numérica del último número de factura
        $lastNumberStr = substr($latestInvoice->invoice_number, strlen($prefix));

        if (is_numeric($lastNumberStr)) {
            return (int)$lastNumberStr + 1;
        }

        // Fallback si el formato no es el esperado
        Log::warning('Formato de número de factura inesperado', [
            'last_invoice_number' => $latestInvoice->invoice_number,
            'extracted_part' => $lastNumberStr,
            'prefix' => $prefix
        ]);

        // Contar facturas con este prefijo como fallback
        $countWithPrefix = Invoice::where('invoice_number', 'LIKE', $prefix . '%')->count();
        return $countWithPrefix + 1;
    }

    /**
     * Generar número de factura de emergencia
     * 
     * @return string
     */
    private function generateFallbackNumber(): string
    {
        $timestamp = now()->timestamp;
        return 'FALLBACK-' . $timestamp;
    }

    /**
     * Validar formato de número de factura
     * 
     * @param string $invoiceNumber
     * @return bool
     */
    public function validateInvoiceNumberFormat(string $invoiceNumber): bool
    {
        // Formato esperado: YYYYMMDD-NNNN
        $pattern = '/^\d{8}-\d{4}$/';
        
        if (preg_match($pattern, $invoiceNumber)) {
            return true;
        }

        // Permitir formatos de fallback
        if (str_starts_with($invoiceNumber, 'FALLBACK-')) {
            return true;
        }

        return false;
    }

    /**
     * Extraer información del número de factura
     * 
     * @param string $invoiceNumber
     * @return array
     */
    public function parseInvoiceNumber(string $invoiceNumber): array
    {
        if (str_starts_with($invoiceNumber, 'FALLBACK-')) {
            return [
                'type' => 'fallback',
                'timestamp' => substr($invoiceNumber, 9),
                'date' => null,
                'sequence' => null
            ];
        }

        if (preg_match('/^(\d{8})-(\d{4})$/', $invoiceNumber, $matches)) {
            $dateStr = $matches[1];
            $sequence = (int)$matches[2];
            
            try {
                $date = \Carbon\Carbon::createFromFormat('Ymd', $dateStr);
                
                return [
                    'type' => 'standard',
                    'date' => $date,
                    'sequence' => $sequence,
                    'formatted_date' => $date->format('Y-m-d')
                ];
            } catch (\Exception $e) {
                Log::warning('Error parseando fecha del número de factura', [
                    'invoice_number' => $invoiceNumber,
                    'date_string' => $dateStr
                ]);
            }
        }

        return [
            'type' => 'unknown',
            'raw' => $invoiceNumber
        ];
    }

    /**
     * Obtener estadísticas de números de factura
     * 
     * @param string|null $date Fecha en formato Y-m-d (opcional)
     * @return array
     */
    public function getInvoiceNumberStats(?string $date = null): array
    {
        try {
            $targetDate = $date ? \Carbon\Carbon::parse($date) : now();
            $prefix = $targetDate->format('Ymd') . '-';

            $stats = [
                'date' => $targetDate->format('Y-m-d'),
                'prefix' => $prefix,
                'total_invoices' => Invoice::where('invoice_number', 'LIKE', $prefix . '%')->count(),
                'last_sequence' => 0,
                'next_sequence' => 1
            ];

            $latestInvoice = Invoice::where('invoice_number', 'LIKE', $prefix . '%')
                ->orderBy('id', 'desc')
                ->first();

            if ($latestInvoice) {
                $lastNumberStr = substr($latestInvoice->invoice_number, strlen($prefix));
                if (is_numeric($lastNumberStr)) {
                    $stats['last_sequence'] = (int)$lastNumberStr;
                    $stats['next_sequence'] = $stats['last_sequence'] + 1;
                }
            }

            return $stats;

        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas de números de factura', [
                'error' => $e->getMessage(),
                'date' => $date
            ]);

            return [
                'error' => 'Error obteniendo estadísticas',
                'date' => $date
            ];
        }
    }

    /**
     * Verificar si un número de factura ya existe
     * 
     * @param string $invoiceNumber
     * @return bool
     */
    public function invoiceNumberExists(string $invoiceNumber): bool
    {
        return Invoice::where('invoice_number', $invoiceNumber)->exists();
    }

    /**
     * Generar múltiples números de factura consecutivos
     * 
     * @param int $count Cantidad de números a generar
     * @param string|null $prefix Prefijo personalizado (opcional)
     * @return array
     */
    public function generateMultipleInvoiceNumbers(int $count, ?string $prefix = null): array
    {
        if ($count <= 0 || $count > 100) {
            throw new \InvalidArgumentException('La cantidad debe estar entre 1 y 100');
        }

        $prefix = $prefix ?? $this->generateDatePrefix();
        $startingNumber = $this->getNextSequentialNumber($prefix);
        
        $numbers = [];
        for ($i = 0; $i < $count; $i++) {
            $numbers[] = $prefix . str_pad($startingNumber + $i, 4, '0', STR_PAD_LEFT);
        }

        return $numbers;
    }

    /**
     * Reservar un rango de números de factura
     * 
     * @param int $count
     * @param string|null $prefix
     * @return array
     */
    public function reserveInvoiceNumbers(int $count, ?string $prefix = null): array
    {
        // Esta funcionalidad requeriría una tabla de números reservados
        // Por ahora solo generamos los números
        return $this->generateMultipleInvoiceNumbers($count, $prefix);
    }
}

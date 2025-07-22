<?php

namespace App\Domains\Invoices\Services;

use App\Domains\Invoices\Models\Invoice;
use App\Domains\Invoices\Models\InvoiceItem;
use App\Domains\Invoices\DataTransferObjects\CreateInvoiceDTO;
use App\Domains\Invoices\DataTransferObjects\InvoiceItemDTO;
use App\Services\InvoiceNumberService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Class InvoiceGenerator
 * 
 * Servicio especializado para la generación de facturas
 * Aplica el principio de Single Responsibility (SRP)
 * Maneja toda la lógica de negocio para generar facturas
 */
class InvoiceGenerator
{
    public function __construct(
        private InvoiceNumberService $invoiceNumberService
    ) {}

    /**
     * Generar una nueva factura
     * 
     * @param CreateInvoiceDTO $dto
     * @return array
     */
    public function generateInvoice(CreateInvoiceDTO $dto): array
    {
        try {
            // Validar DTO
            if (!$dto->isValid()) {
                return [
                    'success' => false,
                    'message' => 'Datos de factura inválidos',
                    'errors' => $dto->getValidationErrors(),
                    'data' => null
                ];
            }

            // Generar factura en transacción
            $invoice = DB::transaction(function () use ($dto) {
                return $this->createInvoiceRecord($dto);
            });

            Log::info('Factura generada exitosamente', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'client_id' => $invoice->client_id,
                'total_amount' => $invoice->total_amount
            ]);

            return [
                'success' => true,
                'message' => 'Factura generada exitosamente',
                'errors' => [],
                'data' => $invoice
            ];

        } catch (\Exception $e) {
            Log::error('Error al generar factura', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'dto_data' => $dto->toArray()
            ]);

            return [
                'success' => false,
                'message' => 'Error interno al generar la factura',
                'errors' => ['general' => 'Error interno del servidor'],
                'data' => null
            ];
        }
    }

    /**
     * Generar factura automática para servicios
     * 
     * @param int $clientId
     * @param array $services
     * @return array
     */
    public function generateServiceInvoice(int $clientId, array $services): array
    {
        try {
            // Calcular totales
            $subtotal = 0;
            $items = [];

            foreach ($services as $service) {
                $itemAmount = $service['price'] ?? 0;
                $setupFee = $service['setup_fee'] ?? 0;
                $subtotal += $itemAmount + $setupFee;

                $items[] = [
                    'description' => $service['description'] ?? 'Servicio',
                    'quantity' => 1,
                    'unit_price' => $itemAmount,
                    'total_price' => $itemAmount,
                    'setup_fee' => $setupFee,
                    'product_id' => $service['product_id'] ?? null,
                    'item_type' => $service['type'] ?? 'new_service',
                    'taxable' => $service['taxable'] ?? true,
                ];
            }

            // Crear DTO para la factura
            $dto = CreateInvoiceDTO::fromServiceData($clientId, [
                'amount' => $subtotal,
                'total_amount' => $subtotal,
                'currency_code' => 'USD',
                'items' => $items,
            ]);

            return $this->generateInvoice($dto);

        } catch (\Exception $e) {
            Log::error('Error al generar factura de servicios', [
                'client_id' => $clientId,
                'error' => $e->getMessage(),
                'services_count' => count($services)
            ]);

            return [
                'success' => false,
                'message' => 'Error al generar factura de servicios',
                'errors' => ['general' => $e->getMessage()],
                'data' => null
            ];
        }
    }

    /**
     * Generar factura de renovación
     * 
     * @param int $clientId
     * @param array $renewalServices
     * @return array
     */
    public function generateRenewalInvoice(int $clientId, array $renewalServices): array
    {
        try {
            $subtotal = 0;
            $items = [];

            foreach ($renewalServices as $service) {
                $renewalAmount = $service['renewal_price'] ?? 0;
                $subtotal += $renewalAmount;

                $items[] = [
                    'description' => 'Renovación: ' . ($service['description'] ?? 'Servicio'),
                    'quantity' => 1,
                    'unit_price' => $renewalAmount,
                    'total_price' => $renewalAmount,
                    'client_service_id' => $service['client_service_id'] ?? null,
                    'item_type' => 'renewal',
                    'taxable' => $service['taxable'] ?? true,
                ];
            }

            $dto = CreateInvoiceDTO::fromServiceData($clientId, [
                'amount' => $subtotal,
                'total_amount' => $subtotal,
                'currency_code' => 'USD',
                'notes' => 'Factura de renovación de servicios',
                'items' => $items,
            ]);

            return $this->generateInvoice($dto);

        } catch (\Exception $e) {
            Log::error('Error al generar factura de renovación', [
                'client_id' => $clientId,
                'error' => $e->getMessage(),
                'services_count' => count($renewalServices)
            ]);

            return [
                'success' => false,
                'message' => 'Error al generar factura de renovación',
                'errors' => ['general' => $e->getMessage()],
                'data' => null
            ];
        }
    }

    /**
     * Crear el registro de factura en la base de datos
     * 
     * @param CreateInvoiceDTO $dto
     * @return Invoice
     */
    private function createInvoiceRecord(CreateInvoiceDTO $dto): Invoice
    {
        $invoiceData = $dto->toArray();

        // Generar número de factura si no se proporcionó
        if (empty($invoiceData['invoice_number'])) {
            $invoiceData['invoice_number'] = $this->invoiceNumberService->generateNextInvoiceNumber();
        }

        // Establecer fechas por defecto
        if (empty($invoiceData['issue_date'])) {
            $invoiceData['issue_date'] = Carbon::now()->format('Y-m-d');
        }

        if (empty($invoiceData['due_date'])) {
            $issueDate = Carbon::parse($invoiceData['issue_date']);
            $invoiceData['due_date'] = $issueDate->addDays(30)->format('Y-m-d');
        }

        // Crear factura
        $invoice = Invoice::create($invoiceData);

        // Crear items de factura
        if ($dto->hasItems()) {
            $this->createInvoiceItems($invoice, $dto->getItems());
        }

        return $invoice->fresh(['items']);
    }

    /**
     * Crear items de factura
     * 
     * @param Invoice $invoice
     * @param array $items
     * @return void
     */
    private function createInvoiceItems(Invoice $invoice, array $items): void
    {
        foreach ($items as $itemData) {
            $itemDto = InvoiceItemDTO::fromRequest($itemData, $invoice->id);
            
            if ($itemDto->isValid()) {
                InvoiceItem::create($itemDto->toArray());
            } else {
                Log::warning('Item de factura inválido omitido', [
                    'invoice_id' => $invoice->id,
                    'item_data' => $itemData,
                    'errors' => $itemDto->getValidationErrors()
                ]);
            }
        }
    }

    /**
     * Calcular impuestos para una factura
     * 
     * @param float $subtotal
     * @param float $tax1Rate
     * @param float $tax2Rate
     * @return array
     */
    public function calculateTaxes(float $subtotal, float $tax1Rate = 0, float $tax2Rate = 0): array
    {
        $tax1Amount = $subtotal * ($tax1Rate / 100);
        $tax2Amount = $subtotal * ($tax2Rate / 100);
        $totalAmount = $subtotal + $tax1Amount + $tax2Amount;

        return [
            'subtotal' => $subtotal,
            'tax1_rate' => $tax1Rate,
            'tax1_amount' => round($tax1Amount, 2),
            'tax2_rate' => $tax2Rate,
            'tax2_amount' => round($tax2Amount, 2),
            'total_amount' => round($totalAmount, 2),
        ];
    }

    /**
     * Validar datos de cliente para facturación
     * 
     * @param int $clientId
     * @return array
     */
    public function validateClientForInvoicing(int $clientId): array
    {
        // Esta validación se puede expandir según las reglas de negocio
        $client = \App\Domains\Users\Models\User::find($clientId);

        if (!$client) {
            return [
                'valid' => false,
                'errors' => ['Cliente no encontrado']
            ];
        }

        if ($client->status !== 'active') {
            return [
                'valid' => false,
                'errors' => ['Cliente inactivo']
            ];
        }

        return [
            'valid' => true,
            'errors' => []
        ];
    }
}

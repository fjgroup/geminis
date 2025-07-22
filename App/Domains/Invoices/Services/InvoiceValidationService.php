<?php

namespace App\Domains\Invoices\Services;

use App\Domains\Invoices\Models\Invoice;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para validaciones de negocio de facturas
 * 
 * Extrae la lógica de validación del modelo Invoice
 */
class InvoiceValidationService
{
    /**
     * Verificar si una factura puede ser cancelada como nuevo servicio
     * 
     * @param Invoice $invoice
     * @return array
     */
    public function canInvoiceBeCancelledAsNewService(Invoice $invoice): array
    {
        try {
            $canCancel = true;
            $reasons = [];

            // Verificar estado de la factura
            if ($invoice->status !== 'unpaid') {
                $canCancel = false;
                $reasons[] = 'Solo se pueden cancelar facturas no pagadas';
            }

            // Cargar items y servicios relacionados si no están cargados
            $invoice->loadMissing(['items', 'items.clientService']);

            foreach ($invoice->items as $item) {
                // Verificar tipos de item permitidos
                if (!in_array($item->item_type, ['new_service', 'web-hosting'])) {
                    $canCancel = false;
                    $reasons[] = "El item '{$item->description}' no es de tipo nuevo servicio";
                    continue;
                }

                // Verificar estado del servicio asociado
                if ($item->client_service_id !== null && $item->clientService) {
                    $serviceStatus = $item->clientService->status;
                    
                    if (in_array($serviceStatus, ['active', 'suspended'])) {
                        $canCancel = false;
                        $reasons[] = "El servicio '{$item->description}' ya está activo o suspendido";
                    }
                }
            }

            Log::info('InvoiceValidationService - Verificación de cancelación', [
                'invoice_id' => $invoice->id,
                'can_cancel' => $canCancel,
                'reasons_count' => count($reasons)
            ]);

            return [
                'can_cancel' => $canCancel,
                'reasons' => $reasons,
                'invoice_status' => $invoice->status,
                'items_count' => $invoice->items->count()
            ];

        } catch (\Exception $e) {
            Log::error('InvoiceValidationService - Error verificando cancelación', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id
            ]);

            return [
                'can_cancel' => false,
                'reasons' => ['Error al verificar el estado de la factura'],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validar si una factura puede ser pagada
     * 
     * @param Invoice $invoice
     * @return array
     */
    public function canInvoiceBePaid(Invoice $invoice): array
    {
        try {
            $canPay = true;
            $reasons = [];

            // Verificar estado
            if ($invoice->status !== 'unpaid') {
                $canPay = false;
                $reasons[] = 'La factura no está en estado "no pagada"';
            }

            // Verificar fecha de vencimiento
            if ($invoice->due_date && $invoice->due_date->isPast()) {
                // No impedir el pago, pero advertir
                $reasons[] = 'La factura está vencida desde ' . $invoice->due_date->format('d/m/Y');
            }

            // Verificar monto
            if ($invoice->total_amount <= 0) {
                $canPay = false;
                $reasons[] = 'El monto de la factura debe ser mayor a cero';
            }

            // Verificar que tenga items
            $invoice->loadMissing('items');
            if ($invoice->items->isEmpty()) {
                $canPay = false;
                $reasons[] = 'La factura no tiene items';
            }

            return [
                'can_pay' => $canPay,
                'reasons' => $reasons,
                'total_amount' => $invoice->total_amount,
                'status' => $invoice->status,
                'is_overdue' => $invoice->due_date ? $invoice->due_date->isPast() : false
            ];

        } catch (\Exception $e) {
            Log::error('InvoiceValidationService - Error validando pago', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id
            ]);

            return [
                'can_pay' => false,
                'reasons' => ['Error al validar la factura para pago'],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validar datos de factura antes de crear
     * 
     * @param array $data
     * @return array
     */
    public function validateInvoiceData(array $data): array
    {
        $errors = [];

        // Validar cliente
        if (empty($data['client_id'])) {
            $errors['client_id'] = 'El cliente es requerido';
        }

        // Validar fechas
        if (empty($data['issue_date'])) {
            $errors['issue_date'] = 'La fecha de emisión es requerida';
        }

        if (empty($data['due_date'])) {
            $errors['due_date'] = 'La fecha de vencimiento es requerida';
        }

        // Validar montos
        if (!isset($data['subtotal']) || $data['subtotal'] < 0) {
            $errors['subtotal'] = 'El subtotal debe ser mayor o igual a cero';
        }

        if (!isset($data['total_amount']) || $data['total_amount'] <= 0) {
            $errors['total_amount'] = 'El monto total debe ser mayor a cero';
        }

        // Validar moneda
        if (empty($data['currency_code'])) {
            $errors['currency_code'] = 'El código de moneda es requerido';
        }

        // Validar items si están presentes
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $index => $item) {
                if (empty($item['description'])) {
                    $errors["items.{$index}.description"] = 'La descripción del item es requerida';
                }

                if (!isset($item['amount']) || $item['amount'] <= 0) {
                    $errors["items.{$index}.amount"] = 'El monto del item debe ser mayor a cero';
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Verificar si una factura puede ser editada
     * 
     * @param Invoice $invoice
     * @return array
     */
    public function canInvoiceBeEdited(Invoice $invoice): array
    {
        try {
            $canEdit = true;
            $reasons = [];

            // No se pueden editar facturas pagadas
            if ($invoice->status === 'paid') {
                $canEdit = false;
                $reasons[] = 'No se pueden editar facturas pagadas';
            }

            // No se pueden editar facturas canceladas
            if ($invoice->status === 'cancelled') {
                $canEdit = false;
                $reasons[] = 'No se pueden editar facturas canceladas';
            }

            // Verificar si tiene transacciones pendientes
            $invoice->loadMissing('transactions');
            $pendingTransactions = $invoice->transactions->where('status', 'pending');
            
            if ($pendingTransactions->isNotEmpty()) {
                $canEdit = false;
                $reasons[] = 'La factura tiene transacciones pendientes';
            }

            return [
                'can_edit' => $canEdit,
                'reasons' => $reasons,
                'status' => $invoice->status,
                'has_pending_transactions' => $pendingTransactions->isNotEmpty()
            ];

        } catch (\Exception $e) {
            Log::error('InvoiceValidationService - Error verificando edición', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id
            ]);

            return [
                'can_edit' => false,
                'reasons' => ['Error al verificar el estado de la factura'],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verificar integridad de datos de factura
     * 
     * @param Invoice $invoice
     * @return array
     */
    public function validateInvoiceIntegrity(Invoice $invoice): array
    {
        try {
            $issues = [];
            $warnings = [];

            // Verificar que el total coincida con la suma de items
            $invoice->loadMissing('items');
            $itemsTotal = $invoice->items->sum('amount');
            
            if (abs($itemsTotal - $invoice->subtotal) > 0.01) {
                $issues[] = "El subtotal ({$invoice->subtotal}) no coincide con la suma de items ({$itemsTotal})";
            }

            // Verificar cálculo de impuestos
            $calculatedTax1 = $invoice->subtotal * ($invoice->tax1_rate / 100);
            if (abs($calculatedTax1 - $invoice->tax1_amount) > 0.01) {
                $warnings[] = "El cálculo del impuesto 1 puede ser incorrecto";
            }

            // Verificar total
            $calculatedTotal = $invoice->subtotal + $invoice->tax1_amount + $invoice->tax2_amount;
            if (abs($calculatedTotal - $invoice->total_amount) > 0.01) {
                $issues[] = "El total calculado ({$calculatedTotal}) no coincide con el total almacenado ({$invoice->total_amount})";
            }

            // Verificar fechas
            if ($invoice->due_date && $invoice->issue_date && $invoice->due_date->lt($invoice->issue_date)) {
                $warnings[] = "La fecha de vencimiento es anterior a la fecha de emisión";
            }

            return [
                'is_valid' => empty($issues),
                'issues' => $issues,
                'warnings' => $warnings,
                'items_total' => $itemsTotal,
                'calculated_total' => $calculatedTotal ?? 0
            ];

        } catch (\Exception $e) {
            Log::error('InvoiceValidationService - Error validando integridad', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id
            ]);

            return [
                'is_valid' => false,
                'issues' => ['Error al validar la integridad de la factura'],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener resumen de validación completo
     * 
     * @param Invoice $invoice
     * @return array
     */
    public function getCompleteValidationSummary(Invoice $invoice): array
    {
        return [
            'can_be_paid' => $this->canInvoiceBePaid($invoice),
            'can_be_cancelled' => $this->canInvoiceBeCancelledAsNewService($invoice),
            'can_be_edited' => $this->canInvoiceBeEdited($invoice),
            'integrity_check' => $this->validateInvoiceIntegrity($invoice)
        ];
    }
}

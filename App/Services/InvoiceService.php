<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

/**
 * Class InvoiceService
 * 
 * Servicio para el manejo de facturas
 * Centraliza la lógica de negocio relacionada con facturas
 */
class InvoiceService
{
    /**
     * Crear una nueva factura
     */
    public function createInvoice(array $invoiceData): Invoice
    {
        try {
            // Generar número de factura si no se proporciona
            if (!isset($invoiceData['invoice_number'])) {
                $invoiceData['invoice_number'] = $this->generateInvoiceNumber();
            }

            // Establecer fechas por defecto
            if (!isset($invoiceData['issue_date'])) {
                $invoiceData['issue_date'] = now();
            }

            if (!isset($invoiceData['due_date'])) {
                $invoiceData['due_date'] = now()->addDays(30);
            }

            $invoice = Invoice::create($invoiceData);

            Log::info('InvoiceService - Factura creada', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'client_id' => $invoice->client_id,
                'total_amount' => $invoice->total_amount
            ]);

            return $invoice;

        } catch (\Exception $e) {
            Log::error('InvoiceService - Error creando factura', [
                'error' => $e->getMessage(),
                'invoice_data' => $invoiceData
            ]);

            throw $e;
        }
    }

    /**
     * Agregar item a una factura
     */
    public function addInvoiceItem(Invoice $invoice, array $itemData): InvoiceItem
    {
        try {
            $itemData['invoice_id'] = $invoice->id;

            // Calcular precio total si no se proporciona
            if (!isset($itemData['total_price'])) {
                $itemData['total_price'] = $itemData['quantity'] * $itemData['unit_price'];
            }

            $item = InvoiceItem::create($itemData);

            // Recalcular totales de la factura
            $this->recalculateInvoiceTotals($invoice);

            Log::info('InvoiceService - Item agregado a factura', [
                'invoice_id' => $invoice->id,
                'item_id' => $item->id,
                'description' => $item->description,
                'total_price' => $item->total_price
            ]);

            return $item;

        } catch (\Exception $e) {
            Log::error('InvoiceService - Error agregando item a factura', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id,
                'item_data' => $itemData
            ]);

            throw $e;
        }
    }

    /**
     * Actualizar estado de factura
     */
    public function updateInvoiceStatus(Invoice $invoice, string $newStatus): bool
    {
        try {
            $validStatuses = ['draft', 'unpaid', 'paid', 'overdue', 'cancelled', 'pending_activation', 'pending_confirmation'];

            if (!in_array($newStatus, $validStatuses)) {
                throw new \InvalidArgumentException("Estado de factura inválido: {$newStatus}");
            }

            $oldStatus = $invoice->status;
            $success = $invoice->update(['status' => $newStatus]);

            if ($success) {
                Log::info('InvoiceService - Estado de factura actualizado', [
                    'invoice_id' => $invoice->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus
                ]);

                // Ejecutar acciones específicas según el nuevo estado
                $this->handleStatusChange($invoice, $oldStatus, $newStatus);
            }

            return $success;

        } catch (\Exception $e) {
            Log::error('InvoiceService - Error actualizando estado de factura', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id,
                'new_status' => $newStatus
            ]);

            return false;
        }
    }

    /**
     * Marcar factura como pagada
     */
    public function markAsPaid(Invoice $invoice, array $paymentData = []): bool
    {
        try {
            $updateData = [
                'status' => 'paid',
                'paid_at' => now()
            ];

            if (isset($paymentData['payment_method'])) {
                $updateData['payment_method'] = $paymentData['payment_method'];
            }

            if (isset($paymentData['transaction_id'])) {
                $updateData['transaction_id'] = $paymentData['transaction_id'];
            }

            $success = $invoice->update($updateData);

            if ($success) {
                Log::info('InvoiceService - Factura marcada como pagada', [
                    'invoice_id' => $invoice->id,
                    'payment_data' => $paymentData
                ]);

                // Activar servicios asociados si existen
                $this->activateAssociatedServices($invoice);
            }

            return $success;

        } catch (\Exception $e) {
            Log::error('InvoiceService - Error marcando factura como pagada', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id
            ]);

            return false;
        }
    }

    /**
     * Obtener facturas de un cliente
     */
    public function getClientInvoices(User $client, array $filters = []): Collection
    {
        try {
            $query = $client->invoices();

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['date_from'])) {
                $query->where('issue_date', '>=', $filters['date_from']);
            }

            if (isset($filters['date_to'])) {
                $query->where('issue_date', '<=', $filters['date_to']);
            }

            return $query->orderBy('issue_date', 'desc')->get();

        } catch (\Exception $e) {
            Log::error('InvoiceService - Error obteniendo facturas del cliente', [
                'error' => $e->getMessage(),
                'client_id' => $client->id
            ]);

            return collect();
        }
    }

    /**
     * Calcular totales de factura
     */
    public function calculateInvoiceTotals(Invoice $invoice): array
    {
        try {
            $items = $invoice->items;
            
            $subtotal = $items->sum('total_price');
            $taxAmount = $invoice->tax_amount ?? 0;
            $total = $subtotal + $taxAmount;

            return [
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $total
            ];

        } catch (\Exception $e) {
            Log::error('InvoiceService - Error calculando totales', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id
            ]);

            return [
                'subtotal' => 0,
                'tax_amount' => 0,
                'total_amount' => 0
            ];
        }
    }

    /**
     * Generar número de factura único
     */
    private function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $count = Invoice::whereYear('created_at', $year)->count() + 1;
        
        return 'INV-' . $year . '-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Recalcular totales de factura
     */
    private function recalculateInvoiceTotals(Invoice $invoice): void
    {
        $totals = $this->calculateInvoiceTotals($invoice);
        
        $invoice->update([
            'subtotal' => $totals['subtotal'],
            'total_amount' => $totals['total_amount']
        ]);
    }

    /**
     * Manejar cambios de estado
     */
    private function handleStatusChange(Invoice $invoice, string $oldStatus, string $newStatus): void
    {
        // Lógica específica para cambios de estado
        if ($newStatus === 'paid' && $oldStatus !== 'paid') {
            $this->activateAssociatedServices($invoice);
        }

        if ($newStatus === 'cancelled') {
            $this->cancelAssociatedServices($invoice);
        }
    }

    /**
     * Activar servicios asociados a la factura
     */
    private function activateAssociatedServices(Invoice $invoice): void
    {
        try {
            // Buscar servicios del cliente que estén pendientes
            $client = $invoice->client;
            $pendingServices = $client->clientServices()
                ->where('status', 'pending')
                ->get();

            foreach ($pendingServices as $service) {
                $service->update(['status' => 'active']);
                
                Log::info('InvoiceService - Servicio activado', [
                    'service_id' => $service->id,
                    'invoice_id' => $invoice->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error('InvoiceService - Error activando servicios', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id
            ]);
        }
    }

    /**
     * Cancelar servicios asociados a la factura
     */
    private function cancelAssociatedServices(Invoice $invoice): void
    {
        try {
            $client = $invoice->client;
            $services = $client->clientServices()
                ->where('status', 'pending')
                ->get();

            foreach ($services as $service) {
                $service->update(['status' => 'cancelled']);
                
                Log::info('InvoiceService - Servicio cancelado', [
                    'service_id' => $service->id,
                    'invoice_id' => $invoice->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error('InvoiceService - Error cancelando servicios', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id
            ]);
        }
    }
}

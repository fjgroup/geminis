<?php

namespace App\Domains\Invoices\Application\Services;

use App\Domains\Invoices\Infrastructure\Persistence\Models\Invoice;
use App\Domains\BillingAndPayments\Infrastructure\Persistence\Models\Transaction;
use App\Domains\Users\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Servicio de gestión general de facturas
 * 
 * Aplica Single Responsibility Principle - gestión y operaciones de facturas
 * Ubicado en Application layer según arquitectura hexagonal
 */
class InvoiceManagementService
{
    /**
     * Obtener facturas con filtros
     */
    public function getInvoices(array $filters = [], int $perPage = 10): array
    {
        try {
            $query = Invoice::with(['client', 'items']);

            // Aplicar filtros
            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['client_id'])) {
                $query->where('client_id', $filters['client_id']);
            }

            if (isset($filters['date_from'])) {
                $query->where('issue_date', '>=', $filters['date_from']);
            }

            if (isset($filters['date_to'])) {
                $query->where('issue_date', '<=', $filters['date_to']);
            }

            // Ordenar por prioridad: pending_confirmation primero, luego por fecha
            $invoices = $query->orderByRaw("CASE WHEN status = 'pending_confirmation' THEN 0 ELSE 1 END ASC")
                ->orderByRaw("CASE WHEN status = 'pending_confirmation' THEN updated_at ELSE issue_date END ASC")
                ->orderBy('id', 'desc')
                ->paginate($perPage);

            return [
                'success' => true,
                'data' => $invoices,
                'message' => 'Facturas obtenidas exitosamente'
            ];

        } catch (\Exception $e) {
            Log::error('Error obteniendo facturas', [
                'filters' => $filters,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'data' => collect()->paginate($perPage),
                'message' => 'Error al obtener las facturas'
            ];
        }
    }

    /**
     * Obtener facturas pendientes de confirmación
     */
    public function getPendingFundAdditions(): array
    {
        try {
            $pendingFundAdditions = Transaction::with(['client', 'paymentMethod'])
                ->where('type', 'credit_added')
                ->where('status', 'pending')
                ->orderBy('transaction_date', 'asc')
                ->get();

            return [
                'success' => true,
                'data' => $pendingFundAdditions,
                'count' => $pendingFundAdditions->count()
            ];

        } catch (\Exception $e) {
            Log::error('Error obteniendo adiciones de fondos pendientes', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'data' => collect(),
                'count' => 0
            ];
        }
    }

    /**
     * Marcar factura como pagada
     */
    public function markAsPaid(int $invoiceId, array $paymentData = []): array
    {
        try {
            DB::beginTransaction();

            $invoice = Invoice::findOrFail($invoiceId);

            if ($invoice->status === 'paid') {
                return [
                    'success' => false,
                    'message' => 'La factura ya está marcada como pagada',
                    'invoice' => $invoice
                ];
            }

            $invoice->update([
                'status' => 'paid',
                'paid_date' => $paymentData['paid_date'] ?? Carbon::now()->toDateString(),
            ]);

            // Registrar transacción si se proporciona información de pago
            if (!empty($paymentData)) {
                $this->createPaymentTransaction($invoice, $paymentData);
            }

            DB::commit();

            Log::info('Factura marcada como pagada', [
                'invoice_id' => $invoice->id,
                'marked_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => 'Factura marcada como pagada exitosamente',
                'invoice' => $invoice->fresh()
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error marcando factura como pagada', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
                'marked_by' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Error al marcar la factura como pagada',
                'invoice' => null
            ];
        }
    }

    /**
     * Cancelar factura
     */
    public function cancelInvoice(int $invoiceId, string $reason = null): array
    {
        try {
            $invoice = Invoice::findOrFail($invoiceId);

            if ($invoice->status === 'paid') {
                return [
                    'success' => false,
                    'message' => 'No se puede cancelar una factura pagada',
                    'invoice' => $invoice
                ];
            }

            $notes = $invoice->notes_to_client ?? '';
            $cancellationNote = "\n[" . now()->format('Y-m-d H:i:s') . "] Factura cancelada por " . auth()->user()->name;
            if ($reason) {
                $cancellationNote .= ". Razón: {$reason}";
            }

            $invoice->update([
                'status' => 'cancelled',
                'notes_to_client' => $notes . $cancellationNote
            ]);

            Log::info('Factura cancelada', [
                'invoice_id' => $invoice->id,
                'reason' => $reason,
                'cancelled_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => 'Factura cancelada exitosamente',
                'invoice' => $invoice->fresh()
            ];

        } catch (\Exception $e) {
            Log::error('Error cancelando factura', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
                'cancelled_by' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Error al cancelar la factura',
                'invoice' => null
            ];
        }
    }

    /**
     * Obtener facturas vencidas
     */
    public function getOverdueInvoices(): array
    {
        try {
            $overdueInvoices = Invoice::where('status', 'unpaid')
                ->where('due_date', '<', Carbon::now()->toDateString())
                ->with(['client', 'items'])
                ->orderBy('due_date', 'asc')
                ->get();

            return [
                'success' => true,
                'invoices' => $overdueInvoices,
                'count' => $overdueInvoices->count()
            ];

        } catch (\Exception $e) {
            Log::error('Error obteniendo facturas vencidas', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'invoices' => collect(),
                'count' => 0
            ];
        }
    }

    /**
     * Obtener estadísticas de facturas
     */
    public function getInvoiceStats(): array
    {
        try {
            $totalInvoices = Invoice::count();
            $paidInvoices = Invoice::where('status', 'paid')->count();
            $unpaidInvoices = Invoice::where('status', 'unpaid')->count();
            $overdueInvoices = Invoice::where('status', 'unpaid')
                ->where('due_date', '<', Carbon::now()->toDateString())
                ->count();
            $totalRevenue = Invoice::where('status', 'paid')->sum('total_amount');
            $pendingRevenue = Invoice::where('status', 'unpaid')->sum('total_amount');

            return [
                'total_invoices' => $totalInvoices,
                'paid_invoices' => $paidInvoices,
                'unpaid_invoices' => $unpaidInvoices,
                'overdue_invoices' => $overdueInvoices,
                'total_revenue' => $totalRevenue,
                'pending_revenue' => $pendingRevenue,
            ];

        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas de facturas', [
                'error' => $e->getMessage()
            ]);

            return [
                'total_invoices' => 0,
                'paid_invoices' => 0,
                'unpaid_invoices' => 0,
                'overdue_invoices' => 0,
                'total_revenue' => 0,
                'pending_revenue' => 0,
            ];
        }
    }

    /**
     * Buscar facturas por criterios
     */
    public function searchInvoices(array $criteria): array
    {
        try {
            $query = Invoice::with(['client', 'items']);

            if (isset($criteria['invoice_number'])) {
                $query->where('invoice_number', 'like', '%' . $criteria['invoice_number'] . '%');
            }

            if (isset($criteria['client_email'])) {
                $query->whereHas('client', function ($q) use ($criteria) {
                    $q->where('email', 'like', '%' . $criteria['client_email'] . '%');
                });
            }

            if (isset($criteria['amount_min'])) {
                $query->where('total_amount', '>=', $criteria['amount_min']);
            }

            if (isset($criteria['amount_max'])) {
                $query->where('total_amount', '<=', $criteria['amount_max']);
            }

            $invoices = $query->orderBy('created_at', 'desc')->get();

            return [
                'success' => true,
                'invoices' => $invoices,
                'count' => $invoices->count()
            ];

        } catch (\Exception $e) {
            Log::error('Error buscando facturas', [
                'criteria' => $criteria,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'invoices' => collect(),
                'count' => 0
            ];
        }
    }

    /**
     * Obtener facturas de un cliente
     */
    public function getClientInvoices(int $clientId, array $filters = []): array
    {
        try {
            $query = Invoice::where('client_id', $clientId)
                ->with(['items']);

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            $invoices = $query->orderBy('issue_date', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            return [
                'success' => true,
                'invoices' => $invoices,
                'count' => $invoices->count()
            ];

        } catch (\Exception $e) {
            Log::error('Error obteniendo facturas del cliente', [
                'client_id' => $clientId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'invoices' => collect(),
                'count' => 0
            ];
        }
    }

    /**
     * Crear transacción de pago
     */
    private function createPaymentTransaction(Invoice $invoice, array $paymentData): void
    {
        Transaction::create([
            'client_id' => $invoice->client_id,
            'invoice_id' => $invoice->id,
            'type' => 'payment',
            'status' => 'completed',
            'amount' => $invoice->total_amount,
            'currency_code' => $invoice->currency_code,
            'transaction_date' => $paymentData['paid_date'] ?? Carbon::now(),
            'description' => "Pago de factura #{$invoice->invoice_number}",
            'payment_method_id' => $paymentData['payment_method_id'] ?? null,
            'gateway_transaction_id' => $paymentData['gateway_transaction_id'] ?? null,
        ]);
    }

    /**
     * Validar si una factura puede ser modificada
     */
    public function canModifyInvoice(Invoice $invoice): bool
    {
        // No se pueden modificar facturas pagadas o canceladas
        return !in_array($invoice->status, ['paid', 'cancelled', 'refunded']);
    }

    /**
     * Obtener próximas facturas a vencer
     */
    public function getUpcomingDueInvoices(int $days = 7): array
    {
        try {
            $dueDate = Carbon::now()->addDays($days);

            $invoices = Invoice::where('status', 'unpaid')
                ->where('due_date', '<=', $dueDate->toDateString())
                ->where('due_date', '>=', Carbon::now()->toDateString())
                ->with(['client', 'items'])
                ->orderBy('due_date', 'asc')
                ->get();

            return [
                'success' => true,
                'invoices' => $invoices,
                'count' => $invoices->count()
            ];

        } catch (\Exception $e) {
            Log::error('Error obteniendo próximas facturas a vencer', [
                'days' => $days,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'invoices' => collect(),
                'count' => 0
            ];
        }
    }

    /**
     * Actualizar factura
     */
    public function updateInvoice(int $invoiceId, array $updateData): array
    {
        try {
            $invoice = Invoice::findOrFail($invoiceId);

            if (!$this->canModifyInvoice($invoice)) {
                return [
                    'success' => false,
                    'message' => 'Esta factura no puede ser modificada',
                    'invoice' => $invoice
                ];
            }

            $invoice->update($updateData);

            Log::info('Factura actualizada', [
                'invoice_id' => $invoice->id,
                'updated_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => 'Factura actualizada exitosamente',
                'invoice' => $invoice->fresh()
            ];

        } catch (\Exception $e) {
            Log::error('Error actualizando factura', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
                'updated_by' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Error al actualizar la factura',
                'invoice' => null
            ];
        }
    }
}

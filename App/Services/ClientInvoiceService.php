<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ServiceProvisioningService;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Servicio para la gestión de facturas del cliente
 * 
 * Extrae la lógica de negocio del ClientInvoiceController aplicando el SRP
 */
class ClientInvoiceService
{
    public function __construct(
        private ServiceProvisioningService $serviceProvisioningService
    ) {}

    /**
     * Obtener facturas del cliente con paginación
     */
    public function getClientInvoices(User $client, int $perPage = 10): array
    {
        try {
            $invoices = $client->invoices()
                ->with('items')
                ->orderByDesc('issue_date')
                ->orderByDesc('id')
                ->paginate($perPage);

            return [
                'success' => true,
                'data' => $invoices
            ];

        } catch (\Exception $e) {
            Log::error('ClientInvoiceService - Error obteniendo facturas del cliente', [
                'error' => $e->getMessage(),
                'client_id' => $client->id
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Error al obtener las facturas: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener detalles de una factura específica
     */
    public function getInvoiceDetails(Invoice $invoice): array
    {
        try {
            $invoice->load([
                'client',
                'reseller',
                'items.clientService',
                'items.product',
                'items.productPricing.billingCycle',
                'transactions' => function ($query) {
                    $query->where('type', 'payment')
                          ->with('paymentMethod')
                          ->latest('transaction_date')
                          ->limit(1);
                }
            ]);

            return [
                'success' => true,
                'data' => $invoice
            ];

        } catch (\Exception $e) {
            Log::error('ClientInvoiceService - Error obteniendo detalles de factura', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Error al obtener detalles de la factura: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Procesar pago con balance de cuenta
     */
    public function payWithBalance(User $client, Invoice $invoice): array
    {
        Log::info('ClientInvoiceService - Attempting to pay with balance', [
            'invoice_id' => $invoice->id,
            'client_id' => $client->id,
            'invoice_amount' => $invoice->total_amount,
            'client_balance' => $client->balance
        ]);

        // Validaciones
        if ($invoice->status !== 'unpaid') {
            return [
                'success' => false,
                'message' => 'Esta factura no está pendiente de pago.'
            ];
        }

        if ($client->balance < $invoice->total_amount) {
            return [
                'success' => false,
                'message' => 'Saldo insuficiente para pagar esta factura.'
            ];
        }

        DB::beginTransaction();
        try {
            // Descontar del balance del cliente
            $client->decrement('balance', $invoice->total_amount);

            // Crear transacción
            $transaction = Transaction::create([
                'invoice_id' => $invoice->id,
                'client_id' => $client->id,
                'reseller_id' => $client->reseller_id,
                'gateway_slug' => 'balance',
                'gateway_transaction_id' => 'BAL-' . strtoupper(Str::random(10)),
                'type' => 'payment',
                'amount' => $invoice->total_amount,
                'currency_code' => $invoice->currency_code,
                'status' => 'completed',
                'description' => "Pago de Factura #{$invoice->invoice_number} usando saldo de cuenta.",
                'transaction_date' => Carbon::now(),
            ]);

            // Actualizar estado de la factura
            $invoice->update([
                'status' => 'paid',
                'paid_date' => Carbon::now()
            ]);

            // Aprovisionar servicios
            $this->serviceProvisioningService->provisionServicesForInvoice($invoice);

            // Extender fechas de vencimiento para renovaciones
            $this->extendRenewalDueDates($invoice);

            DB::commit();

            Log::info('ClientInvoiceService - Payment with balance completed successfully', [
                'invoice_id' => $invoice->id,
                'client_id' => $client->id,
                'transaction_id' => $transaction->id
            ]);

            return [
                'success' => true,
                'data' => [
                    'invoice' => $invoice->fresh(),
                    'transaction' => $transaction
                ],
                'message' => 'Factura pagada exitosamente usando tu saldo de cuenta. Los servicios están siendo aprovisionados.'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('ClientInvoiceService - Error processing payment with balance', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id,
                'client_id' => $client->id,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Ocurrió un error al procesar tu pago. Por favor, inténtalo de nuevo.'
            ];
        }
    }

    /**
     * Cancelar reporte de pago manual
     */
    public function cancelPaymentReport(Invoice $invoice): array
    {
        if ($invoice->status !== 'pending_confirmation') {
            Log::warning('ClientInvoiceService - Attempt to cancel payment report for non-pending invoice', [
                'invoice_id' => $invoice->id,
                'current_status' => $invoice->status
            ]);

            return [
                'success' => false,
                'message' => 'Este reporte de pago no puede ser anulado porque la factura no está pendiente de confirmación.'
            ];
        }

        DB::beginTransaction();
        try {
            // Buscar la transacción de pago pendiente más reciente
            $paymentTransaction = $invoice->transactions()
                ->where('type', 'payment')
                ->where('status', 'pending')
                ->latest('created_at')
                ->first();

            if ($paymentTransaction) {
                $paymentTransaction->update(['status' => 'client_cancelled']);
                
                Log::info('ClientInvoiceService - Payment transaction cancelled by client', [
                    'transaction_id' => $paymentTransaction->id,
                    'invoice_id' => $invoice->id
                ]);
            } else {
                Log::warning('ClientInvoiceService - No pending payment transaction found for pending_confirmation invoice', [
                    'invoice_id' => $invoice->id
                ]);
            }

            // Actualizar estado de la factura
            $invoice->update(['status' => 'unpaid']);

            DB::commit();

            return [
                'success' => true,
                'data' => $invoice->fresh(),
                'message' => 'Tu reporte de pago ha sido anulado. La factura está nuevamente marcada como no pagada.'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('ClientInvoiceService - Error cancelling payment report', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id
            ]);

            return [
                'success' => false,
                'message' => 'Ocurrió un error al intentar anular tu reporte de pago. Por favor, inténtalo de nuevo.'
            ];
        }
    }

    /**
     * Solicitar cancelación de factura y servicios asociados
     */
    public function requestInvoiceCancellation(Invoice $invoice): array
    {
        DB::beginTransaction();
        try {
            // Actualizar estado de la factura
            $invoice->update(['status' => 'cancelled']);

            // Cargar items y servicios relacionados
            $invoice->loadMissing('items.clientService');

            $cancelledServices = 0;
            foreach ($invoice->items as $item) {
                if ($item->clientService && $item->clientService->status === 'pending') {
                    $item->clientService->update(['status' => 'cancelled']);
                    $cancelledServices++;
                }
            }

            DB::commit();

            Log::info('ClientInvoiceService - Invoice cancellation requested', [
                'invoice_id' => $invoice->id,
                'cancelled_services' => $cancelledServices
            ]);

            return [
                'success' => true,
                'data' => $invoice->fresh(),
                'message' => 'La factura y los servicios pendientes asociados han sido cancelados.'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('ClientInvoiceService - Error requesting invoice cancellation', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id
            ]);

            return [
                'success' => false,
                'message' => 'Ocurrió un error al intentar cancelar la factura.'
            ];
        }
    }

    /**
     * Extender fechas de vencimiento para renovaciones
     */
    private function extendRenewalDueDates(Invoice $invoice): void
    {
        try {
            $invoice->loadMissing(['items.clientService.billingCycle']);

            foreach ($invoice->items as $item) {
                if ($item->item_type === 'renewal' && $item->clientService) {
                    $clientService = $item->clientService;

                    if ($clientService->billingCycle) {
                        try {
                            $extended = $clientService->extendRenewal($clientService->billingCycle);
                            
                            if ($extended) {
                                Log::info('ClientInvoiceService - Successfully extended due date', [
                                    'client_service_id' => $clientService->id,
                                    'invoice_id' => $invoice->id
                                ]);
                            } else {
                                Log::error('ClientInvoiceService - Failed to save ClientService after extending due date', [
                                    'client_service_id' => $clientService->id,
                                    'invoice_id' => $invoice->id
                                ]);
                            }
                        } catch (\Exception $e) {
                            Log::error('ClientInvoiceService - Exception extending due date', [
                                'error' => $e->getMessage(),
                                'client_service_id' => $clientService->id,
                                'invoice_id' => $invoice->id
                            ]);
                        }
                    } else {
                        Log::warning('ClientInvoiceService - ClientService missing billingCycle relationship', [
                            'client_service_id' => $clientService->id,
                            'invoice_item_id' => $item->id,
                            'invoice_id' => $invoice->id
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('ClientInvoiceService - Error in extendRenewalDueDates', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id
            ]);
        }
    }

    /**
     * Obtener estadísticas de facturas del cliente
     */
    public function getClientInvoiceStats(User $client): array
    {
        try {
            $stats = [
                'total_invoices' => $client->invoices()->count(),
                'unpaid_invoices' => $client->invoices()->where('status', 'unpaid')->count(),
                'paid_invoices' => $client->invoices()->where('status', 'paid')->count(),
                'pending_confirmation' => $client->invoices()->where('status', 'pending_confirmation')->count(),
                'total_amount_unpaid' => $client->invoices()->where('status', 'unpaid')->sum('total_amount'),
                'total_amount_paid' => $client->invoices()->where('status', 'paid')->sum('total_amount'),
            ];

            return [
                'success' => true,
                'data' => $stats
            ];

        } catch (\Exception $e) {
            Log::error('ClientInvoiceService - Error getting invoice stats', [
                'error' => $e->getMessage(),
                'client_id' => $client->id
            ]);

            return [
                'success' => false,
                'data' => [],
                'message' => 'Error al obtener estadísticas de facturas'
            ];
        }
    }

    /**
     * Verificar si el cliente puede pagar con balance
     */
    public function canPayWithBalance(User $client, Invoice $invoice): bool
    {
        return $invoice->status === 'unpaid' && $client->balance >= $invoice->total_amount;
    }

    /**
     * Obtener facturas por estado
     */
    public function getInvoicesByStatus(User $client, string $status, int $perPage = 10): LengthAwarePaginator
    {
        return $client->invoices()
            ->where('status', $status)
            ->with('items')
            ->orderByDesc('issue_date')
            ->orderByDesc('id')
            ->paginate($perPage);
    }
}

<?php

namespace App\Domains\Invoices\Services;

use App\Domains\Invoices\Models\Invoice;
use App\Domains\Invoices\Models\InvoiceItem;
use App\Models\Transaction;
use App\Domains\Users\Models\User;
use App\Services\ServiceProvisioningService;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Servicio para la gestión de facturas en el panel de administración
 *
 * Extrae la lógica de negocio del AdminInvoiceController aplicando el SRP
 */
class InvoiceManagementService
{
    public function __construct(
        private ServiceProvisioningService $serviceProvisioningService
    ) {}

    /**
     * Obtener facturas paginadas con filtros (OPTIMIZADO)
     */
    public function getInvoices(array $filters = [], int $perPage = 10): array
    {
        try {
            // Cache key basado en filtros
            $cacheKey = 'invoices_' . md5(serialize($filters)) . '_' . $perPage;

            return app(\App\Services\PerformanceOptimizationService::class)->cacheOperation(
                $cacheKey,
                function () use ($filters, $perPage) {
                    $query = Invoice::query();

                    // Eager loading optimizado - solo cargar lo necesario
                    $query->with([
                        'client:id,name,email',
                        'items:id,invoice_id,description,amount'
                    ]);

                    // Aplicar filtros con índices optimizados
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

                    // Ordenamiento especial: pending_confirmation primero
                    $invoices = $query
                        ->orderByRaw("CASE WHEN status = 'pending_confirmation' THEN 0 ELSE 1 END ASC")
                        ->orderByRaw("CASE WHEN status = 'pending_confirmation' THEN updated_at ELSE issue_date END ASC")
                        ->orderBy('id', 'desc')
                        ->paginate($perPage);

                    return [
                        'success' => true,
                        'data' => $invoices,
                        'message' => 'Facturas obtenidas exitosamente'
                    ];
                },
                900 // 15 minutos de cache
            );

        } catch (\Exception $e) {
            Log::error('InvoiceManagementService - Error obteniendo facturas', [
                'error' => $e->getMessage(),
                'filters' => $filters
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Error al obtener las facturas: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener transacciones de adición de fondos pendientes
     */
    public function getPendingFundAdditions(): Collection
    {
        try {
            return Transaction::with(['client', 'paymentMethod'])
                ->where('type', 'credit_added')
                ->where('status', 'pending')
                ->orderBy('transaction_date', 'asc')
                ->get();

        } catch (\Exception $e) {
            Log::error('InvoiceManagementService - Error obteniendo adiciones de fondos pendientes', [
                'error' => $e->getMessage()
            ]);

            return collect();
        }
    }

    /**
     * Obtener datos para el formulario de creación de factura
     */
    public function getFormData(): array
    {
        try {
            $clients = User::select('id', 'name', 'email')
                ->orderBy('name')
                ->get();

            $possibleStatuses = ['unpaid', 'paid', 'cancelled'];
            $defaultCurrency = 'USD';
            $currencies = ['USD', 'EUR', 'GBP'];

            return [
                'success' => true,
                'data' => [
                    'clients' => $clients,
                    'possibleStatuses' => $possibleStatuses,
                    'defaultCurrency' => $defaultCurrency,
                    'currencies' => $currencies
                ]
            ];

        } catch (\Exception $e) {
            Log::error('InvoiceManagementService - Error obteniendo datos del formulario', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'data' => [],
                'message' => 'Error al obtener datos del formulario: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Crear una nueva factura manual
     */
    public function createManualInvoice(array $data): array
    {
        DB::beginTransaction();
        try {
            // Calcular subtotal
            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $subtotal += ($item['quantity'] * $item['unit_price']);
            }
            $totalAmount = $subtotal;

            // Crear factura
            $invoice = Invoice::create([
                'client_id' => $data['client_id'],
                'reseller_id' => User::find($data['client_id'])->reseller_id ?? null,
                'invoice_number' => 'MINV-' . strtoupper(Str::random(10)),
                'issue_date' => Carbon::parse($data['issue_date']),
                'due_date' => Carbon::parse($data['due_date']),
                'paid_date' => ($data['status'] === 'paid') ? Carbon::now() : null,
                'status' => $data['status'],
                'subtotal' => $subtotal,
                'total_amount' => $totalAmount,
                'currency_code' => $data['currency_code'],
                'notes_to_client' => $data['notes_to_client'] ?? null,
            ]);

            // Crear items de la factura
            foreach ($data['items'] as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            DB::commit();

            Log::info('InvoiceManagementService - Factura manual creada', [
                'invoice_id' => $invoice->id,
                'client_id' => $data['client_id']
            ]);

            return [
                'success' => true,
                'data' => $invoice,
                'message' => 'Factura creada exitosamente'
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('InvoiceManagementService - Error creando factura manual', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Error al crear la factura: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Actualizar una factura existente
     */
    public function updateInvoice(Invoice $invoice, array $data): array
    {
        DB::beginTransaction();
        try {
            // Actualizar datos básicos de la factura
            $updateData = [
                'issue_date' => Carbon::parse($data['issue_date']),
                'due_date' => Carbon::parse($data['due_date']),
                'status' => $data['status'],
                'notes_to_client' => $data['notes_to_client'] ?? null,
            ];

            // Si se marca como pagada, establecer fecha de pago
            if ($data['status'] === 'paid' && !$invoice->paid_date) {
                $updateData['paid_date'] = Carbon::now();
            } elseif ($data['status'] !== 'paid') {
                $updateData['paid_date'] = null;
            }

            $invoice->update($updateData);

            DB::commit();

            Log::info('InvoiceManagementService - Factura actualizada', [
                'invoice_id' => $invoice->id,
                'changes' => $updateData
            ]);

            return [
                'success' => true,
                'data' => $invoice->fresh(),
                'message' => 'Factura actualizada exitosamente'
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('InvoiceManagementService - Error actualizando factura', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id,
                'data' => $data
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Error al actualizar la factura: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Eliminar una factura
     */
    public function deleteInvoice(Invoice $invoice): array
    {
        DB::beginTransaction();
        try {
            // Verificar que la factura se pueda eliminar
            if ($invoice->status === 'paid') {
                return [
                    'success' => false,
                    'message' => 'No se puede eliminar una factura pagada'
                ];
            }

            // Eliminar items relacionados
            $invoice->items()->delete();

            // Eliminar la factura
            $invoice->delete();

            DB::commit();

            Log::info('InvoiceManagementService - Factura eliminada', [
                'invoice_id' => $invoice->id
            ]);

            return [
                'success' => true,
                'message' => 'Factura eliminada exitosamente'
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('InvoiceManagementService - Error eliminando factura', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id
            ]);

            return [
                'success' => false,
                'message' => 'Error al eliminar la factura: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Registrar transacción manual para una factura
     */
    public function storeManualTransaction(Invoice $invoice, array $data): array
    {
        DB::beginTransaction();
        try {
            // Crear la transacción
            $transaction = Transaction::create([
                'client_id' => $invoice->client_id,
                'invoice_id' => $invoice->id,
                'order_id' => $invoice->order_id,
                'payment_method_id' => $data['payment_method_id'],
                'gateway_slug' => 'manual_admin',
                'gateway_transaction_id' => $data['reference_number'],
                'amount' => $data['amount'],
                'currency_code' => $invoice->currency_code,
                'status' => 'completed',
                'type' => 'payment',
                'transaction_date' => Carbon::parse($data['payment_date']),
                'paid_date' => Carbon::parse($data['payment_date']),
                'description' => "Pago manual registrado por administrador para factura #{$invoice->invoice_number}",
                'fees_amount' => 0,
            ]);

            // Actualizar estado de la factura si el pago cubre el total
            if ($data['amount'] >= $invoice->total_amount) {
                $invoice->update([
                    'status' => 'paid',
                    'paid_date' => Carbon::parse($data['payment_date'])
                ]);

                // Activar servicios asociados
                $this->activateInvoiceServices($invoice);
            }

            DB::commit();

            Log::info('InvoiceManagementService - Transacción manual registrada', [
                'invoice_id' => $invoice->id,
                'transaction_id' => $transaction->id,
                'amount' => $data['amount']
            ]);

            return [
                'success' => true,
                'data' => $transaction,
                'message' => 'Pago manual registrado exitosamente'
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('InvoiceManagementService - Error registrando transacción manual', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id,
                'data' => $data
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Error al registrar el pago manual: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Activar servicios asociados a una factura
     */
    public function activateInvoiceServices(Invoice $invoice): array
    {
        try {
            $activatedServices = [];

            // Buscar servicios en los items de la factura
            foreach ($invoice->items as $item) {
                if ($item->client_service_id) {
                    $service = $item->clientService;
                    if ($service && $service->status === 'pending') {
                        // Usar el servicio de aprovisionamiento
                        $result = $this->serviceProvisioningService->activateService($service);

                        if ($result['success']) {
                            $activatedServices[] = $service->id;
                        }
                    }
                }
            }

            Log::info('InvoiceManagementService - Servicios activados', [
                'invoice_id' => $invoice->id,
                'activated_services' => $activatedServices
            ]);

            return [
                'success' => true,
                'data' => $activatedServices,
                'message' => count($activatedServices) . ' servicios activados'
            ];

        } catch (\Exception $e) {
            Log::error('InvoiceManagementService - Error activando servicios', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id
            ]);

            return [
                'success' => false,
                'data' => [],
                'message' => 'Error al activar servicios: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener datos para mostrar una factura específica
     */
    public function getInvoiceShowData(Invoice $invoice): array
    {
        try {
            $invoice->load([
                'client',
                'reseller',
                'items.product',
                'items.clientService',
                'transactions' => function ($query) {
                    $query->where('status', 'completed')
                          ->with('paymentMethod')
                          ->latest('transaction_date');
                }
            ]);

            // Determinar si mostrar formulario de pago manual
            $showManualPaymentForm = in_array($invoice->status, ['unpaid', 'pending_confirmation']);

            // Datos para el formulario de pago manual
            $manualPaymentFormData = [];
            if ($showManualPaymentForm) {
                $manualPaymentFormData = [
                    'payment_methods' => \App\Models\PaymentMethod::where('is_active', true)
                        ->orderBy('name')
                        ->get(['id', 'name']),
                    'remaining_amount' => $invoice->total_amount - $invoice->transactions()
                        ->where('status', 'completed')
                        ->sum('amount')
                ];
            }

            return [
                'success' => true,
                'data' => [
                    'invoice' => $invoice,
                    'showManualPaymentForm' => $showManualPaymentForm,
                    'manualPaymentFormData' => $manualPaymentFormData
                ]
            ];

        } catch (\Exception $e) {
            Log::error('InvoiceManagementService - Error obteniendo datos de factura', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Error al obtener datos de la factura: ' . $e->getMessage()
            ];
        }
    }
}

<?php

namespace App\Domains\ClientServices\Services;

use App\Models\BillingCycle;
use App\Domains\ClientServices\Models\ClientService;
use App\Domains\Invoices\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para la lógica de negocio de ClientService
 * 
 * Extrae la lógica de negocio del modelo ClientService
 */
class ClientServiceBusinessService
{
    /**
     * Extender la fecha de vencimiento de un servicio
     * 
     * @param ClientService $clientService
     * @param BillingCycle $billingCycle
     * @return array
     */
    public function extendServiceRenewal(ClientService $clientService, BillingCycle $billingCycle): array
    {
        try {
            DB::beginTransaction();

            // Asegurar que next_due_date es una instancia de Carbon
            $currentDueDate = Carbon::parse($clientService->next_due_date);

            // Agregar los días del ciclo de facturación
            $newDueDate = $currentDueDate->addDays($billingCycle->days);

            // Actualizar la fecha
            $clientService->next_due_date = $newDueDate;
            $saved = $clientService->save();

            if ($saved) {
                DB::commit();
                
                Log::info('ClientServiceBusinessService - Fecha de vencimiento extendida exitosamente', [
                    'client_service_id' => $clientService->id,
                    'old_due_date' => $currentDueDate->format('Y-m-d'),
                    'new_due_date' => $newDueDate->format('Y-m-d'),
                    'billing_cycle_days' => $billingCycle->days
                ]);

                return [
                    'success' => true,
                    'data' => [
                        'old_due_date' => $currentDueDate,
                        'new_due_date' => $newDueDate,
                        'days_extended' => $billingCycle->days
                    ],
                    'message' => 'Fecha de vencimiento extendida exitosamente'
                ];
            }

            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al guardar la nueva fecha de vencimiento'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('ClientServiceBusinessService - Error extendiendo fecha de vencimiento', [
                'error' => $e->getMessage(),
                'client_service_id' => $clientService->id,
                'billing_cycle_id' => $billingCycle->id
            ]);

            return [
                'success' => false,
                'message' => 'Error al extender la fecha de vencimiento: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener valores posibles de enum para una columna
     * 
     * @param string $column
     * @return array
     */
    public function getEnumValues(string $column): array
    {
        try {
            $clientService = new ClientService();
            $tableName = $clientService->getTable();
            $connectionName = $clientService->getConnectionName();

            $columnDetails = DB::connection($connectionName)
                ->select("SHOW COLUMNS FROM `{$tableName}` WHERE Field = '{$column}'");

            if (empty($columnDetails) || !isset($columnDetails[0]->Type)) {
                Log::error('ClientServiceBusinessService - Columna no encontrada', [
                    'column' => $column,
                    'table' => $tableName
                ]);
                return [];
            }

            $type = $columnDetails[0]->Type;

            if (!preg_match('/^enum\((.*)\)$/', $type, $matches)) {
                Log::warning('ClientServiceBusinessService - Columna no es ENUM', [
                    'column' => $column,
                    'table' => $tableName,
                    'type' => $type
                ]);
                return [];
            }

            if (!isset($matches[1])) {
                Log::error('ClientServiceBusinessService - No se pudieron parsear valores ENUM', [
                    'column' => $column,
                    'table' => $tableName,
                    'type' => $type
                ]);
                return [];
            }

            $enum = [];
            foreach (explode(',', $matches[1]) as $value) {
                $v = trim($value, "'");
                $enum[] = [
                    'value' => $v,
                    'label' => ucfirst(str_replace('_', ' ', $v))
                ];
            }

            return $enum;

        } catch (\Exception $e) {
            Log::error('ClientServiceBusinessService - Error obteniendo valores ENUM', [
                'error' => $e->getMessage(),
                'column' => $column
            ]);
            return [];
        }
    }

    /**
     * Verificar si un servicio puede ser renovado
     * 
     * @param ClientService $clientService
     * @return array
     */
    public function canServiceBeRenewed(ClientService $clientService): array
    {
        try {
            $canRenew = true;
            $reasons = [];

            // Verificar estado del servicio
            if (!in_array($clientService->status, ['active', 'suspended'])) {
                $canRenew = false;
                $reasons[] = 'El servicio debe estar activo o suspendido para ser renovado';
            }

            // Verificar si tiene fecha de vencimiento
            if (!$clientService->next_due_date) {
                $canRenew = false;
                $reasons[] = 'El servicio no tiene fecha de vencimiento configurada';
            }

            // Verificar si ya está vencido por mucho tiempo (más de 30 días)
            if ($clientService->next_due_date) {
                $dueDate = Carbon::parse($clientService->next_due_date);
                $daysPastDue = now()->diffInDays($dueDate, false);
                
                if ($daysPastDue < -30) {
                    $canRenew = false;
                    $reasons[] = 'El servicio está vencido por más de 30 días';
                }
            }

            // Verificar si tiene ciclo de facturación
            if (!$clientService->billing_cycle_id) {
                $canRenew = false;
                $reasons[] = 'El servicio no tiene ciclo de facturación configurado';
            }

            return [
                'can_renew' => $canRenew,
                'reasons' => $reasons,
                'next_due_date' => $clientService->next_due_date,
                'status' => $clientService->status
            ];

        } catch (\Exception $e) {
            Log::error('ClientServiceBusinessService - Error verificando renovación', [
                'error' => $e->getMessage(),
                'client_service_id' => $clientService->id
            ]);

            return [
                'can_renew' => false,
                'reasons' => ['Error al verificar el estado del servicio'],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Calcular próxima fecha de vencimiento
     * 
     * @param ClientService $clientService
     * @param BillingCycle|null $billingCycle
     * @return array
     */
    public function calculateNextDueDate(ClientService $clientService, ?BillingCycle $billingCycle = null): array
    {
        try {
            $billingCycle = $billingCycle ?? $clientService->billingCycle;
            
            if (!$billingCycle) {
                return [
                    'success' => false,
                    'message' => 'No se encontró ciclo de facturación'
                ];
            }

            $currentDueDate = $clientService->next_due_date 
                ? Carbon::parse($clientService->next_due_date)
                : now();

            $nextDueDate = $currentDueDate->copy()->addDays($billingCycle->days);

            return [
                'success' => true,
                'data' => [
                    'current_due_date' => $currentDueDate,
                    'next_due_date' => $nextDueDate,
                    'days_to_add' => $billingCycle->days,
                    'billing_cycle' => $billingCycle->name
                ]
            ];

        } catch (\Exception $e) {
            Log::error('ClientServiceBusinessService - Error calculando próxima fecha', [
                'error' => $e->getMessage(),
                'client_service_id' => $clientService->id
            ]);

            return [
                'success' => false,
                'message' => 'Error calculando próxima fecha de vencimiento'
            ];
        }
    }

    /**
     * Obtener servicios próximos a vencer
     * 
     * @param int $days Días de anticipación
     * @return array
     */
    public function getServicesNearExpiration(int $days = 7): array
    {
        try {
            $targetDate = now()->addDays($days);
            
            $services = ClientService::where('status', 'active')
                ->where('next_due_date', '<=', $targetDate)
                ->where('next_due_date', '>=', now())
                ->with(['client', 'product', 'billingCycle'])
                ->orderBy('next_due_date')
                ->get();

            return [
                'success' => true,
                'data' => $services,
                'count' => $services->count(),
                'target_date' => $targetDate->format('Y-m-d')
            ];

        } catch (\Exception $e) {
            Log::error('ClientServiceBusinessService - Error obteniendo servicios próximos a vencer', [
                'error' => $e->getMessage(),
                'days' => $days
            ]);

            return [
                'success' => false,
                'message' => 'Error obteniendo servicios próximos a vencer'
            ];
        }
    }

    /**
     * Obtener estadísticas de servicios
     * 
     * @param int|null $clientId
     * @return array
     */
    public function getServiceStats(?int $clientId = null): array
    {
        try {
            $query = ClientService::query();
            
            if ($clientId) {
                $query->where('client_id', $clientId);
            }

            $stats = [
                'total' => $query->count(),
                'active' => $query->where('status', 'active')->count(),
                'suspended' => $query->where('status', 'suspended')->count(),
                'pending' => $query->where('status', 'pending')->count(),
                'cancelled' => $query->where('status', 'cancelled')->count(),
                'expired' => $query->where('next_due_date', '<', now())->where('status', 'active')->count(),
                'expiring_soon' => $query->where('next_due_date', '<=', now()->addDays(7))->where('status', 'active')->count()
            ];

            return [
                'success' => true,
                'data' => $stats
            ];

        } catch (\Exception $e) {
            Log::error('ClientServiceBusinessService - Error obteniendo estadísticas', [
                'error' => $e->getMessage(),
                'client_id' => $clientId
            ]);

            return [
                'success' => false,
                'message' => 'Error obteniendo estadísticas de servicios'
            ];
        }
    }
}

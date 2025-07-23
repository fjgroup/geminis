<?php

namespace App\Domains\ClientServices\Application\Services;

use App\Domains\ClientServices\Infrastructure\Persistence\Models\ClientService;
use App\Domains\Users\Infrastructure\Persistence\Models\User;
use App\Domains\Products\Infrastructure\Persistence\Models\Product;
use App\Domains\Products\Infrastructure\Persistence\Models\ProductPricing;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Servicio de gestión general de servicios de cliente
 * 
 * Aplica Single Responsibility Principle - gestión y actualización de servicios
 * Ubicado en Application layer según arquitectura hexagonal
 */
class ClientServiceManagementService
{
    /**
     * Actualizar información de servicio de cliente
     */
    public function updateClientService(int $serviceId, array $updateData): array
    {
        try {
            DB::beginTransaction();

            $service = ClientService::findOrFail($serviceId);

            // Validar permisos de actualización
            $permissionCheck = $this->validateUpdatePermissions($service);
            if (!$permissionCheck['allowed']) {
                return [
                    'success' => false,
                    'message' => $permissionCheck['message'],
                    'service' => null
                ];
            }

            // Preparar datos para actualizar
            $updateFields = [];

            if (isset($updateData['domain_name'])) {
                $updateFields['domain_name'] = $updateData['domain_name'];
            }

            if (isset($updateData['status'])) {
                $updateFields['status'] = $updateData['status'];
            }

            if (isset($updateData['next_due_date'])) {
                $updateFields['next_due_date'] = $updateData['next_due_date'];
            }

            if (isset($updateData['billing_amount'])) {
                $updateFields['billing_amount'] = $updateData['billing_amount'];
            }

            if (isset($updateData['notes'])) {
                $updateFields['notes'] = $updateData['notes'];
            }

            if (isset($updateData['username'])) {
                $updateFields['username'] = $updateData['username'];
            }

            if (isset($updateData['password_encrypted'])) {
                $updateFields['password_encrypted'] = $updateData['password_encrypted'];
            }

            // Actualizar servicio
            $service->update($updateFields);

            DB::commit();

            Log::info('Servicio de cliente actualizado exitosamente', [
                'service_id' => $service->id,
                'updated_fields' => array_keys($updateFields),
                'updated_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => 'Servicio actualizado exitosamente',
                'service' => $service->fresh(['client', 'product', 'productPricing', 'billingCycle'])
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error actualizando servicio de cliente', [
                'service_id' => $serviceId,
                'error' => $e->getMessage(),
                'updated_by' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Error al actualizar el servicio: ' . $e->getMessage(),
                'service' => null
            ];
        }
    }

    /**
     * Cambiar estado de servicio
     */
    public function changeServiceStatus(int $serviceId, string $newStatus): array
    {
        try {
            $service = ClientService::findOrFail($serviceId);

            // Validar estado válido
            $validStatuses = ['active', 'suspended', 'terminated', 'pending', 'pending_configuration'];
            if (!in_array($newStatus, $validStatuses)) {
                return [
                    'success' => false,
                    'message' => 'Estado no válido',
                    'service' => null
                ];
            }

            // Validar permisos
            if (!$this->canChangeServiceStatus($service)) {
                return [
                    'success' => false,
                    'message' => 'No tienes permisos para cambiar el estado de este servicio',
                    'service' => null
                ];
            }

            $oldStatus = $service->status;
            $service->update(['status' => $newStatus]);

            Log::info('Estado de servicio cambiado', [
                'service_id' => $service->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => "Estado cambiado de {$oldStatus} a {$newStatus}",
                'service' => $service->fresh()
            ];

        } catch (\Exception $e) {
            Log::error('Error cambiando estado de servicio', [
                'service_id' => $serviceId,
                'new_status' => $newStatus,
                'error' => $e->getMessage(),
                'changed_by' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Error al cambiar el estado del servicio',
                'service' => null
            ];
        }
    }

    /**
     * Suspender servicio
     */
    public function suspendService(int $serviceId, string $reason = null): array
    {
        try {
            $service = ClientService::findOrFail($serviceId);

            if (!$this->canSuspendService($service)) {
                return [
                    'success' => false,
                    'message' => 'No tienes permisos para suspender este servicio',
                    'service' => null
                ];
            }

            $oldStatus = $service->status;
            $notes = $service->notes ?? '';
            $suspensionNote = "\n[" . now()->format('Y-m-d H:i:s') . "] Servicio suspendido por " . auth()->user()->name;
            if ($reason) {
                $suspensionNote .= ". Razón: {$reason}";
            }

            $service->update([
                'status' => 'suspended',
                'notes' => $notes . $suspensionNote
            ]);

            Log::info('Servicio suspendido', [
                'service_id' => $service->id,
                'old_status' => $oldStatus,
                'reason' => $reason,
                'suspended_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => 'Servicio suspendido exitosamente',
                'service' => $service->fresh()
            ];

        } catch (\Exception $e) {
            Log::error('Error suspendiendo servicio', [
                'service_id' => $serviceId,
                'error' => $e->getMessage(),
                'suspended_by' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Error al suspender el servicio',
                'service' => null
            ];
        }
    }

    /**
     * Reactivar servicio suspendido
     */
    public function unsuspendService(int $serviceId): array
    {
        try {
            $service = ClientService::findOrFail($serviceId);

            if ($service->status !== 'suspended') {
                return [
                    'success' => false,
                    'message' => 'El servicio no está suspendido',
                    'service' => null
                ];
            }

            if (!$this->canUnsuspendService($service)) {
                return [
                    'success' => false,
                    'message' => 'No tienes permisos para reactivar este servicio',
                    'service' => null
                ];
            }

            $notes = $service->notes ?? '';
            $reactivationNote = "\n[" . now()->format('Y-m-d H:i:s') . "] Servicio reactivado por " . auth()->user()->name;

            $service->update([
                'status' => 'active',
                'notes' => $notes . $reactivationNote
            ]);

            Log::info('Servicio reactivado', [
                'service_id' => $service->id,
                'reactivated_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => 'Servicio reactivado exitosamente',
                'service' => $service->fresh()
            ];

        } catch (\Exception $e) {
            Log::error('Error reactivando servicio', [
                'service_id' => $serviceId,
                'error' => $e->getMessage(),
                'reactivated_by' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Error al reactivar el servicio',
                'service' => null
            ];
        }
    }

    /**
     * Obtener servicios de un cliente
     */
    public function getClientServices(int $clientId, array $filters = []): array
    {
        try {
            $query = ClientService::where('client_id', $clientId)
                ->with(['product', 'productPricing', 'billingCycle']);

            // Aplicar filtros
            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['product_id'])) {
                $query->where('product_id', $filters['product_id']);
            }

            $services = $query->orderBy('created_at', 'desc')->get();

            return [
                'success' => true,
                'services' => $services,
                'count' => $services->count()
            ];

        } catch (\Exception $e) {
            Log::error('Error obteniendo servicios del cliente', [
                'client_id' => $clientId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'services' => collect(),
                'count' => 0
            ];
        }
    }

    /**
     * Validar permisos de actualización
     */
    private function validateUpdatePermissions(ClientService $service): array
    {
        $currentUser = auth()->user();

        if (!$currentUser) {
            return ['allowed' => false, 'message' => 'Usuario no autenticado'];
        }

        // Admins pueden actualizar cualquier servicio
        if ($currentUser->role === 'admin') {
            return ['allowed' => true, 'message' => ''];
        }

        // Resellers pueden actualizar servicios de sus clientes
        if ($currentUser->role === 'reseller' && $service->reseller_id === $currentUser->id) {
            return ['allowed' => true, 'message' => ''];
        }

        // Los clientes pueden actualizar ciertos campos de sus servicios
        if ($currentUser->role === 'client' && $service->client_id === $currentUser->id) {
            return ['allowed' => true, 'message' => ''];
        }

        return ['allowed' => false, 'message' => 'No tienes permisos para actualizar este servicio'];
    }

    /**
     * Verificar si se puede cambiar el estado del servicio
     */
    private function canChangeServiceStatus(ClientService $service): bool
    {
        $currentUser = auth()->user();

        if (!$currentUser) {
            return false;
        }

        // Admins pueden cambiar cualquier estado
        if ($currentUser->role === 'admin') {
            return true;
        }

        // Resellers pueden cambiar el estado de servicios de sus clientes
        if ($currentUser->role === 'reseller' && $service->reseller_id === $currentUser->id) {
            return true;
        }

        return false;
    }

    /**
     * Verificar si se puede suspender el servicio
     */
    private function canSuspendService(ClientService $service): bool
    {
        return $this->canChangeServiceStatus($service);
    }

    /**
     * Verificar si se puede reactivar el servicio
     */
    private function canUnsuspendService(ClientService $service): bool
    {
        return $this->canChangeServiceStatus($service);
    }
}

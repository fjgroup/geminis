<?php

namespace App\Domains\Users\Services;

use App\Contracts\User\UserDeletionServiceInterface;
use App\Domains\Users\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class UserDeletionService
 *
 * Servicio responsable de manejar la eliminación de usuarios y sus datos relacionados
 * Cumple con el Principio de Responsabilidad Única (SRP)
 * Implementa UserDeletionServiceInterface (DIP)
 */
class UserDeletionService implements UserDeletionServiceInterface
{
    /**
     * Eliminar un usuario y manejar todas las dependencias
     *
     * @param User $user
     * @return array
     */
    public function deleteUser(User $user): array
    {
        try {
            DB::beginTransaction();

            // Manejar eliminación según el rol
            $result = match($user->role) {
                'reseller' => $this->deleteReseller($user),
                'client' => $this->deleteClient($user),
                'admin' => $this->deleteAdmin($user),
                default => $this->deleteGenericUser($user)
            };

            if ($result['success']) {
                DB::commit();
                Log::info('Usuario eliminado exitosamente', [
                    'user_id' => $user->id,
                    'role' => $user->role,
                    'email' => $user->email
                ]);
            } else {
                DB::rollBack();
            }

            return $result;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error eliminando usuario', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error interno al eliminar el usuario',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Eliminar un revendedor y manejar sus clientes
     *
     * @param User $reseller
     * @return array
     */
    private function deleteReseller(User $reseller): array
    {
        // Verificar si tiene clientes activos
        $activeClients = $reseller->clients()->where('status', 'active')->count();

        if ($activeClients > 0) {
            return [
                'success' => false,
                'message' => "No se puede eliminar el revendedor. Tiene {$activeClients} clientes activos.",
                'active_clients' => $activeClients
            ];
        }

        // Desasociar clientes inactivos
        $reseller->clients()->update(['reseller_id' => null]);

        // Eliminar perfil de revendedor
        if ($reseller->resellerProfile) {
            $reseller->resellerProfile->delete();
        }

        // Eliminar el usuario
        $reseller->delete();

        return [
            'success' => true,
            'message' => 'Revendedor eliminado exitosamente',
            'clients_unassigned' => $reseller->clients()->count()
        ];
    }

    /**
     * Eliminar un cliente y manejar sus servicios
     *
     * @param User $client
     * @return array
     */
    private function deleteClient(User $client): array
    {
        // Verificar si tiene servicios activos
        $activeServices = $client->clientServices()->where('status', 'active')->count();

        if ($activeServices > 0) {
            return [
                'success' => false,
                'message' => "No se puede eliminar el cliente. Tiene {$activeServices} servicios activos.",
                'active_services' => $activeServices
            ];
        }

        // Marcar servicios como cancelados
        $client->clientServices()->update(['status' => 'cancelled']);

        // Marcar facturas pendientes como canceladas
        $client->invoices()->where('status', 'pending')->update(['status' => 'cancelled']);

        // Eliminar el usuario
        $client->delete();

        return [
            'success' => true,
            'message' => 'Cliente eliminado exitosamente',
            'services_cancelled' => $client->clientServices()->count()
        ];
    }

    /**
     * Eliminar un administrador (con restricciones)
     *
     * @param User $admin
     * @return array
     */
    private function deleteAdmin(User $admin): array
    {
        // Verificar que no sea el último administrador
        $adminCount = User::where('role', 'admin')->where('id', '!=', $admin->id)->count();

        if ($adminCount === 0) {
            return [
                'success' => false,
                'message' => 'No se puede eliminar el último administrador del sistema'
            ];
        }

        // Eliminar el administrador
        $admin->delete();

        return [
            'success' => true,
            'message' => 'Administrador eliminado exitosamente'
        ];
    }

    /**
     * Eliminar un usuario genérico
     *
     * @param User $user
     * @return array
     */
    private function deleteGenericUser(User $user): array
    {
        $user->delete();

        return [
            'success' => true,
            'message' => 'Usuario eliminado exitosamente'
        ];
    }

    /**
     * Verificar si un usuario puede ser eliminado
     *
     * @param User $user
     * @return array
     */
    public function canUserBeDeleted(User $user): array
    {
        switch ($user->role) {
            case 'reseller':
                $activeClients = $user->clients()->where('status', 'active')->count();
                return [
                    'can_delete' => $activeClients === 0,
                    'reason' => $activeClients > 0 ? "Tiene {$activeClients} clientes activos" : null
                ];

            case 'client':
                $activeServices = $user->clientServices()->where('status', 'active')->count();
                return [
                    'can_delete' => $activeServices === 0,
                    'reason' => $activeServices > 0 ? "Tiene {$activeServices} servicios activos" : null
                ];

            case 'admin':
                $adminCount = User::where('role', 'admin')->where('id', '!=', $user->id)->count();
                return [
                    'can_delete' => $adminCount > 0,
                    'reason' => $adminCount === 0 ? 'Es el último administrador del sistema' : null
                ];

            default:
                return ['can_delete' => true, 'reason' => null];
        }
    }
}

<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * Class UserService
 * 
 * Servicio para el manejo de usuarios
 * Centraliza la lógica de negocio relacionada con usuarios
 */
class UserService
{
    /**
     * Crear un nuevo usuario
     */
    public function createUser(array $userData): ?User
    {
        try {
            // Validar datos requeridos
            $this->validateUserData($userData);

            $user = User::create($userData);

            Log::info('UserService - Usuario creado exitosamente', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role
            ]);

            return $user;

        } catch (\Exception $e) {
            Log::error('UserService - Error creando usuario', [
                'error' => $e->getMessage(),
                'email' => $userData['email'] ?? 'N/A'
            ]);

            return null;
        }
    }

    /**
     * Actualizar perfil de usuario
     */
    public function updateProfile(User $user, array $data): bool
    {
        try {
            // Campos permitidos para actualización
            $allowedFields = [
                'name', 'company_name', 'phone', 'address', 
                'city', 'state', 'postal_code', 'country'
            ];

            $updateData = array_intersect_key($data, array_flip($allowedFields));

            $success = $user->update($updateData);

            if ($success) {
                Log::info('UserService - Perfil actualizado', [
                    'user_id' => $user->id,
                    'updated_fields' => array_keys($updateData)
                ]);
            }

            return $success;

        } catch (\Exception $e) {
            Log::error('UserService - Error actualizando perfil', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);

            return false;
        }
    }

    /**
     * Cambiar contraseña de usuario
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): array
    {
        try {
            // Verificar contraseña actual
            if (!Hash::check($currentPassword, $user->password)) {
                return [
                    'success' => false,
                    'message' => 'La contraseña actual es incorrecta'
                ];
            }

            // Actualizar contraseña
            $user->update([
                'password' => Hash::make($newPassword)
            ]);

            Log::info('UserService - Contraseña cambiada', [
                'user_id' => $user->id
            ]);

            return [
                'success' => true,
                'message' => 'Contraseña actualizada exitosamente'
            ];

        } catch (\Exception $e) {
            Log::error('UserService - Error cambiando contraseña', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);

            return [
                'success' => false,
                'message' => 'Error al cambiar la contraseña'
            ];
        }
    }

    /**
     * Migrar carrito anónimo cuando el usuario se loguea
     */
    public function migrateAnonymousCart(User $user): bool
    {
        try {
            // Esta funcionalidad se delegará al CartService
            app(CartService::class)->migrateCartOnLogin($user->id);

            Log::info('UserService - Carrito migrado al login', [
                'user_id' => $user->id
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('UserService - Error migrando carrito', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);

            return false;
        }
    }

    /**
     * Obtener estadísticas del usuario
     */
    public function getUserStats(User $user): array
    {
        try {
            $stats = [
                'services_count' => 0,
                'active_services_count' => 0,
                'unpaid_invoices_count' => 0,
                'pending_invoices_count' => 0,
                'account_balance' => 0.0,
                'last_login' => null
            ];

            if ($user->role === 'client') {
                $stats['services_count'] = $user->clientServices()->count();
                $stats['active_services_count'] = $user->clientServices()
                    ->where('status', 'active')->count();
                $stats['unpaid_invoices_count'] = $user->invoices()
                    ->where('status', 'unpaid')->count();
                $stats['pending_invoices_count'] = $user->invoices()
                    ->whereIn('status', ['pending_activation', 'pending_confirmation'])
                    ->count();
                $stats['account_balance'] = $user->balance ?? 0.0;
            }

            if ($user->role === 'reseller') {
                $stats['clients_count'] = $user->clients()->count();
                $stats['active_clients_count'] = $user->clients()
                    ->where('status', 'active')->count();
            }

            $stats['last_login'] = $user->last_login_at;

            return $stats;

        } catch (\Exception $e) {
            Log::error('UserService - Error obteniendo estadísticas', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);

            return [];
        }
    }

    /**
     * Buscar usuarios por criterios
     */
    public function searchUsers(array $criteria, int $limit = 20): array
    {
        try {
            $query = User::query();

            if (isset($criteria['role'])) {
                $query->where('role', $criteria['role']);
            }

            if (isset($criteria['search'])) {
                $searchTerm = $criteria['search'];
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('company_name', 'LIKE', "%{$searchTerm}%");
                });
            }

            if (isset($criteria['status'])) {
                $query->where('status', $criteria['status']);
            }

            if (isset($criteria['reseller_id'])) {
                $query->where('reseller_id', $criteria['reseller_id']);
            }

            $users = $query->orderBy('name')
                ->select('id', 'name', 'email', 'company_name', 'role', 'status')
                ->limit($limit)
                ->get()
                ->map(function ($user) {
                    return [
                        'value' => $user->id,
                        'label' => $this->formatUserLabel($user),
                        'role' => $user->role,
                        'status' => $user->status
                    ];
                });

            return $users->toArray();

        } catch (\Exception $e) {
            Log::error('UserService - Error buscando usuarios', [
                'error' => $e->getMessage(),
                'criteria' => $criteria
            ]);

            return [];
        }
    }

    /**
     * Activar/desactivar usuario
     */
    public function toggleUserStatus(User $user): bool
    {
        try {
            $newStatus = $user->status === 'active' ? 'inactive' : 'active';
            
            $success = $user->update(['status' => $newStatus]);

            if ($success) {
                Log::info('UserService - Estado de usuario cambiado', [
                    'user_id' => $user->id,
                    'old_status' => $user->status,
                    'new_status' => $newStatus,
                    'changed_by' => Auth::id()
                ]);
            }

            return $success;

        } catch (\Exception $e) {
            Log::error('UserService - Error cambiando estado de usuario', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);

            return false;
        }
    }

    /**
     * Validar datos de usuario
     */
    private function validateUserData(array $userData): void
    {
        $required = ['name', 'email', 'password', 'role'];
        
        foreach ($required as $field) {
            if (!isset($userData[$field]) || empty($userData[$field])) {
                throw new \InvalidArgumentException("Campo requerido faltante: {$field}");
            }
        }

        if (!in_array($userData['role'], ['admin', 'reseller', 'client'])) {
            throw new \InvalidArgumentException("Rol inválido: {$userData['role']}");
        }

        if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Email inválido: {$userData['email']}");
        }
    }

    /**
     * Formatear etiqueta de usuario para selects
     */
    private function formatUserLabel(User $user): string
    {
        $label = $user->name;
        
        if ($user->company_name) {
            $label .= " ({$user->company_name})";
        } else {
            $label .= " ({$user->email})";
        }

        return $label;
    }
}

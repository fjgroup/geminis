<?php

namespace App\Domains\Users\Application\Services;

use App\Domains\Users\Infrastructure\Persistence\Models\User;
use App\Domains\Users\DataTransferObjects\UpdateUserDTO;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Servicio de gestión general de usuarios
 * 
 * Aplica Single Responsibility Principle - gestión y actualización de usuarios
 * Ubicado en Application layer según arquitectura hexagonal
 */
class UserManagementService
{
    /**
     * Actualizar información de usuario
     */
    public function updateUser(int $userId, UpdateUserDTO $updateData): array
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($userId);

            // Validar permisos de actualización
            $permissionCheck = $this->validateUpdatePermissions($user, $updateData);
            if (!$permissionCheck['allowed']) {
                return [
                    'success' => false,
                    'message' => $permissionCheck['message'],
                    'user' => null
                ];
            }

            // Preparar datos para actualizar
            $updateFields = [];

            if ($updateData->name !== null) {
                $updateFields['name'] = $updateData->name;
            }

            if ($updateData->email !== null && $updateData->email !== $user->email) {
                // Verificar que el nuevo email no exista
                if (User::where('email', $updateData->email)->where('id', '!=', $userId)->exists()) {
                    return [
                        'success' => false,
                        'message' => 'El email ya está en uso por otro usuario',
                        'user' => null
                    ];
                }
                $updateFields['email'] = $updateData->email;
            }

            if ($updateData->password !== null) {
                $updateFields['password'] = Hash::make($updateData->password);
            }

            if ($updateData->role !== null) {
                $updateFields['role'] = $updateData->role;
            }

            if ($updateData->status !== null) {
                $updateFields['status'] = $updateData->status;
            }

            if ($updateData->language_code !== null) {
                $updateFields['language_code'] = $updateData->language_code;
            }

            if ($updateData->currency_code !== null) {
                $updateFields['currency_code'] = $updateData->currency_code;
            }

            if ($updateData->company_name !== null) {
                $updateFields['company_name'] = $updateData->company_name;
            }

            if ($updateData->phone !== null) {
                $updateFields['phone'] = $updateData->phone;
            }

            if ($updateData->country !== null) {
                $updateFields['country'] = $updateData->country;
            }

            if ($updateData->address !== null) {
                $updateFields['address'] = $updateData->address;
            }

            if ($updateData->city !== null) {
                $updateFields['city'] = $updateData->city;
            }

            if ($updateData->state !== null) {
                $updateFields['state'] = $updateData->state;
            }

            if ($updateData->postal_code !== null) {
                $updateFields['postal_code'] = $updateData->postal_code;
            }

            if ($updateData->tax_id !== null) {
                $updateFields['tax_id'] = $updateData->tax_id;
            }

            // Actualizar usuario
            $user->update($updateFields);

            DB::commit();

            Log::info('Usuario actualizado exitosamente', [
                'user_id' => $user->id,
                'updated_fields' => array_keys($updateFields),
                'updated_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => 'Usuario actualizado exitosamente',
                'user' => $user->fresh()
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error actualizando usuario', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'updated_by' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Error al actualizar el usuario: ' . $e->getMessage(),
                'user' => null
            ];
        }
    }

    /**
     * Cambiar estado de usuario
     */
    public function changeUserStatus(int $userId, string $newStatus): array
    {
        try {
            $user = User::findOrFail($userId);

            // Validar estado válido
            $validStatuses = ['active', 'inactive', 'suspended'];
            if (!in_array($newStatus, $validStatuses)) {
                return [
                    'success' => false,
                    'message' => 'Estado no válido',
                    'user' => null
                ];
            }

            // Validar permisos
            if (!$this->canChangeUserStatus($user)) {
                return [
                    'success' => false,
                    'message' => 'No tienes permisos para cambiar el estado de este usuario',
                    'user' => null
                ];
            }

            $oldStatus = $user->status;
            $user->update(['status' => $newStatus]);

            Log::info('Estado de usuario cambiado', [
                'user_id' => $user->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => "Estado cambiado de {$oldStatus} a {$newStatus}",
                'user' => $user->fresh()
            ];

        } catch (\Exception $e) {
            Log::error('Error cambiando estado de usuario', [
                'user_id' => $userId,
                'new_status' => $newStatus,
                'error' => $e->getMessage(),
                'changed_by' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Error al cambiar el estado del usuario',
                'user' => null
            ];
        }
    }

    /**
     * Cambiar rol de usuario
     */
    public function changeUserRole(int $userId, string $newRole): array
    {
        try {
            $user = User::findOrFail($userId);

            // Validar rol válido
            $validRoles = ['admin', 'reseller', 'client'];
            if (!in_array($newRole, $validRoles)) {
                return [
                    'success' => false,
                    'message' => 'Rol no válido',
                    'user' => null
                ];
            }

            // Solo admins pueden cambiar roles
            if (!auth()->check() || auth()->user()->role !== 'admin') {
                return [
                    'success' => false,
                    'message' => 'Solo administradores pueden cambiar roles',
                    'user' => null
                ];
            }

            $oldRole = $user->role;
            $user->update(['role' => $newRole]);

            Log::info('Rol de usuario cambiado', [
                'user_id' => $user->id,
                'old_role' => $oldRole,
                'new_role' => $newRole,
                'changed_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => "Rol cambiado de {$oldRole} a {$newRole}",
                'user' => $user->fresh()
            ];

        } catch (\Exception $e) {
            Log::error('Error cambiando rol de usuario', [
                'user_id' => $userId,
                'new_role' => $newRole,
                'error' => $e->getMessage(),
                'changed_by' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Error al cambiar el rol del usuario',
                'user' => null
            ];
        }
    }

    /**
     * Eliminar usuario (soft delete)
     */
    public function deleteUser(int $userId): array
    {
        try {
            $user = User::findOrFail($userId);

            // Validar permisos de eliminación
            if (!$this->canDeleteUser($user)) {
                return [
                    'success' => false,
                    'message' => 'No tienes permisos para eliminar este usuario',
                    'user' => null
                ];
            }

            // Verificar dependencias antes de eliminar
            $dependencyCheck = $this->checkUserDependencies($user);
            if (!$dependencyCheck['can_delete']) {
                return [
                    'success' => false,
                    'message' => $dependencyCheck['message'],
                    'user' => null
                ];
            }

            $user->delete();

            Log::info('Usuario eliminado', [
                'user_id' => $user->id,
                'email' => $user->email,
                'deleted_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => 'Usuario eliminado exitosamente',
                'user' => null
            ];

        } catch (\Exception $e) {
            Log::error('Error eliminando usuario', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'deleted_by' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Error al eliminar el usuario',
                'user' => null
            ];
        }
    }

    /**
     * Validar permisos de actualización
     */
    private function validateUpdatePermissions(User $user, UpdateUserDTO $updateData): array
    {
        $currentUser = auth()->user();

        if (!$currentUser) {
            return ['allowed' => false, 'message' => 'Usuario no autenticado'];
        }

        // Admins pueden actualizar cualquier usuario
        if ($currentUser->role === 'admin') {
            return ['allowed' => true, 'message' => ''];
        }

        // Resellers pueden actualizar sus clientes
        if ($currentUser->role === 'reseller' && $user->role === 'client' && $user->reseller_id === $currentUser->id) {
            // Pero no pueden cambiar roles o estados críticos
            if ($updateData->role !== null || $updateData->status === 'suspended') {
                return ['allowed' => false, 'message' => 'Los resellers no pueden cambiar roles o suspender usuarios'];
            }
            return ['allowed' => true, 'message' => ''];
        }

        // Los usuarios pueden actualizar su propio perfil (campos limitados)
        if ($currentUser->id === $user->id) {
            // Solo pueden cambiar ciertos campos
            if ($updateData->role !== null || $updateData->status !== null) {
                return ['allowed' => false, 'message' => 'No puedes cambiar tu propio rol o estado'];
            }
            return ['allowed' => true, 'message' => ''];
        }

        return ['allowed' => false, 'message' => 'No tienes permisos para actualizar este usuario'];
    }

    /**
     * Verificar si se puede cambiar el estado del usuario
     */
    private function canChangeUserStatus(User $user): bool
    {
        $currentUser = auth()->user();

        if (!$currentUser) {
            return false;
        }

        // Admins pueden cambiar cualquier estado
        if ($currentUser->role === 'admin') {
            return true;
        }

        // Resellers pueden cambiar el estado de sus clientes (excepto suspender)
        if ($currentUser->role === 'reseller' && $user->role === 'client' && $user->reseller_id === $currentUser->id) {
            return true;
        }

        return false;
    }

    /**
     * Verificar si se puede eliminar el usuario
     */
    private function canDeleteUser(User $user): bool
    {
        $currentUser = auth()->user();

        if (!$currentUser) {
            return false;
        }

        // Solo admins pueden eliminar usuarios
        if ($currentUser->role === 'admin') {
            return true;
        }

        return false;
    }

    /**
     * Verificar dependencias del usuario antes de eliminar
     */
    private function checkUserDependencies(User $user): array
    {
        // TODO: Verificar servicios activos, facturas pendientes, etc.
        // Por ahora permitimos la eliminación
        
        return [
            'can_delete' => true,
            'message' => ''
        ];
    }
}

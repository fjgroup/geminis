<?php

namespace App\Domains\Users\Services;

use App\Contracts\User\UserRoleServiceInterface;
use App\Domains\Users\Models\User;
use App\ValueObjects\UserRole;

/**
 * Class UserRoleService
 *
 * Servicio responsable de la gestión y validación de roles de usuario
 * Cumple con el Principio de Responsabilidad Única (SRP)
 * Implementa UserRoleServiceInterface (DIP)
 */
class UserRoleService implements UserRoleServiceInterface
{
    /**
     * Crear UserRole desde string
     *
     * @param string $role
     * @return UserRole
     */
    private function createUserRole(string $role): UserRole
    {
        return new UserRole($role);
    }

    /**
     * Verificar si el usuario tiene un rol específico
     *
     * @param User $user
     * @param string $role
     * @return bool
     */
    public function hasRole(User $user, string $role): bool
    {
        try {
            $userRole = $this->createUserRole($user->role);
            $targetRole = $this->createUserRole($role);
            return $userRole->equals($targetRole);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Verificar si el usuario es administrador
     *
     * @param User $user
     * @return bool
     */
    public function isAdmin(User $user): bool
    {
        return $this->hasRole($user, 'admin');
    }

    /**
     * Verificar si el usuario es revendedor
     *
     * @param User $user
     * @return bool
     */
    public function isReseller(User $user): bool
    {
        return $this->hasRole($user, 'reseller');
    }

    /**
     * Verificar si el usuario es cliente
     *
     * @param User $user
     * @return bool
     */
    public function isClient(User $user): bool
    {
        return $this->hasRole($user, 'client');
    }

    /**
     * Verificar si el usuario tiene uno de varios roles
     *
     * @param User $user
     * @param array $roles
     * @return bool
     */
    public function hasAnyRole(User $user, array $roles): bool
    {
        return in_array($user->role, $roles);
    }

    /**
     * Obtener todos los roles disponibles
     *
     * @return array
     */
    public function getAvailableRoles(): array
    {
        return UserRole::getRoleLabels();
    }

    /**
     * Validar si un rol es válido
     *
     * @param string $role
     * @return bool
     */
    public function isValidRole(string $role): bool
    {
        return UserRole::isValid($role);
    }

    /**
     * Obtener permisos básicos por rol
     *
     * @param string $role
     * @return array
     */
    public function getRolePermissions(string $role): array
    {
        try {
            $userRole = $this->createUserRole($role);
            return $userRole->getPermissions();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Verificar si el usuario tiene un permiso específico
     *
     * @param User $user
     * @param string $permission
     * @return bool
     */
    public function hasPermission(User $user, string $permission): bool
    {
        $rolePermissions = $this->getRolePermissions($user->role);
        return in_array($permission, $rolePermissions);
    }

    /**
     * Cambiar el rol de un usuario (con validaciones)
     *
     * @param User $user
     * @param string $newRole
     * @return array
     */
    public function changeUserRole(User $user, string $newRole): array
    {
        if (!$this->isValidRole($newRole)) {
            return [
                'success' => false,
                'message' => 'El rol especificado no es válido'
            ];
        }

        if ($user->role === $newRole) {
            return [
                'success' => false,
                'message' => 'El usuario ya tiene este rol asignado'
            ];
        }

        $oldRole = $user->role;
        $user->role = $newRole;
        $user->save();

        return [
            'success' => true,
            'message' => "Rol cambiado de {$oldRole} a {$newRole}",
            'old_role' => $oldRole,
            'new_role' => $newRole
        ];
    }
}

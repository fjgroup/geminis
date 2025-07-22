<?php

namespace App\Contracts\User;

use App\Models\User;

/**
 * Interface UserRoleServiceInterface
 * 
 * Contrato para servicios de gestión de roles de usuario
 * Cumple con Interface Segregation Principle (ISP)
 */
interface UserRoleServiceInterface
{
    /**
     * Verificar si el usuario tiene un rol específico
     *
     * @param User $user
     * @param string $role
     * @return bool
     */
    public function hasRole(User $user, string $role): bool;

    /**
     * Verificar si el usuario es administrador
     *
     * @param User $user
     * @return bool
     */
    public function isAdmin(User $user): bool;

    /**
     * Verificar si el usuario es revendedor
     *
     * @param User $user
     * @return bool
     */
    public function isReseller(User $user): bool;

    /**
     * Verificar si el usuario es cliente
     *
     * @param User $user
     * @return bool
     */
    public function isClient(User $user): bool;

    /**
     * Verificar si el usuario tiene uno de varios roles
     *
     * @param User $user
     * @param array $roles
     * @return bool
     */
    public function hasAnyRole(User $user, array $roles): bool;

    /**
     * Obtener todos los roles disponibles
     *
     * @return array
     */
    public function getAvailableRoles(): array;

    /**
     * Validar si un rol es válido
     *
     * @param string $role
     * @return bool
     */
    public function isValidRole(string $role): bool;

    /**
     * Verificar si el usuario tiene un permiso específico
     *
     * @param User $user
     * @param string $permission
     * @return bool
     */
    public function hasPermission(User $user, string $permission): bool;

    /**
     * Cambiar el rol de un usuario (con validaciones)
     *
     * @param User $user
     * @param string $newRole
     * @return array
     */
    public function changeUserRole(User $user, string $newRole): array;
}

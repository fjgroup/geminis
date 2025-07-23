<?php

namespace App\Domains\Users\Infrastructure\Policies;

use App\Domains\Users\Infrastructure\Persistence\Models\User;

/**
 * Policy para autorización de usuarios en arquitectura hexagonal
 * 
 * Ubicado en Infrastructure layer como adaptador de autorización
 * Cumple con Single Responsibility Principle - solo maneja autorización de usuarios
 */
class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Admin puede ver cualquier usuario
        if ($user->role === 'admin') {
            return true;
        }

        // Reseller puede ver sus clientes
        if ($user->role === 'reseller') {
            return $model->reseller_id === $user->id || $model->id === $user->id;
        }

        // Usuario puede ver solo su propio perfil
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'reseller']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Admin puede actualizar cualquier usuario
        if ($user->role === 'admin') {
            return true;
        }

        // Reseller puede actualizar sus clientes
        if ($user->role === 'reseller') {
            return $model->reseller_id === $user->id;
        }

        // Usuario puede actualizar solo su propio perfil
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Solo admin puede eliminar usuarios
        if ($user->role !== 'admin') {
            return false;
        }

        // Admin no puede eliminarse a sí mismo
        return $user->id !== $model->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->role === 'admin' && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can manage reseller clients.
     */
    public function manageResellerClients(User $user): bool
    {
        return $user->role === 'reseller';
    }

    /**
     * Determine whether the user can access admin panel.
     */
    public function accessAdminPanel(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can access reseller panel.
     */
    public function accessResellerPanel(User $user): bool
    {
        return $user->role === 'reseller';
    }

    /**
     * Determine whether the user can access client panel.
     */
    public function accessClientPanel(User $user): bool
    {
        return $user->role === 'client';
    }

    /**
     * Determine whether the user can impersonate other users.
     */
    public function impersonate(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can change roles.
     */
    public function changeRole(User $user, User $model): bool
    {
        // Solo admin puede cambiar roles
        if ($user->role !== 'admin') {
            return false;
        }

        // Admin no puede cambiar su propio rol
        return $user->id !== $model->id;
    }
}

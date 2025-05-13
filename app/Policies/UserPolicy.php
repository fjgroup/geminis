<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;


class UserPolicy
{
    // No es estrictamente necesario si solo retornas booleanos:
    // use Illuminate\Auth\Access\HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $adminUser): bool
    {
        return $adminUser->role === 'admin';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $adminUser, User $targetUser): bool
    {
        return $adminUser->role === 'admin';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $adminUser): bool
    {
        return $adminUser->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $adminUser, User $targetUser): bool
    {
        if ($adminUser->role !== 'admin') {
            return false;
        }
        // Opcional: Un admin no puede editarse a sí mismo ciertos campos sensibles aquí.
        // if ($adminUser->id === $targetUser->id) {
        //     return false; // O permitir solo ciertos campos
        // }
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $adminUser, User $targetUser): bool
    {
        if ($adminUser->role !== 'admin') {
            return false;
        }
        // Un admin no puede eliminarse a sí mismo.
        if ($adminUser->id === $targetUser->id) {
            return false;
        }
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $adminUser, User $targetUser): bool
    {
        return $adminUser->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $adminUser, User $targetUser): bool
    {
        // Considera restringir esto aún más, ej. solo un superadmin
        return $adminUser->role === 'admin';
    }
}
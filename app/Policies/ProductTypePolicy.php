<?php
namespace App\Policies;

use App\Models\ProductType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductTypePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Solo administradores pueden ver tipos de productos
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProductType $productType): bool
    {
        // Solo administradores pueden ver tipos de productos específicos
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Solo administradores pueden crear tipos de productos
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProductType $productType): bool
    {
        // Solo administradores pueden actualizar tipos de productos
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProductType $productType): bool
    {
        // Solo administradores pueden eliminar tipos de productos
        // Verificar que no haya productos asociados
        if ($productType->products()->exists()) {
            return false;
        }

        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ProductType $productType): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ProductType $productType): bool
    {
        // Solo super administradores pueden eliminar permanentemente
        return $user->role === 'admin' && $user->email === config('app.super_admin_email');
    }
}

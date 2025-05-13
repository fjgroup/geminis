<?php

namespace App\Policies;

use App\Models\ProductPricing;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductPricingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProductPricing $productPricing): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Solo los administradores pueden crear precios para los productos.
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProductPricing $productPricing): bool
    {
        // Solo los administradores pueden actualizar precios.
        // Asegúrate de que el rol del usuario autenticado sea 'admin'.
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProductPricing $productPricing): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ProductPricing $productPricing): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ProductPricing $productPricing): bool
    {
        return false;
    }
}

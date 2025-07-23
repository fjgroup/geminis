<?php

namespace App\Domains\Products\Infrastructure\Policies;

use App\Domains\Users\Infrastructure\Persistence\Models\User;
use App\Domains\Products\Infrastructure\Persistence\Models\Product;

/**
 * Policy para autorización de productos en arquitectura hexagonal
 * 
 * Ubicado en Infrastructure layer como adaptador de autorización
 * Cumple con Single Responsibility Principle - solo maneja autorización de productos
 */
class ProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Todos los usuarios autenticados pueden ver productos
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Product $product): bool
    {
        // Admin puede ver cualquier producto
        if ($user->role === 'admin') {
            return true;
        }

        // Otros usuarios solo pueden ver productos activos
        return $product->is_active;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Product $product): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Product $product): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Product $product): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can manage product pricing.
     */
    public function managePricing(User $user, Product $product): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can manage configurable options.
     */
    public function manageConfigurableOptions(User $user, Product $product): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can purchase the product.
     */
    public function purchase(User $user, Product $product): bool
    {
        // Solo clientes pueden comprar productos
        if ($user->role !== 'client') {
            return false;
        }

        // El producto debe estar activo y disponible
        return $product->is_active && $product->status === 'active';
    }

    /**
     * Determine whether the user can view product pricing.
     */
    public function viewPricing(User $user, Product $product): bool
    {
        // Admin puede ver precios de cualquier producto
        if ($user->role === 'admin') {
            return true;
        }

        // Resellers y clientes pueden ver precios de productos activos
        return $product->is_active;
    }

    /**
     * Determine whether the user can activate/deactivate products.
     */
    public function toggleStatus(User $user, Product $product): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can view product statistics.
     */
    public function viewStatistics(User $user, Product $product): bool
    {
        return $user->role === 'admin';
    }
}

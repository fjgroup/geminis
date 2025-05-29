<?php

namespace App\Policies;

use App\Models\Product; // Corregido de Produc a Product
use App\Models\User;


class ProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admins can view all products. Clients can view available products. Resellers view their own or resellable.
        // For now, allow admins and clients to view the list.
        return $user->role === 'admin' || $user->role === 'client';
    }


    /**
     * Determine whether the user can view the model.
     */

    public function view(User $user, Product $product): bool
    {
        // Admins can view any product. Clients can view any product.
        // Resellers can view their own products or platform products that are resellable.
        return $user->role === 'admin'
            || $user->role === 'client'
            || ($user->role === 'reseller' && $product->owner_id === $user->id)
            || ($user->role === 'reseller' && is_null($product->owner_id) && $product->is_resellable_by_default);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Solo los administradores pueden crear productos de plataforma.
        // Los revendedores pueden crear productos si tienen el permiso (esto se manejará en ResellerProfile o una policy más granular).
        // Por ahora, solo admins para productos globales.
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Product $product): bool // Corregido Produc a Product
    {
        // Los administradores pueden actualizar cualquier producto.
        // Los revendedores solo pueden actualizar sus propios productos.
        return $user->role === 'admin'
            || ($user->role === 'reseller' && $product->owner_id === $user->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Product $product): bool // Corregido Produc a Product
    {
        // Similar a update: Admins pueden borrar cualquiera, revendedores solo los suyos.
        // Considerar no permitir borrar productos si tienen servicios activos asociados.
        return $user->role === 'admin'
            || ($user->role === 'reseller' && $product->owner_id === $user->id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Product $product): bool // Corregido Produc a Product
    {
        return $user->role === 'admin'; // Generalmente solo admins pueden restaurar
    }

    /**
     * Determine whether the user can permanently delete the model.
     */

    public function forceDelete(User $user, Product $product): bool // Corregido Produc a Product
    {
        return $user->role === 'admin'; // Y quizás solo un superadmin o con precauciones
    }
}

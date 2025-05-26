<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admins, Resellers, y Clients pueden ver listas de órdenes (que serán filtradas por el controlador)
        return $user->hasRole('admin') ||
               $user->hasRole('reseller') ||
               $user->hasRole('client');
    }


    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        // Admins pueden ver cualquier orden
        if ($user->hasRole('admin')) {
            return true;
        }

        // Clients pueden ver sus propias órdenes
        if ($user->hasRole('client')) {
            return $user->id === $order->client_id;
        }

        // Resellers pueden ver órdenes de sus propios clientes
        return $user->hasRole('reseller') && $order->client && $order->client->reseller_id === $user->id;
    }
    

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('client');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Order $order): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Order $order): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Order $order): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        return false;
    }
}

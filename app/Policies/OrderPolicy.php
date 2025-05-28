<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization; // Added import

class OrderPolicy
{
    use HandlesAuthorization; // Added trait

    /**
     * Determine whether the user can view any models.
     * (From previous verification for admin/client lists)
     */
    public function viewAny(User $user): bool
    {
        // Admins, Resellers, y Clients pueden ver listas de órdenes (que serán filtradas por el controlador)
        return $user->isAdmin() || $user->hasRole('reseller') || $user->hasRole('client');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        if ($user->hasRole('reseller') && $order->client && $order->client->reseller_id === $user->id) {
             return true; // Reseller can view their own client's order
        }
        if ($user->hasRole('client') && $user->id === $order->client_id) {
            return true; // Client can view their own order
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     * (From previous verification for client order creation)
     */
    public function create(User $user): bool
    {
        // Only clients can create orders through the standard process
        // Admins might create orders via a different mechanism if needed (e.g. "create on behalf of")
        return $user->hasRole('client');
    }

    /**
     * Determine whether the user can update the model.
     * Only Admins can update orders through this specific admin functionality.
     */
    public function update(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     * Admins can delete orders (soft delete).
     * Clients can delete their own orders if status is 'pending_payment'.
     */
    public function delete(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true; // Admins can delete (soft delete)
        }

        // Client can delete their own order if it's pending payment
        if ($user->id === $order->client_id && $order->status === 'pending_payment') {
            return true;
        }
        
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can request cancellation for a paid order
     * that is pending execution.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Order  $order
     * @return bool
     */
    public function requestPostPaymentCancellation(User $user, Order $order): bool
    {
        // Client must own the order
        if ($user->id !== $order->client_id) {
            return false;
        }

        // Order must be in a state that allows cancellation request
        // e.g., 'paid_pending_execution'
        // This status was added to the orders table ENUM previously
        return $order->status === 'paid_pending_execution';
    }
}

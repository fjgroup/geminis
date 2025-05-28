<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     * Only admins should be able to view a list of all transactions.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        // Admins can view any list of transactions.
        // Clients can view lists of their own transactions.
        return $user->isAdmin() || $user->hasRole('client');
    }

    /**
     * Determine whether the user can view the model.
     * For now, if a user can view any, they can view a specific one.
     * More granular control can be added if clients/resellers need to see specific transactions.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Transaction $transaction)
    {
        if ($user->isAdmin()) {
            return true;
        }
        // Clients can view their own specific transactions.
        return $user->hasRole('client') && $user->id === $transaction->client_id;
    }

    /**
     * Determine whether the user can create models.
     * Only admins should be able to manually create transactions.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     * For now, disallow updates directly on transactions.
     * Updates might happen via reversals or new related transactions.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Transaction $transaction)
    {
        return false; // Or return $user->isAdmin() if admins can edit certain fields
    }

    /**
     * Determine whether the user can delete the model.
     * Generally, financial transactions should not be deleted.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Transaction $transaction)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Transaction $transaction)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Transaction $transaction)
    {
        return false;
    }
}

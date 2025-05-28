<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
// use Illuminate\Auth\Access\Response; // Not strictly needed if not using Response objects
use Illuminate\Auth\Access\HandlesAuthorization; // Added import

class InvoicePolicy
{
    use HandlesAuthorization; // Added trait

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admins can view any list of invoices.
        // Clients can view lists of invoices (which will be scoped by the controller).
        return $user->isAdmin() || $user->hasRole('client');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        // Admins can view any invoice.
        if ($user->isAdmin()) {
            return true;
        }

        // Clients can view their own invoices.
        if ($user->hasRole('client')) {
            return $user->id === $invoice->client_id;
        }

        // Resellers can view invoices of their own clients.
        // Note: This assumes reseller_id is correctly populated on the invoice,
        // or client relationship on invoice can be traversed to check client's reseller_id.
        // If reseller_id is directly on the invoice:
        // return $user->hasRole('reseller') && $user->id === $invoice->reseller_id;
        // If checking through the client associated with the invoice:
        // return $user->hasRole('reseller') && $invoice->client && $invoice->client->reseller_id === $user->id;
        
        // For now, let's stick to the original request for client and admin,
        // but ideally, reseller logic should be here too if they can view invoices.
        // Let's assume for now resellers viewing invoices is handled by admin view or not implemented for individual invoices.
        return false; 
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin(); // Assuming User model has isAdmin() method
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        return false; // O $user->isAdmin(); si se desea permitir la actualización a admins
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        return false; // O $user->isAdmin(); si se desea permitir la eliminación a admins
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Invoice $invoice): bool
    {
        return false; // O $user->isAdmin(); si se desea permitir la restauración a admins
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Invoice $invoice): bool
    {
        return false; // O $user->isAdmin(); si se desea permitir la eliminación forzada a admins
    }
}

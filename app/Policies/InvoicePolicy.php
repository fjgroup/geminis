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
        return $user->isAdmin() || $user->hasRole('reseller') || $user->hasRole('client');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        // Ensure client relationship is loaded for efficiency if not already
        $invoice->loadMissing('client');
        if ($user->hasRole('reseller') && $invoice->client && $invoice->client->reseller_id === $user->id) {
            return true;
        }
        if ($user->hasRole('client') && $user->id === $invoice->client_id) {
            return true;
        }
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
        // Only admins can edit invoices.
        // Additional logic could be added, e.g., preventing edits on 'paid' or 'cancelled' invoices
        // unless the user has a specific higher privilege, but for now, admin override is fine.
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        // Only admins can delete invoices.
        // Consider adding logic to prevent deletion of invoices with non-reversed payments,
        // or if related order is still active, etc. For now, admin override.
        // Soft deletes are used, so data is recoverable.
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Invoice $invoice): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Invoice $invoice): bool
    {
        return $user->isAdmin(); // Or false, to prevent force deletes via policy
    }

    /**
     * Determine whether the user can pay the invoice with their balance.
     */
    public function payWithBalance(User $user, Invoice $invoice): bool
    {
        // User must be the client of the invoice and the invoice must be unpaid.
        return $user->id === $invoice->client_id && $invoice->status === 'unpaid';
    }

    /**
     * Determine whether the user can initiate a payment for the invoice.
     */
    public function pay(User $user, Invoice $invoice): bool
    {
        // User must be the client of the invoice and the invoice must be unpaid.
        return $user->id === $invoice->client_id && $invoice->status === 'unpaid';
    }

    /**
     * Determine whether the user can cancel a reported payment for the invoice.
     */
    public function cancelPaymentReport(User $user, Invoice $invoice): bool
    {
        // The user must be the client who owns the invoice,
        // and the invoice must be in 'pending_confirmation' status.
        return $user->id === $invoice->client_id && $invoice->status === 'pending_confirmation';
    }

    /**
     * Determine whether the user can request cancellation for a new service invoice.
     */
    public function requestCancellationForNewServiceInvoice(User $user, Invoice $invoice): bool
    {
        // Check if the user is the client who owns the invoice
        // and if the invoice is cancellable as a new service.
        return $user->id === $invoice->client_id && $invoice->isCancellableAsNewService();
    }
}

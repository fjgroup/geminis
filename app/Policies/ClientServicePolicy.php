<?php

namespace App\Policies;

use App\Models\ClientService;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization; // Added
use Illuminate\Auth\Access\Response; // Keep if Response objects are used

class ClientServicePolicy
{
    use HandlesAuthorization; // Added

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admins, Resellers, and Clients can view lists of services.
        // Actual scoping (e.g., reseller sees only their clients' services) is done in the controller.
        return $user->isAdmin() || $user->hasRole('reseller') || $user->hasRole('client');
    }

    /**
     * Determine whether the user can renew the client service.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ClientService  $clientService
     * @return bool
     */
    public function renewService(User $user, ClientService $clientService): bool
    {
        // Allow renewal if the user owns the service and its status is Active or Suspended.
        // Further checks (like existing unpaid renewal invoice) are handled in the controller.
        return $user->id === $clientService->client_id && in_array($clientService->status, ['Active', 'Suspended']);
    }

    /**
     * Determine whether the user can process an upgrade/downgrade for the client service.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ClientService  $clientService
     * @return bool
     */
    public function processUpgradeDowngrade(User $user, ClientService $clientService): bool
    {
        // Only allow processing if the service is Active and the user owns the service.
        // Further validation (e.g., selected plan is valid) is done in the controller.
        return $user->id === $clientService->client_id && $clientService->status === 'Active';
    }

    /**
     * Determine whether the user can view upgrade/downgrade options for the client service.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ClientService  $clientService
     * @return bool
     */
    public function viewUpgradeDowngradeOptions(User $user, ClientService $clientService): bool
    {
        // Only allow viewing options if the service is Active
        // and the user owns the service.
        return $user->id === $clientService->client_id && $clientService->status === 'Active';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ClientService $service): bool // Parameter name changed for clarity
    {
        if ($user->isAdmin()) {
            return true;
        }
        // Ensure client relationship is loaded for efficiency
        $service->loadMissing('client');
        if ($user->hasRole('reseller') && $service->client && $service->client->reseller_id === $user->id) {
            return true;
        }
        if ($user->hasRole('client') && $user->id === $service->client_id) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can request cancellation for the client service.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ClientService  $clientService
     * @return bool
     */
    public function requestCancellation(User $user, ClientService $clientService): bool
    {
        return $user->id === $clientService->client_id && in_array($clientService->status, ['Active', 'Suspended']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Esta política básica no implementa la creación.
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ClientService $clientService): bool
    {
        // Esta política básica no implementa la actualización.
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ClientService $clientService): bool
    {
        // Esta política básica no implementa la eliminación.
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ClientService $clientService): bool
    {
        // Esta política básica no implementa la restauración.
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ClientService $clientService): bool
    {
        // Esta política básica no implementa la eliminación forzada.
        return false;
    }
}

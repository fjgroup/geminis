<?php

namespace App\Domains\ClientServices\Infrastructure\Policies;

use App\Domains\Users\Infrastructure\Persistence\Models\User;
use App\Domains\ClientServices\Infrastructure\Persistence\Models\ClientService;

/**
 * Policy para autorización de servicios de cliente en arquitectura hexagonal
 * 
 * Ubicado en Infrastructure layer como adaptador de autorización
 * Cumple con Single Responsibility Principle - solo maneja autorización de servicios
 */
class ClientServicePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'reseller', 'client']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ClientService $clientService): bool
    {
        // Admin puede ver cualquier servicio
        if ($user->role === 'admin') {
            return true;
        }

        // Reseller puede ver servicios de sus clientes
        if ($user->role === 'reseller') {
            return $clientService->reseller_id === $user->id;
        }

        // Cliente puede ver solo sus propios servicios
        return $user->role === 'client' && $clientService->client_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'reseller']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ClientService $clientService): bool
    {
        // Admin puede actualizar cualquier servicio
        if ($user->role === 'admin') {
            return true;
        }

        // Reseller puede actualizar servicios de sus clientes
        if ($user->role === 'reseller') {
            return $clientService->reseller_id === $user->id;
        }

        // Cliente no puede actualizar servicios directamente
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ClientService $clientService): bool
    {
        // Solo admin puede eliminar servicios
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can suspend the service.
     */
    public function suspend(User $user, ClientService $clientService): bool
    {
        // Admin puede suspender cualquier servicio
        if ($user->role === 'admin') {
            return true;
        }

        // Reseller puede suspender servicios de sus clientes
        if ($user->role === 'reseller') {
            return $clientService->reseller_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can unsuspend the service.
     */
    public function unsuspend(User $user, ClientService $clientService): bool
    {
        return $this->suspend($user, $clientService);
    }

    /**
     * Determine whether the user can terminate the service.
     */
    public function terminate(User $user, ClientService $clientService): bool
    {
        // Admin puede terminar cualquier servicio
        if ($user->role === 'admin') {
            return true;
        }

        // Reseller puede terminar servicios de sus clientes
        if ($user->role === 'reseller') {
            return $clientService->reseller_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can renew the service.
     */
    public function renew(User $user, ClientService $clientService): bool
    {
        // Admin puede renovar cualquier servicio
        if ($user->role === 'admin') {
            return true;
        }

        // Reseller puede renovar servicios de sus clientes
        if ($user->role === 'reseller') {
            return $clientService->reseller_id === $user->id;
        }

        // Cliente puede renovar sus propios servicios
        return $user->role === 'client' && $clientService->client_id === $user->id;
    }

    /**
     * Determine whether the user can upgrade/downgrade the service.
     */
    public function changeProduct(User $user, ClientService $clientService): bool
    {
        // Admin puede cambiar producto de cualquier servicio
        if ($user->role === 'admin') {
            return true;
        }

        // Reseller puede cambiar producto de servicios de sus clientes
        if ($user->role === 'reseller') {
            return $clientService->reseller_id === $user->id;
        }

        // Cliente puede cambiar producto de sus propios servicios
        return $user->role === 'client' && $clientService->client_id === $user->id;
    }

    /**
     * Determine whether the user can view service details.
     */
    public function viewDetails(User $user, ClientService $clientService): bool
    {
        return $this->view($user, $clientService);
    }

    /**
     * Determine whether the user can manage service configuration.
     */
    public function manageConfiguration(User $user, ClientService $clientService): bool
    {
        // Admin puede gestionar configuración de cualquier servicio
        if ($user->role === 'admin') {
            return true;
        }

        // Reseller puede gestionar configuración de servicios de sus clientes
        if ($user->role === 'reseller') {
            return $clientService->reseller_id === $user->id;
        }

        return false;
    }
}

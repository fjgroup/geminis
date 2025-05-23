<?php

namespace App\Policies;

use App\Models\ClientService;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClientServicePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Permitir a cualquier usuario autenticado ver la lista de sus servicios.
        // La lógica de filtrar por user_id se maneja en el controlador.
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ClientService $clientService): Response
    {
        // Permitir al usuario ver el servicio solo si le pertenece.
        return $user->id === $clientService->client_id
                    ? Response::allow()
                    : Response::deny('No tienes permiso para ver este servicio.');
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

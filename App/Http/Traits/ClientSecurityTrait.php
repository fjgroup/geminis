<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait ClientSecurityTrait
{
    /**
     * Verificar que el usuario autenticado sea el propietario del recurso
     */
    protected function ensureOwnership($resourceUserId)
    {
        if (Auth::id() !== $resourceUserId) {
            abort(403, 'No tienes permisos para acceder a este recurso.');
        }
    }

    /**
     * Verificar que el usuario sea cliente
     */
    protected function ensureClientRole()
    {
        if (Auth::user()->role !== 'client') {
            abort(403, 'Solo los clientes pueden acceder a esta funcionalidad.');
        }
    }

    /**
     * Obtener el ID del usuario autenticado
     */
    protected function getAuthenticatedUserId()
    {
        return Auth::id();
    }

    /**
     * Verificar que el usuario esté autenticado
     */
    protected function ensureAuthenticated()
    {
        if (!Auth::check()) {
            abort(401, 'Debes estar autenticado para acceder a este recurso.');
        }
    }
}

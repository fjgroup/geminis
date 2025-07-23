<?php

namespace App\Http\Requests\Admin;

/**
 * Alias para mantener compatibilidad con código existente
 * 
 * @deprecated Use App\Domains\Users\Infrastructure\Http\Requests\StoreUserRequest instead
 */
class StoreUserRequest extends \App\Domains\Users\Infrastructure\Http\Requests\StoreUserRequest
{
    // Esta clase extiende la nueva ubicación hexagonal
    // Mantiene compatibilidad mientras se migra el código existente
}

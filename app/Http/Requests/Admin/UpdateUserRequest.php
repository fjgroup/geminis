<?php

namespace App\Http\Requests\Admin;

/**
 * Alias para mantener compatibilidad con código existente
 * 
 * @deprecated Use App\Domains\Users\Infrastructure\Http\Requests\UpdateUserRequest instead
 */
class UpdateUserRequest extends \App\Domains\Users\Infrastructure\Http\Requests\UpdateUserRequest
{
    // Esta clase extiende la nueva ubicación hexagonal
    // Mantiene compatibilidad mientras se migra el código existente
}

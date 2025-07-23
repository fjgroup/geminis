<?php

namespace App\Http\Requests;

/**
 * Alias para mantener compatibilidad con código existente
 * 
 * @deprecated Use App\Domains\Users\Infrastructure\Http\Requests\ProfileUpdateRequest instead
 */
class ProfileUpdateRequest extends \App\Domains\Users\Infrastructure\Http\Requests\ProfileUpdateRequest
{
    // Esta clase extiende la nueva ubicación hexagonal
    // Mantiene compatibilidad mientras se migra el código existente
}

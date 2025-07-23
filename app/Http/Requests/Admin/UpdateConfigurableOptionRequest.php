<?php

namespace App\Http\Requests\Admin;

/**
 * Alias para mantener compatibilidad con código existente
 * 
 * @deprecated Use App\Domains\Products\Infrastructure\Http\Requests\UpdateConfigurableOptionRequest instead
 */
class UpdateConfigurableOptionRequest extends \App\Domains\Products\Infrastructure\Http\Requests\UpdateConfigurableOptionRequest
{
    // Esta clase extiende la nueva ubicación hexagonal
    // Mantiene compatibilidad mientras se migra el código existente
}

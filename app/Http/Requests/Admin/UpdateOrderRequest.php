<?php

namespace App\Http\Requests\Admin;

/**
 * Alias para mantener compatibilidad con código existente
 * 
 * @deprecated Use App\Domains\Orders\Infrastructure\Http\Requests\UpdateOrderRequest instead
 */
class UpdateOrderRequest extends \App\Domains\Orders\Infrastructure\Http\Requests\UpdateOrderRequest
{
    // Esta clase extiende la nueva ubicación hexagonal
    // Mantiene compatibilidad mientras se migra el código existente
}

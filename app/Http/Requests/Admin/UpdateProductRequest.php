<?php

namespace App\Http\Requests\Admin;

/**
 * Alias para mantener compatibilidad con código existente
 * 
 * @deprecated Use App\Domains\Products\Infrastructure\Http\Requests\UpdateProductRequest instead
 */
class UpdateProductRequest extends \App\Domains\Products\Infrastructure\Http\Requests\UpdateProductRequest
{
    // Esta clase extiende la nueva ubicación hexagonal
    // Mantiene compatibilidad mientras se migra el código existente
}

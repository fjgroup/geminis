<?php

namespace App\Http\Requests\Admin;

/**
 * Alias para mantener compatibilidad con código existente
 * 
 * @deprecated Use App\Domains\Products\Infrastructure\Http\Requests\StoreProductTypeRequest instead
 */
class StoreProductTypeRequest extends \App\Domains\Products\Infrastructure\Http\Requests\StoreProductTypeRequest
{
    // Esta clase extiende la nueva ubicación hexagonal
    // Mantiene compatibilidad mientras se migra el código existente
}

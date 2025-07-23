<?php

namespace App\Http\Requests\Admin;

/**
 * Alias para mantener compatibilidad con código existente
 * 
 * @deprecated Use App\Domains\ClientServices\Infrastructure\Http\Requests\StoreClientServiceRequest instead
 */
class StoreClientServiceRequest extends \App\Domains\ClientServices\Infrastructure\Http\Requests\StoreClientServiceRequest
{
    // Esta clase extiende la nueva ubicación hexagonal
    // Mantiene compatibilidad mientras se migra el código existente
}

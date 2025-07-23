<?php

namespace App\Http\Requests\Admin;

/**
 * Alias para mantener compatibilidad con código existente
 * 
 * @deprecated Use App\Domains\BillingAndPayments\Infrastructure\Http\Requests\StoreTransactionRequest instead
 */
class StoreTransactionRequest extends \App\Domains\BillingAndPayments\Infrastructure\Http\Requests\StoreTransactionRequest
{
    // Esta clase extiende la nueva ubicación hexagonal
    // Mantiene compatibilidad mientras se migra el código existente
}

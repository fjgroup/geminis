<?php

namespace App\Http\Requests\Admin;

/**
 * Alias para mantener compatibilidad con código existente
 * 
 * @deprecated Use App\Domains\Invoices\Infrastructure\Http\Requests\StoreManualInvoiceRequest instead
 */
class StoreManualInvoiceRequest extends \App\Domains\Invoices\Infrastructure\Http\Requests\StoreManualInvoiceRequest
{
    // Esta clase extiende la nueva ubicación hexagonal
    // Mantiene compatibilidad mientras se migra el código existente
}

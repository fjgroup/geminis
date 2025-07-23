<?php

namespace App\Http\Requests\Admin;

/**
 * Alias para mantener compatibilidad con código existente
 * 
 * @deprecated Use App\Domains\Invoices\Infrastructure\Http\Requests\UpdateInvoiceRequest instead
 */
class UpdateInvoiceRequest extends \App\Domains\Invoices\Infrastructure\Http\Requests\UpdateInvoiceRequest
{
    // Esta clase extiende la nueva ubicación hexagonal
    // Mantiene compatibilidad mientras se migra el código existente
}

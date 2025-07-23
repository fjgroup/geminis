<?php

namespace App\Http\Requests\Admin;

/**
 * Alias para mantener compatibilidad con código existente
 * 
 * @deprecated Use App\Domains\BillingAndPayments\Infrastructure\Http\Requests\ConfirmManualPaymentRequest instead
 */
class ConfirmManualPaymentRequest extends \App\Domains\BillingAndPayments\Infrastructure\Http\Requests\ConfirmManualPaymentRequest
{
    // Esta clase extiende la nueva ubicación hexagonal
    // Mantiene compatibilidad mientras se migra el código existente
}

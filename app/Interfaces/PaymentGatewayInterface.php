<?php

namespace App\Interfaces;

/**
 * Alias para mantener compatibilidad con código existente
 * 
 * @deprecated Use App\Domains\BillingAndPayments\Application\Interfaces\PaymentGatewayInterface instead
 */
interface PaymentGatewayInterface extends \App\Domains\BillingAndPayments\Application\Interfaces\PaymentGatewayInterface
{
    // Esta interfaz extiende la nueva ubicación hexagonal
    // Mantiene compatibilidad mientras se migra el código existente
}

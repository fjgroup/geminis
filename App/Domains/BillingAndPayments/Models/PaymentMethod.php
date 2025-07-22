<?php

namespace App\Domains\BillingAndPayments\Models;

/**
 * Modelo de compatibilidad para PaymentMethod
 * 
 * @deprecated Use App\Domains\BillingAndPayments\Domain\Entities\PaymentMethod instead
 * 
 * Este modelo existe solo para mantener compatibilidad con código existente.
 * Todas las nuevas implementaciones deben usar la entidad del dominio.
 */
class PaymentMethod extends \App\Domains\BillingAndPayments\Domain\Entities\PaymentMethod
{
    // Este modelo extiende de la entidad del dominio para mantener compatibilidad
    // No agregar lógica aquí - usar la entidad del dominio directamente
}

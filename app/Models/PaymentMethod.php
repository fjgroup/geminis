<?php

namespace App\Models;

/**
 * Modelo de compatibilidad para PaymentMethod
 * 
 * @deprecated Use App\Domains\BillingAndPayments\Models\PaymentMethod instead
 * 
 * Este modelo existe solo para mantener compatibilidad con código existente.
 * Todas las nuevas implementaciones deben usar el modelo del dominio BillingAndPayments.
 */
class PaymentMethod extends \App\Domains\BillingAndPayments\Models\PaymentMethod
{
    // Este modelo extiende del modelo del dominio para mantener compatibilidad
    // No agregar lógica aquí - usar el modelo del dominio directamente
}

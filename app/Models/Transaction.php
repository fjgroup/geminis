<?php

namespace App\Models;

/**
 * Modelo de compatibilidad para Transaction
 * 
 * @deprecated Use App\Domains\BillingAndPayments\Models\Transaction instead
 * 
 * Este modelo existe solo para mantener compatibilidad con código existente.
 * Todas las nuevas implementaciones deben usar el modelo del dominio BillingAndPayments.
 */
class Transaction extends \App\Domains\BillingAndPayments\Models\Transaction
{
    // Este modelo extiende del modelo del dominio para mantener compatibilidad
    // No agregar lógica aquí - usar el modelo del dominio directamente
}

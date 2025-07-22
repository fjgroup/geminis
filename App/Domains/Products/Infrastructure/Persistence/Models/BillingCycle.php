<?php

namespace App\Domains\Products\Infrastructure\Persistence\Models;

/**
 * Modelo de compatibilidad para BillingCycle
 * 
 * @deprecated Use App\Domains\Products\Models\BillingCycle instead
 * 
 * Este modelo existe solo para mantener compatibilidad con código existente.
 * Todas las nuevas implementaciones deben usar el modelo del dominio Products.
 */
class BillingCycle extends \App\Domains\Products\Models\BillingCycle
{
    // Este modelo extiende del modelo del dominio para mantener compatibilidad
    // No agregar lógica aquí - usar el modelo del dominio directamente
}

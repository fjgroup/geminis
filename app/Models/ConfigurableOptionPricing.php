<?php

namespace App\Models;

/**
 * Modelo de compatibilidad para ConfigurableOptionPricing
 * 
 * @deprecated Use App\Domains\Products\Models\ConfigurableOptionPricing instead
 * 
 * Este modelo existe solo para mantener compatibilidad con código existente.
 * Todas las nuevas implementaciones deben usar el modelo del dominio Products.
 */
class ConfigurableOptionPricing extends \App\Domains\Products\Models\ConfigurableOptionPricing
{
    // Este modelo extiende del modelo del dominio para mantener compatibilidad
    // No agregar lógica aquí - usar el modelo del dominio directamente
}

<?php

namespace App\Models;

/**
 * Modelo de compatibilidad para DiscountPercentage
 * 
 * @deprecated Use App\Domains\Products\Models\DiscountPercentage instead
 * 
 * Este modelo existe solo para mantener compatibilidad con código existente.
 * Todas las nuevas implementaciones deben usar el modelo del dominio Products.
 */
class DiscountPercentage extends \App\Domains\Products\Models\DiscountPercentage
{
    // Este modelo extiende del modelo del dominio para mantener compatibilidad
    // No agregar lógica aquí - usar el modelo del dominio directamente
}

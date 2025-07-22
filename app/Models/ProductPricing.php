<?php

namespace App\Models;

/**
 * Modelo de compatibilidad para ProductPricing
 * 
 * @deprecated Use App\Domains\Products\Models\ProductPricing instead
 * 
 * Este modelo existe solo para mantener compatibilidad con código existente.
 * Todas las nuevas implementaciones deben usar el modelo del dominio Products.
 */
class ProductPricing extends \App\Domains\Products\Models\ProductPricing
{
    // Este modelo extiende del modelo del dominio para mantener compatibilidad
    // No agregar lógica aquí - usar el modelo del dominio directamente
}

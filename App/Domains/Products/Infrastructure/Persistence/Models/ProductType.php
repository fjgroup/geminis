<?php

namespace App\Models;

/**
 * Modelo de compatibilidad para ProductType
 * 
 * @deprecated Use App\Domains\Products\Models\ProductType instead
 * 
 * Este modelo existe solo para mantener compatibilidad con código existente.
 * Todas las nuevas implementaciones deben usar el modelo del dominio Products.
 */
class ProductType extends \App\Domains\Products\Models\ProductType
{
    // Este modelo extiende del modelo del dominio para mantener compatibilidad
    // No agregar lógica aquí - usar el modelo del dominio directamente
}

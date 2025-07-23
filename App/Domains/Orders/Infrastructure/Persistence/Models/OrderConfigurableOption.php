<?php

namespace App\Domains\Orders\Infrastructure\Persistence\Models;

/**
 * Modelo de compatibilidad para OrderConfigurableOption
 * 
 * @deprecated Use App\Domains\Orders\Models\OrderConfigurableOption instead
 * 
 * Este modelo existe solo para mantener compatibilidad con código existente.
 * Todas las nuevas implementaciones deben usar el modelo del dominio Orders.
 */
class OrderConfigurableOption extends \App\Domains\Orders\Models\OrderConfigurableOption
{
    // Este modelo extiende del modelo del dominio para mantener compatibilidad
    // No agregar lógica aquí - usar el modelo del dominio directamente
}

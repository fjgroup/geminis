<?php

namespace App\Models;

/**
 * Modelo de compatibilidad para ResellerProfile
 * 
 * @deprecated Use App\Domains\Users\Models\ResellerProfile instead
 * 
 * Este modelo existe solo para mantener compatibilidad con código existente.
 * Todas las nuevas implementaciones deben usar el modelo del dominio Users.
 */
class ResellerProfile extends \App\Domains\Users\Models\ResellerProfile
{
    // Este modelo extiende del modelo del dominio para mantener compatibilidad
    // No agregar lógica aquí - usar el modelo del dominio directamente
}

<?php

namespace App\Http\Controllers;

/**
 * Alias para mantener compatibilidad con rutas existentes
 * 
 * @deprecated Use App\Domains\Shared\Infrastructure\Http\Controllers\LandingPageController instead
 */
class LandingPageController extends \App\Domains\Shared\Infrastructure\Http\Controllers\LandingPageController
{
    // Esta clase extiende la nueva ubicación hexagonal
    // Mantiene compatibilidad mientras se actualizan las rutas
}

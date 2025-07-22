<?php

namespace App\Domains\BillingAndPayments\Models;

/**
 * Modelo de compatibilidad para Transaction
 * 
 * @deprecated Use App\Domains\BillingAndPayments\Domain\Entities\Transaction instead
 * 
 * Este modelo existe solo para mantener compatibilidad con código existente.
 * Todas las nuevas implementaciones deben usar la entidad del dominio.
 */
class Transaction extends \App\Domains\BillingAndPayments\Domain\Entities\Transaction
{
    // Este modelo extiende de la entidad del dominio para mantener compatibilidad
    // No agregar lógica aquí - usar la entidad del dominio directamente
}

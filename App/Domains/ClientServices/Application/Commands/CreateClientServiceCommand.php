<?php

namespace App\Domains\ClientServices\Application\Commands;

use Carbon\Carbon;

/**
 * Comando para crear un servicio de cliente
 */
class CreateClientServiceCommand
{
    public function __construct(
        public readonly string $clientId,
        public readonly string $productId,
        public readonly float $price,
        public readonly string $currency,
        public readonly Carbon $nextDueDate,
        public readonly ?string $resellerId = null
    ) {}
}

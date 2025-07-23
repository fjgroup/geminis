<?php

namespace App\Domains\Products\Domain\Events;

use App\Domains\Products\Domain\ValueObjects\ProductPrice;

/**
 * Evento de Dominio - ProductPriceChanged
 * 
 * Se dispara cuando cambia el precio de un producto
 */
final readonly class ProductPriceChanged
{
    public function __construct(
        public string $productId,
        public ProductPrice $oldPrice,
        public ProductPrice $newPrice,
        public \DateTimeImmutable $occurredAt = new \DateTimeImmutable()
    ) {}

    public function toArray(): array
    {
        return [
            'product_id' => $this->productId,
            'old_price' => $this->oldPrice->toArray(),
            'new_price' => $this->newPrice->toArray(),
            'occurred_at' => $this->occurredAt->format('Y-m-d H:i:s')
        ];
    }
}

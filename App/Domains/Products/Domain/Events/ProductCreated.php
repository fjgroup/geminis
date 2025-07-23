<?php

namespace App\Domains\Products\Domain\Events;

use App\Domains\Products\Domain\ValueObjects\ProductPrice;

/**
 * Evento de Dominio - ProductCreated
 * 
 * Se dispara cuando se crea un nuevo producto
 */
final readonly class ProductCreated
{
    public function __construct(
        public string $productId,
        public string $productName,
        public ProductPrice $price,
        public \DateTimeImmutable $occurredAt = new \DateTimeImmutable()
    ) {}

    public function toArray(): array
    {
        return [
            'product_id' => $this->productId,
            'product_name' => $this->productName,
            'price' => $this->price->toArray(),
            'occurred_at' => $this->occurredAt->format('Y-m-d H:i:s')
        ];
    }
}

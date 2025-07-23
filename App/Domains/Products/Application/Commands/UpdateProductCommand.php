<?php

namespace App\Domains\Products\Application\Commands;

use App\Domains\Products\Domain\ValueObjects\ProductPrice;

/**
 * Command - UpdateProductCommand
 */
final readonly class UpdateProductCommand
{
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        public ProductPrice $price
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty(trim($this->id))) {
            throw new \InvalidArgumentException('Product ID cannot be empty');
        }

        if (empty(trim($this->name))) {
            throw new \InvalidArgumentException('Product name cannot be empty');
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            name: $data['name'] ?? '',
            description: $data['description'] ?? '',
            price: ProductPrice::fromAmount($data['price'] ?? 0, $data['currency'] ?? 'USD')
        );
    }
}

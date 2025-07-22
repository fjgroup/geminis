<?php

namespace App\Domains\Products\Application\Commands;

/**
 * Command - CreateProductCommand
 * 
 * ✅ BENEFICIOS vs MVC Tradicional:
 * - Inmutable (thread-safe)
 * - Type-safe (no arrays asociativos)
 * - Validación automática en construcción
 * - Fácil de testear
 * - Reutilizable desde cualquier entrada
 * - Documentación automática con tipos
 */
final readonly class CreateProductCommand
{
    public function __construct(
        public string $name,
        public string $description,
        public float $price,
        public string $currency,
        public string $productTypeId
    ) {
        $this->validate();
    }

    /**
     * ✅ BENEFICIO: Validación automática en construcción
     */
    private function validate(): void
    {
        if (empty(trim($this->name))) {
            throw new \InvalidArgumentException('Product name cannot be empty');
        }

        if ($this->price < 0) {
            throw new \InvalidArgumentException('Product price cannot be negative');
        }

        if (empty(trim($this->productTypeId))) {
            throw new \InvalidArgumentException('Product type ID cannot be empty');
        }

        if (empty(trim($this->currency))) {
            throw new \InvalidArgumentException('Currency cannot be empty');
        }
    }

    /**
     * ✅ BENEFICIO: Factory desde array (para compatibilidad)
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            description: $data['description'] ?? '',
            price: (float) ($data['price'] ?? 0),
            currency: $data['currency'] ?? 'USD',
            productTypeId: $data['product_type_id'] ?? ''
        );
    }

    /**
     * Conversión a array para logging/debugging
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'currency' => $this->currency,
            'product_type_id' => $this->productTypeId
        ];
    }
}

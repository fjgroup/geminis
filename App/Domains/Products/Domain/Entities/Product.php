<?php

namespace App\Domains\Products\Domain\Entities;

use App\Domains\Products\Domain\ValueObjects\ProductPrice;
use App\Domains\Products\Domain\ValueObjects\ProductStatus;
use App\Domains\Products\Domain\Events\ProductCreated;
use App\Domains\Products\Domain\Events\ProductPriceChanged;
use App\Domains\Shared\Domain\ValueObjects\Money;
use InvalidArgumentException;

/**
 * Entidad de Dominio Pura - Product
 * 
 * ✅ BENEFICIOS vs MVC Tradicional:
 * - Sin dependencias de framework (Laravel/Eloquent)
 * - Lógica de negocio encapsulada en la entidad
 * - Inmutable y thread-safe
 * - Fácil de testear unitariamente
 * - Reutilizable en cualquier contexto (web, CLI, API, microservicios)
 * - Principios SOLID aplicados
 */
final class Product
{
    private array $domainEvents = [];

    public function __construct(
        private readonly string $id,
        private string $name,
        private string $description,
        private ProductPrice $price,
        private ProductStatus $status,
        private readonly string $productTypeId,
        private readonly \DateTimeImmutable $createdAt
    ) {
        $this->validateName($name);
        $this->validateDescription($description);
    }

    /**
     * Factory method para crear un nuevo producto
     * 
     * ✅ BENEFICIO: Encapsula la lógica de creación y valida reglas de negocio
     */
    public static function create(
        string $id,
        string $name,
        string $description,
        ProductPrice $price,
        string $productTypeId
    ): self {
        $product = new self(
            id: $id,
            name: $name,
            description: $description,
            price: $price,
            status: ProductStatus::draft(),
            productTypeId: $productTypeId,
            createdAt: new \DateTimeImmutable()
        );

        // ✅ BENEFICIO: Eventos de dominio para comunicación entre bounded contexts
        $product->recordDomainEvent(new ProductCreated($id, $name, $price));

        return $product;
    }

    /**
     * Cambiar precio del producto
     * 
     * ✅ BENEFICIO: Lógica de negocio encapsulada, no en controladores
     */
    public function changePrice(ProductPrice $newPrice): void
    {
        if ($this->status->isDiscontinued()) {
            throw new InvalidArgumentException('Cannot change price of discontinued product');
        }

        $oldPrice = $this->price;
        $this->price = $newPrice;

        // ✅ BENEFICIO: Eventos automáticos cuando cambia el estado
        $this->recordDomainEvent(new ProductPriceChanged($this->id, $oldPrice, $newPrice));
    }

    /**
     * Activar producto
     * 
     * ✅ BENEFICIO: Reglas de negocio claras y centralizadas
     */
    public function activate(): void
    {
        if ($this->price->isZero()) {
            throw new InvalidArgumentException('Cannot activate product with zero price');
        }

        $this->status = ProductStatus::active();
    }

    /**
     * Descontinuar producto
     */
    public function discontinue(): void
    {
        $this->status = ProductStatus::discontinued();
    }

    /**
     * Verificar si el producto puede ser ordenado
     * 
     * ✅ BENEFICIO: Lógica de negocio compleja encapsulada
     */
    public function canBeOrdered(): bool
    {
        return $this->status->isActive() && !$this->price->isZero();
    }

    /**
     * Calcular precio con descuento
     * 
     * ✅ BENEFICIO: Cálculos de negocio en la entidad, no en servicios externos
     */
    public function calculateDiscountedPrice(float $discountPercentage): ProductPrice
    {
        if ($discountPercentage < 0 || $discountPercentage > 100) {
            throw new InvalidArgumentException('Discount percentage must be between 0 and 100');
        }

        $discountAmount = $this->price->getAmount() * ($discountPercentage / 100);
        $discountedAmount = $this->price->getAmount() - $discountAmount;

        return new ProductPrice(new Money($discountedAmount, $this->price->getCurrency()));
    }

    /**
     * Actualizar información básica
     */
    public function updateInfo(string $name, string $description): void
    {
        $this->validateName($name);
        $this->validateDescription($description);
        
        $this->name = $name;
        $this->description = $description;
    }

    // ✅ BENEFICIO: Validaciones de dominio centralizadas
    private function validateName(string $name): void
    {
        if (empty(trim($name))) {
            throw new InvalidArgumentException('Product name cannot be empty');
        }

        if (strlen($name) > 255) {
            throw new InvalidArgumentException('Product name cannot exceed 255 characters');
        }
    }

    private function validateDescription(string $description): void
    {
        if (strlen($description) > 1000) {
            throw new InvalidArgumentException('Product description cannot exceed 1000 characters');
        }
    }

    // ✅ BENEFICIO: Eventos de dominio para comunicación desacoplada
    private function recordDomainEvent(object $event): void
    {
        $this->domainEvents[] = $event;
    }

    public function getDomainEvents(): array
    {
        return $this->domainEvents;
    }

    public function clearDomainEvents(): void
    {
        $this->domainEvents = [];
    }

    // Getters inmutables
    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPrice(): ProductPrice
    {
        return $this->price;
    }

    public function getStatus(): ProductStatus
    {
        return $this->status;
    }

    public function getProductTypeId(): string
    {
        return $this->productTypeId;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * ✅ BENEFICIO: Comparación de entidades por identidad, no por referencia
     */
    public function equals(Product $other): bool
    {
        return $this->id === $other->id;
    }

    /**
     * ✅ BENEFICIO: Representación string para debugging
     */
    public function __toString(): string
    {
        return sprintf(
            'Product[id=%s, name=%s, price=%s, status=%s]',
            $this->id,
            $this->name,
            $this->price,
            $this->status
        );
    }
}

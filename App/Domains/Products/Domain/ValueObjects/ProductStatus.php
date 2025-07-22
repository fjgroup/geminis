<?php

namespace App\Domains\Products\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Value Object - ProductStatus
 * 
 * ✅ BENEFICIOS vs MVC Tradicional:
 * - Estados válidos definidos explícitamente
 * - Imposible crear estados inválidos
 * - Lógica de transiciones de estado encapsulada
 * - Type safety completo
 * - Fácil de extender sin romper código existente
 */
final readonly class ProductStatus
{
    private const DRAFT = 'draft';
    private const ACTIVE = 'active';
    private const INACTIVE = 'inactive';
    private const DISCONTINUED = 'discontinued';

    private const VALID_STATUSES = [
        self::DRAFT,
        self::ACTIVE,
        self::INACTIVE,
        self::DISCONTINUED
    ];

    private function __construct(
        private string $value
    ) {
        $this->validate();
    }

    /**
     * Factory methods para cada estado válido
     * ✅ BENEFICIO: Imposible crear estados inválidos
     */
    public static function draft(): self
    {
        return new self(self::DRAFT);
    }

    public static function active(): self
    {
        return new self(self::ACTIVE);
    }

    public static function inactive(): self
    {
        return new self(self::INACTIVE);
    }

    public static function discontinued(): self
    {
        return new self(self::DISCONTINUED);
    }

    /**
     * Factory desde string (para persistencia)
     */
    public static function fromString(string $status): self
    {
        return new self($status);
    }

    /**
     * ✅ BENEFICIO: Validación automática de estados
     */
    private function validate(): void
    {
        if (!in_array($this->value, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid product status: %s. Valid statuses are: %s',
                    $this->value,
                    implode(', ', self::VALID_STATUSES)
                )
            );
        }
    }

    /**
     * ✅ BENEFICIO: Verificaciones de estado type-safe
     */
    public function isDraft(): bool
    {
        return $this->value === self::DRAFT;
    }

    public function isActive(): bool
    {
        return $this->value === self::ACTIVE;
    }

    public function isInactive(): bool
    {
        return $this->value === self::INACTIVE;
    }

    public function isDiscontinued(): bool
    {
        return $this->value === self::DISCONTINUED;
    }

    /**
     * ✅ BENEFICIO: Lógica de negocio para transiciones de estado
     */
    public function canTransitionTo(ProductStatus $newStatus): bool
    {
        return match ($this->value) {
            self::DRAFT => in_array($newStatus->value, [self::ACTIVE, self::DISCONTINUED]),
            self::ACTIVE => in_array($newStatus->value, [self::INACTIVE, self::DISCONTINUED]),
            self::INACTIVE => in_array($newStatus->value, [self::ACTIVE, self::DISCONTINUED]),
            self::DISCONTINUED => false, // No se puede cambiar desde discontinued
        };
    }

    /**
     * Verificar si el producto está disponible para venta
     */
    public function isAvailableForSale(): bool
    {
        return $this->value === self::ACTIVE;
    }

    /**
     * Verificar si se puede editar el producto
     */
    public function isEditable(): bool
    {
        return !$this->isDiscontinued();
    }

    /**
     * Obtener estados válidos para transición
     */
    public function getValidTransitions(): array
    {
        return match ($this->value) {
            self::DRAFT => [self::ACTIVE, self::DISCONTINUED],
            self::ACTIVE => [self::INACTIVE, self::DISCONTINUED],
            self::INACTIVE => [self::ACTIVE, self::DISCONTINUED],
            self::DISCONTINUED => [],
        };
    }

    /**
     * Obtener todos los estados válidos
     */
    public static function getAllValidStatuses(): array
    {
        return self::VALID_STATUSES;
    }

    /**
     * ✅ BENEFICIO: Comparación por valor
     */
    public function equals(ProductStatus $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Conversiones
     */
    public function toString(): string
    {
        return $this->value;
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->getLabel(),
            'is_active' => $this->isActive(),
            'is_available_for_sale' => $this->isAvailableForSale(),
            'is_editable' => $this->isEditable()
        ];
    }

    /**
     * Obtener etiqueta legible para humanos
     */
    public function getLabel(): string
    {
        return match ($this->value) {
            self::DRAFT => 'Borrador',
            self::ACTIVE => 'Activo',
            self::INACTIVE => 'Inactivo',
            self::DISCONTINUED => 'Descontinuado',
        };
    }

    /**
     * Obtener color para UI
     */
    public function getColor(): string
    {
        return match ($this->value) {
            self::DRAFT => 'gray',
            self::ACTIVE => 'green',
            self::INACTIVE => 'yellow',
            self::DISCONTINUED => 'red',
        };
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

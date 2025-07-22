<?php

namespace App\Domains\Products\Domain\ValueObjects;

use App\Domains\Shared\Domain\ValueObjects\Money;
use InvalidArgumentException;

/**
 * Value Object - ProductPrice
 * 
 * ✅ BENEFICIOS vs MVC Tradicional:
 * - Inmutable (thread-safe)
 * - Validaciones automáticas
 * - Lógica de negocio encapsulada
 * - Reutilizable en toda la aplicación
 * - Comparación por valor, no por referencia
 * - Sin dependencias de framework
 */
final readonly class ProductPrice
{
    public function __construct(
        private Money $money
    ) {
        $this->validate();
    }

    /**
     * Factory methods para casos comunes
     */
    public static function free(): self
    {
        return new self(Money::zero('USD'));
    }

    public static function fromAmount(float $amount, string $currency = 'USD'): self
    {
        return new self(new Money($amount, $currency));
    }

    /**
     * ✅ BENEFICIO: Validaciones de dominio automáticas
     */
    private function validate(): void
    {
        if ($this->money->getAmount() < 0) {
            throw new InvalidArgumentException('Product price cannot be negative');
        }
    }

    /**
     * ✅ BENEFICIO: Operaciones de negocio encapsuladas
     */
    public function addTax(float $taxPercentage): self
    {
        if ($taxPercentage < 0) {
            throw new InvalidArgumentException('Tax percentage cannot be negative');
        }

        $taxAmount = $this->money->getAmount() * ($taxPercentage / 100);
        $totalAmount = $this->money->getAmount() + $taxAmount;

        return new self(new Money($totalAmount, $this->money->getCurrency()));
    }

    /**
     * Aplicar descuento
     */
    public function applyDiscount(float $discountPercentage): self
    {
        if ($discountPercentage < 0 || $discountPercentage > 100) {
            throw new InvalidArgumentException('Discount percentage must be between 0 and 100');
        }

        $discountAmount = $this->money->getAmount() * ($discountPercentage / 100);
        $discountedAmount = $this->money->getAmount() - $discountAmount;

        return new self(new Money($discountedAmount, $this->money->getCurrency()));
    }

    /**
     * Comparar precios
     */
    public function isGreaterThan(ProductPrice $other): bool
    {
        return $this->money->isGreaterThan($other->money);
    }

    public function isLessThan(ProductPrice $other): bool
    {
        return $this->money->isLessThan($other->money);
    }

    public function equals(ProductPrice $other): bool
    {
        return $this->money->equals($other->money);
    }

    /**
     * Verificaciones de estado
     */
    public function isZero(): bool
    {
        return $this->money->isZero();
    }

    public function isFree(): bool
    {
        return $this->isZero();
    }

    /**
     * Conversiones
     */
    public function toArray(): array
    {
        return [
            'amount' => $this->money->getAmount(),
            'currency' => $this->money->getCurrency(),
            'formatted' => $this->format()
        ];
    }

    public function format(): string
    {
        return $this->money->format();
    }

    /**
     * Getters
     */
    public function getAmount(): float
    {
        return $this->money->getAmount();
    }

    public function getCurrency(): string
    {
        return $this->money->getCurrency();
    }

    public function getMoney(): Money
    {
        return $this->money;
    }

    /**
     * ✅ BENEFICIO: Representación string para debugging
     */
    public function __toString(): string
    {
        return $this->format();
    }
}

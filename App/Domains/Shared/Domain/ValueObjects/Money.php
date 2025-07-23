<?php

namespace App\Domains\Shared\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Value Object - Money
 * 
 * Representa una cantidad monetaria con validaciones de dominio
 * Inmutable y thread-safe
 */
final readonly class Money
{
    public function __construct(
        private float $amount,
        private string $currency
    ) {
        $this->validate();
    }

    /**
     * Factory methods
     */
    public static function zero(string $currency = 'USD'): self
    {
        return new self(0.0, $currency);
    }

    public static function fromCents(int $cents, string $currency = 'USD'): self
    {
        return new self($cents / 100, $currency);
    }

    /**
     * Validaciones de dominio
     */
    private function validate(): void
    {
        if ($this->amount < 0) {
            throw new InvalidArgumentException('Money amount cannot be negative');
        }

        if (empty(trim($this->currency))) {
            throw new InvalidArgumentException('Currency cannot be empty');
        }

        if (strlen($this->currency) !== 3) {
            throw new InvalidArgumentException('Currency must be 3 characters (ISO 4217)');
        }
    }

    /**
     * Operaciones matemáticas
     */
    public function add(Money $other): self
    {
        $this->ensureSameCurrency($other);
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(Money $other): self
    {
        $this->ensureSameCurrency($other);
        return new self($this->amount - $other->amount, $this->currency);
    }

    public function multiply(float $factor): self
    {
        return new self($this->amount * $factor, $this->currency);
    }

    public function divide(float $divisor): self
    {
        if ($divisor == 0) {
            throw new InvalidArgumentException('Cannot divide by zero');
        }
        return new self($this->amount / $divisor, $this->currency);
    }

    /**
     * Comparaciones
     */
    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }

    public function isGreaterThan(Money $other): bool
    {
        $this->ensureSameCurrency($other);
        return $this->amount > $other->amount;
    }

    public function isLessThan(Money $other): bool
    {
        $this->ensureSameCurrency($other);
        return $this->amount < $other->amount;
    }

    public function isZero(): bool
    {
        return $this->amount === 0.0;
    }

    /**
     * Conversiones
     */
    public function toCents(): int
    {
        return (int) round($this->amount * 100);
    }

    public function format(): string
    {
        return match ($this->currency) {
            'USD' => '$' . number_format($this->amount, 2),
            'EUR' => '€' . number_format($this->amount, 2),
            'GBP' => '£' . number_format($this->amount, 2),
            default => $this->currency . ' ' . number_format($this->amount, 2)
        };
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'formatted' => $this->format()
        ];
    }

    /**
     * Getters
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Validación de moneda
     */
    private function ensureSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                "Cannot operate on different currencies: {$this->currency} vs {$other->currency}"
            );
        }
    }

    public function __toString(): string
    {
        return $this->format();
    }
}

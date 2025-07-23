<?php

namespace App\Domains\ClientServices\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Value Object para el precio de un servicio
 */
class ServicePrice
{
    public function __construct(
        private float $amount,
        private string $currency = 'USD'
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Service price cannot be negative');
        }

        if (empty($currency)) {
            throw new InvalidArgumentException('Currency cannot be empty');
        }
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getFormattedAmount(): string
    {
        return number_format($this->amount, 2);
    }

    public function add(ServicePrice $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Cannot add prices with different currencies');
        }

        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(ServicePrice $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Cannot subtract prices with different currencies');
        }

        $newAmount = $this->amount - $other->amount;
        if ($newAmount < 0) {
            throw new InvalidArgumentException('Resulting price cannot be negative');
        }

        return new self($newAmount, $this->currency);
    }

    public function equals(ServicePrice $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }

    public function __toString(): string
    {
        return "{$this->getFormattedAmount()} {$this->currency}";
    }
}

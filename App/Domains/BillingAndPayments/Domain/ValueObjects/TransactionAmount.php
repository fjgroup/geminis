<?php

namespace App\Domains\BillingAndPayments\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Value Object para representar el monto de una transacción
 * 
 * Cumple con los principios de DDD - Value Objects son inmutables
 * Encapsula la lógica de validación y formateo de montos
 */
final class TransactionAmount
{
    private float $amount;
    private string $currency;

    public function __construct(float $amount, string $currency = 'USD')
    {
        $this->validateAmount($amount);
        $this->validateCurrency($currency);
        
        $this->amount = round($amount, 2);
        $this->currency = strtoupper($currency);
    }

    /**
     * Crear desde string
     */
    public static function fromString(string $amountString, string $currency = 'USD'): self
    {
        $amount = (float) $amountString;
        return new self($amount, $currency);
    }

    /**
     * Crear monto cero
     */
    public static function zero(string $currency = 'USD'): self
    {
        return new self(0.0, $currency);
    }

    /**
     * Obtener el monto
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * Obtener la moneda
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Verificar si es cero
     */
    public function isZero(): bool
    {
        return $this->amount === 0.0;
    }

    /**
     * Verificar si es positivo
     */
    public function isPositive(): bool
    {
        return $this->amount > 0.0;
    }

    /**
     * Verificar si es negativo
     */
    public function isNegative(): bool
    {
        return $this->amount < 0.0;
    }

    /**
     * Sumar otro monto
     */
    public function add(TransactionAmount $other): self
    {
        $this->ensureSameCurrency($other);
        return new self($this->amount + $other->amount, $this->currency);
    }

    /**
     * Restar otro monto
     */
    public function subtract(TransactionAmount $other): self
    {
        $this->ensureSameCurrency($other);
        return new self($this->amount - $other->amount, $this->currency);
    }

    /**
     * Multiplicar por un factor
     */
    public function multiply(float $factor): self
    {
        return new self($this->amount * $factor, $this->currency);
    }

    /**
     * Comparar con otro monto
     */
    public function equals(TransactionAmount $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }

    /**
     * Comparar si es mayor que otro monto
     */
    public function greaterThan(TransactionAmount $other): bool
    {
        $this->ensureSameCurrency($other);
        return $this->amount > $other->amount;
    }

    /**
     * Comparar si es menor que otro monto
     */
    public function lessThan(TransactionAmount $other): bool
    {
        $this->ensureSameCurrency($other);
        return $this->amount < $other->amount;
    }

    /**
     * Formatear para mostrar
     */
    public function format(): string
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }

    /**
     * Convertir a string
     */
    public function toString(): string
    {
        return $this->format();
    }

    /**
     * Convertir a array
     */
    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'formatted' => $this->format()
        ];
    }

    /**
     * Validar monto
     */
    private function validateAmount(float $amount): void
    {
        if (!is_finite($amount)) {
            throw new InvalidArgumentException('El monto debe ser un número finito');
        }
    }

    /**
     * Validar moneda
     */
    private function validateCurrency(string $currency): void
    {
        if (empty($currency) || strlen($currency) !== 3) {
            throw new InvalidArgumentException('La moneda debe ser un código de 3 caracteres');
        }
    }

    /**
     * Asegurar que las monedas sean iguales
     */
    private function ensureSameCurrency(TransactionAmount $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                "No se pueden operar montos con diferentes monedas: {$this->currency} vs {$other->currency}"
            );
        }
    }

    /**
     * Método mágico para string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}

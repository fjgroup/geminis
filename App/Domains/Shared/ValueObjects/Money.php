<?php

namespace App\Domains\Shared\ValueObjects;

use InvalidArgumentException;

/**
 * Class Money
 * 
 * Value Object para representar dinero de forma inmutable
 * Encapsula cantidad y moneda con validaciones
 * Aplica principios de DDD - Value Objects
 */
final class Money
{
    private readonly float $amount;
    private readonly string $currency;

    private const SUPPORTED_CURRENCIES = [
        'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CHF', 'CNY', 'MXN', 'BRL'
    ];

    public function __construct(float $amount, string $currency = 'USD')
    {
        $this->validateAmount($amount);
        $this->validateCurrency($currency);
        
        $this->amount = round($amount, 2);
        $this->currency = strtoupper($currency);
    }

    /**
     * Crear Money desde string
     * 
     * @param string $moneyString Formato: "100.50 USD" o "100.50"
     * @return self
     */
    public static function fromString(string $moneyString): self
    {
        $parts = explode(' ', trim($moneyString));
        
        if (count($parts) === 1) {
            return new self((float) $parts[0]);
        }
        
        if (count($parts) === 2) {
            return new self((float) $parts[0], $parts[1]);
        }
        
        throw new InvalidArgumentException("Invalid money format: {$moneyString}");
    }

    /**
     * Crear Money cero
     * 
     * @param string $currency
     * @return self
     */
    public static function zero(string $currency = 'USD'): self
    {
        return new self(0.0, $currency);
    }

    /**
     * Obtener cantidad
     * 
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * Obtener moneda
     * 
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Sumar dinero (misma moneda)
     * 
     * @param Money $other
     * @return self
     */
    public function add(Money $other): self
    {
        $this->ensureSameCurrency($other);
        
        return new self($this->amount + $other->amount, $this->currency);
    }

    /**
     * Restar dinero (misma moneda)
     * 
     * @param Money $other
     * @return self
     */
    public function subtract(Money $other): self
    {
        $this->ensureSameCurrency($other);
        
        return new self($this->amount - $other->amount, $this->currency);
    }

    /**
     * Multiplicar por factor
     * 
     * @param float $factor
     * @return self
     */
    public function multiply(float $factor): self
    {
        return new self($this->amount * $factor, $this->currency);
    }

    /**
     * Dividir por factor
     * 
     * @param float $divisor
     * @return self
     */
    public function divide(float $divisor): self
    {
        if ($divisor == 0) {
            throw new InvalidArgumentException('Cannot divide by zero');
        }
        
        return new self($this->amount / $divisor, $this->currency);
    }

    /**
     * Verificar si es igual a otro Money
     * 
     * @param Money $other
     * @return bool
     */
    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }

    /**
     * Verificar si es mayor que otro Money
     * 
     * @param Money $other
     * @return bool
     */
    public function isGreaterThan(Money $other): bool
    {
        $this->ensureSameCurrency($other);
        
        return $this->amount > $other->amount;
    }

    /**
     * Verificar si es menor que otro Money
     * 
     * @param Money $other
     * @return bool
     */
    public function isLessThan(Money $other): bool
    {
        $this->ensureSameCurrency($other);
        
        return $this->amount < $other->amount;
    }

    /**
     * Verificar si es cero
     * 
     * @return bool
     */
    public function isZero(): bool
    {
        return $this->amount === 0.0;
    }

    /**
     * Verificar si es positivo
     * 
     * @return bool
     */
    public function isPositive(): bool
    {
        return $this->amount > 0.0;
    }

    /**
     * Verificar si es negativo
     * 
     * @return bool
     */
    public function isNegative(): bool
    {
        return $this->amount < 0.0;
    }

    /**
     * Formatear como string
     * 
     * @param bool $includeCurrency
     * @return string
     */
    public function format(bool $includeCurrency = true): string
    {
        $formatted = number_format($this->amount, 2);
        
        if ($includeCurrency) {
            return "{$formatted} {$this->currency}";
        }
        
        return $formatted;
    }

    /**
     * Convertir a array
     * 
     * @return array
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
     * Representación como string
     * 
     * @return string
     */
    public function __toString(): string
    {
        return $this->format();
    }

    /**
     * Validar cantidad
     * 
     * @param float $amount
     * @throws InvalidArgumentException
     */
    private function validateAmount(float $amount): void
    {
        if (!is_finite($amount)) {
            throw new InvalidArgumentException('Amount must be a finite number');
        }
    }

    /**
     * Validar moneda
     * 
     * @param string $currency
     * @throws InvalidArgumentException
     */
    private function validateCurrency(string $currency): void
    {
        $currency = strtoupper($currency);
        
        if (!in_array($currency, self::SUPPORTED_CURRENCIES)) {
            throw new InvalidArgumentException("Unsupported currency: {$currency}");
        }
    }

    /**
     * Asegurar que las monedas sean iguales
     * 
     * @param Money $other
     * @throws InvalidArgumentException
     */
    private function ensureSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                "Currency mismatch: {$this->currency} vs {$other->currency}"
            );
        }
    }
}

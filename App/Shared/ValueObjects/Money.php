<?php

namespace App\Shared\ValueObjects;

use InvalidArgumentException;

/**
 * Class Money
 * 
 * Value Object para representar dinero de manera inmutable
 * Implementa mejores prácticas de Domain-Driven Design
 */
class Money
{
    private float $amount;
    private string $currency;

    /**
     * Constructor
     *
     * @param float $amount
     * @param string $currency
     * @throws InvalidArgumentException
     */
    public function __construct(float $amount, string $currency = 'USD')
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('El monto no puede ser negativo');
        }

        if (empty($currency) || strlen($currency) !== 3) {
            throw new InvalidArgumentException('La moneda debe ser un código de 3 caracteres');
        }

        $this->amount = round($amount, 2);
        $this->currency = strtoupper($currency);
    }

    /**
     * Crear instancia desde string
     *
     * @param string $moneyString Formato: "100.50 USD"
     * @return static
     */
    public static function fromString(string $moneyString): self
    {
        $parts = explode(' ', trim($moneyString));
        
        if (count($parts) !== 2) {
            throw new InvalidArgumentException('Formato inválido. Use: "100.50 USD"');
        }

        return new self((float) $parts[0], $parts[1]);
    }

    /**
     * Obtener el monto
     *
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * Obtener la moneda
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
     * @return Money
     * @throws InvalidArgumentException
     */
    public function add(Money $other): Money
    {
        $this->ensureSameCurrency($other);
        return new Money($this->amount + $other->amount, $this->currency);
    }

    /**
     * Restar dinero (misma moneda)
     *
     * @param Money $other
     * @return Money
     * @throws InvalidArgumentException
     */
    public function subtract(Money $other): Money
    {
        $this->ensureSameCurrency($other);
        return new Money($this->amount - $other->amount, $this->currency);
    }

    /**
     * Multiplicar por un factor
     *
     * @param float $multiplier
     * @return Money
     */
    public function multiply(float $multiplier): Money
    {
        return new Money($this->amount * $multiplier, $this->currency);
    }

    /**
     * Dividir por un divisor
     *
     * @param float $divisor
     * @return Money
     * @throws InvalidArgumentException
     */
    public function divide(float $divisor): Money
    {
        if ($divisor == 0) {
            throw new InvalidArgumentException('No se puede dividir por cero');
        }

        return new Money($this->amount / $divisor, $this->currency);
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
     * Formatear como string
     *
     * @return string
     */
    public function toString(): string
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }

    /**
     * Formatear como moneda
     *
     * @param string $locale
     * @return string
     */
    public function format(string $locale = 'en_US'): string
    {
        if (class_exists('NumberFormatter')) {
            $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
            return $formatter->formatCurrency($this->amount, $this->currency);
        }

        return $this->toString();
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
            'formatted' => $this->toString()
        ];
    }

    /**
     * Representación como string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Asegurar que ambos Money tienen la misma moneda
     *
     * @param Money $other
     * @throws InvalidArgumentException
     */
    private function ensureSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                "No se pueden operar monedas diferentes: {$this->currency} vs {$other->currency}"
            );
        }
    }
}

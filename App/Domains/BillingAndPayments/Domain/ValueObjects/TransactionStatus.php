<?php

namespace App\Domains\BillingAndPayments\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Value Object para representar el estado de una transacción
 * 
 * Cumple con los principios de DDD - Value Objects son inmutables
 * Encapsula la lógica de validación de estados de transacción
 */
final class TransactionStatus
{
    public const PENDING = 'pending';
    public const COMPLETED = 'completed';
    public const FAILED = 'failed';
    public const CANCELLED = 'cancelled';
    public const REFUNDED = 'refunded';
    public const PROCESSING = 'processing';

    private string $status;

    private const VALID_STATUSES = [
        self::PENDING,
        self::COMPLETED,
        self::FAILED,
        self::CANCELLED,
        self::REFUNDED,
        self::PROCESSING,
    ];

    private const STATUS_LABELS = [
        self::PENDING => 'Pendiente',
        self::COMPLETED => 'Completada',
        self::FAILED => 'Fallida',
        self::CANCELLED => 'Cancelada',
        self::REFUNDED => 'Reembolsada',
        self::PROCESSING => 'Procesando',
    ];

    private const FINAL_STATUSES = [
        self::COMPLETED,
        self::FAILED,
        self::CANCELLED,
        self::REFUNDED,
    ];

    public function __construct(string $status)
    {
        $this->validateStatus($status);
        $this->status = $status;
    }

    /**
     * Crear estado pendiente
     */
    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    /**
     * Crear estado completado
     */
    public static function completed(): self
    {
        return new self(self::COMPLETED);
    }

    /**
     * Crear estado fallido
     */
    public static function failed(): self
    {
        return new self(self::FAILED);
    }

    /**
     * Crear estado cancelado
     */
    public static function cancelled(): self
    {
        return new self(self::CANCELLED);
    }

    /**
     * Crear estado reembolsado
     */
    public static function refunded(): self
    {
        return new self(self::REFUNDED);
    }

    /**
     * Crear estado procesando
     */
    public static function processing(): self
    {
        return new self(self::PROCESSING);
    }

    /**
     * Obtener el valor del estado
     */
    public function getValue(): string
    {
        return $this->status;
    }

    /**
     * Obtener la etiqueta del estado
     */
    public function getLabel(): string
    {
        return self::STATUS_LABELS[$this->status];
    }

    /**
     * Verificar si es pendiente
     */
    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }

    /**
     * Verificar si está completada
     */
    public function isCompleted(): bool
    {
        return $this->status === self::COMPLETED;
    }

    /**
     * Verificar si falló
     */
    public function isFailed(): bool
    {
        return $this->status === self::FAILED;
    }

    /**
     * Verificar si está cancelada
     */
    public function isCancelled(): bool
    {
        return $this->status === self::CANCELLED;
    }

    /**
     * Verificar si está reembolsada
     */
    public function isRefunded(): bool
    {
        return $this->status === self::REFUNDED;
    }

    /**
     * Verificar si está procesando
     */
    public function isProcessing(): bool
    {
        return $this->status === self::PROCESSING;
    }

    /**
     * Verificar si es un estado final (no puede cambiar)
     */
    public function isFinal(): bool
    {
        return in_array($this->status, self::FINAL_STATUSES);
    }

    /**
     * Verificar si puede cambiar a otro estado
     */
    public function canChangeTo(TransactionStatus $newStatus): bool
    {
        // Si el estado actual es final, no puede cambiar
        if ($this->isFinal()) {
            return false;
        }

        // Lógica de transiciones válidas
        return match ($this->status) {
            self::PENDING => in_array($newStatus->status, [self::COMPLETED, self::FAILED, self::CANCELLED, self::PROCESSING]),
            self::PROCESSING => in_array($newStatus->status, [self::COMPLETED, self::FAILED, self::CANCELLED]),
            default => false,
        };
    }

    /**
     * Obtener todos los estados válidos
     */
    public static function getAllStatuses(): array
    {
        return self::VALID_STATUSES;
    }

    /**
     * Obtener todas las etiquetas
     */
    public static function getAllLabels(): array
    {
        return self::STATUS_LABELS;
    }

    /**
     * Comparar con otro estado
     */
    public function equals(TransactionStatus $other): bool
    {
        return $this->status === $other->status;
    }

    /**
     * Convertir a string
     */
    public function toString(): string
    {
        return $this->status;
    }

    /**
     * Convertir a array
     */
    public function toArray(): array
    {
        return [
            'value' => $this->status,
            'label' => $this->getLabel(),
            'is_final' => $this->isFinal(),
        ];
    }

    /**
     * Validar estado
     */
    private function validateStatus(string $status): void
    {
        if (!in_array($status, self::VALID_STATUSES)) {
            throw new InvalidArgumentException(
                "Estado de transacción inválido: {$status}. Estados válidos: " . implode(', ', self::VALID_STATUSES)
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

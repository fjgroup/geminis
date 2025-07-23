<?php

namespace App\Domains\ClientServices\Domain\ValueObjects;

use App\Domains\Shared\Domain\ValueObjects\Enum;
use InvalidArgumentException;

/**
 * Value Object para el estado de un servicio
 */
class ServiceStatus extends Enum
{
    private const ACTIVE = 'active';
    private const SUSPENDED = 'suspended';
    private const TERMINATED = 'terminated';
    private const PENDING = 'pending';

    private const VALID_STATUSES = [
        self::ACTIVE,
        self::SUSPENDED,
        self::TERMINATED,
        self::PENDING
    ];

    public function __construct(private string $value)
    {
        if (!in_array($value, self::VALID_STATUSES)) {
            throw new InvalidArgumentException("Invalid service status: {$value}");
        }
    }

    public static function active(): self
    {
        return new self(self::ACTIVE);
    }

    public static function suspended(): self
    {
        return new self(self::SUSPENDED);
    }

    public static function terminated(): self
    {
        return new self(self::TERMINATED);
    }

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public function isActive(): bool
    {
        return $this->value === self::ACTIVE;
    }

    public function isSuspended(): bool
    {
        return $this->value === self::SUSPENDED;
    }

    public function isTerminated(): bool
    {
        return $this->value === self::TERMINATED;
    }

    public function isPending(): bool
    {
        return $this->value === self::PENDING;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

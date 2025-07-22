<?php

namespace App\Domains\Shared\ValueObjects;

use InvalidArgumentException;

/**
 * Class Email
 * 
 * Value Object para representar emails de forma inmutable
 * Encapsula validación y normalización de emails
 * Aplica principios de DDD - Value Objects
 */
final class Email
{
    private readonly string $value;

    public function __construct(string $email)
    {
        $normalizedEmail = $this->normalize($email);
        $this->validate($normalizedEmail);
        
        $this->value = $normalizedEmail;
    }

    /**
     * Crear Email desde string
     * 
     * @param string $email
     * @return self
     */
    public static function fromString(string $email): self
    {
        return new self($email);
    }

    /**
     * Obtener valor del email
     * 
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Obtener parte local (antes del @)
     * 
     * @return string
     */
    public function getLocalPart(): string
    {
        return explode('@', $this->value)[0];
    }

    /**
     * Obtener dominio (después del @)
     * 
     * @return string
     */
    public function getDomain(): string
    {
        return explode('@', $this->value)[1];
    }

    /**
     * Verificar si es igual a otro Email
     * 
     * @param Email $other
     * @return bool
     */
    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Verificar si pertenece a un dominio específico
     * 
     * @param string $domain
     * @return bool
     */
    public function belongsToDomain(string $domain): bool
    {
        return strtolower($this->getDomain()) === strtolower($domain);
    }

    /**
     * Verificar si es un email corporativo (no proveedores gratuitos)
     * 
     * @return bool
     */
    public function isCorporate(): bool
    {
        $freeProviders = [
            'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com',
            'aol.com', 'icloud.com', 'protonmail.com', 'mail.com'
        ];
        
        $domain = strtolower($this->getDomain());
        
        return !in_array($domain, $freeProviders);
    }

    /**
     * Verificar si es un email temporal/desechable
     * 
     * @return bool
     */
    public function isTemporary(): bool
    {
        $temporaryProviders = [
            '10minutemail.com', 'tempmail.org', 'guerrillamail.com',
            'mailinator.com', 'throwaway.email', 'temp-mail.org'
        ];
        
        $domain = strtolower($this->getDomain());
        
        return in_array($domain, $temporaryProviders);
    }

    /**
     * Obtener versión enmascarada para mostrar
     * 
     * @return string
     */
    public function getMasked(): string
    {
        $localPart = $this->getLocalPart();
        $domain = $this->getDomain();
        
        if (strlen($localPart) <= 2) {
            return "**@{$domain}";
        }
        
        $maskedLocal = substr($localPart, 0, 2) . str_repeat('*', strlen($localPart) - 2);
        
        return "{$maskedLocal}@{$domain}";
    }

    /**
     * Convertir a array
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'local_part' => $this->getLocalPart(),
            'domain' => $this->getDomain(),
            'is_corporate' => $this->isCorporate(),
            'is_temporary' => $this->isTemporary(),
            'masked' => $this->getMasked()
        ];
    }

    /**
     * Representación como string
     * 
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Normalizar email
     * 
     * @param string $email
     * @return string
     */
    private function normalize(string $email): string
    {
        return strtolower(trim($email));
    }

    /**
     * Validar email
     * 
     * @param string $email
     * @throws InvalidArgumentException
     */
    private function validate(string $email): void
    {
        if (empty($email)) {
            throw new InvalidArgumentException('Email cannot be empty');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email format: {$email}");
        }

        // Validaciones adicionales
        if (strlen($email) > 254) {
            throw new InvalidArgumentException('Email is too long (max 254 characters)');
        }

        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            throw new InvalidArgumentException("Invalid email format: {$email}");
        }

        [$localPart, $domain] = $parts;

        if (strlen($localPart) > 64) {
            throw new InvalidArgumentException('Email local part is too long (max 64 characters)');
        }

        if (strlen($domain) > 253) {
            throw new InvalidArgumentException('Email domain is too long (max 253 characters)');
        }

        if (empty($localPart) || empty($domain)) {
            throw new InvalidArgumentException("Invalid email format: {$email}");
        }
    }
}

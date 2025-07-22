<?php

namespace App\ValueObjects;

use InvalidArgumentException;

/**
 * Class UserRole
 * 
 * Value Object para representar roles de usuario de manera type-safe
 * Implementa mejores prácticas de Domain-Driven Design
 */
class UserRole
{
    public const ADMIN = 'admin';
    public const RESELLER = 'reseller';
    public const CLIENT = 'client';

    private const VALID_ROLES = [
        self::ADMIN,
        self::RESELLER,
        self::CLIENT
    ];

    private const ROLE_LABELS = [
        self::ADMIN => 'Administrador',
        self::RESELLER => 'Revendedor',
        self::CLIENT => 'Cliente'
    ];

    private const ROLE_PERMISSIONS = [
        self::ADMIN => [
            'manage_users',
            'manage_products',
            'manage_invoices',
            'manage_transactions',
            'view_reports',
            'manage_settings'
        ],
        self::RESELLER => [
            'manage_clients',
            'view_products',
            'create_invoices',
            'view_transactions',
            'view_reports'
        ],
        self::CLIENT => [
            'view_services',
            'view_invoices',
            'make_payments',
            'update_profile'
        ]
    ];

    private string $value;

    /**
     * Constructor
     *
     * @param string $value
     * @throws InvalidArgumentException
     */
    public function __construct(string $value)
    {
        if (!in_array($value, self::VALID_ROLES)) {
            throw new InvalidArgumentException("Rol inválido: {$value}");
        }

        $this->value = $value;
    }

    /**
     * Crear rol Admin
     *
     * @return static
     */
    public static function admin(): self
    {
        return new self(self::ADMIN);
    }

    /**
     * Crear rol Reseller
     *
     * @return static
     */
    public static function reseller(): self
    {
        return new self(self::RESELLER);
    }

    /**
     * Crear rol Client
     *
     * @return static
     */
    public static function client(): self
    {
        return new self(self::CLIENT);
    }

    /**
     * Obtener el valor del rol
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Obtener la etiqueta del rol
     *
     * @return string
     */
    public function getLabel(): string
    {
        return self::ROLE_LABELS[$this->value];
    }

    /**
     * Obtener permisos del rol
     *
     * @return array
     */
    public function getPermissions(): array
    {
        return self::ROLE_PERMISSIONS[$this->value] ?? [];
    }

    /**
     * Verificar si tiene un permiso específico
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->getPermissions());
    }

    /**
     * Verificar si es admin
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->value === self::ADMIN;
    }

    /**
     * Verificar si es reseller
     *
     * @return bool
     */
    public function isReseller(): bool
    {
        return $this->value === self::RESELLER;
    }

    /**
     * Verificar si es client
     *
     * @return bool
     */
    public function isClient(): bool
    {
        return $this->value === self::CLIENT;
    }

    /**
     * Verificar si es igual a otro rol
     *
     * @param UserRole $other
     * @return bool
     */
    public function equals(UserRole $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Verificar si puede gestionar otro rol
     *
     * @param UserRole $other
     * @return bool
     */
    public function canManage(UserRole $other): bool
    {
        // Admin puede gestionar todos
        if ($this->isAdmin()) {
            return true;
        }

        // Reseller puede gestionar clientes
        if ($this->isReseller() && $other->isClient()) {
            return true;
        }

        return false;
    }

    /**
     * Obtener todos los roles válidos
     *
     * @return array
     */
    public static function getValidRoles(): array
    {
        return self::VALID_ROLES;
    }

    /**
     * Obtener todas las etiquetas de roles
     *
     * @return array
     */
    public static function getRoleLabels(): array
    {
        return self::ROLE_LABELS;
    }

    /**
     * Verificar si un valor es un rol válido
     *
     * @param string $value
     * @return bool
     */
    public static function isValid(string $value): bool
    {
        return in_array($value, self::VALID_ROLES);
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
            'label' => $this->getLabel(),
            'permissions' => $this->getPermissions()
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
}

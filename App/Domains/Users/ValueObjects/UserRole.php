<?php

namespace App\Domains\Users\ValueObjects;

use InvalidArgumentException;

/**
 * Class UserRole
 * 
 * Value Object para representar roles de usuario de forma inmutable
 * Encapsula validación y lógica de roles
 * Aplica principios de DDD - Value Objects
 */
final class UserRole
{
    private readonly string $value;

    public const ADMIN = 'admin';
    public const RESELLER = 'reseller';
    public const CLIENT = 'client';

    private const VALID_ROLES = [
        self::ADMIN,
        self::RESELLER,
        self::CLIENT,
    ];

    private const ROLE_HIERARCHY = [
        self::ADMIN => 3,
        self::RESELLER => 2,
        self::CLIENT => 1,
    ];

    private const ROLE_PERMISSIONS = [
        self::ADMIN => [
            'manage_users',
            'manage_products',
            'manage_invoices',
            'manage_services',
            'view_reports',
            'manage_system',
            'manage_resellers',
            'manage_clients',
        ],
        self::RESELLER => [
            'manage_clients',
            'view_reports',
            'manage_services',
            'create_invoices',
            'view_products',
        ],
        self::CLIENT => [
            'view_services',
            'view_invoices',
            'update_profile',
        ],
    ];

    public function __construct(string $role)
    {
        $normalizedRole = $this->normalize($role);
        $this->validate($normalizedRole);
        
        $this->value = $normalizedRole;
    }

    /**
     * Crear UserRole desde string
     * 
     * @param string $role
     * @return self
     */
    public static function fromString(string $role): self
    {
        return new self($role);
    }

    /**
     * Crear rol Admin
     * 
     * @return self
     */
    public static function admin(): self
    {
        return new self(self::ADMIN);
    }

    /**
     * Crear rol Reseller
     * 
     * @return self
     */
    public static function reseller(): self
    {
        return new self(self::RESELLER);
    }

    /**
     * Crear rol Client
     * 
     * @return self
     */
    public static function client(): self
    {
        return new self(self::CLIENT);
    }

    /**
     * Obtener valor del rol
     * 
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Verificar si es igual a otro UserRole
     * 
     * @param UserRole $other
     * @return bool
     */
    public function equals(UserRole $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Verificar si es Admin
     * 
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->value === self::ADMIN;
    }

    /**
     * Verificar si es Reseller
     * 
     * @return bool
     */
    public function isReseller(): bool
    {
        return $this->value === self::RESELLER;
    }

    /**
     * Verificar si es Client
     * 
     * @return bool
     */
    public function isClient(): bool
    {
        return $this->value === self::CLIENT;
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
     * Obtener todos los permisos del rol
     * 
     * @return array
     */
    public function getPermissions(): array
    {
        return self::ROLE_PERMISSIONS[$this->value] ?? [];
    }

    /**
     * Verificar si puede gestionar otro rol
     * 
     * @param UserRole $other
     * @return bool
     */
    public function canManage(UserRole $other): bool
    {
        return $this->getHierarchyLevel() > $other->getHierarchyLevel();
    }

    /**
     * Obtener nivel en la jerarquía
     * 
     * @return int
     */
    public function getHierarchyLevel(): int
    {
        return self::ROLE_HIERARCHY[$this->value] ?? 0;
    }

    /**
     * Verificar si puede crear usuarios de un rol específico
     * 
     * @param UserRole $targetRole
     * @return bool
     */
    public function canCreateRole(UserRole $targetRole): bool
    {
        // Admin puede crear cualquier rol
        if ($this->isAdmin()) {
            return true;
        }
        
        // Reseller solo puede crear clientes
        if ($this->isReseller()) {
            return $targetRole->isClient();
        }
        
        // Client no puede crear usuarios
        return false;
    }

    /**
     * Obtener roles que puede gestionar
     * 
     * @return array
     */
    public function getManagedRoles(): array
    {
        if ($this->isAdmin()) {
            return [self::ADMIN, self::RESELLER, self::CLIENT];
        }
        
        if ($this->isReseller()) {
            return [self::CLIENT];
        }
        
        return [];
    }

    /**
     * Obtener nombre legible del rol
     * 
     * @return string
     */
    public function getDisplayName(): string
    {
        return match ($this->value) {
            self::ADMIN => 'Administrador',
            self::RESELLER => 'Revendedor',
            self::CLIENT => 'Cliente',
            default => ucfirst($this->value),
        };
    }

    /**
     * Obtener descripción del rol
     * 
     * @return string
     */
    public function getDescription(): string
    {
        return match ($this->value) {
            self::ADMIN => 'Acceso completo al sistema',
            self::RESELLER => 'Puede gestionar clientes y servicios',
            self::CLIENT => 'Acceso a sus servicios y facturas',
            default => 'Rol personalizado',
        };
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
            'display_name' => $this->getDisplayName(),
            'description' => $this->getDescription(),
            'hierarchy_level' => $this->getHierarchyLevel(),
            'permissions' => $this->getPermissions(),
            'managed_roles' => $this->getManagedRoles(),
        ];
    }

    /**
     * Obtener todos los roles válidos
     * 
     * @return array
     */
    public static function getAllRoles(): array
    {
        return self::VALID_ROLES;
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
     * Normalizar rol
     * 
     * @param string $role
     * @return string
     */
    private function normalize(string $role): string
    {
        return strtolower(trim($role));
    }

    /**
     * Validar rol
     * 
     * @param string $role
     * @throws InvalidArgumentException
     */
    private function validate(string $role): void
    {
        if (empty($role)) {
            throw new InvalidArgumentException('Role cannot be empty');
        }

        if (!in_array($role, self::VALID_ROLES)) {
            throw new InvalidArgumentException(
                "Invalid role: {$role}. Valid roles are: " . implode(', ', self::VALID_ROLES)
            );
        }
    }
}

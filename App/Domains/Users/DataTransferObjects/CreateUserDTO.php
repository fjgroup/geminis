<?php

namespace App\Domains\Users\DataTransferObjects;

/**
 * Class CreateUserDTO
 * 
 * Data Transfer Object para la creación de usuarios
 * Encapsula todos los datos necesarios para crear un usuario
 * Aplica el principio de Single Responsibility
 */
class CreateUserDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly string $role = 'client',
        public readonly ?int $reseller_id = null,
        public readonly ?string $company_name = null,
        public readonly ?string $phone = null,
        public readonly ?string $address = null,
        public readonly ?string $city = null,
        public readonly ?string $state = null,
        public readonly ?string $postal_code = null,
        public readonly ?string $country = null,
        public readonly ?string $company_logo = null,
        public readonly string $status = 'active',
        public readonly string $language_code = 'es',
        public readonly string $currency_code = 'USD',
        public readonly float $balance = 0.00,
        public readonly ?array $reseller_profile = null,
    ) {}

    /**
     * Crear DTO desde datos de request validados
     * 
     * @param array $data
     * @return self
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            role: $data['role'] ?? 'client',
            reseller_id: $data['reseller_id'] ?? null,
            company_name: $data['company_name'] ?? null,
            phone: $data['phone'] ?? null,
            address: $data['address'] ?? null,
            city: $data['city'] ?? null,
            state: $data['state'] ?? null,
            postal_code: $data['postal_code'] ?? null,
            country: $data['country'] ?? null,
            company_logo: $data['company_logo'] ?? null,
            status: $data['status'] ?? 'active',
            language_code: $data['language_code'] ?? 'es',
            currency_code: $data['currency_code'] ?? 'USD',
            balance: $data['balance'] ?? 0.00,
            reseller_profile: $data['reseller_profile'] ?? null,
        );
    }

    /**
     * Crear DTO para cliente de reseller
     * 
     * @param array $data
     * @param int $resellerId
     * @return self
     */
    public static function fromResellerClientRequest(array $data, int $resellerId): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            role: 'client',
            reseller_id: $resellerId,
            company_name: $data['company_name'] ?? null,
            phone: $data['phone'] ?? null,
            address: $data['address'] ?? null,
            city: $data['city'] ?? null,
            state: $data['state'] ?? null,
            postal_code: $data['postal_code'] ?? null,
            country: $data['country'] ?? null,
            status: 'active',
            language_code: $data['language_code'] ?? 'es',
            currency_code: $data['currency_code'] ?? 'USD',
            balance: 0.00,
        );
    }

    /**
     * Convertir DTO a array para Eloquent
     * 
     * @return array
     */
    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'role' => $this->role,
            'reseller_id' => $this->reseller_id,
            'company_name' => $this->company_name,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'company_logo' => $this->company_logo,
            'status' => $this->status,
            'language_code' => $this->language_code,
            'currency_code' => $this->currency_code,
            'balance' => $this->balance,
        ];

        // Filtrar valores nulos
        return array_filter($data, fn($value) => $value !== null);
    }

    /**
     * Obtener datos del perfil de reseller
     * 
     * @return array|null
     */
    public function getResellerProfileData(): ?array
    {
        return $this->reseller_profile;
    }

    /**
     * Validar que los datos del DTO son válidos
     * 
     * @return bool
     */
    public function isValid(): bool
    {
        return !empty($this->name) && 
               !empty($this->email) && 
               !empty($this->password) &&
               filter_var($this->email, FILTER_VALIDATE_EMAIL) !== false &&
               in_array($this->role, ['admin', 'reseller', 'client']) &&
               strlen($this->password) >= 8;
    }

    /**
     * Obtener errores de validación
     * 
     * @return array
     */
    public function getValidationErrors(): array
    {
        $errors = [];

        if (empty($this->name)) {
            $errors[] = 'El nombre es requerido';
        }

        if (empty($this->email)) {
            $errors[] = 'El email es requerido';
        } elseif (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'El email no tiene un formato válido';
        }

        if (empty($this->password)) {
            $errors[] = 'La contraseña es requerida';
        } elseif (strlen($this->password) < 8) {
            $errors[] = 'La contraseña debe tener al menos 8 caracteres';
        }

        if (!in_array($this->role, ['admin', 'reseller', 'client'])) {
            $errors[] = 'El rol debe ser admin, reseller o client';
        }

        if ($this->balance < 0) {
            $errors[] = 'El balance no puede ser negativo';
        }

        // Validaciones específicas para resellers
        if ($this->role === 'reseller' && $this->reseller_profile) {
            $profile = $this->reseller_profile;
            
            if (isset($profile['commission_rate']) && ($profile['commission_rate'] < 0 || $profile['commission_rate'] > 100)) {
                $errors[] = 'La tasa de comisión debe estar entre 0 y 100';
            }
            
            if (isset($profile['max_clients']) && $profile['max_clients'] < 0) {
                $errors[] = 'El máximo de clientes no puede ser negativo';
            }
        }

        return $errors;
    }

    /**
     * Verificar si es un usuario reseller
     * 
     * @return bool
     */
    public function isReseller(): bool
    {
        return $this->role === 'reseller';
    }

    /**
     * Verificar si es un cliente de reseller
     * 
     * @return bool
     */
    public function isResellerClient(): bool
    {
        return $this->role === 'client' && $this->reseller_id !== null;
    }

    /**
     * Verificar si es un administrador
     * 
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}

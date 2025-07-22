<?php

namespace App\Domains\Users\DataTransferObjects;

/**
 * Class UpdateUserDTO
 * 
 * Data Transfer Object para la actualización de usuarios
 * Permite actualizaciones parciales usando propiedades opcionales
 */
class UpdateUserDTO
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $email = null,
        public readonly ?string $password = null,
        public readonly ?string $role = null,
        public readonly ?int $reseller_id = null,
        public readonly ?string $company_name = null,
        public readonly ?string $phone = null,
        public readonly ?string $address = null,
        public readonly ?string $city = null,
        public readonly ?string $state = null,
        public readonly ?string $postal_code = null,
        public readonly ?string $country = null,
        public readonly ?string $company_logo = null,
        public readonly ?string $status = null,
        public readonly ?string $language_code = null,
        public readonly ?string $currency_code = null,
        public readonly ?float $balance = null,
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
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            password: $data['password'] ?? null,
            role: $data['role'] ?? null,
            reseller_id: $data['reseller_id'] ?? null,
            company_name: $data['company_name'] ?? null,
            phone: $data['phone'] ?? null,
            address: $data['address'] ?? null,
            city: $data['city'] ?? null,
            state: $data['state'] ?? null,
            postal_code: $data['postal_code'] ?? null,
            country: $data['country'] ?? null,
            company_logo: $data['company_logo'] ?? null,
            status: $data['status'] ?? null,
            language_code: $data['language_code'] ?? null,
            currency_code: $data['currency_code'] ?? null,
            balance: $data['balance'] ?? null,
            reseller_profile: $data['reseller_profile'] ?? null,
        );
    }

    /**
     * Convertir DTO a array para Eloquent (solo campos no nulos)
     * 
     * @return array
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->name !== null) $data['name'] = $this->name;
        if ($this->email !== null) $data['email'] = $this->email;
        if ($this->password !== null) $data['password'] = $this->password;
        if ($this->role !== null) $data['role'] = $this->role;
        if ($this->reseller_id !== null) $data['reseller_id'] = $this->reseller_id;
        if ($this->company_name !== null) $data['company_name'] = $this->company_name;
        if ($this->phone !== null) $data['phone'] = $this->phone;
        if ($this->address !== null) $data['address'] = $this->address;
        if ($this->city !== null) $data['city'] = $this->city;
        if ($this->state !== null) $data['state'] = $this->state;
        if ($this->postal_code !== null) $data['postal_code'] = $this->postal_code;
        if ($this->country !== null) $data['country'] = $this->country;
        if ($this->company_logo !== null) $data['company_logo'] = $this->company_logo;
        if ($this->status !== null) $data['status'] = $this->status;
        if ($this->language_code !== null) $data['language_code'] = $this->language_code;
        if ($this->currency_code !== null) $data['currency_code'] = $this->currency_code;
        if ($this->balance !== null) $data['balance'] = $this->balance;

        return $data;
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
     * Verificar si hay campos para actualizar
     * 
     * @return bool
     */
    public function hasUpdates(): bool
    {
        return !empty($this->toArray()) || $this->reseller_profile !== null;
    }

    /**
     * Obtener lista de campos que se van a actualizar
     * 
     * @return array
     */
    public function getUpdatedFields(): array
    {
        $fields = array_keys($this->toArray());
        
        if ($this->reseller_profile !== null) {
            $fields[] = 'reseller_profile';
        }
        
        return $fields;
    }

    /**
     * Validar que los datos del DTO son válidos
     * 
     * @return bool
     */
    public function isValid(): bool
    {
        // Para actualizaciones, validamos solo los campos que no son nulos
        if ($this->name !== null && empty($this->name)) {
            return false;
        }

        if ($this->email !== null && filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }

        if ($this->password !== null && strlen($this->password) < 8) {
            return false;
        }

        if ($this->role !== null && !in_array($this->role, ['admin', 'reseller', 'client'])) {
            return false;
        }

        if ($this->balance !== null && $this->balance < 0) {
            return false;
        }

        return true;
    }

    /**
     * Obtener errores de validación
     * 
     * @return array
     */
    public function getValidationErrors(): array
    {
        $errors = [];

        if ($this->name !== null && empty($this->name)) {
            $errors[] = 'El nombre no puede estar vacío';
        }

        if ($this->email !== null && filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'El email no tiene un formato válido';
        }

        if ($this->password !== null && strlen($this->password) < 8) {
            $errors[] = 'La contraseña debe tener al menos 8 caracteres';
        }

        if ($this->role !== null && !in_array($this->role, ['admin', 'reseller', 'client'])) {
            $errors[] = 'El rol debe ser admin, reseller o client';
        }

        if ($this->balance !== null && $this->balance < 0) {
            $errors[] = 'El balance no puede ser negativo';
        }

        // Validaciones específicas para resellers
        if ($this->reseller_profile !== null) {
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
     * Crear DTO para actualizar solo el estado
     * 
     * @param string $status
     * @return self
     */
    public static function forStatusUpdate(string $status): self
    {
        return new self(status: $status);
    }

    /**
     * Crear DTO para actualizar solo el rol
     * 
     * @param string $role
     * @return self
     */
    public static function forRoleUpdate(string $role): self
    {
        return new self(role: $role);
    }

    /**
     * Crear DTO para actualizar solo el balance
     * 
     * @param float $balance
     * @return self
     */
    public static function forBalanceUpdate(float $balance): self
    {
        return new self(balance: $balance);
    }

    /**
     * Crear DTO para actualizar solo la contraseña
     * 
     * @param string $password
     * @return self
     */
    public static function forPasswordUpdate(string $password): self
    {
        return new self(password: $password);
    }

    /**
     * Verificar si se está actualizando el rol
     * 
     * @return bool
     */
    public function isUpdatingRole(): bool
    {
        return $this->role !== null;
    }

    /**
     * Verificar si se está actualizando el perfil de reseller
     * 
     * @return bool
     */
    public function isUpdatingResellerProfile(): bool
    {
        return $this->reseller_profile !== null;
    }

    /**
     * Verificar si se está actualizando la contraseña
     * 
     * @return bool
     */
    public function isUpdatingPassword(): bool
    {
        return $this->password !== null;
    }
}

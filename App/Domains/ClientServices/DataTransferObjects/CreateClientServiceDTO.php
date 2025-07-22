<?php

namespace App\Domains\ClientServices\DataTransferObjects;

use Carbon\Carbon;

/**
 * Class CreateClientServiceDTO
 * 
 * Data Transfer Object para la creación de servicios de cliente
 * Encapsula todos los datos necesarios para crear un servicio
 * Aplica el principio de Single Responsibility
 */
class CreateClientServiceDTO
{
    public function __construct(
        public readonly int $client_id,
        public readonly ?int $reseller_id = null,
        public readonly int $product_id,
        public readonly int $product_pricing_id,
        public readonly int $billing_cycle_id,
        public readonly ?string $domain_name = null,
        public readonly ?string $username = null,
        public readonly ?string $password_encrypted = null,
        public readonly string $status = 'pending',
        public readonly ?Carbon $registration_date = null,
        public readonly ?Carbon $next_due_date = null,
        public readonly ?Carbon $termination_date = null,
        public readonly float $billing_amount = 0.00,
        public readonly ?string $notes = null,
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
            client_id: $data['client_id'],
            reseller_id: $data['reseller_id'] ?? null,
            product_id: $data['product_id'],
            product_pricing_id: $data['product_pricing_id'],
            billing_cycle_id: $data['billing_cycle_id'],
            domain_name: $data['domain_name'] ?? null,
            username: $data['username'] ?? null,
            password_encrypted: $data['password_encrypted'] ?? null,
            status: $data['status'] ?? 'pending',
            registration_date: isset($data['registration_date']) ? Carbon::parse($data['registration_date']) : null,
            next_due_date: isset($data['next_due_date']) ? Carbon::parse($data['next_due_date']) : null,
            termination_date: isset($data['termination_date']) ? Carbon::parse($data['termination_date']) : null,
            billing_amount: $data['billing_amount'] ?? 0.00,
            notes: $data['notes'] ?? null,
        );
    }

    /**
     * Crear DTO para servicio automático desde factura
     * 
     * @param int $clientId
     * @param array $invoiceItemData
     * @return self
     */
    public static function fromInvoiceItem(int $clientId, array $invoiceItemData): self
    {
        $registrationDate = Carbon::now();
        
        // Calcular próxima fecha de vencimiento basada en el ciclo de facturación
        $nextDueDate = $registrationDate->copy();
        if (isset($invoiceItemData['billing_cycle_days'])) {
            $nextDueDate->addDays($invoiceItemData['billing_cycle_days']);
        } else {
            $nextDueDate->addMonth(); // Default: 1 mes
        }

        return new self(
            client_id: $clientId,
            reseller_id: $invoiceItemData['reseller_id'] ?? null,
            product_id: $invoiceItemData['product_id'],
            product_pricing_id: $invoiceItemData['product_pricing_id'],
            billing_cycle_id: $invoiceItemData['billing_cycle_id'],
            domain_name: $invoiceItemData['domain_name'] ?? null,
            username: $invoiceItemData['username'] ?? null,
            password_encrypted: $invoiceItemData['password_encrypted'] ?? null,
            status: 'active', // Servicios desde facturas pagadas son activos
            registration_date: $registrationDate,
            next_due_date: $nextDueDate,
            billing_amount: $invoiceItemData['billing_amount'] ?? 0.00,
            notes: $invoiceItemData['notes'] ?? null,
        );
    }

    /**
     * Crear DTO para servicio de prueba/demo
     * 
     * @param int $clientId
     * @param int $productId
     * @param array $trialData
     * @return self
     */
    public static function forTrial(int $clientId, int $productId, array $trialData): self
    {
        $registrationDate = Carbon::now();
        $trialDays = $trialData['trial_days'] ?? 30;
        $nextDueDate = $registrationDate->copy()->addDays($trialDays);

        return new self(
            client_id: $clientId,
            reseller_id: $trialData['reseller_id'] ?? null,
            product_id: $productId,
            product_pricing_id: $trialData['product_pricing_id'],
            billing_cycle_id: $trialData['billing_cycle_id'],
            domain_name: $trialData['domain_name'] ?? null,
            username: $trialData['username'] ?? null,
            password_encrypted: $trialData['password_encrypted'] ?? null,
            status: 'trial',
            registration_date: $registrationDate,
            next_due_date: $nextDueDate,
            billing_amount: 0.00, // Trials son gratuitos
            notes: 'Servicio de prueba - ' . $trialDays . ' días',
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
            'client_id' => $this->client_id,
            'reseller_id' => $this->reseller_id,
            'product_id' => $this->product_id,
            'product_pricing_id' => $this->product_pricing_id,
            'billing_cycle_id' => $this->billing_cycle_id,
            'domain_name' => $this->domain_name,
            'username' => $this->username,
            'password_encrypted' => $this->password_encrypted,
            'status' => $this->status,
            'registration_date' => $this->registration_date?->format('Y-m-d'),
            'next_due_date' => $this->next_due_date?->format('Y-m-d'),
            'termination_date' => $this->termination_date?->format('Y-m-d'),
            'billing_amount' => $this->billing_amount,
            'notes' => $this->notes,
        ];

        // Filtrar valores nulos
        return array_filter($data, fn($value) => $value !== null);
    }

    /**
     * Validar que los datos del DTO son válidos
     * 
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->client_id > 0 &&
               $this->product_id > 0 &&
               $this->product_pricing_id > 0 &&
               $this->billing_cycle_id > 0 &&
               $this->billing_amount >= 0 &&
               in_array($this->status, ['pending', 'active', 'suspended', 'cancelled', 'trial']);
    }

    /**
     * Obtener errores de validación
     * 
     * @return array
     */
    public function getValidationErrors(): array
    {
        $errors = [];

        if ($this->client_id <= 0) {
            $errors[] = 'El ID del cliente es requerido y debe ser válido';
        }

        if ($this->product_id <= 0) {
            $errors[] = 'El ID del producto es requerido y debe ser válido';
        }

        if ($this->product_pricing_id <= 0) {
            $errors[] = 'El ID del pricing del producto es requerido y debe ser válido';
        }

        if ($this->billing_cycle_id <= 0) {
            $errors[] = 'El ID del ciclo de facturación es requerido y debe ser válido';
        }

        if ($this->billing_amount < 0) {
            $errors[] = 'El monto de facturación no puede ser negativo';
        }

        if (!in_array($this->status, ['pending', 'active', 'suspended', 'cancelled', 'trial'])) {
            $errors[] = 'El estado del servicio no es válido';
        }

        if ($this->next_due_date && $this->registration_date && $this->next_due_date->lt($this->registration_date)) {
            $errors[] = 'La fecha de vencimiento no puede ser anterior a la fecha de registro';
        }

        return $errors;
    }

    /**
     * Verificar si es un servicio de prueba
     * 
     * @return bool
     */
    public function isTrial(): bool
    {
        return $this->status === 'trial';
    }

    /**
     * Verificar si es un servicio activo
     * 
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Verificar si tiene dominio asociado
     * 
     * @return bool
     */
    public function hasDomain(): bool
    {
        return !empty($this->domain_name);
    }

    /**
     * Verificar si tiene credenciales de acceso
     * 
     * @return bool
     */
    public function hasCredentials(): bool
    {
        return !empty($this->username) && !empty($this->password_encrypted);
    }

    /**
     * Calcular días hasta el vencimiento
     * 
     * @return int|null
     */
    public function getDaysUntilDue(): ?int
    {
        if (!$this->next_due_date) {
            return null;
        }

        return Carbon::now()->diffInDays($this->next_due_date, false);
    }

    /**
     * Verificar si el servicio está próximo a vencer
     * 
     * @param int $days
     * @return bool
     */
    public function isDueSoon(int $days = 7): bool
    {
        $daysUntilDue = $this->getDaysUntilDue();
        
        return $daysUntilDue !== null && $daysUntilDue <= $days && $daysUntilDue >= 0;
    }

    /**
     * Verificar si el servicio está vencido
     * 
     * @return bool
     */
    public function isOverdue(): bool
    {
        $daysUntilDue = $this->getDaysUntilDue();
        
        return $daysUntilDue !== null && $daysUntilDue < 0;
    }
}

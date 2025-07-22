<?php

namespace App\Domains\Invoices\DataTransferObjects;

/**
 * Class InvoiceItemDTO
 * 
 * Data Transfer Object para items de factura
 * Encapsula todos los datos necesarios para crear un item de factura
 */
class InvoiceItemDTO
{
    public function __construct(
        public readonly int $invoice_id,
        public readonly ?int $client_service_id = null,
        public readonly string $description = '',
        public readonly int $quantity = 1,
        public readonly float $unit_price = 0.00,
        public readonly float $total_price = 0.00,
        public readonly bool $taxable = true,
        public readonly ?int $product_id = null,
        public readonly ?int $product_pricing_id = null,
        public readonly float $setup_fee = 0.00,
        public readonly ?string $domain_name = null,
        public readonly ?int $registration_period_years = null,
        public readonly string $item_type = 'new_service',
    ) {}

    /**
     * Crear DTO desde datos de request
     * 
     * @param array $data
     * @param int $invoiceId
     * @return self
     */
    public static function fromRequest(array $data, int $invoiceId): self
    {
        return new self(
            invoice_id: $invoiceId,
            client_service_id: $data['client_service_id'] ?? null,
            description: $data['description'] ?? '',
            quantity: $data['quantity'] ?? 1,
            unit_price: $data['unit_price'] ?? 0.00,
            total_price: $data['total_price'] ?? ($data['unit_price'] ?? 0.00) * ($data['quantity'] ?? 1),
            taxable: $data['taxable'] ?? true,
            product_id: $data['product_id'] ?? null,
            product_pricing_id: $data['product_pricing_id'] ?? null,
            setup_fee: $data['setup_fee'] ?? 0.00,
            domain_name: $data['domain_name'] ?? null,
            registration_period_years: $data['registration_period_years'] ?? null,
            item_type: $data['item_type'] ?? 'new_service',
        );
    }

    /**
     * Crear DTO para nuevo servicio
     * 
     * @param int $invoiceId
     * @param int $productId
     * @param array $serviceData
     * @return self
     */
    public static function forNewService(int $invoiceId, int $productId, array $serviceData): self
    {
        return new self(
            invoice_id: $invoiceId,
            product_id: $productId,
            product_pricing_id: $serviceData['product_pricing_id'] ?? null,
            description: $serviceData['description'] ?? 'Nuevo servicio',
            quantity: 1,
            unit_price: $serviceData['price'] ?? 0.00,
            total_price: $serviceData['price'] ?? 0.00,
            setup_fee: $serviceData['setup_fee'] ?? 0.00,
            domain_name: $serviceData['domain_name'] ?? null,
            item_type: 'new_service',
            taxable: $serviceData['taxable'] ?? true,
        );
    }

    /**
     * Crear DTO para renovación de servicio
     * 
     * @param int $invoiceId
     * @param int $clientServiceId
     * @param array $renewalData
     * @return self
     */
    public static function forServiceRenewal(int $invoiceId, int $clientServiceId, array $renewalData): self
    {
        return new self(
            invoice_id: $invoiceId,
            client_service_id: $clientServiceId,
            product_id: $renewalData['product_id'] ?? null,
            description: $renewalData['description'] ?? 'Renovación de servicio',
            quantity: 1,
            unit_price: $renewalData['price'] ?? 0.00,
            total_price: $renewalData['price'] ?? 0.00,
            item_type: 'renewal',
            taxable: $renewalData['taxable'] ?? true,
        );
    }

    /**
     * Crear DTO para upgrade de servicio
     * 
     * @param int $invoiceId
     * @param int $clientServiceId
     * @param array $upgradeData
     * @return self
     */
    public static function forServiceUpgrade(int $invoiceId, int $clientServiceId, array $upgradeData): self
    {
        return new self(
            invoice_id: $invoiceId,
            client_service_id: $clientServiceId,
            product_id: $upgradeData['new_product_id'] ?? null,
            description: $upgradeData['description'] ?? 'Upgrade de servicio',
            quantity: 1,
            unit_price: $upgradeData['price_difference'] ?? 0.00,
            total_price: $upgradeData['price_difference'] ?? 0.00,
            item_type: 'upgrade',
            taxable: $upgradeData['taxable'] ?? true,
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
            'invoice_id' => $this->invoice_id,
            'client_service_id' => $this->client_service_id,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'total_price' => $this->total_price,
            'taxable' => $this->taxable,
            'product_id' => $this->product_id,
            'product_pricing_id' => $this->product_pricing_id,
            'setup_fee' => $this->setup_fee,
            'domain_name' => $this->domain_name,
            'registration_period_years' => $this->registration_period_years,
            'item_type' => $this->item_type,
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
        return $this->invoice_id > 0 &&
               !empty($this->description) &&
               $this->quantity > 0 &&
               $this->unit_price >= 0 &&
               $this->total_price >= 0 &&
               $this->setup_fee >= 0 &&
               in_array($this->item_type, ['new_service', 'renewal', 'upgrade', 'addon', 'domain']);
    }

    /**
     * Obtener errores de validación
     * 
     * @return array
     */
    public function getValidationErrors(): array
    {
        $errors = [];

        if ($this->invoice_id <= 0) {
            $errors[] = 'El ID de la factura es requerido y debe ser válido';
        }

        if (empty($this->description)) {
            $errors[] = 'La descripción del item es requerida';
        }

        if ($this->quantity <= 0) {
            $errors[] = 'La cantidad debe ser mayor a 0';
        }

        if ($this->unit_price < 0) {
            $errors[] = 'El precio unitario no puede ser negativo';
        }

        if ($this->total_price < 0) {
            $errors[] = 'El precio total no puede ser negativo';
        }

        if ($this->setup_fee < 0) {
            $errors[] = 'La tarifa de configuración no puede ser negativa';
        }

        if (!in_array($this->item_type, ['new_service', 'renewal', 'upgrade', 'addon', 'domain'])) {
            $errors[] = 'El tipo de item no es válido';
        }

        // Validar coherencia entre precio unitario, cantidad y total
        $expectedTotal = $this->unit_price * $this->quantity;
        if (abs($expectedTotal - $this->total_price) > 0.01) {
            $errors[] = 'El precio total no coincide con precio unitario × cantidad';
        }

        return $errors;
    }

    /**
     * Calcular precio total basado en precio unitario y cantidad
     * 
     * @return float
     */
    public function calculateTotalPrice(): float
    {
        return $this->unit_price * $this->quantity;
    }

    /**
     * Verificar si el item es taxable
     * 
     * @return bool
     */
    public function isTaxable(): bool
    {
        return $this->taxable;
    }

    /**
     * Verificar si es un nuevo servicio
     * 
     * @return bool
     */
    public function isNewService(): bool
    {
        return $this->item_type === 'new_service';
    }

    /**
     * Verificar si es una renovación
     * 
     * @return bool
     */
    public function isRenewal(): bool
    {
        return $this->item_type === 'renewal';
    }

    /**
     * Verificar si es un upgrade
     * 
     * @return bool
     */
    public function isUpgrade(): bool
    {
        return $this->item_type === 'upgrade';
    }

    /**
     * Verificar si tiene setup fee
     * 
     * @return bool
     */
    public function hasSetupFee(): bool
    {
        return $this->setup_fee > 0;
    }

    /**
     * Obtener precio total incluyendo setup fee
     * 
     * @return float
     */
    public function getTotalWithSetupFee(): float
    {
        return $this->total_price + $this->setup_fee;
    }
}

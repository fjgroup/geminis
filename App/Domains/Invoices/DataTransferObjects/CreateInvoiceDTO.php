<?php

namespace App\Domains\Invoices\DataTransferObjects;

use Carbon\Carbon;

/**
 * Class CreateInvoiceDTO
 * 
 * Data Transfer Object para la creación de facturas
 * Encapsula todos los datos necesarios para crear una factura
 * Aplica el principio de Single Responsibility
 */
class CreateInvoiceDTO
{
    public function __construct(
        public readonly int $client_id,
        public readonly ?int $reseller_id = null,
        public readonly ?string $invoice_number = null,
        public readonly ?Carbon $issue_date = null,
        public readonly ?Carbon $due_date = null,
        public readonly ?Carbon $paid_date = null,
        public readonly string $status = 'pending',
        public readonly ?string $paypal_order_id = null,
        public readonly float $subtotal = 0.00,
        public readonly ?string $tax1_name = null,
        public readonly float $tax1_rate = 0.00,
        public readonly float $tax1_amount = 0.00,
        public readonly ?string $tax2_name = null,
        public readonly float $tax2_rate = 0.00,
        public readonly float $tax2_amount = 0.00,
        public readonly float $total_amount = 0.00,
        public readonly string $currency_code = 'USD',
        public readonly ?string $notes_to_client = null,
        public readonly ?string $admin_notes = null,
        public readonly ?Carbon $requested_date = null,
        public readonly ?string $ip_address = null,
        public readonly ?string $payment_gateway_slug = null,
        public readonly array $items = [],
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
            invoice_number: $data['invoice_number'] ?? null,
            issue_date: isset($data['issue_date']) ? Carbon::parse($data['issue_date']) : null,
            due_date: isset($data['due_date']) ? Carbon::parse($data['due_date']) : null,
            paid_date: isset($data['paid_date']) ? Carbon::parse($data['paid_date']) : null,
            status: $data['status'] ?? 'pending',
            paypal_order_id: $data['paypal_order_id'] ?? null,
            subtotal: $data['subtotal'] ?? 0.00,
            tax1_name: $data['tax1_name'] ?? null,
            tax1_rate: $data['tax1_rate'] ?? 0.00,
            tax1_amount: $data['tax1_amount'] ?? 0.00,
            tax2_name: $data['tax2_name'] ?? null,
            tax2_rate: $data['tax2_rate'] ?? 0.00,
            tax2_amount: $data['tax2_amount'] ?? 0.00,
            total_amount: $data['total_amount'] ?? 0.00,
            currency_code: $data['currency_code'] ?? 'USD',
            notes_to_client: $data['notes_to_client'] ?? null,
            admin_notes: $data['admin_notes'] ?? null,
            requested_date: isset($data['requested_date']) ? Carbon::parse($data['requested_date']) : null,
            ip_address: $data['ip_address'] ?? null,
            payment_gateway_slug: $data['payment_gateway_slug'] ?? null,
            items: $data['items'] ?? [],
        );
    }

    /**
     * Crear DTO para factura automática de servicio
     * 
     * @param int $clientId
     * @param array $serviceData
     * @return self
     */
    public static function fromServiceData(int $clientId, array $serviceData): self
    {
        $issueDate = Carbon::now();
        $dueDate = $issueDate->copy()->addDays(30); // 30 días para pagar

        return new self(
            client_id: $clientId,
            reseller_id: $serviceData['reseller_id'] ?? null,
            issue_date: $issueDate,
            due_date: $dueDate,
            status: 'pending',
            subtotal: $serviceData['amount'] ?? 0.00,
            total_amount: $serviceData['total_amount'] ?? $serviceData['amount'] ?? 0.00,
            currency_code: $serviceData['currency_code'] ?? 'USD',
            notes_to_client: $serviceData['notes'] ?? null,
            items: $serviceData['items'] ?? [],
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
            'invoice_number' => $this->invoice_number,
            'issue_date' => $this->issue_date?->format('Y-m-d'),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'paid_date' => $this->paid_date?->format('Y-m-d'),
            'status' => $this->status,
            'paypal_order_id' => $this->paypal_order_id,
            'subtotal' => $this->subtotal,
            'tax1_name' => $this->tax1_name,
            'tax1_rate' => $this->tax1_rate,
            'tax1_amount' => $this->tax1_amount,
            'tax2_name' => $this->tax2_name,
            'tax2_rate' => $this->tax2_rate,
            'tax2_amount' => $this->tax2_amount,
            'total_amount' => $this->total_amount,
            'currency_code' => $this->currency_code,
            'notes_to_client' => $this->notes_to_client,
            'admin_notes' => $this->admin_notes,
            'requested_date' => $this->requested_date?->format('Y-m-d H:i:s'),
            'ip_address' => $this->ip_address,
            'payment_gateway_slug' => $this->payment_gateway_slug,
        ];

        // Filtrar valores nulos
        return array_filter($data, fn($value) => $value !== null);
    }

    /**
     * Obtener items de la factura
     * 
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Validar que los datos del DTO son válidos
     * 
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->client_id > 0 &&
               $this->subtotal >= 0 &&
               $this->total_amount >= 0 &&
               $this->tax1_rate >= 0 && $this->tax1_rate <= 100 &&
               $this->tax2_rate >= 0 && $this->tax2_rate <= 100 &&
               in_array($this->status, ['pending', 'paid', 'cancelled', 'overdue']) &&
               !empty($this->currency_code);
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

        if ($this->subtotal < 0) {
            $errors[] = 'El subtotal no puede ser negativo';
        }

        if ($this->total_amount < 0) {
            $errors[] = 'El total no puede ser negativo';
        }

        if ($this->tax1_rate < 0 || $this->tax1_rate > 100) {
            $errors[] = 'La tasa del impuesto 1 debe estar entre 0 y 100';
        }

        if ($this->tax2_rate < 0 || $this->tax2_rate > 100) {
            $errors[] = 'La tasa del impuesto 2 debe estar entre 0 y 100';
        }

        if (!in_array($this->status, ['pending', 'paid', 'cancelled', 'overdue'])) {
            $errors[] = 'El estado de la factura no es válido';
        }

        if (empty($this->currency_code)) {
            $errors[] = 'El código de moneda es requerido';
        }

        if ($this->due_date && $this->issue_date && $this->due_date->lt($this->issue_date)) {
            $errors[] = 'La fecha de vencimiento no puede ser anterior a la fecha de emisión';
        }

        return $errors;
    }

    /**
     * Verificar si la factura tiene items
     * 
     * @return bool
     */
    public function hasItems(): bool
    {
        return !empty($this->items);
    }

    /**
     * Verificar si la factura tiene impuestos
     * 
     * @return bool
     */
    public function hasTaxes(): bool
    {
        return $this->tax1_rate > 0 || $this->tax2_rate > 0;
    }

    /**
     * Verificar si es una factura pagada
     * 
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid' && $this->paid_date !== null;
    }

    /**
     * Calcular total con impuestos
     * 
     * @return float
     */
    public function calculateTotalWithTaxes(): float
    {
        return $this->subtotal + $this->tax1_amount + $this->tax2_amount;
    }
}

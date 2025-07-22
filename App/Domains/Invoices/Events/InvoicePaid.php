<?php

namespace App\Domains\Invoices\Events;

use App\Domains\Invoices\Models\Invoice;
use App\Domains\Shared\ValueObjects\Money;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class InvoicePaid
 * 
 * Domain Event que se dispara cuando se paga una factura
 * Permite reaccionar al pago de facturas de forma desacoplada
 * Aplica principios de DDD - Domain Events
 */
class InvoicePaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public readonly Invoice $invoice;
    public readonly Money $paidAmount;
    public readonly string $paymentMethod;
    public readonly ?string $transactionId;
    public readonly array $metadata;
    public readonly \DateTimeImmutable $occurredAt;

    public function __construct(
        Invoice $invoice,
        Money $paidAmount,
        string $paymentMethod,
        ?string $transactionId = null,
        array $metadata = []
    ) {
        $this->invoice = $invoice;
        $this->paidAmount = $paidAmount;
        $this->paymentMethod = $paymentMethod;
        $this->transactionId = $transactionId;
        $this->metadata = $metadata;
        $this->occurredAt = new \DateTimeImmutable();
    }

    /**
     * Obtener la factura pagada
     * 
     * @return Invoice
     */
    public function getInvoice(): Invoice
    {
        return $this->invoice;
    }

    /**
     * Obtener el monto pagado
     * 
     * @return Money
     */
    public function getPaidAmount(): Money
    {
        return $this->paidAmount;
    }

    /**
     * Obtener el método de pago
     * 
     * @return string
     */
    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    /**
     * Obtener ID de transacción
     * 
     * @return string|null
     */
    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    /**
     * Obtener metadatos del evento
     * 
     * @return array
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Obtener cuándo ocurrió el evento
     * 
     * @return \DateTimeImmutable
     */
    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }

    /**
     * Verificar si es pago completo
     * 
     * @return bool
     */
    public function isFullPayment(): bool
    {
        $invoiceTotal = new Money($this->invoice->total_amount, $this->invoice->currency_code);
        return $this->paidAmount->equals($invoiceTotal);
    }

    /**
     * Verificar si es pago parcial
     * 
     * @return bool
     */
    public function isPartialPayment(): bool
    {
        return !$this->isFullPayment();
    }

    /**
     * Verificar si la factura tiene servicios para activar
     * 
     * @return bool
     */
    public function hasServicesToActivate(): bool
    {
        return $this->invoice->items()->whereNotNull('client_service_id')->exists();
    }

    /**
     * Obtener IDs de servicios a activar
     * 
     * @return array
     */
    public function getServiceIdsToActivate(): array
    {
        return $this->invoice->items()
                           ->whereNotNull('client_service_id')
                           ->pluck('client_service_id')
                           ->toArray();
    }

    /**
     * Verificar si es factura de reseller
     * 
     * @return bool
     */
    public function isResellerInvoice(): bool
    {
        return !empty($this->invoice->reseller_id);
    }

    /**
     * Obtener información del evento para logging
     * 
     * @return array
     */
    public function toLogArray(): array
    {
        return [
            'event' => 'InvoicePaid',
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'client_id' => $this->invoice->client_id,
            'reseller_id' => $this->invoice->reseller_id,
            'paid_amount' => $this->paidAmount->format(),
            'payment_method' => $this->paymentMethod,
            'transaction_id' => $this->transactionId,
            'is_full_payment' => $this->isFullPayment(),
            'services_to_activate' => $this->getServiceIdsToActivate(),
            'occurred_at' => $this->occurredAt->format('Y-m-d H:i:s'),
            'metadata' => $this->metadata,
        ];
    }

    /**
     * Convertir a array para serialización
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'invoice' => [
                'id' => $this->invoice->id,
                'invoice_number' => $this->invoice->invoice_number,
                'client_id' => $this->invoice->client_id,
                'reseller_id' => $this->invoice->reseller_id,
                'total_amount' => $this->invoice->total_amount,
                'currency_code' => $this->invoice->currency_code,
            ],
            'payment' => [
                'amount' => $this->paidAmount->toArray(),
                'method' => $this->paymentMethod,
                'transaction_id' => $this->transactionId,
                'is_full_payment' => $this->isFullPayment(),
            ],
            'services_to_activate' => $this->getServiceIdsToActivate(),
            'metadata' => $this->metadata,
            'occurred_at' => $this->occurredAt->format('c'),
        ];
    }
}

<?php

namespace App\Domains\Invoices\UseCases;

use App\Domains\Invoices\Models\Invoice;
use App\Domains\Invoices\Repositories\IInvoiceRepository;
use App\Domains\Invoices\Events\InvoicePaid;
use App\Domains\Shared\ValueObjects\Money;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Class ProcessInvoicePaymentUseCase
 * 
 * Caso de uso para procesar pagos de facturas
 * Encapsula toda la lógica de negocio para pagos
 * Aplica principios de Arquitectura Hexagonal - Use Cases
 */
class ProcessInvoicePaymentUseCase
{
    public function __construct(
        private IInvoiceRepository $invoiceRepository
    ) {}

    /**
     * Ejecutar caso de uso de procesamiento de pago
     * 
     * @param int $invoiceId
     * @param float $amount
     * @param string $currency
     * @param string $paymentMethod
     * @param string|null $transactionId
     * @param array $metadata
     * @return array
     */
    public function execute(
        int $invoiceId,
        float $amount,
        string $currency,
        string $paymentMethod,
        ?string $transactionId = null,
        array $metadata = []
    ): array {
        try {
            // Obtener factura
            $invoice = $this->invoiceRepository->findById($invoiceId);
            if (!$invoice) {
                return [
                    'success' => false,
                    'message' => 'Factura no encontrada',
                    'errors' => ['invoice' => 'Factura no existe'],
                    'data' => null
                ];
            }

            // Validar pago
            $validation = $this->validatePayment($invoice, $amount, $currency, $paymentMethod);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Pago inválido',
                    'errors' => $validation['errors'],
                    'data' => null
                ];
            }

            // Procesar pago en transacción
            $result = DB::transaction(function () use ($invoice, $amount, $currency, $paymentMethod, $transactionId, $metadata) {
                return $this->processPayment($invoice, $amount, $currency, $paymentMethod, $transactionId, $metadata);
            });

            return $result;

        } catch (\Exception $e) {
            Log::error('Error en ProcessInvoicePaymentUseCase', [
                'invoice_id' => $invoiceId,
                'amount' => $amount,
                'currency' => $currency,
                'payment_method' => $paymentMethod,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Error interno al procesar pago',
                'errors' => ['general' => 'Error interno del servidor'],
                'data' => null
            ];
        }
    }

    /**
     * Validar pago
     * 
     * @param Invoice $invoice
     * @param float $amount
     * @param string $currency
     * @param string $paymentMethod
     * @return array
     */
    private function validatePayment(Invoice $invoice, float $amount, string $currency, string $paymentMethod): array
    {
        $errors = [];

        // Validar estado de factura
        if ($invoice->status === 'paid') {
            $errors[] = 'La factura ya está pagada';
        }

        if ($invoice->status === 'cancelled') {
            $errors[] = 'No se puede pagar una factura cancelada';
        }

        // Validar moneda
        if (strtoupper($currency) !== strtoupper($invoice->currency_code)) {
            $errors[] = "Moneda incorrecta. Esperada: {$invoice->currency_code}, recibida: {$currency}";
        }

        // Validar monto
        if ($amount <= 0) {
            $errors[] = 'El monto debe ser mayor a cero';
        }

        if ($amount > $invoice->total_amount) {
            $errors[] = 'El monto no puede ser mayor al total de la factura';
        }

        // Validar método de pago
        $validPaymentMethods = ['credit_card', 'paypal', 'bank_transfer', 'cash', 'check'];
        if (!in_array($paymentMethod, $validPaymentMethods)) {
            $errors[] = 'Método de pago inválido';
        }

        // Validar fecha de vencimiento
        if ($invoice->due_date && now()->gt($invoice->due_date)) {
            // Permitir pago pero marcar como tardío
            Log::info('Pago tardío procesado', [
                'invoice_id' => $invoice->id,
                'due_date' => $invoice->due_date,
                'payment_date' => now()
            ]);
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Procesar pago
     * 
     * @param Invoice $invoice
     * @param float $amount
     * @param string $currency
     * @param string $paymentMethod
     * @param string|null $transactionId
     * @param array $metadata
     * @return array
     */
    private function processPayment(
        Invoice $invoice,
        float $amount,
        string $currency,
        string $paymentMethod,
        ?string $transactionId,
        array $metadata
    ): array {
        $paidAmount = new Money($amount, $currency);

        // Marcar factura como pagada
        $updatedInvoice = $this->invoiceRepository->markAsPaid(
            $invoice,
            $paidAmount,
            $paymentMethod,
            $transactionId
        );

        // Disparar evento de dominio
        Event::dispatch(new InvoicePaid(
            $updatedInvoice,
            $paidAmount,
            $paymentMethod,
            $transactionId,
            $metadata
        ));

        Log::info('Factura pagada exitosamente', [
            'invoice_id' => $updatedInvoice->id,
            'invoice_number' => $updatedInvoice->invoice_number,
            'amount' => $paidAmount->format(),
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId
        ]);

        return [
            'success' => true,
            'message' => 'Pago procesado exitosamente',
            'errors' => [],
            'data' => [
                'invoice' => $updatedInvoice,
                'payment' => [
                    'amount' => $paidAmount->toArray(),
                    'method' => $paymentMethod,
                    'transaction_id' => $transactionId,
                    'processed_at' => now()->toISOString()
                ]
            ]
        ];
    }

    /**
     * Verificar si el pago activa servicios
     * 
     * @param Invoice $invoice
     * @return bool
     */
    private function shouldActivateServices(Invoice $invoice): bool
    {
        return $invoice->items()
                      ->whereNotNull('client_service_id')
                      ->exists();
    }

    /**
     * Obtener servicios a activar
     * 
     * @param Invoice $invoice
     * @return array
     */
    private function getServicesToActivate(Invoice $invoice): array
    {
        return $invoice->items()
                      ->whereNotNull('client_service_id')
                      ->with('clientService')
                      ->get()
                      ->pluck('clientService')
                      ->filter()
                      ->toArray();
    }
}

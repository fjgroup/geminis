<?php

namespace App\Domains\BillingAndPayments\Application\UseCases;

use App\Domains\BillingAndPayments\Domain\Entities\Transaction;
use App\Domains\BillingAndPayments\Domain\ValueObjects\TransactionAmount;
use App\Domains\BillingAndPayments\Domain\ValueObjects\TransactionStatus;
use App\Domains\BillingAndPayments\Interfaces\TransactionRepositoryInterface;
use App\Domains\Users\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Caso de uso para crear una transacción
 * 
 * Cumple con el principio de responsabilidad única (SRP)
 * Encapsula la lógica de negocio para crear transacciones
 * Parte de la capa Application en arquitectura hexagonal
 */
class CreateTransactionUseCase
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepository
    ) {}

    /**
     * Ejecutar el caso de uso
     *
     * @param CreateTransactionCommand $command
     * @return CreateTransactionResponse
     */
    public function execute(CreateTransactionCommand $command): CreateTransactionResponse
    {
        try {
            DB::beginTransaction();

            // Validar datos del comando
            $this->validateCommand($command);

            // Crear Value Objects
            $amount = new TransactionAmount($command->amount, $command->currency);
            $status = new TransactionStatus($command->status ?? TransactionStatus::PENDING);

            // Crear la transacción
            $transaction = $this->transactionRepository->create([
                'invoice_id' => $command->invoiceId,
                'client_id' => $command->clientId,
                'reseller_id' => $command->resellerId,
                'payment_method_id' => $command->paymentMethodId,
                'gateway_slug' => $command->gatewaySlug,
                'gateway_transaction_id' => $command->gatewayTransactionId,
                'type' => $command->type,
                'amount' => $amount->getAmount(),
                'currency_code' => $amount->getCurrency(),
                'status' => $status->getValue(),
                'fees_amount' => $command->feesAmount ?? 0,
                'description' => $command->description ?? '',
                'transaction_date' => $command->transactionDate ?? now(),
                'admin_notes' => $command->adminNotes,
            ]);

            // Si la transacción es exitosa, procesar lógica adicional
            if ($status->isCompleted() && $command->invoiceId) {
                $this->processCompletedTransaction($transaction);
            }

            DB::commit();

            Log::info('Transacción creada exitosamente', [
                'transaction_id' => $transaction->id,
                'client_id' => $command->clientId,
                'amount' => $amount->format()
            ]);

            return new CreateTransactionResponse(
                success: true,
                transaction: $transaction,
                message: 'Transacción creada exitosamente'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al crear transacción', [
                'error' => $e->getMessage(),
                'command' => $command->toArray()
            ]);

            return new CreateTransactionResponse(
                success: false,
                transaction: null,
                message: 'Error al crear la transacción: ' . $e->getMessage(),
                error: $e->getMessage()
            );
        }
    }

    /**
     * Validar el comando
     */
    private function validateCommand(CreateTransactionCommand $command): void
    {
        if (empty($command->clientId)) {
            throw new \InvalidArgumentException('El ID del cliente es requerido');
        }

        if (empty($command->paymentMethodId)) {
            throw new \InvalidArgumentException('El ID del método de pago es requerido');
        }

        if (empty($command->type)) {
            throw new \InvalidArgumentException('El tipo de transacción es requerido');
        }

        if (!is_numeric($command->amount) || $command->amount <= 0) {
            throw new \InvalidArgumentException('El monto debe ser un número positivo');
        }

        if (!in_array($command->type, ['payment', 'refund', 'credit', 'debit'])) {
            throw new \InvalidArgumentException('Tipo de transacción inválido');
        }

        // Validar que el cliente existe
        if (!User::find($command->clientId)) {
            throw new \InvalidArgumentException('El cliente especificado no existe');
        }
    }

    /**
     * Procesar transacción completada
     */
    private function processCompletedTransaction(Transaction $transaction): void
    {
        // Aquí se implementaría la lógica para:
        // - Marcar factura como pagada
        // - Activar servicios asociados
        // - Enviar notificaciones
        // - Etc.
        
        Log::info('Procesando transacción completada', [
            'transaction_id' => $transaction->id,
            'invoice_id' => $transaction->invoice_id
        ]);
    }
}

/**
 * Comando para crear transacción
 */
class CreateTransactionCommand
{
    public function __construct(
        public readonly int $clientId,
        public readonly int $paymentMethodId,
        public readonly string $type,
        public readonly float $amount,
        public readonly string $currency = 'USD',
        public readonly ?int $invoiceId = null,
        public readonly ?int $resellerId = null,
        public readonly ?string $gatewaySlug = null,
        public readonly ?string $gatewayTransactionId = null,
        public readonly ?string $status = null,
        public readonly ?float $feesAmount = null,
        public readonly ?string $description = null,
        public readonly ?\DateTime $transactionDate = null,
        public readonly ?string $adminNotes = null,
    ) {}

    public function toArray(): array
    {
        return [
            'client_id' => $this->clientId,
            'payment_method_id' => $this->paymentMethodId,
            'type' => $this->type,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'invoice_id' => $this->invoiceId,
            'reseller_id' => $this->resellerId,
            'gateway_slug' => $this->gatewaySlug,
            'gateway_transaction_id' => $this->gatewayTransactionId,
            'status' => $this->status,
            'fees_amount' => $this->feesAmount,
            'description' => $this->description,
            'transaction_date' => $this->transactionDate?->format('Y-m-d H:i:s'),
            'admin_notes' => $this->adminNotes,
        ];
    }
}

/**
 * Respuesta del caso de uso
 */
class CreateTransactionResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly ?Transaction $transaction,
        public readonly string $message,
        public readonly ?string $error = null
    ) {}

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'transaction' => $this->transaction?->toArray(),
            'message' => $this->message,
            'error' => $this->error,
        ];
    }
}

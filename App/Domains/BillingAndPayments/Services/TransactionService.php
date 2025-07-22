<?php

namespace App\Domains\BillingAndPayments\Services;

use App\Domains\BillingAndPayments\Models\Transaction;
use App\Domains\BillingAndPayments\Models\PaymentMethod;
use App\Domains\Users\Models\User;
use App\Domains\Invoices\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para gestión de transacciones
 * 
 * Cumple con el principio de responsabilidad única (SRP)
 * Maneja toda la lógica de negocio relacionada con transacciones
 */
class TransactionService
{
    /**
     * Crear una nueva transacción
     *
     * @param array $data
     * @return array
     */
    public function createTransaction(array $data): array
    {
        try {
            DB::beginTransaction();

            // Validar datos requeridos
            $this->validateTransactionData($data);

            // Crear la transacción
            $transaction = Transaction::create([
                'invoice_id' => $data['invoice_id'] ?? null,
                'client_id' => $data['client_id'],
                'reseller_id' => $data['reseller_id'] ?? null,
                'payment_method_id' => $data['payment_method_id'],
                'gateway_slug' => $data['gateway_slug'] ?? null,
                'gateway_transaction_id' => $data['gateway_transaction_id'] ?? null,
                'type' => $data['type'],
                'amount' => $data['amount'],
                'currency_code' => $data['currency_code'] ?? 'USD',
                'status' => $data['status'] ?? 'pending',
                'fees_amount' => $data['fees_amount'] ?? 0,
                'description' => $data['description'] ?? '',
                'transaction_date' => $data['transaction_date'] ?? now(),
                'admin_notes' => $data['admin_notes'] ?? null,
            ]);

            // Si la transacción es exitosa y está asociada a una factura, marcarla como pagada
            if ($transaction->status === 'completed' && $transaction->invoice_id) {
                $this->processInvoicePayment($transaction);
            }

            DB::commit();

            Log::info('Transacción creada exitosamente', [
                'transaction_id' => $transaction->id,
                'client_id' => $transaction->client_id,
                'amount' => $transaction->amount
            ]);

            return [
                'success' => true,
                'data' => $transaction->load(['client', 'paymentMethod', 'invoice']),
                'message' => 'Transacción creada exitosamente'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al crear transacción', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Error al crear la transacción: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Actualizar estado de una transacción
     *
     * @param Transaction $transaction
     * @param string $status
     * @param string|null $adminNotes
     * @return array
     */
    public function updateTransactionStatus(Transaction $transaction, string $status, ?string $adminNotes = null): array
    {
        try {
            DB::beginTransaction();

            $oldStatus = $transaction->status;
            
            $transaction->update([
                'status' => $status,
                'admin_notes' => $adminNotes ?? $transaction->admin_notes
            ]);

            // Si la transacción se marca como completada y tiene factura asociada
            if ($status === 'completed' && $transaction->invoice_id && $oldStatus !== 'completed') {
                $this->processInvoicePayment($transaction);
            }

            DB::commit();

            Log::info('Estado de transacción actualizado', [
                'transaction_id' => $transaction->id,
                'old_status' => $oldStatus,
                'new_status' => $status
            ]);

            return [
                'success' => true,
                'data' => $transaction->fresh(['client', 'paymentMethod', 'invoice']),
                'message' => 'Estado de transacción actualizado exitosamente'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al actualizar estado de transacción', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Error al actualizar el estado de la transacción'
            ];
        }
    }

    /**
     * Obtener transacciones de un cliente
     *
     * @param User $client
     * @param array $filters
     * @return Collection
     */
    public function getClientTransactions(User $client, array $filters = []): Collection
    {
        $query = $client->transactions()->with(['paymentMethod', 'invoice']);

        // Aplicar filtros
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['date_from'])) {
            $query->where('transaction_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('transaction_date', '<=', $filters['date_to']);
        }

        return $query->orderBy('transaction_date', 'desc')->get();
    }

    /**
     * Validar datos de transacción
     *
     * @param array $data
     * @throws \InvalidArgumentException
     */
    private function validateTransactionData(array $data): void
    {
        $required = ['client_id', 'payment_method_id', 'type', 'amount'];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new \InvalidArgumentException("El campo {$field} es requerido");
            }
        }

        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            throw new \InvalidArgumentException("El monto debe ser un número positivo");
        }

        if (!in_array($data['type'], ['payment', 'refund', 'credit', 'debit'])) {
            throw new \InvalidArgumentException("Tipo de transacción inválido");
        }
    }

    /**
     * Procesar pago de factura
     *
     * @param Transaction $transaction
     */
    private function processInvoicePayment(Transaction $transaction): void
    {
        if ($transaction->invoice) {
            // Aquí se implementaría la lógica para marcar la factura como pagada
            // y activar servicios asociados
            Log::info('Procesando pago de factura', [
                'transaction_id' => $transaction->id,
                'invoice_id' => $transaction->invoice_id
            ]);
        }
    }
}

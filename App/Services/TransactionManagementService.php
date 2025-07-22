<?php
namespace App\Services;

use App\Models\Invoice;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class TransactionManagementService
 *
 * Servicio para el manejo de transacciones
 * Centraliza la lógica de negocio relacionada con transacciones y pagos
 */
class TransactionManagementService
{
    /**
     * Obtener transacciones con filtros
     */
    public function getTransactions(array $filters = [], int $perPage = 10): Collection
    {
        try {
            $query = Transaction::with(['client:id,name', 'invoice:id,invoice_number']);

            // Aplicar filtros
            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['type'])) {
                $query->where('type', $filters['type']);
            }

            if (isset($filters['client_id'])) {
                $query->where('client_id', $filters['client_id']);
            }

            if (isset($filters['date_from'])) {
                $query->where('transaction_date', '>=', $filters['date_from']);
            }

            if (isset($filters['date_to'])) {
                $query->where('transaction_date', '<=', $filters['date_to']);
            }

            if (isset($filters['search'])) {
                $searchTerm = $filters['search'];
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('gateway_transaction_id', 'LIKE', "%{$searchTerm}%")
                        ->orWhereHas('client', fn($qr) => $qr->where('name', 'LIKE', "%{$searchTerm}%"))
                        ->orWhereHas('invoice', fn($qr) => $qr->where('invoice_number', 'LIKE', "%{$searchTerm}%"));
                });
            }

            return $query->latest()->paginate($perPage);

        } catch (\Exception $e) {
            Log::error('Error en TransactionManagementService::getTransactions', [
                'error'   => $e->getMessage(),
                'filters' => $filters,
            ]);

            return collect();
        }
    }

    /**
     * Confirmar pago manual
     */
    public function confirmManualPayment(array $validatedData): array
    {
        try {
            DB::beginTransaction();

            $invoice = Invoice::findOrFail($validatedData['invoice_id']);

            // Verificar que la factura no esté ya pagada
            if ($invoice->status === 'paid') {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Esta factura ya está marcada como pagada',
                ];
            }

            // Verificar que el monto coincida
            if (isset($validatedData['amount']) && $validatedData['amount'] != $invoice->total_amount) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'El monto no coincide con el total de la factura',
                ];
            }

            // Crear la transacción
            $transaction = Transaction::create([
                'client_id'              => $invoice->client_id,
                'invoice_id'             => $invoice->id,
                'order_id'               => $invoice->order_id,
                'payment_method_id'      => $validatedData['payment_method_id'],
                'gateway_slug'           => 'manual_payment',
                'gateway_transaction_id' => $validatedData['reference_number'] ?? null,
                'amount'                 => $validatedData['amount'] ?? $invoice->total_amount,
                'currency_code'          => $invoice->currency_code ?? 'USD',
                'status'                 => 'completed',
                'type'                   => 'payment',
                'transaction_date'       => Carbon::parse($validatedData['transaction_date']),
                'notes'                  => $validatedData['notes'] ?? 'Pago manual confirmado por administrador',
            ]);

            // Actualizar el estado de la factura
            $invoice->update([
                'status'    => 'paid',
                'paid_date' => Carbon::parse($validatedData['transaction_date']),
            ]);

            DB::commit();

            Log::info('TransactionManagementService - Pago manual confirmado', [
                'transaction_id' => $transaction->id,
                'invoice_id'     => $invoice->id,
                'amount'         => $transaction->amount,
                'confirmed_by'   => auth()->id(),
            ]);

            return [
                'success' => true,
                'data'    => $transaction,
                'message' => 'Pago manual confirmado y factura marcada como pagada',
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error en TransactionManagementService::confirmManualPayment', [
                'error' => $e->getMessage(),
                'data'  => $validatedData,
            ]);

            return [
                'success' => false,
                'message' => 'Error al confirmar el pago manual',
            ];
        }
    }

    /**
     * Confirmar una transacción pendiente
     */
    public function confirmTransaction(Transaction $transaction): array
    {
        try {
            if ($transaction->status === 'completed') {
                return [
                    'success' => false,
                    'message' => 'Esta transacción ya ha sido confirmada anteriormente',
                ];
            }

            if ($transaction->status !== 'pending') {
                return [
                    'success' => false,
                    'message' => 'Solo las transacciones pendientes pueden ser confirmadas. Estado actual: ' . $transaction->status,
                ];
            }

            DB::beginTransaction();

            // Actualizar estado de la transacción
            $transaction->update([
                'status'       => 'completed',
                'confirmed_at' => now(),
                'confirmed_by' => auth()->id(),
            ]);

            // Si es una adición de fondos, actualizar balance del cliente
            if ($transaction->type === 'credit_added') {
                $this->addFundsToClient($transaction);
            }

            // Si es un pago de factura, actualizar la factura
            if ($transaction->type === 'payment' && $transaction->invoice_id) {
                $this->updateInvoiceFromTransaction($transaction);
            }

            DB::commit();

            Log::info('TransactionManagementService - Transacción confirmada', [
                'transaction_id' => $transaction->id,
                'type'           => $transaction->type,
                'amount'         => $transaction->amount,
                'confirmed_by'   => auth()->id(),
            ]);

            return [
                'success' => true,
                'data'    => $transaction,
                'message' => 'Transacción confirmada exitosamente',
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error en TransactionManagementService::confirmTransaction', [
                'error'          => $e->getMessage(),
                'transaction_id' => $transaction->id,
            ]);

            return [
                'success' => false,
                'message' => 'Error al confirmar la transacción',
            ];
        }
    }

    /**
     * Rechazar una transacción pendiente
     */
    public function rejectTransaction(Transaction $transaction, ?string $reason = null): array
    {
        try {
            if ($transaction->status !== 'pending') {
                return [
                    'success' => false,
                    'message' => 'Solo las transacciones pendientes pueden ser rechazadas',
                ];
            }

            $transaction->update([
                'status'      => 'failed',
                'notes'       => ($transaction->notes ? $transaction->notes . "\n" : '') .
                "Rechazada por administrador: " . ($reason ?? 'Sin razón especificada'),
                'rejected_at' => now(),
                'rejected_by' => auth()->id(),
            ]);

            Log::info('TransactionManagementService - Transacción rechazada', [
                'transaction_id' => $transaction->id,
                'reason'         => $reason,
                'rejected_by'    => auth()->id(),
            ]);

            return [
                'success' => true,
                'data'    => $transaction,
                'message' => 'Transacción rechazada exitosamente',
            ];

        } catch (\Exception $e) {
            Log::error('Error en TransactionManagementService::rejectTransaction', [
                'error'          => $e->getMessage(),
                'transaction_id' => $transaction->id,
            ]);

            return [
                'success' => false,
                'message' => 'Error al rechazar la transacción',
            ];
        }
    }

    /**
     * Obtener transacciones pendientes de adición de fondos
     */
    public function getPendingFundAdditions(): Collection
    {
        try {
            return Transaction::with(['client', 'paymentMethod'])
                ->where('type', 'credit_added')
                ->where('status', 'pending')
                ->orderBy('transaction_date', 'asc')
                ->get();

        } catch (\Exception $e) {
            Log::error('Error en TransactionManagementService::getPendingFundAdditions', [
                'error' => $e->getMessage(),
            ]);

            return collect();
        }
    }

    /**
     * Crear transacción de reembolso
     */
    public function createRefund(Transaction $originalTransaction, float $amount, string $reason): array
    {
        try {
            if ($originalTransaction->status !== 'completed') {
                return [
                    'success' => false,
                    'message' => 'Solo se pueden reembolsar transacciones completadas',
                ];
            }

            if ($amount > $originalTransaction->amount) {
                return [
                    'success' => false,
                    'message' => 'El monto del reembolso no puede ser mayor al monto original',
                ];
            }

            DB::beginTransaction();

            $refund = Transaction::create([
                'client_id'              => $originalTransaction->client_id,
                'invoice_id'             => $originalTransaction->invoice_id,
                'order_id'               => $originalTransaction->order_id,
                'payment_method_id'      => $originalTransaction->payment_method_id,
                'gateway_slug'           => $originalTransaction->gateway_slug,
                'gateway_transaction_id' => 'refund_' . $originalTransaction->gateway_transaction_id,
                'amount'                 => -$amount, // Monto negativo para reembolso
                'currency_code'          => $originalTransaction->currency_code,
                'status'                 => 'completed',
                'type'                   => 'refund',
                'transaction_date'       => now(),
                'notes'                  => "Reembolso de transacción #{$originalTransaction->id}. Razón: {$reason}",
                'parent_transaction_id'  => $originalTransaction->id,
            ]);

            // Actualizar balance del cliente si es necesario
            if ($originalTransaction->type === 'credit_added') {
                $client = $originalTransaction->client;
                $client->decrement('balance', $amount);
            }

            DB::commit();

            Log::info('TransactionManagementService - Reembolso creado', [
                'refund_id'               => $refund->id,
                'original_transaction_id' => $originalTransaction->id,
                'amount'                  => $amount,
                'reason'                  => $reason,
                'created_by'              => auth()->id(),
            ]);

            return [
                'success' => true,
                'data'    => $refund,
                'message' => 'Reembolso creado exitosamente',
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error en TransactionManagementService::createRefund', [
                'error'                   => $e->getMessage(),
                'original_transaction_id' => $originalTransaction->id,
                'amount'                  => $amount,
            ]);

            return [
                'success' => false,
                'message' => 'Error al crear el reembolso',
            ];
        }
    }

    /**
     * Agregar fondos al cliente
     */
    private function addFundsToClient(Transaction $transaction): void
    {
        $client = $transaction->client;
        $client->increment('balance', $transaction->amount);

        Log::info('TransactionManagementService - Fondos agregados al cliente', [
            'client_id'   => $client->id,
            'amount'      => $transaction->amount,
            'new_balance' => $client->fresh()->balance,
        ]);
    }

    /**
     * Actualizar factura desde transacción
     */
    private function updateInvoiceFromTransaction(Transaction $transaction): void
    {
        if ($transaction->invoice) {
            $transaction->invoice->update([
                'status'    => 'paid',
                'paid_date' => $transaction->transaction_date,
            ]);

            Log::info('TransactionManagementService - Factura actualizada desde transacción', [
                'invoice_id'     => $transaction->invoice_id,
                'transaction_id' => $transaction->id,
            ]);
        }
    }
}

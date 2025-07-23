<?php

namespace App\Domains\BillingAndPayments\Application\Services;

use App\Domains\BillingAndPayments\Application\Interfaces\PaymentGatewayInterface;
use App\Domains\BillingAndPayments\Infrastructure\Persistence\Models\Transaction;
use App\Domains\BillingAndPayments\Infrastructure\Persistence\Models\PaymentMethod;
use App\Domains\Invoices\Infrastructure\Persistence\Models\Invoice;
use App\Domains\Users\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Servicio de gestión de gateways de pago
 * 
 * Aplica Single Responsibility Principle - gestión de diferentes gateways de pago
 * Ubicado en Application layer según arquitectura hexagonal
 */
class PaymentGatewayService
{
    private array $gateways = [];

    /**
     * Registrar un gateway de pago
     */
    public function registerGateway(string $name, PaymentGatewayInterface $gateway): void
    {
        $this->gateways[$name] = $gateway;
    }

    /**
     * Obtener gateway por nombre
     */
    public function getGateway(string $name): ?PaymentGatewayInterface
    {
        return $this->gateways[$name] ?? null;
    }

    /**
     * Procesar pago a través del gateway especificado
     */
    public function processPayment(string $gatewayName, array $paymentData): array
    {
        try {
            $gateway = $this->getGateway($gatewayName);
            
            if (!$gateway) {
                return [
                    'success' => false,
                    'message' => "Gateway de pago '{$gatewayName}' no encontrado",
                    'transaction' => null
                ];
            }

            // Validar configuración del gateway
            if (!$gateway->validateConfiguration()) {
                return [
                    'success' => false,
                    'message' => "Gateway de pago '{$gatewayName}' no está configurado correctamente",
                    'transaction' => null
                ];
            }

            // Procesar el pago
            $result = $gateway->processPayment($paymentData);

            if ($result['success']) {
                // Registrar transacción exitosa
                $transaction = $this->createTransaction($paymentData, $result, 'completed');
                
                Log::info('Pago procesado exitosamente', [
                    'gateway' => $gatewayName,
                    'transaction_id' => $transaction->id,
                    'amount' => $paymentData['amount']
                ]);

                return [
                    'success' => true,
                    'message' => 'Pago procesado exitosamente',
                    'transaction' => $transaction,
                    'gateway_response' => $result
                ];
            } else {
                // Registrar transacción fallida
                $transaction = $this->createTransaction($paymentData, $result, 'failed');
                
                Log::warning('Pago fallido', [
                    'gateway' => $gatewayName,
                    'transaction_id' => $transaction->id,
                    'error' => $result['message']
                ]);

                return [
                    'success' => false,
                    'message' => $result['message'] ?? 'Error procesando el pago',
                    'transaction' => $transaction,
                    'gateway_response' => $result
                ];
            }

        } catch (\Exception $e) {
            Log::error('Error procesando pago', [
                'gateway' => $gatewayName,
                'error' => $e->getMessage(),
                'payment_data' => $paymentData
            ]);

            return [
                'success' => false,
                'message' => 'Error interno procesando el pago',
                'transaction' => null
            ];
        }
    }

    /**
     * Procesar reembolso
     */
    public function processRefund(string $gatewayName, string $transactionId, float $amount): array
    {
        try {
            $gateway = $this->getGateway($gatewayName);
            
            if (!$gateway) {
                return [
                    'success' => false,
                    'message' => "Gateway de pago '{$gatewayName}' no encontrado"
                ];
            }

            $result = $gateway->processRefund($transactionId, $amount);

            if ($result['success']) {
                // Crear transacción de reembolso
                $originalTransaction = Transaction::where('gateway_transaction_id', $transactionId)->first();
                
                if ($originalTransaction) {
                    $refundTransaction = Transaction::create([
                        'client_id' => $originalTransaction->client_id,
                        'invoice_id' => $originalTransaction->invoice_id,
                        'type' => 'refund',
                        'status' => 'completed',
                        'amount' => -$amount, // Negativo para reembolso
                        'currency_code' => $originalTransaction->currency_code,
                        'transaction_date' => now(),
                        'description' => "Reembolso de transacción #{$originalTransaction->id}",
                        'gateway_transaction_id' => $result['refund_id'] ?? null,
                        'payment_method_id' => $originalTransaction->payment_method_id,
                    ]);

                    Log::info('Reembolso procesado exitosamente', [
                        'gateway' => $gatewayName,
                        'original_transaction_id' => $originalTransaction->id,
                        'refund_transaction_id' => $refundTransaction->id,
                        'amount' => $amount
                    ]);
                }

                return [
                    'success' => true,
                    'message' => 'Reembolso procesado exitosamente',
                    'gateway_response' => $result
                ];
            } else {
                Log::warning('Reembolso fallido', [
                    'gateway' => $gatewayName,
                    'transaction_id' => $transactionId,
                    'error' => $result['message']
                ]);

                return [
                    'success' => false,
                    'message' => $result['message'] ?? 'Error procesando el reembolso'
                ];
            }

        } catch (\Exception $e) {
            Log::error('Error procesando reembolso', [
                'gateway' => $gatewayName,
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error interno procesando el reembolso'
            ];
        }
    }

    /**
     * Verificar estado de pago
     */
    public function getPaymentStatus(string $gatewayName, string $transactionId): array
    {
        try {
            $gateway = $this->getGateway($gatewayName);
            
            if (!$gateway) {
                return [
                    'success' => false,
                    'message' => "Gateway de pago '{$gatewayName}' no encontrado"
                ];
            }

            return $gateway->getPaymentStatus($transactionId);

        } catch (\Exception $e) {
            Log::error('Error verificando estado de pago', [
                'gateway' => $gatewayName,
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error verificando el estado del pago'
            ];
        }
    }

    /**
     * Obtener métodos de pago disponibles para un gateway
     */
    public function getAvailablePaymentMethods(string $gatewayName): array
    {
        try {
            $gateway = $this->getGateway($gatewayName);
            
            if (!$gateway) {
                return [];
            }

            return $gateway->getAvailablePaymentMethods();

        } catch (\Exception $e) {
            Log::error('Error obteniendo métodos de pago', [
                'gateway' => $gatewayName,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Obtener todos los gateways registrados
     */
    public function getRegisteredGateways(): array
    {
        $gateways = [];
        
        foreach ($this->gateways as $name => $gateway) {
            $gateways[] = [
                'name' => $name,
                'display_name' => $gateway->getName(),
                'is_configured' => $gateway->validateConfiguration(),
                'available_methods' => $gateway->getAvailablePaymentMethods()
            ];
        }

        return $gateways;
    }

    /**
     * Procesar pago de factura
     */
    public function processInvoicePayment(Invoice $invoice, array $paymentData): array
    {
        try {
            DB::beginTransaction();

            // Preparar datos del pago
            $paymentData = array_merge($paymentData, [
                'amount' => $invoice->total_amount,
                'currency' => $invoice->currency_code,
                'invoice_id' => $invoice->id,
                'client_id' => $invoice->client_id,
                'description' => "Pago de factura #{$invoice->invoice_number}"
            ]);

            // Procesar pago
            $result = $this->processPayment($paymentData['gateway'], $paymentData);

            if ($result['success']) {
                // Marcar factura como pagada
                $invoice->update([
                    'status' => 'paid',
                    'paid_date' => now()->toDateString()
                ]);

                DB::commit();

                return [
                    'success' => true,
                    'message' => 'Factura pagada exitosamente',
                    'invoice' => $invoice->fresh(),
                    'transaction' => $result['transaction']
                ];
            } else {
                DB::rollBack();
                return $result;
            }

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error procesando pago de factura', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error procesando el pago de la factura'
            ];
        }
    }

    /**
     * Crear transacción en la base de datos
     */
    private function createTransaction(array $paymentData, array $gatewayResult, string $status): Transaction
    {
        return Transaction::create([
            'client_id' => $paymentData['client_id'],
            'invoice_id' => $paymentData['invoice_id'] ?? null,
            'type' => 'payment',
            'status' => $status,
            'amount' => $paymentData['amount'],
            'currency_code' => $paymentData['currency'] ?? 'USD',
            'transaction_date' => now(),
            'description' => $paymentData['description'] ?? 'Pago procesado',
            'gateway_transaction_id' => $gatewayResult['transaction_id'] ?? null,
            'payment_method_id' => $paymentData['payment_method_id'] ?? null,
        ]);
    }
}

<?php

namespace App\Services;

use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\User;
use App\Services\PayPalService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Servicio para la gestión de adición de fondos
 * 
 * Extrae la lógica de negocio del ClientFundAdditionController aplicando el SRP
 */
class FundAdditionService
{
    public function __construct(
        private PayPalService $payPalService
    ) {}

    /**
     * Obtener datos para el formulario de adición de fondos
     */
    public function getFormData(User $client): array
    {
        try {
            $paymentMethods = PaymentMethod::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'is_automatic', 'type', 'account_holder_name', 
                       'account_number', 'bank_name', 'branch_name', 'swift_code', 'iban', 
                       'instructions', 'logo_url']);

            $currencyCode = $client->currency_code ?? 'USD';

            return [
                'success' => true,
                'data' => [
                    'paymentMethods' => $paymentMethods,
                    'currencyCode' => $currencyCode,
                    'currentBalance' => $client->balance,
                    'formattedBalance' => $client->formatted_balance,
                ]
            ];

        } catch (\Exception $e) {
            Log::error('FundAdditionService - Error obteniendo datos del formulario', [
                'error' => $e->getMessage(),
                'client_id' => $client->id
            ]);

            return [
                'success' => false,
                'data' => [],
                'message' => 'Error al cargar el formulario de adición de fondos'
            ];
        }
    }

    /**
     * Procesar solicitud manual de adición de fondos
     */
    public function processManualFundAddition(User $client, array $data): array
    {
        DB::beginTransaction();
        try {
            $transaction = Transaction::create([
                'client_id' => $client->id,
                'invoice_id' => null,
                'order_id' => null,
                'payment_method_id' => $data['payment_method_id'],
                'gateway_slug' => 'manual_fund_addition',
                'gateway_transaction_id' => $data['reference_number'],
                'amount' => $data['amount'],
                'currency_code' => $client->currency_code ?? 'USD',
                'status' => 'pending',
                'type' => 'credit_added',
                'transaction_date' => $data['payment_date'],
                'description' => 'Solicitud de adición de fondos por cliente.',
                'fees_amount' => 0,
            ]);

            DB::commit();

            Log::info('FundAdditionService - Solicitud manual de fondos creada', [
                'transaction_id' => $transaction->id,
                'client_id' => $client->id,
                'amount' => $data['amount']
            ]);

            return [
                'success' => true,
                'data' => $transaction,
                'message' => 'Tu solicitud para agregar fondos ha sido enviada y está pendiente de confirmación.'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('FundAdditionService - Error procesando solicitud manual', [
                'error' => $e->getMessage(),
                'client_id' => $client->id,
                'data' => $data
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Error al procesar la solicitud de fondos: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Iniciar pago con PayPal para adición de fondos
     */
    public function initiatePayPalPayment(User $client, float $amount): array
    {
        try {
            // Validar monto mínimo
            if ($amount < 30.00) {
                return [
                    'success' => false,
                    'message' => 'Para agregar fondos con PayPal, el monto mínimo es de $30.00 USD.'
                ];
            }

            $currencyCode = $client->currency_code ?? config('paypal.currency', 'USD');
            $fundAdditionIdentifier = 'FUNDS-' . $client->id . '-' . strtoupper(Str::random(8));

            $successUrl = route('client.funds.paypal.success');
            $cancelUrl = route('client.funds.paypal.cancel');
            $descriptionSuffix = 'Usuario ID: ' . $client->id;

            $paypalOrderDetails = $this->payPalService->createFundAdditionOrder(
                $amount,
                $currencyCode,
                $descriptionSuffix,
                $fundAdditionIdentifier,
                $successUrl,
                $cancelUrl
            );

            if ($paypalOrderDetails && isset($paypalOrderDetails['approval_link'])) {
                return [
                    'success' => true,
                    'data' => [
                        'approval_link' => $paypalOrderDetails['approval_link'],
                        'order_id' => $paypalOrderDetails['order_id'],
                        'session_data' => [
                            'paypal_fund_order_id' => $paypalOrderDetails['order_id'],
                            'paypal_fund_amount' => $amount,
                            'paypal_fund_currency' => $currencyCode,
                            'paypal_fund_identifier' => $fundAdditionIdentifier,
                        ]
                    ],
                    'message' => 'Orden de PayPal creada exitosamente'
                ];
            }

            Log::error('FundAdditionService - PayPal: Failed to get approval link', [
                'client_id' => $client->id,
                'amount' => $amount,
                'paypal_response' => $paypalOrderDetails
            ]);

            return [
                'success' => false,
                'message' => 'No se pudo iniciar el pago con PayPal. Por favor, intente más tarde.'
            ];

        } catch (\Exception $e) {
            Log::error('FundAdditionService - PayPal initiation exception', [
                'error' => $e->getMessage(),
                'client_id' => $client->id,
                'amount' => $amount,
                'trace' => substr($e->getTraceAsString(), 0, 500)
            ]);

            return [
                'success' => false,
                'message' => 'Ocurrió un error inesperado con PayPal. Por favor, contacte a soporte.'
            ];
        }
    }

    /**
     * Procesar éxito de pago PayPal
     */
    public function handlePayPalSuccess(User $client, array $sessionData): array
    {
        $paypalOrderId = $sessionData['paypal_fund_order_id'] ?? null;
        $amount = $sessionData['paypal_fund_amount'] ?? null;
        $currencyCode = $sessionData['paypal_fund_currency'] ?? null;

        if (!$paypalOrderId || !is_numeric($amount) || !$currencyCode) {
            Log::error('FundAdditionService - PayPal success: Missing or invalid session data', [
                'session_data' => $sessionData,
                'client_id' => $client->id
            ]);

            return [
                'success' => false,
                'message' => 'Error al procesar el pago de PayPal: Sesión inválida o datos corruptos.'
            ];
        }

        DB::beginTransaction();
        try {
            $captureResponse = $this->payPalService->captureOrder($paypalOrderId);

            if ($captureResponse && isset($captureResponse['status']) && $captureResponse['status'] === 'COMPLETED') {
                $paypalPaymentMethod = PaymentMethod::where('slug', 'paypal')->first();

                $transaction = Transaction::create([
                    'client_id' => $client->id,
                    'invoice_id' => null,
                    'payment_method_id' => $paypalPaymentMethod ? $paypalPaymentMethod->id : null,
                    'gateway_slug' => 'paypal',
                    'gateway_transaction_id' => $captureResponse['paypal_capture_id'] ?? 'N/A',
                    'type' => 'credit_added',
                    'amount' => (float) $amount,
                    'currency_code' => $currencyCode,
                    'status' => 'completed',
                    'description' => "Adición de fondos vía PayPal. ID de captura: " . ($captureResponse['paypal_capture_id'] ?? 'N/A'),
                    'transaction_date' => Carbon::now(),
                    'fees_amount' => $captureResponse['paypal_fee'] ?? 0.00,
                ]);

                $client->increment('balance', (float) $amount);

                DB::commit();

                Log::info('FundAdditionService - PayPal payment completed successfully', [
                    'transaction_id' => $transaction->id,
                    'client_id' => $client->id,
                    'amount' => $amount,
                    'paypal_order_id' => $paypalOrderId
                ]);

                return [
                    'success' => true,
                    'data' => $transaction,
                    'message' => "Fondos agregados exitosamente a tu cuenta por {$currencyCode} " . number_format((float)$amount, 2) . "."
                ];

            } else {
                Log::error('FundAdditionService - PayPal capture failed or status not COMPLETED', [
                    'client_id' => $client->id,
                    'paypal_order_id' => $paypalOrderId,
                    'capture_response' => $captureResponse
                ]);

                return [
                    'success' => false,
                    'message' => 'Falló la captura del pago con PayPal. No se agregaron fondos.'
                ];
            }

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('FundAdditionService - PayPal success processing error', [
                'error' => $e->getMessage(),
                'client_id' => $client->id,
                'paypal_order_id' => $paypalOrderId,
                'trace' => substr($e->getTraceAsString(), 0, 500)
            ]);

            return [
                'success' => false,
                'message' => 'Error al registrar la adición de fondos después del pago. Contacte a soporte.'
            ];
        }
    }

    /**
     * Manejar cancelación de PayPal
     */
    public function handlePayPalCancel(User $client): array
    {
        Log::info('FundAdditionService - PayPal fund addition cancelled by user', [
            'client_id' => $client->id
        ]);

        return [
            'success' => true,
            'message' => 'La adición de fondos con PayPal fue cancelada.'
        ];
    }

    /**
     * Obtener historial de adiciones de fondos del cliente
     */
    public function getFundAdditionHistory(User $client, int $perPage = 10): Collection
    {
        try {
            return Transaction::where('client_id', $client->id)
                ->where('type', 'credit_added')
                ->with('paymentMethod:id,name,type')
                ->orderBy('transaction_date', 'desc')
                ->paginate($perPage);

        } catch (\Exception $e) {
            Log::error('FundAdditionService - Error obteniendo historial de fondos', [
                'error' => $e->getMessage(),
                'client_id' => $client->id
            ]);

            return collect();
        }
    }

    /**
     * Validar datos de adición manual de fondos
     */
    public function validateManualFundAddition(array $data): array
    {
        $errors = [];

        if (!isset($data['amount']) || !is_numeric($data['amount']) || $data['amount'] <= 0) {
            $errors['amount'] = 'El monto debe ser un número positivo';
        }

        if (!isset($data['payment_method_id']) || !PaymentMethod::where('id', $data['payment_method_id'])->where('is_active', true)->exists()) {
            $errors['payment_method_id'] = 'Método de pago inválido';
        }

        if (!isset($data['reference_number']) || empty(trim($data['reference_number']))) {
            $errors['reference_number'] = 'El número de referencia es requerido';
        }

        if (!isset($data['payment_date']) || !strtotime($data['payment_date'])) {
            $errors['payment_date'] = 'Fecha de pago inválida';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Obtener estadísticas de adición de fondos del cliente
     */
    public function getFundAdditionStats(User $client): array
    {
        try {
            $stats = [
                'total_added' => Transaction::where('client_id', $client->id)
                    ->where('type', 'credit_added')
                    ->where('status', 'completed')
                    ->sum('amount'),
                'pending_additions' => Transaction::where('client_id', $client->id)
                    ->where('type', 'credit_added')
                    ->where('status', 'pending')
                    ->count(),
                'last_addition' => Transaction::where('client_id', $client->id)
                    ->where('type', 'credit_added')
                    ->where('status', 'completed')
                    ->latest('transaction_date')
                    ->first(),
            ];

            return [
                'success' => true,
                'data' => $stats
            ];

        } catch (\Exception $e) {
            Log::error('FundAdditionService - Error obteniendo estadísticas', [
                'error' => $e->getMessage(),
                'client_id' => $client->id
            ]);

            return [
                'success' => false,
                'data' => [],
                'message' => 'Error al obtener estadísticas'
            ];
        }
    }
}

<?php

namespace App\Patterns\Strategy;

use Illuminate\Support\Facades\Log;

/**
 * Class CreditCardPaymentStrategy
 * 
 * Implementa el procesamiento de pagos con tarjeta de crédito
 * Implementa el patrón Strategy
 */
class CreditCardPaymentStrategy implements PaymentStrategyInterface
{
    private const FEE_PERCENTAGE = 0.029; // 2.9%
    private const MIN_AMOUNT = 1.00;
    private const MAX_AMOUNT = 10000.00;

    /**
     * Procesar un pago con tarjeta de crédito
     *
     * @param float $amount
     * @param array $paymentData
     * @return array
     */
    public function processPayment(float $amount, array $paymentData): array
    {
        try {
            // Validar datos
            $validation = $this->validatePaymentData($paymentData);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Datos de pago inválidos',
                    'errors' => $validation['errors']
                ];
            }

            // Verificar disponibilidad
            if (!$this->isAvailable($amount)) {
                return [
                    'success' => false,
                    'message' => 'Monto fuera del rango permitido para tarjeta de crédito'
                ];
            }

            // Simular procesamiento con gateway de pago
            $transactionId = $this->processWithGateway($amount, $paymentData);

            if ($transactionId) {
                $fees = $this->calculateFees($amount);
                
                Log::info('Pago con tarjeta de crédito procesado', [
                    'amount' => $amount,
                    'fees' => $fees,
                    'transaction_id' => $transactionId
                ]);

                return [
                    'success' => true,
                    'message' => 'Pago procesado exitosamente',
                    'transaction_id' => $transactionId,
                    'amount' => $amount,
                    'fees' => $fees,
                    'net_amount' => $amount - $fees,
                    'payment_method' => 'credit_card'
                ];
            }

            return [
                'success' => false,
                'message' => 'Error procesando el pago con tarjeta de crédito'
            ];

        } catch (\Exception $e) {
            Log::error('Error en pago con tarjeta de crédito', [
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error interno procesando el pago',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validar datos de pago
     *
     * @param array $paymentData
     * @return array
     */
    public function validatePaymentData(array $paymentData): array
    {
        $errors = [];

        // Validar número de tarjeta
        if (!isset($paymentData['card_number']) || !$this->isValidCardNumber($paymentData['card_number'])) {
            $errors['card_number'] = 'Número de tarjeta inválido';
        }

        // Validar fecha de expiración
        if (!isset($paymentData['expiry_month']) || !isset($paymentData['expiry_year'])) {
            $errors['expiry'] = 'Fecha de expiración requerida';
        } elseif (!$this->isValidExpiry($paymentData['expiry_month'], $paymentData['expiry_year'])) {
            $errors['expiry'] = 'Fecha de expiración inválida';
        }

        // Validar CVV
        if (!isset($paymentData['cvv']) || !$this->isValidCvv($paymentData['cvv'])) {
            $errors['cvv'] = 'CVV inválido';
        }

        // Validar nombre del titular
        if (!isset($paymentData['cardholder_name']) || empty(trim($paymentData['cardholder_name']))) {
            $errors['cardholder_name'] = 'Nombre del titular requerido';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Obtener información del método de pago
     *
     * @return array
     */
    public function getPaymentMethodInfo(): array
    {
        return [
            'name' => 'Tarjeta de Crédito',
            'code' => 'credit_card',
            'description' => 'Pago con tarjeta de crédito Visa, MasterCard, American Express',
            'fee_percentage' => self::FEE_PERCENTAGE * 100,
            'min_amount' => self::MIN_AMOUNT,
            'max_amount' => self::MAX_AMOUNT,
            'processing_time' => 'Inmediato',
            'supported_currencies' => ['USD', 'EUR', 'MXN'],
            'requires_fields' => [
                'card_number',
                'expiry_month',
                'expiry_year',
                'cvv',
                'cardholder_name'
            ]
        ];
    }

    /**
     * Verificar si el método de pago está disponible
     *
     * @param float $amount
     * @return bool
     */
    public function isAvailable(float $amount): bool
    {
        return $amount >= self::MIN_AMOUNT && $amount <= self::MAX_AMOUNT;
    }

    /**
     * Calcular comisiones del método de pago
     *
     * @param float $amount
     * @return float
     */
    public function calculateFees(float $amount): float
    {
        return round($amount * self::FEE_PERCENTAGE, 2);
    }

    /**
     * Validar número de tarjeta usando algoritmo de Luhn
     *
     * @param string $cardNumber
     * @return bool
     */
    private function isValidCardNumber(string $cardNumber): bool
    {
        $cardNumber = preg_replace('/\D/', '', $cardNumber);
        
        if (strlen($cardNumber) < 13 || strlen($cardNumber) > 19) {
            return false;
        }

        // Algoritmo de Luhn
        $sum = 0;
        $alternate = false;
        
        for ($i = strlen($cardNumber) - 1; $i >= 0; $i--) {
            $digit = intval($cardNumber[$i]);
            
            if ($alternate) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit = ($digit % 10) + 1;
                }
            }
            
            $sum += $digit;
            $alternate = !$alternate;
        }
        
        return ($sum % 10) === 0;
    }

    /**
     * Validar fecha de expiración
     *
     * @param string $month
     * @param string $year
     * @return bool
     */
    private function isValidExpiry(string $month, string $year): bool
    {
        $month = intval($month);
        $year = intval($year);
        
        if ($month < 1 || $month > 12) {
            return false;
        }
        
        $currentYear = intval(date('Y'));
        $currentMonth = intval(date('m'));
        
        if ($year < $currentYear || ($year == $currentYear && $month < $currentMonth)) {
            return false;
        }
        
        return true;
    }

    /**
     * Validar CVV
     *
     * @param string $cvv
     * @return bool
     */
    private function isValidCvv(string $cvv): bool
    {
        return preg_match('/^\d{3,4}$/', $cvv);
    }

    /**
     * Simular procesamiento con gateway de pago
     *
     * @param float $amount
     * @param array $paymentData
     * @return string|null
     */
    private function processWithGateway(float $amount, array $paymentData): ?string
    {
        // Simular llamada a API de gateway de pago
        // En implementación real, aquí iría la integración con Stripe, PayPal, etc.
        
        // Simular éxito/fallo (90% éxito)
        if (rand(1, 10) <= 9) {
            return 'cc_' . uniqid() . '_' . time();
        }
        
        return null;
    }
}

<?php

namespace App\Patterns\Strategy;

/**
 * Interface PaymentStrategyInterface
 * 
 * Define el contrato para diferentes estrategias de pago
 * Implementa el patrón Strategy
 */
interface PaymentStrategyInterface
{
    /**
     * Procesar un pago
     *
     * @param float $amount
     * @param array $paymentData
     * @return array
     */
    public function processPayment(float $amount, array $paymentData): array;

    /**
     * Validar datos de pago
     *
     * @param array $paymentData
     * @return array
     */
    public function validatePaymentData(array $paymentData): array;

    /**
     * Obtener información del método de pago
     *
     * @return array
     */
    public function getPaymentMethodInfo(): array;

    /**
     * Verificar si el método de pago está disponible
     *
     * @param float $amount
     * @return bool
     */
    public function isAvailable(float $amount): bool;

    /**
     * Calcular comisiones del método de pago
     *
     * @param float $amount
     * @return float
     */
    public function calculateFees(float $amount): float;
}

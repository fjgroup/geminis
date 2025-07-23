<?php

namespace App\Domains\BillingAndPayments\Application\Interfaces;

/**
 * Interfaz para gateways de pago
 * 
 * Define el contrato que deben cumplir todos los gateways de pago
 * Aplica Dependency Inversion Principle de SOLID
 */
interface PaymentGatewayInterface
{
    /**
     * Procesar un pago
     */
    public function processPayment(array $paymentData): array;

    /**
     * Verificar el estado de un pago
     */
    public function getPaymentStatus(string $transactionId): array;

    /**
     * Procesar un reembolso
     */
    public function processRefund(string $transactionId, float $amount): array;

    /**
     * Obtener métodos de pago disponibles
     */
    public function getAvailablePaymentMethods(): array;

    /**
     * Validar configuración del gateway
     */
    public function validateConfiguration(): bool;

    /**
     * Obtener nombre del gateway
     */
    public function getName(): string;
}

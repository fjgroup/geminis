<?php

namespace App\Patterns\Strategy;

use Illuminate\Support\Facades\Log;

/**
 * Class PaymentProcessor
 * 
 * Contexto para el patrón Strategy de procesamiento de pagos
 * Permite cambiar dinámicamente la estrategia de pago
 */
class PaymentProcessor
{
    private PaymentStrategyInterface $strategy;
    private array $availableStrategies = [];

    /**
     * Constructor
     *
     * @param PaymentStrategyInterface|null $strategy
     */
    public function __construct(?PaymentStrategyInterface $strategy = null)
    {
        if ($strategy) {
            $this->strategy = $strategy;
        }
    }

    /**
     * Establecer la estrategia de pago
     *
     * @param PaymentStrategyInterface $strategy
     * @return void
     */
    public function setStrategy(PaymentStrategyInterface $strategy): void
    {
        $this->strategy = $strategy;
        
        Log::debug('Estrategia de pago cambiada', [
            'strategy' => get_class($strategy)
        ]);
    }

    /**
     * Registrar una estrategia disponible
     *
     * @param string $name
     * @param PaymentStrategyInterface $strategy
     * @return void
     */
    public function registerStrategy(string $name, PaymentStrategyInterface $strategy): void
    {
        $this->availableStrategies[$name] = $strategy;
        
        Log::debug('Estrategia de pago registrada', [
            'name' => $name,
            'strategy' => get_class($strategy)
        ]);
    }

    /**
     * Usar una estrategia por nombre
     *
     * @param string $name
     * @return bool
     */
    public function useStrategy(string $name): bool
    {
        if (isset($this->availableStrategies[$name])) {
            $this->setStrategy($this->availableStrategies[$name]);
            return true;
        }

        Log::warning('Estrategia de pago no encontrada', ['name' => $name]);
        return false;
    }

    /**
     * Procesar un pago usando la estrategia actual
     *
     * @param float $amount
     * @param array $paymentData
     * @return array
     */
    public function processPayment(float $amount, array $paymentData): array
    {
        if (!isset($this->strategy)) {
            return [
                'success' => false,
                'message' => 'No hay estrategia de pago configurada'
            ];
        }

        Log::info('Iniciando procesamiento de pago', [
            'amount' => $amount,
            'strategy' => get_class($this->strategy)
        ]);

        $startTime = microtime(true);
        $result = $this->strategy->processPayment($amount, $paymentData);
        $processingTime = round((microtime(true) - $startTime) * 1000, 2);

        $result['processing_time_ms'] = $processingTime;
        $result['strategy_used'] = get_class($this->strategy);

        Log::info('Pago procesado', [
            'success' => $result['success'] ?? false,
            'processing_time_ms' => $processingTime,
            'strategy' => get_class($this->strategy)
        ]);

        return $result;
    }

    /**
     * Validar datos de pago usando la estrategia actual
     *
     * @param array $paymentData
     * @return array
     */
    public function validatePaymentData(array $paymentData): array
    {
        if (!isset($this->strategy)) {
            return [
                'valid' => false,
                'errors' => ['strategy' => 'No hay estrategia de pago configurada']
            ];
        }

        return $this->strategy->validatePaymentData($paymentData);
    }

    /**
     * Obtener información del método de pago actual
     *
     * @return array
     */
    public function getPaymentMethodInfo(): array
    {
        if (!isset($this->strategy)) {
            return [
                'error' => 'No hay estrategia de pago configurada'
            ];
        }

        return $this->strategy->getPaymentMethodInfo();
    }

    /**
     * Verificar si el método de pago actual está disponible
     *
     * @param float $amount
     * @return bool
     */
    public function isAvailable(float $amount): bool
    {
        if (!isset($this->strategy)) {
            return false;
        }

        return $this->strategy->isAvailable($amount);
    }

    /**
     * Calcular comisiones del método de pago actual
     *
     * @param float $amount
     * @return float
     */
    public function calculateFees(float $amount): float
    {
        if (!isset($this->strategy)) {
            return 0.0;
        }

        return $this->strategy->calculateFees($amount);
    }

    /**
     * Obtener todas las estrategias disponibles
     *
     * @return array
     */
    public function getAvailableStrategies(): array
    {
        $strategies = [];
        
        foreach ($this->availableStrategies as $name => $strategy) {
            $strategies[$name] = $strategy->getPaymentMethodInfo();
        }

        return $strategies;
    }

    /**
     * Encontrar la mejor estrategia para un monto dado
     *
     * @param float $amount
     * @param array $criteria
     * @return string|null
     */
    public function findBestStrategy(float $amount, array $criteria = []): ?string
    {
        $bestStrategy = null;
        $lowestFee = PHP_FLOAT_MAX;

        foreach ($this->availableStrategies as $name => $strategy) {
            if (!$strategy->isAvailable($amount)) {
                continue;
            }

            $fee = $strategy->calculateFees($amount);
            
            // Criterio por defecto: menor comisión
            if ($fee < $lowestFee) {
                $lowestFee = $fee;
                $bestStrategy = $name;
            }
        }

        if ($bestStrategy) {
            Log::debug('Mejor estrategia encontrada', [
                'strategy' => $bestStrategy,
                'fee' => $lowestFee,
                'amount' => $amount
            ]);
        }

        return $bestStrategy;
    }

    /**
     * Procesar pago con la mejor estrategia disponible
     *
     * @param float $amount
     * @param array $paymentData
     * @param array $criteria
     * @return array
     */
    public function processWithBestStrategy(float $amount, array $paymentData, array $criteria = []): array
    {
        $bestStrategy = $this->findBestStrategy($amount, $criteria);
        
        if (!$bestStrategy) {
            return [
                'success' => false,
                'message' => 'No hay estrategias de pago disponibles para este monto'
            ];
        }

        $this->useStrategy($bestStrategy);
        return $this->processPayment($amount, $paymentData);
    }

    /**
     * Obtener estadísticas de las estrategias
     *
     * @return array
     */
    public function getStrategiesStats(): array
    {
        return [
            'total_strategies' => count($this->availableStrategies),
            'current_strategy' => isset($this->strategy) ? get_class($this->strategy) : null,
            'available_strategies' => array_keys($this->availableStrategies)
        ];
    }
}

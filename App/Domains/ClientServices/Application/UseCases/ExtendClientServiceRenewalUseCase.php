<?php

namespace App\Domains\ClientServices\Application\UseCases;

use App\Domains\ClientServices\Application\Services\ClientServiceBusinessService;
use App\Domains\ClientServices\Infrastructure\Persistence\Models\ClientService;
use App\Domains\Products\Infrastructure\Persistence\Models\BillingCycle;

/**
 * Use Case para extender la renovación de un servicio de cliente
 * 
 * Cumple con Single Responsibility Principle - una sola operación de negocio
 * Ubicado en Application layer según arquitectura hexagonal
 */
class ExtendClientServiceRenewalUseCase
{
    public function __construct(
        private ClientServiceBusinessService $businessService
    ) {}

    /**
     * Execute the use case
     *
     * @param ClientService $clientService
     * @param BillingCycle $billingCycle
     * @return array
     */
    public function execute(ClientService $clientService, BillingCycle $billingCycle): array
    {
        // Validate the service can be renewed
        $validation = $this->businessService->validateForRenewal($clientService, $billingCycle);
        
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation['errors'],
            ];
        }

        // Extend the renewal
        return $this->businessService->extendServiceRenewal($clientService, $billingCycle);
    }

    /**
     * Execute with validation and pricing calculation
     *
     * @param ClientService $clientService
     * @param BillingCycle $billingCycle
     * @return array
     */
    public function executeWithPricing(ClientService $clientService, BillingCycle $billingCycle): array
    {
        $result = $this->execute($clientService, $billingCycle);
        
        if ($result['success']) {
            $result['renewal_price'] = $this->businessService->getRenewalPrice($clientService, $billingCycle);
        }
        
        return $result;
    }
}

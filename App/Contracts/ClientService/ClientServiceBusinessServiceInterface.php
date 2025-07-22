<?php

namespace App\Contracts\ClientService;

use App\Models\ClientService;
use App\Models\BillingCycle;

/**
 * Interface ClientServiceBusinessServiceInterface
 * 
 * Contrato para servicios de lógica de negocio de servicios del cliente
 * Cumple con Interface Segregation Principle (ISP)
 */
interface ClientServiceBusinessServiceInterface
{
    /**
     * Obtener valores enum posibles para una columna
     *
     * @param string $column
     * @return array
     */
    public function getEnumValues(string $column): array;

    /**
     * Extender renovación de servicio
     *
     * @param ClientService $clientService
     * @param BillingCycle $billingCycle
     * @return array
     */
    public function extendServiceRenewal(ClientService $clientService, BillingCycle $billingCycle): array;

    /**
     * Validar si un servicio puede ser renovado
     *
     * @param ClientService $clientService
     * @return array
     */
    public function canServiceBeRenewed(ClientService $clientService): array;

    /**
     * Calcular próxima fecha de vencimiento
     *
     * @param ClientService $clientService
     * @param BillingCycle $billingCycle
     * @return \Carbon\Carbon
     */
    public function calculateNextDueDate(ClientService $clientService, BillingCycle $billingCycle): \Carbon\Carbon;
}

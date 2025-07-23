<?php

namespace App\Domains\ClientServices\Application\UseCases;

use App\Domains\ClientServices\Application\Services\ClientServiceBusinessService;

/**
 * Use Case para obtener valores enum de ClientService
 * 
 * Cumple con Single Responsibility Principle - una sola operación de consulta
 * Ubicado en Application layer según arquitectura hexagonal
 */
class GetClientServiceEnumValuesUseCase
{
    public function __construct(
        private ClientServiceBusinessService $businessService
    ) {}

    /**
     * Execute the use case
     *
     * @param string $column
     * @return array
     */
    public function execute(string $column): array
    {
        return $this->businessService->getEnumValues($column);
    }

    /**
     * Get all enum values for common columns
     *
     * @return array
     */
    public function getAllEnumValues(): array
    {
        return [
            'status' => $this->execute('status'),
            // Add other enum columns as needed
        ];
    }

    /**
     * Get status enum values with labels
     *
     * @return array
     */
    public function getStatusValuesWithLabels(): array
    {
        $statuses = $this->execute('status');
        
        $labels = [
            'pending' => 'Pendiente',
            'active' => 'Activo',
            'suspended' => 'Suspendido',
            'terminated' => 'Terminado',
            'cancelled' => 'Cancelado',
            'fraud' => 'Fraude',
            'pending_configuration' => 'Configuración Pendiente',
            'provisioning_failed' => 'Fallo en Aprovisionamiento',
        ];

        return array_map(function ($status) use ($labels) {
            return [
                'value' => $status,
                'label' => $labels[$status] ?? ucfirst(str_replace('_', ' ', $status)),
            ];
        }, $statuses);
    }
}

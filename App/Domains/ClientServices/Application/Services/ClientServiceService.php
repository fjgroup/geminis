<?php

namespace App\Domains\ClientServices\Application\Services;

use App\Domains\ClientServices\Infrastructure\Persistence\Models\ClientService;
use App\Domains\Users\Infrastructure\Persistence\Models\User;
use App\Domains\Invoices\Infrastructure\Persistence\Models\Invoice;
use Illuminate\Support\Facades\Log;

/**
 * Servicio principal para operaciones de servicios de cliente
 * 
 * Aplica Single Responsibility Principle - operaciones de consulta y procesamiento
 * Ubicado en Application layer según arquitectura hexagonal
 */
class ClientServiceService
{
    /**
     * Obtener servicios del cliente con detalles completos
     */
    public function getClientServicesWithDetails(User $user): array
    {
        try {
            $clientServices = $user->clientServices()
                ->with(['product', 'productPricing', 'billingCycle'])
                ->get();

            // Procesar servicios para agregar información de configuraciones adicionales
            $processedServices = [];

            foreach ($clientServices as $service) {
                // Parsear opciones configurables si existen
                $configurableOptionsData = null;
                if (!empty($service->notes)) {
                    $configurableOptionsData = $this->parseConfigurableOptionsWithPrices($service);
                }

                // Servicio principal
                $processedServices[] = [
                    'id' => $service->id,
                    'product_name' => $service->product->name,
                    'domain_name' => $service->domain_name,
                    'status' => $service->status,
                    'next_due_date' => $service->next_due_date,
                    'billing_amount' => $service->billing_amount,
                    'billing_cycle_name' => $service->billingCycle ? $service->billingCycle->name : 'N/A',
                    'formatted_billing_amount' => '$' . number_format($service->billing_amount, 2),
                    'formatted_next_due_date' => $service->next_due_date ? $service->next_due_date->format('d/m/Y') : 'N/A',
                    'is_additional_config' => false,
                    'parent_service_id' => null,
                    'configurable_options' => $configurableOptionsData,
                ];

                // Agregar configuraciones adicionales como servicios separados
                if ($configurableOptionsData && !empty($configurableOptionsData['options'])) {
                    foreach ($configurableOptionsData['options'] as $option) {
                        $additionalConfigService = [
                            'id' => $service->id . '_config_' . $option['slug'],
                            'product_name' => $option['name'],
                            'domain_name' => $service->domain_name,
                            'status' => $service->status,
                            'next_due_date' => $service->next_due_date,
                            'billing_amount' => $option['price'],
                            'billing_cycle_name' => $service->billingCycle ? $service->billingCycle->name : 'N/A',
                            'formatted_billing_amount' => '$' . number_format($option['price'], 2),
                            'formatted_next_due_date' => $service->next_due_date ? $service->next_due_date->format('d/m/Y') : 'N/A',
                            'is_additional_config' => true,
                            'parent_service_id' => $service->id,
                        ];

                        $processedServices[] = $additionalConfigService;
                    }
                }
            }

            // Obtener información adicional del usuario
            $actionableInvoicesCount = $user->invoices()
                ->whereIn('status', ['pending_activation', 'pending_confirmation', 'unpaid'])
                ->count();

            $unpaidInvoicesCount = $user->invoices()
                ->where('status', 'unpaid')
                ->count();

            return [
                'success' => true,
                'data' => [
                    'clientServices' => collect($processedServices),
                    'actionableInvoicesCount' => $actionableInvoicesCount,
                    'unpaidInvoicesCount' => $unpaidInvoicesCount,
                    'accountBalance' => $user->balance ?? 0,
                    'formattedAccountBalance' => '$' . number_format($user->balance ?? 0, 2),
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Error obteniendo servicios del cliente con detalles', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error al obtener los servicios del cliente',
                'data' => []
            ];
        }
    }

    /**
     * Obtener servicios activos de un cliente
     */
    public function getActiveClientServices(int $clientId): array
    {
        try {
            $services = ClientService::where('client_id', $clientId)
                ->where('status', 'active')
                ->with(['product', 'productPricing', 'billingCycle'])
                ->orderBy('created_at', 'desc')
                ->get();

            return [
                'success' => true,
                'services' => $services,
                'count' => $services->count()
            ];

        } catch (\Exception $e) {
            Log::error('Error obteniendo servicios activos del cliente', [
                'client_id' => $clientId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'services' => collect(),
                'count' => 0
            ];
        }
    }

    /**
     * Obtener servicios próximos a vencer
     */
    public function getServicesNearExpiration(int $days = 7): array
    {
        try {
            $expirationDate = now()->addDays($days);

            $services = ClientService::where('status', 'active')
                ->where('next_due_date', '<=', $expirationDate)
                ->where('next_due_date', '>=', now())
                ->with(['client', 'product', 'productPricing'])
                ->orderBy('next_due_date', 'asc')
                ->get();

            return [
                'success' => true,
                'services' => $services,
                'count' => $services->count()
            ];

        } catch (\Exception $e) {
            Log::error('Error obteniendo servicios próximos a vencer', [
                'days' => $days,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'services' => collect(),
                'count' => 0
            ];
        }
    }

    /**
     * Obtener estadísticas de servicios
     */
    public function getServiceStats(): array
    {
        try {
            $totalServices = ClientService::count();
            $activeServices = ClientService::where('status', 'active')->count();
            $suspendedServices = ClientService::where('status', 'suspended')->count();
            $pendingServices = ClientService::whereIn('status', ['pending', 'pending_configuration'])->count();
            $recentServices = ClientService::where('created_at', '>=', now()->subDays(7))->count();

            return [
                'total_services' => $totalServices,
                'active_services' => $activeServices,
                'suspended_services' => $suspendedServices,
                'pending_services' => $pendingServices,
                'recent_services' => $recentServices,
            ];

        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas de servicios', [
                'error' => $e->getMessage()
            ]);

            return [
                'total_services' => 0,
                'active_services' => 0,
                'suspended_services' => 0,
                'pending_services' => 0,
                'recent_services' => 0,
            ];
        }
    }

    /**
     * Buscar servicios por criterios
     */
    public function searchServices(array $criteria): array
    {
        try {
            $query = ClientService::with(['client', 'product', 'productPricing', 'billingCycle']);

            if (isset($criteria['client_id'])) {
                $query->where('client_id', $criteria['client_id']);
            }

            if (isset($criteria['product_id'])) {
                $query->where('product_id', $criteria['product_id']);
            }

            if (isset($criteria['status'])) {
                $query->where('status', $criteria['status']);
            }

            if (isset($criteria['domain_name'])) {
                $query->where('domain_name', 'like', '%' . $criteria['domain_name'] . '%');
            }

            if (isset($criteria['reseller_id'])) {
                $query->where('reseller_id', $criteria['reseller_id']);
            }

            $services = $query->orderBy('created_at', 'desc')->get();

            return [
                'success' => true,
                'services' => $services,
                'count' => $services->count()
            ];

        } catch (\Exception $e) {
            Log::error('Error buscando servicios', [
                'criteria' => $criteria,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'services' => collect(),
                'count' => 0
            ];
        }
    }

    /**
     * Parsear opciones configurables con precios desde las notas del servicio
     */
    private function parseConfigurableOptionsWithPrices(ClientService $service): ?array
    {
        if (empty($service->notes)) {
            return null;
        }

        $options = [];
        $lines = explode("\n", $service->notes);

        foreach ($lines as $line) {
            $line = trim($line);
            
            // Buscar patrones como "2 GB de espacio web adicional - $5.00"
            if (preg_match('/^(.+?)\s*-\s*\$(\d+(?:\.\d{2})?)$/', $line, $matches)) {
                $description = trim($matches[1]);
                $price = floatval($matches[2]);

                // Generar slug desde la descripción
                $slug = strtolower(str_replace([' ', 'á', 'é', 'í', 'ó', 'ú'], ['-', 'a', 'e', 'i', 'o', 'u'], $description));
                $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);

                $options[] = [
                    'name' => $description,
                    'slug' => $slug,
                    'price' => $price,
                    'formatted_price' => '$' . number_format($price, 2)
                ];
            }
        }

        return empty($options) ? null : [
            'options' => $options,
            'total_additional_cost' => array_sum(array_column($options, 'price'))
        ];
    }

    /**
     * Validar si un servicio puede ser modificado
     */
    public function canModifyService(ClientService $service, User $user): bool
    {
        // Admins pueden modificar cualquier servicio
        if ($user->role === 'admin') {
            return true;
        }

        // Resellers pueden modificar servicios de sus clientes
        if ($user->role === 'reseller' && $service->reseller_id === $user->id) {
            return true;
        }

        // Los clientes pueden modificar ciertos aspectos de sus servicios
        if ($user->role === 'client' && $service->client_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Obtener próximas renovaciones para un reseller
     */
    public function getUpcomingRenewalsForReseller(int $resellerId, int $days = 30): array
    {
        try {
            $renewalDate = now()->addDays($days);

            $services = ClientService::where('reseller_id', $resellerId)
                ->where('status', 'active')
                ->where('next_due_date', '<=', $renewalDate)
                ->where('next_due_date', '>=', now())
                ->with(['client', 'product', 'productPricing'])
                ->orderBy('next_due_date', 'asc')
                ->get();

            return [
                'success' => true,
                'services' => $services,
                'count' => $services->count()
            ];

        } catch (\Exception $e) {
            Log::error('Error obteniendo próximas renovaciones para reseller', [
                'reseller_id' => $resellerId,
                'days' => $days,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'services' => collect(),
                'count' => 0
            ];
        }
    }
}

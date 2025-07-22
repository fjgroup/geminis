<?php

namespace App\Domains\ClientServices\Services;

use App\Domains\ClientServices\Models\ClientService;
use App\Domains\Products\Models\Product;
use App\Models\ProductPricing;
use App\Domains\Users\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Servicio para la gestión de servicios desde el lado del cliente
 *
 * Extrae la lógica de negocio del ClientServiceController aplicando el SRP
 */
class ClientServiceService
{
    /**
     * Obtener servicios de un cliente con información procesada
     */
    public function getClientServicesWithDetails(User $client): array
    {
        try {
            $clientServices = $client->clientServices()
                ->with(['product', 'productPricing', 'billingCycle'])
                ->get();

            $processedServices = [];

            foreach ($clientServices as $service) {
                // Parsear opciones configurables si existen
                $configurableOptionsData = null;
                if (!empty($service->notes)) {
                    $configurableOptionsData = $this->parseConfigurableOptionsWithPrices($service);
                }

                // Agregar información de configuraciones al servicio base
                $service->configurable_options_details = $configurableOptionsData['options'] ?? [];
                $service->configurable_options_total = $configurableOptionsData['total_price'] ?? 0;
                $service->has_configurable_options = !empty($configurableOptionsData['options']);

                // Agregar el servicio base con toda la información
                $processedServices[] = $service;

                // Si hay opciones configurables, crear un "servicio adicional" virtual
                if (!empty($configurableOptionsData['options'])) {
                    $additionalConfigService = (object) [
                        'id' => $service->id . '_config',
                        'product' => (object) [
                            'name' => 'Configuraciones Adicionales - ' . $service->product->name,
                            'id' => 'config_' . $service->product->id,
                        ],
                        'productPricing' => $service->productPricing,
                        'billingCycle' => $service->billingCycle,
                        'domain_name' => null,
                        'status' => $service->status,
                        'next_due_date' => $service->next_due_date,
                        'billing_amount' => $configurableOptionsData['total_price'],
                        'configurable_options_details' => $configurableOptionsData['options'],
                        'configurable_options_total' => $configurableOptionsData['total_price'],
                        'has_configurable_options' => true,
                        'is_additional_config' => true,
                        'parent_service_id' => $service->id,
                    ];

                    $processedServices[] = $additionalConfigService;
                }
            }

            // Obtener estadísticas adicionales
            $actionableInvoicesCount = $client->invoices()
                ->whereIn('status', ['pending_activation', 'pending_confirmation', 'unpaid'])
                ->count();

            $unpaidInvoicesCount = $client->invoices()
                ->where('status', 'unpaid')
                ->count();

            return [
                'success' => true,
                'data' => [
                    'clientServices' => collect($processedServices),
                    'actionableInvoicesCount' => $actionableInvoicesCount,
                    'unpaidInvoicesCount' => $unpaidInvoicesCount,
                    'accountBalance' => $client->balance,
                    'formattedAccountBalance' => $client->formatted_balance,
                ]
            ];

        } catch (\Exception $e) {
            Log::error('ClientServiceService - Error obteniendo servicios del cliente', [
                'error' => $e->getMessage(),
                'client_id' => $client->id
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Error al obtener los servicios: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Parsear opciones configurables con precios desde las notas del servicio
     */
    private function parseConfigurableOptionsWithPrices(ClientService $service): array
    {
        try {
            $options = [];
            $totalPrice = 0;

            if (empty($service->notes)) {
                return ['options' => $options, 'total_price' => $totalPrice];
            }

            // Parsear las notas que contienen las opciones configurables
            $lines = explode("\n", $service->notes);

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                // Buscar patrones como "Opción: Valor - $10.00"
                if (preg_match('/^(.+?):\s*(.+?)\s*-\s*\$?(\d+\.?\d*)/', $line, $matches)) {
                    $optionName = trim($matches[1]);
                    $optionValue = trim($matches[2]);
                    $price = floatval($matches[3]);

                    $options[] = [
                        'name' => $optionName,
                        'value' => $optionValue,
                        'price' => $price,
                        'formatted_price' => '$' . number_format($price, 2)
                    ];

                    $totalPrice += $price;
                }
            }

            return [
                'options' => $options,
                'total_price' => $totalPrice
            ];

        } catch (\Exception $e) {
            Log::error('ClientServiceService - Error parseando opciones configurables', [
                'error' => $e->getMessage(),
                'service_id' => $service->id
            ]);

            return ['options' => [], 'total_price' => 0];
        }
    }

    /**
     * Obtener opciones de upgrade/downgrade para un servicio
     */
    public function getUpgradeDowngradeOptions(ClientService $service): array
    {
        try {
            $service->loadMissing(['product.productType', 'productPricing.billingCycle']);

            if (!$service->product || !$service->product->productType) {
                return [
                    'success' => false,
                    'message' => 'No se pudo determinar el tipo de producto del servicio actual.'
                ];
            }

            $currentProductType = $service->product->productType;
            $currentBillingCycle = $service->productPricing->billingCycle ?? null;

            if (!$currentBillingCycle) {
                return [
                    'success' => false,
                    'message' => 'No se pudo determinar el ciclo de facturación actual del servicio.'
                ];
            }

            // Obtener productos del mismo tipo
            $availableProducts = Product::where('product_type_id', $currentProductType->id)
                ->where('status', 'active')
                ->where('id', '!=', $service->product_id)
                ->with(['pricings' => function ($query) use ($currentBillingCycle) {
                    $query->where('billing_cycle_id', $currentBillingCycle->id)
                          ->with('billingCycle');
                }])
                ->get()
                ->filter(function ($product) {
                    return $product->pricings->isNotEmpty();
                })
                ->map(function ($product) use ($service) {
                    $pricing = $product->pricings->first();
                    $currentPrice = $service->billing_amount;
                    $newPrice = $pricing->price;
                    $priceDifference = $newPrice - $currentPrice;

                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'current_price' => $currentPrice,
                        'new_price' => $newPrice,
                        'price_difference' => $priceDifference,
                        'is_upgrade' => $priceDifference > 0,
                        'is_downgrade' => $priceDifference < 0,
                        'formatted_current_price' => '$' . number_format($currentPrice, 2),
                        'formatted_new_price' => '$' . number_format($newPrice, 2),
                        'formatted_price_difference' => ($priceDifference >= 0 ? '+' : '') . '$' . number_format($priceDifference, 2),
                        'pricing_id' => $pricing->id,
                    ];
                });

            return [
                'success' => true,
                'data' => [
                    'service' => $service,
                    'availableProducts' => $availableProducts,
                    'currentProductType' => $currentProductType,
                    'currentBillingCycle' => $currentBillingCycle,
                ]
            ];

        } catch (\Exception $e) {
            Log::error('ClientServiceService - Error obteniendo opciones de upgrade/downgrade', [
                'error' => $e->getMessage(),
                'service_id' => $service->id
            ]);

            return [
                'success' => false,
                'message' => 'Error al obtener opciones de upgrade/downgrade: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Cambiar contraseña de un servicio
     */
    public function changeServicePassword(ClientService $service, array $data): array
    {
        DB::beginTransaction();
        try {
            // Validar contraseña actual si se proporciona
            if (isset($data['current_password']) && !empty($service->password_encrypted)) {
                if (!Hash::check($data['current_password'], $service->password_encrypted)) {
                    return [
                        'success' => false,
                        'message' => 'La contraseña actual no es correcta.'
                    ];
                }
            }

            // Actualizar la contraseña
            $service->update([
                'password_encrypted' => Hash::make($data['new_password'])
            ]);

            DB::commit();

            Log::info('ClientServiceService - Contraseña de servicio cambiada', [
                'service_id' => $service->id,
                'client_id' => $service->client_id
            ]);

            return [
                'success' => true,
                'message' => 'Contraseña actualizada exitosamente'
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('ClientServiceService - Error cambiando contraseña de servicio', [
                'error' => $e->getMessage(),
                'service_id' => $service->id
            ]);

            return [
                'success' => false,
                'message' => 'Error al cambiar la contraseña: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener detalles de un servicio específico
     */
    public function getServiceDetails(ClientService $service): array
    {
        try {
            $service->load([
                'product.productType',
                'productPricing.billingCycle',
                'client',
                'reseller'
            ]);

            // Parsear opciones configurables
            $configurableOptionsData = $this->parseConfigurableOptionsWithPrices($service);

            return [
                'success' => true,
                'data' => [
                    'service' => $service,
                    'configurable_options' => $configurableOptionsData['options'],
                    'configurable_options_total' => $configurableOptionsData['total_price'],
                    'has_configurable_options' => !empty($configurableOptionsData['options']),
                ]
            ];

        } catch (\Exception $e) {
            Log::error('ClientServiceService - Error obteniendo detalles del servicio', [
                'error' => $e->getMessage(),
                'service_id' => $service->id
            ]);

            return [
                'success' => false,
                'message' => 'Error al obtener detalles del servicio: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener servicios del cliente con filtros (OPTIMIZADO)
     */
    public function getClientServices(User $client, array $filters = []): array
    {
        try {
            // Cache key específico por cliente y filtros
            $cacheKey = "client_services_{$client->id}_" . md5(serialize($filters));

            return app(\App\Services\PerformanceOptimizationService::class)->cacheOperation(
                $cacheKey,
                function () use ($client, $filters) {
                    $query = $client->clientServices();

                    // Eager loading optimizado - solo campos necesarios
                    $query->with([
                        'product:id,name,type,description',
                        'billingCycle:id,name,days',
                        'productPricing:id,price,setup_fee'
                    ]);

                    // Aplicar filtros
                    if (isset($filters['status'])) {
                        $query->where('status', $filters['status']);
                    }

                    if (isset($filters['product_type'])) {
                        $query->whereHas('product', function ($q) use ($filters) {
                            $q->where('type', $filters['product_type']);
                        });
                    }

                    // Ordenamiento optimizado con índice
                    $services = $query
                        ->select(['id', 'client_id', 'product_id', 'billing_cycle_id', 'product_pricing_id',
                                 'status', 'next_due_date', 'created_at', 'updated_at'])
                        ->orderBy('created_at', 'desc')
                        ->get();

                    return [
                        'success' => true,
                        'data' => $services
                    ];
                },
                600 // 10 minutos de cache
            );

        } catch (\Exception $e) {
            Log::error('ClientServiceService - Error obteniendo servicios del cliente', [
                'error' => $e->getMessage(),
                'client_id' => $client->id,
                'filters' => $filters
            ]);

            return [
                'success' => false,
                'data' => collect(),
                'message' => 'Error al obtener los servicios: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Invalidar cache de servicios del cliente
     */
    public function invalidateClientServicesCache(int $clientId): void
    {
        app(\App\Services\PerformanceOptimizationService::class)
            ->invalidateEntityCache('client_services', $clientId);
    }
}

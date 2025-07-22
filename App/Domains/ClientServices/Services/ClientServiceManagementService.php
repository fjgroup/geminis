<?php

namespace App\Domains\ClientServices\Services;

use App\Jobs\ProvisionClientServiceJob;
use App\Domains\ClientServices\Models\ClientService;
use App\Domains\Products\Models\Product;
use App\Domains\Users\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

/**
 * Class ClientServiceManagementService
 * 
 * Servicio para el manejo de servicios de clientes
 * Centraliza la lógica de negocio relacionada con servicios de clientes
 */
class ClientServiceManagementService
{
    /**
     * Obtener servicios de clientes con filtros y paginación
     */
    public function getClientServices(array $filters = [], int $perPage = 10): array
    {
        try {
            $query = ClientService::with([
                'client:id,name', 
                'product:id,name', 
                'reseller:id,name', 
                'billingCycle:id,name'
            ]);

            // Aplicar filtros
            if (isset($filters['search']) && !empty($filters['search'])) {
                $searchTerm = $filters['search'];
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('domain_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhereHas('client', fn($qr) => $qr->where('name', 'LIKE', "%{$searchTerm}%"))
                        ->orWhereHas('product', fn($qr) => $qr->where('name', 'LIKE', "%{$searchTerm}%"));
                });
            }

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['client_id'])) {
                $query->where('client_id', $filters['client_id']);
            }

            if (isset($filters['product_id'])) {
                $query->where('product_id', $filters['product_id']);
            }

            $clientServices = $query->latest('id')
                ->paginate($perPage)
                ->through(fn($service) => [
                    'id' => $service->id,
                    'client_name' => $service->client->name,
                    'product_name' => $service->product->name,
                    'domain_name' => $service->domain_name,
                    'status' => $service->status,
                    'next_due_date_formatted' => $service->next_due_date->format('d/m/Y'),
                    'billing_amount' => $service->billing_amount,
                    'reseller_name' => $service->reseller ? $service->reseller->name : 'N/A (Plataforma)',
                    'billing_cycle_name' => $service->billingCycle ? $service->billingCycle->name : 'N/A',
                ]);

            return [
                'success' => true,
                'data' => $clientServices
            ];

        } catch (\Exception $e) {
            Log::error('Error en ClientServiceManagementService::getClientServices', [
                'error' => $e->getMessage(),
                'filters' => $filters
            ]);

            return [
                'success' => false,
                'message' => 'Error al obtener servicios de clientes'
            ];
        }
    }

    /**
     * Crear un nuevo servicio de cliente
     */
    public function createClientService(array $validatedData): array
    {
        try {
            $clientService = ClientService::create($validatedData);

            Log::info('ClientServiceManagementService - Servicio creado', [
                'service_id' => $clientService->id,
                'client_id' => $clientService->client_id,
                'product_id' => $clientService->product_id,
                'created_by' => Auth::id()
            ]);

            return [
                'success' => true,
                'data' => $clientService,
                'message' => 'Servicio de cliente creado exitosamente'
            ];

        } catch (\Exception $e) {
            Log::error('Error en ClientServiceManagementService::createClientService', [
                'error' => $e->getMessage(),
                'data' => $validatedData
            ]);

            return [
                'success' => false,
                'message' => 'Error al crear el servicio de cliente'
            ];
        }
    }

    /**
     * Actualizar un servicio de cliente con manejo especial de contraseñas
     */
    public function updateClientService(ClientService $clientService, array $validatedData): array
    {
        try {
            // Manejo especial de la contraseña
            if (isset($validatedData['password_encrypted']) && $validatedData['password_encrypted'] !== null) {
                $clientService->password_encrypted = Hash::make($validatedData['password_encrypted']);
                unset($validatedData['password_encrypted']);
            } elseif (isset($validatedData['password_encrypted']) && $validatedData['password_encrypted'] === null) {
                $clientService->password_encrypted = null;
                unset($validatedData['password_encrypted']);
            }

            // Actualizar el resto de los datos
            $clientService->update($validatedData);

            Log::info('ClientServiceManagementService - Servicio actualizado', [
                'service_id' => $clientService->id,
                'updated_by' => Auth::id(),
                'updated_fields' => array_keys($validatedData)
            ]);

            return [
                'success' => true,
                'data' => $clientService,
                'message' => 'Servicio de cliente actualizado exitosamente'
            ];

        } catch (\Exception $e) {
            Log::error('Error en ClientServiceManagementService::updateClientService', [
                'error' => $e->getMessage(),
                'service_id' => $clientService->id,
                'data' => $validatedData
            ]);

            return [
                'success' => false,
                'message' => 'Error al actualizar el servicio de cliente'
            ];
        }
    }

    /**
     * Eliminar un servicio de cliente (soft delete)
     */
    public function deleteClientService(ClientService $clientService): array
    {
        try {
            $clientService->delete();

            Log::info('ClientServiceManagementService - Servicio eliminado', [
                'service_id' => $clientService->id,
                'deleted_by' => Auth::id()
            ]);

            return [
                'success' => true,
                'message' => 'Servicio de cliente eliminado exitosamente'
            ];

        } catch (\Exception $e) {
            Log::error('Error en ClientServiceManagementService::deleteClientService', [
                'error' => $e->getMessage(),
                'service_id' => $clientService->id
            ]);

            return [
                'success' => false,
                'message' => 'Error al eliminar el servicio de cliente'
            ];
        }
    }

    /**
     * Obtener precios de un producto específico
     */
    public function getProductPricings(Product $product): Collection
    {
        try {
            return $product->pricings()->with('billingCycle')->get();

        } catch (\Exception $e) {
            Log::error('Error en ClientServiceManagementService::getProductPricings', [
                'error' => $e->getMessage(),
                'product_id' => $product->id
            ]);

            return collect();
        }
    }

    /**
     * Reintentar aprovisionamiento de un servicio
     */
    public function retryProvisioning(ClientService $clientService): array
    {
        try {
            // Verificar autorización
            Gate::authorize('update', $clientService);

            // Verificar estado del servicio
            if ($clientService->status !== 'provisioning_failed') {
                return [
                    'success' => false,
                    'message' => 'El servicio no está en estado de fallo de aprovisionamiento'
                ];
            }

            // Cargar relaciones necesarias
            $clientService->loadMissing([
                'orderItem.order.client',
                'orderItem.product.productType',
                'orderItem.productPricing.billingCycle',
            ]);

            // Validar que exista orderItem
            if (!$clientService->orderItem) {
                Log::error('No se encontró OrderItem para ClientService', [
                    'service_id' => $clientService->id
                ]);

                return [
                    'success' => false,
                    'message' => 'No se pudo encontrar el ítem de orden asociado'
                ];
            }

            // Validar relaciones necesarias
            if (!$this->validateOrderItemRelations($clientService->orderItem)) {
                return [
                    'success' => false,
                    'message' => 'Faltan datos relacionados con el ítem de orden'
                ];
            }

            // Actualizar estado y agregar nota
            $clientService->status = 'pending_configuration';
            $clientService->notes = ($clientService->notes ? $clientService->notes . "\n" : '') . 
                "Reintento de aprovisionamiento iniciado por admin (" . Auth::user()->name . ") el " . now()->toDateTimeString() . ".";
            $clientService->save();

            // Despachar job de aprovisionamiento
            ProvisionClientServiceJob::dispatch($clientService->orderItem);

            Log::info('ClientServiceManagementService - Reintento de aprovisionamiento despachado', [
                'service_id' => $clientService->id,
                'order_item_id' => $clientService->orderItem->id,
                'initiated_by' => Auth::id()
            ]);

            return [
                'success' => true,
                'message' => 'Se ha encolado el reintento de aprovisionamiento para el servicio'
            ];

        } catch (\Exception $e) {
            Log::error('Error en ClientServiceManagementService::retryProvisioning', [
                'error' => $e->getMessage(),
                'service_id' => $clientService->id
            ]);

            return [
                'success' => false,
                'message' => 'Error al reintentar el aprovisionamiento'
            ];
        }
    }

    /**
     * Obtener datos para formularios (clientes, productos, etc.)
     */
    public function getFormData(): array
    {
        try {
            $clients = User::where('role', 'client')->orderBy('name')->get(['id', 'name']);
            $products = Product::with(['pricings.billingCycle'])
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name']);
            $resellers = User::where('role', 'reseller')->orderBy('name')->get(['id', 'name']);
            $billingCycles = \App\Models\BillingCycle::all();
            $paymentMethods = \App\Models\PaymentMethod::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']);

            return [
                'clients' => $clients->map(fn($user) => ['value' => $user->id, 'label' => $user->name]),
                'products' => $products->map(fn($product) => [
                    'value' => $product->id, 
                    'label' => $product->name, 
                    'pricings' => $product->pricings
                ]),
                'resellers' => $resellers->map(fn($user) => ['value' => $user->id, 'label' => $user->name]),
                'statusOptions' => ClientService::getPossibleEnumValues('status'),
                'billingCycles' => $billingCycles,
                'paymentMethods' => $paymentMethods->map(fn($method) => [
                    'value' => $method->id, 
                    'label' => $method->name
                ])
            ];

        } catch (\Exception $e) {
            Log::error('Error en ClientServiceManagementService::getFormData', [
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Validar relaciones necesarias del OrderItem
     */
    private function validateOrderItemRelations($orderItem): bool
    {
        $requiredRelations = [
            'order' => $orderItem->order,
            'client' => $orderItem->order?->client,
            'product' => $orderItem->product,
            'productType' => $orderItem->product?->productType,
            'productPricing' => $orderItem->productPricing,
            'billingCycle' => $orderItem->productPricing?->billingCycle,
        ];

        foreach ($requiredRelations as $relation => $value) {
            if (!$value) {
                Log::error('Relación faltante en OrderItem', [
                    'order_item_id' => $orderItem->id,
                    'missing_relation' => $relation
                ]);
                return false;
            }
        }

        return true;
    }
}

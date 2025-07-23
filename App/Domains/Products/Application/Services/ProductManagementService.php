<?php

namespace App\Domains\Products\Application\Services;

use App\Domains\Products\Infrastructure\Persistence\Models\Product;
use App\Domains\Products\Infrastructure\Persistence\Models\ProductPricing;
use App\Domains\Products\Infrastructure\Persistence\Models\ConfigurableOptionGroup;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Servicio de gestión general de productos
 * 
 * Aplica Single Responsibility Principle - gestión y actualización de productos
 * Ubicado en Application layer según arquitectura hexagonal
 */
class ProductManagementService
{
    /**
     * Actualizar información de producto
     */
    public function updateProduct(int $productId, array $updateData): array
    {
        try {
            DB::beginTransaction();

            $product = Product::findOrFail($productId);

            // Validar permisos de actualización
            $permissionCheck = $this->validateUpdatePermissions($product);
            if (!$permissionCheck['allowed']) {
                return [
                    'success' => false,
                    'message' => $permissionCheck['message'],
                    'product' => null
                ];
            }

            // Preparar datos para actualizar
            $updateFields = [];

            if (isset($updateData['name'])) {
                $updateFields['name'] = $updateData['name'];
            }

            if (isset($updateData['slug']) && $updateData['slug'] !== $product->slug) {
                // Verificar que el nuevo slug no exista
                if (Product::where('slug', $updateData['slug'])->where('id', '!=', $productId)->exists()) {
                    return [
                        'success' => false,
                        'message' => 'El slug ya está en uso por otro producto',
                        'product' => null
                    ];
                }
                $updateFields['slug'] = $updateData['slug'];
            }

            if (isset($updateData['description'])) {
                $updateFields['description'] = $updateData['description'];
            }

            if (isset($updateData['status'])) {
                $updateFields['status'] = $updateData['status'];
            }

            if (isset($updateData['is_publicly_available'])) {
                $updateFields['is_publicly_available'] = $updateData['is_publicly_available'];
            }

            if (isset($updateData['is_resellable_by_default'])) {
                $updateFields['is_resellable_by_default'] = $updateData['is_resellable_by_default'];
            }

            if (isset($updateData['display_order'])) {
                $updateFields['display_order'] = $updateData['display_order'];
            }

            if (isset($updateData['module_name'])) {
                $updateFields['module_name'] = $updateData['module_name'];
            }

            if (isset($updateData['product_type_id'])) {
                $updateFields['product_type_id'] = $updateData['product_type_id'];
            }

            // Actualizar producto
            $product->update($updateFields);

            DB::commit();

            Log::info('Producto actualizado exitosamente', [
                'product_id' => $product->id,
                'updated_fields' => array_keys($updateFields),
                'updated_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => 'Producto actualizado exitosamente',
                'product' => $product->fresh(['productType', 'pricings.billingCycle'])
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error actualizando producto', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
                'updated_by' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Error al actualizar el producto: ' . $e->getMessage(),
                'product' => null
            ];
        }
    }

    /**
     * Cambiar estado de producto
     */
    public function changeProductStatus(int $productId, string $newStatus): array
    {
        try {
            $product = Product::findOrFail($productId);

            // Validar estado válido
            $validStatuses = ['active', 'inactive', 'draft'];
            if (!in_array($newStatus, $validStatuses)) {
                return [
                    'success' => false,
                    'message' => 'Estado no válido',
                    'product' => null
                ];
            }

            // Validar permisos
            if (!$this->canChangeProductStatus($product)) {
                return [
                    'success' => false,
                    'message' => 'No tienes permisos para cambiar el estado de este producto',
                    'product' => null
                ];
            }

            $oldStatus = $product->status;
            $product->update(['status' => $newStatus]);

            Log::info('Estado de producto cambiado', [
                'product_id' => $product->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => "Estado cambiado de {$oldStatus} a {$newStatus}",
                'product' => $product->fresh()
            ];

        } catch (\Exception $e) {
            Log::error('Error cambiando estado de producto', [
                'product_id' => $productId,
                'new_status' => $newStatus,
                'error' => $e->getMessage(),
                'changed_by' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Error al cambiar el estado del producto',
                'product' => null
            ];
        }
    }

    /**
     * Eliminar producto (soft delete)
     */
    public function deleteProduct(int $productId): array
    {
        try {
            $product = Product::findOrFail($productId);

            // Validar permisos de eliminación
            if (!$this->canDeleteProduct($product)) {
                return [
                    'success' => false,
                    'message' => 'No tienes permisos para eliminar este producto',
                    'product' => null
                ];
            }

            // Verificar dependencias antes de eliminar
            $dependencyCheck = $this->checkProductDependencies($product);
            if (!$dependencyCheck['can_delete']) {
                return [
                    'success' => false,
                    'message' => $dependencyCheck['message'],
                    'product' => null
                ];
            }

            $product->delete();

            Log::info('Producto eliminado', [
                'product_id' => $product->id,
                'name' => $product->name,
                'deleted_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => 'Producto eliminado exitosamente',
                'product' => null
            ];

        } catch (\Exception $e) {
            Log::error('Error eliminando producto', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
                'deleted_by' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Error al eliminar el producto',
                'product' => null
            ];
        }
    }

    /**
     * Duplicar producto
     */
    public function duplicateProduct(int $productId, array $overrides = []): array
    {
        try {
            $originalProduct = Product::with(['pricings', 'configurableOptionGroups'])->findOrFail($productId);

            // Preparar datos del producto duplicado
            $duplicateData = [
                'name' => $overrides['name'] ?? $originalProduct->name . ' (Copia)',
                'slug' => $overrides['slug'] ?? null, // Se generará automáticamente
                'description' => $overrides['description'] ?? $originalProduct->description,
                'module_name' => $originalProduct->module_name,
                'product_type_id' => $originalProduct->product_type_id,
                'status' => $overrides['status'] ?? 'draft',
                'is_publicly_available' => $overrides['is_publicly_available'] ?? false,
                'is_resellable_by_default' => $originalProduct->is_resellable_by_default,
                'display_order' => $originalProduct->display_order,
                'owner_id' => $originalProduct->owner_id,
            ];

            // Usar ProductCreator para crear el duplicado
            $productCreator = app(ProductCreator::class);
            $result = $productCreator->create($duplicateData);

            if (!$result['success']) {
                return $result;
            }

            $duplicatedProduct = $result['product'];

            // Duplicar precios
            foreach ($originalProduct->pricings as $pricing) {
                ProductPricing::create([
                    'product_id' => $duplicatedProduct->id,
                    'billing_cycle_id' => $pricing->billing_cycle_id,
                    'price' => $pricing->price,
                    'setup_fee' => $pricing->setup_fee,
                    'currency_code' => $pricing->currency_code,
                    'is_active' => $pricing->is_active,
                ]);
            }

            Log::info('Producto duplicado exitosamente', [
                'original_product_id' => $productId,
                'duplicated_product_id' => $duplicatedProduct->id,
                'duplicated_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => 'Producto duplicado exitosamente',
                'product' => $duplicatedProduct->load(['productType', 'pricings.billingCycle'])
            ];

        } catch (\Exception $e) {
            Log::error('Error duplicando producto', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
                'duplicated_by' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Error al duplicar el producto',
                'product' => null
            ];
        }
    }

    /**
     * Reordenar productos
     */
    public function reorderProducts(array $productOrders): array
    {
        try {
            DB::beginTransaction();

            foreach ($productOrders as $order) {
                Product::where('id', $order['id'])
                    ->update(['display_order' => $order['display_order']]);
            }

            DB::commit();

            Log::info('Productos reordenados', [
                'product_orders' => $productOrders,
                'reordered_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => 'Productos reordenados exitosamente'
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error reordenando productos', [
                'error' => $e->getMessage(),
                'reordered_by' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Error al reordenar productos'
            ];
        }
    }

    /**
     * Validar permisos de actualización
     */
    private function validateUpdatePermissions(Product $product): array
    {
        $currentUser = auth()->user();

        if (!$currentUser) {
            return ['allowed' => false, 'message' => 'Usuario no autenticado'];
        }

        // Admins pueden actualizar cualquier producto
        if ($currentUser->role === 'admin') {
            return ['allowed' => true, 'message' => ''];
        }

        // Resellers pueden actualizar sus propios productos
        if ($currentUser->role === 'reseller' && $product->owner_id === $currentUser->id) {
            return ['allowed' => true, 'message' => ''];
        }

        return ['allowed' => false, 'message' => 'No tienes permisos para actualizar este producto'];
    }

    /**
     * Verificar si se puede cambiar el estado del producto
     */
    private function canChangeProductStatus(Product $product): bool
    {
        $currentUser = auth()->user();

        if (!$currentUser) {
            return false;
        }

        // Admins pueden cambiar cualquier estado
        if ($currentUser->role === 'admin') {
            return true;
        }

        // Resellers pueden cambiar el estado de sus productos
        if ($currentUser->role === 'reseller' && $product->owner_id === $currentUser->id) {
            return true;
        }

        return false;
    }

    /**
     * Verificar si se puede eliminar el producto
     */
    private function canDeleteProduct(Product $product): bool
    {
        $currentUser = auth()->user();

        if (!$currentUser) {
            return false;
        }

        // Solo admins pueden eliminar productos
        if ($currentUser->role === 'admin') {
            return true;
        }

        return false;
    }

    /**
     * Verificar dependencias del producto antes de eliminar
     */
    private function checkProductDependencies(Product $product): array
    {
        // TODO: Verificar servicios activos, órdenes pendientes, etc.
        // Por ahora permitimos la eliminación
        
        return [
            'can_delete' => true,
            'message' => ''
        ];
    }
}

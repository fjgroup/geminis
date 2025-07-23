<?php

namespace App\Domains\Products\Application\Services;

use App\Domains\Products\Infrastructure\Persistence\Models\Product;
use App\Domains\Products\Infrastructure\Persistence\Models\ProductPricing;
use App\Domains\Products\Infrastructure\Persistence\Models\BillingCycle;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Servicio especializado para actualización de productos
 * 
 * Aplica Single Responsibility Principle - solo se encarga de actualizar productos
 * Ubicado en Application layer según arquitectura hexagonal
 */
class ProductUpdater
{
    /**
     * Actualizar producto completo
     */
    public function update(int $productId, array $updateData): array
    {
        try {
            DB::beginTransaction();

            $product = Product::findOrFail($productId);

            // Validar permisos
            if (!$this->canUpdateProduct($product)) {
                return [
                    'success' => false,
                    'message' => 'No tienes permisos para actualizar este producto',
                    'product' => null
                ];
            }

            // Actualizar campos básicos del producto
            $this->updateBasicFields($product, $updateData);

            // Actualizar precios si se proporcionan
            if (isset($updateData['pricings'])) {
                $this->updatePricings($product, $updateData['pricings']);
            }

            DB::commit();

            Log::info('Producto actualizado completamente', [
                'product_id' => $product->id,
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
     * Actualizar solo información básica del producto
     */
    public function updateBasicInfo(int $productId, array $basicData): array
    {
        try {
            $product = Product::findOrFail($productId);

            if (!$this->canUpdateProduct($product)) {
                return [
                    'success' => false,
                    'message' => 'No tienes permisos para actualizar este producto',
                    'product' => null
                ];
            }

            $this->updateBasicFields($product, $basicData);

            Log::info('Información básica de producto actualizada', [
                'product_id' => $product->id,
                'updated_fields' => array_keys($basicData),
                'updated_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => 'Información básica actualizada exitosamente',
                'product' => $product->fresh()
            ];

        } catch (\Exception $e) {
            Log::error('Error actualizando información básica del producto', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
                'updated_by' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Error al actualizar la información básica',
                'product' => null
            ];
        }
    }

    /**
     * Actualizar precios del producto
     */
    public function updatePricings(Product $product, array $pricingsData): array
    {
        try {
            DB::beginTransaction();

            // Eliminar precios existentes si se especifica
            if (isset($pricingsData['replace_all']) && $pricingsData['replace_all']) {
                $product->pricings()->delete();
            }

            // Procesar cada precio
            foreach ($pricingsData['pricings'] ?? $pricingsData as $pricingData) {
                if (isset($pricingData['id'])) {
                    // Actualizar precio existente
                    $this->updateExistingPricing($pricingData['id'], $pricingData);
                } else {
                    // Crear nuevo precio
                    $this->createNewPricing($product, $pricingData);
                }
            }

            DB::commit();

            Log::info('Precios de producto actualizados', [
                'product_id' => $product->id,
                'updated_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => 'Precios actualizados exitosamente',
                'product' => $product->fresh(['pricings.billingCycle'])
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error actualizando precios del producto', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'updated_by' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Error al actualizar los precios',
                'product' => null
            ];
        }
    }

    /**
     * Actualizar disponibilidad pública del producto
     */
    public function updatePublicAvailability(int $productId, bool $isPublic): array
    {
        try {
            $product = Product::findOrFail($productId);

            if (!$this->canUpdateProduct($product)) {
                return [
                    'success' => false,
                    'message' => 'No tienes permisos para actualizar este producto',
                    'product' => null
                ];
            }

            $product->update(['is_publicly_available' => $isPublic]);

            $status = $isPublic ? 'público' : 'privado';

            Log::info('Disponibilidad pública de producto actualizada', [
                'product_id' => $product->id,
                'is_public' => $isPublic,
                'updated_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => "Producto marcado como {$status} exitosamente",
                'product' => $product->fresh()
            ];

        } catch (\Exception $e) {
            Log::error('Error actualizando disponibilidad pública del producto', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
                'updated_by' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Error al actualizar la disponibilidad pública',
                'product' => null
            ];
        }
    }

    /**
     * Actualizar orden de visualización
     */
    public function updateDisplayOrder(int $productId, int $displayOrder): array
    {
        try {
            $product = Product::findOrFail($productId);

            if (!$this->canUpdateProduct($product)) {
                return [
                    'success' => false,
                    'message' => 'No tienes permisos para actualizar este producto',
                    'product' => null
                ];
            }

            $product->update(['display_order' => $displayOrder]);

            Log::info('Orden de visualización de producto actualizado', [
                'product_id' => $product->id,
                'display_order' => $displayOrder,
                'updated_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => 'Orden de visualización actualizado exitosamente',
                'product' => $product->fresh()
            ];

        } catch (\Exception $e) {
            Log::error('Error actualizando orden de visualización del producto', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
                'updated_by' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Error al actualizar el orden de visualización',
                'product' => null
            ];
        }
    }

    /**
     * Actualizar campos básicos del producto
     */
    private function updateBasicFields(Product $product, array $updateData): void
    {
        $allowedFields = [
            'name', 'slug', 'description', 'status', 'is_publicly_available',
            'is_resellable_by_default', 'display_order', 'module_name',
            'product_type_id', 'welcome_email_template_id'
        ];

        $updateFields = array_intersect_key($updateData, array_flip($allowedFields));

        if (!empty($updateFields)) {
            $product->update($updateFields);
        }
    }

    /**
     * Actualizar precio existente
     */
    private function updateExistingPricing(int $pricingId, array $pricingData): void
    {
        $pricing = ProductPricing::findOrFail($pricingId);

        $allowedFields = [
            'billing_cycle_id', 'price', 'setup_fee', 'currency_code', 'is_active'
        ];

        $updateFields = array_intersect_key($pricingData, array_flip($allowedFields));

        if (!empty($updateFields)) {
            $pricing->update($updateFields);
        }
    }

    /**
     * Crear nuevo precio
     */
    private function createNewPricing(Product $product, array $pricingData): ProductPricing
    {
        // Validar billing_cycle_id
        $billingCycle = BillingCycle::find($pricingData['billing_cycle_id']);
        if (!$billingCycle) {
            throw new \Exception('Ciclo de facturación no encontrado');
        }

        return ProductPricing::create([
            'product_id' => $product->id,
            'billing_cycle_id' => $pricingData['billing_cycle_id'],
            'price' => $pricingData['price'],
            'setup_fee' => $pricingData['setup_fee'] ?? 0,
            'currency_code' => $pricingData['currency_code'] ?? 'USD',
            'is_active' => $pricingData['is_active'] ?? true,
        ]);
    }

    /**
     * Verificar si se puede actualizar el producto
     */
    private function canUpdateProduct(Product $product): bool
    {
        $currentUser = auth()->user();

        if (!$currentUser) {
            return false;
        }

        // Admins pueden actualizar cualquier producto
        if ($currentUser->role === 'admin') {
            return true;
        }

        // Resellers pueden actualizar sus propios productos
        if ($currentUser->role === 'reseller' && $product->owner_id === $currentUser->id) {
            return true;
        }

        return false;
    }

    /**
     * Validar datos de actualización
     */
    public function validateUpdateData(array $updateData): array
    {
        $errors = [];

        // Validar estado si se proporciona
        if (isset($updateData['status'])) {
            $validStatuses = ['active', 'inactive', 'draft'];
            if (!in_array($updateData['status'], $validStatuses)) {
                $errors[] = 'Estado no válido';
            }
        }

        // Validar precios si se proporcionan
        if (isset($updateData['pricings'])) {
            foreach ($updateData['pricings'] as $index => $pricingData) {
                if (isset($pricingData['price']) && $pricingData['price'] < 0) {
                    $errors[] = "Precio #{$index}: El precio debe ser mayor o igual a 0";
                }
                if (isset($pricingData['setup_fee']) && $pricingData['setup_fee'] < 0) {
                    $errors[] = "Precio #{$index}: La tarifa de configuración debe ser mayor o igual a 0";
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}

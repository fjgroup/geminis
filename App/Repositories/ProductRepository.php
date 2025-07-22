<?php

namespace App\Repositories;

use App\Contracts\ProductRepositoryInterface;
use App\Domains\Products\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Class ProductRepository
 *
 * Implementación del repositorio de productos
 * Maneja el acceso a datos de productos
 */
class ProductRepository implements ProductRepositoryInterface
{
    /**
     * Obtener todos los productos activos
     */
    public function getActiveProducts(): Collection
    {
        return Product::where('status', 'active')
            ->where('is_publicly_available', true)
            ->with(['pricings.billingCycle', 'productType'])
            ->orderBy('display_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Obtener productos por tipo
     */
    public function getProductsByTypes(array $typeIds): Collection
    {
        return Product::whereIn('product_type_id', $typeIds)
            ->where('status', 'active')
            ->where('is_publicly_available', true)
            ->with(['pricings.billingCycle', 'productType'])
            ->orderBy('display_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Obtener un producto por ID con sus relaciones
     */
    public function findWithRelations(int $productId, array $relations = []): ?Product
    {
        $defaultRelations = ['pricings.billingCycle', 'productType'];
        $relations = array_merge($defaultRelations, $relations);

        return Product::with($relations)->find($productId);
    }

    /**
     * Obtener productos paginados con filtros
     */
    public function getPaginatedProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Product::query()
            ->where('status', 'active')
            ->where('is_publicly_available', true)
            ->with(['pricings.billingCycle', 'productType']);

        // Aplicar filtros
        if (isset($filters['type_id'])) {
            $query->where('product_type_id', $filters['type_id']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (isset($filters['price_min'])) {
            $query->whereHas('pricings', function ($q) use ($filters) {
                $q->where('price', '>=', $filters['price_min']);
            });
        }

        if (isset($filters['price_max'])) {
            $query->whereHas('pricings', function ($q) use ($filters) {
                $q->where('price', '<=', $filters['price_max']);
            });
        }

        return $query->orderBy('display_order', 'asc')
            ->orderBy('name', 'asc')
            ->paginate($perPage);
    }

    /**
     * Verificar disponibilidad de un producto
     */
    public function checkAvailability(Product $product, int $quantity = 1): bool
    {
        // Verificar si el producto está activo
        if ($product->status !== 'active') {
            return false;
        }

        // Verificar stock si se trackea
        if ($product->track_stock) {
            return $product->stock_quantity >= $quantity;
        }

        return true;
    }

    /**
     * Calcular precio de un producto con opciones configurables
     */
    public function calculateProductPrice(Product $product, array $configurableOptions = [], int $billingCycleId = null): float
    {
        try {
            // Precio base del producto
            $basePrice = 0.0;

            if ($billingCycleId) {
                $pricing = $product->pricings()
                    ->where('billing_cycle_id', $billingCycleId)
                    ->first();
                $basePrice = $pricing ? $pricing->price : 0.0;
            } else {
                // Usar el precio más bajo disponible
                $pricing = $product->pricings()
                    ->orderBy('price', 'asc')
                    ->first();
                $basePrice = $pricing ? $pricing->price : 0.0;
            }

            // Agregar precio de opciones configurables
            $optionsPrice = 0.0;
            foreach ($configurableOptions as $optionId => $value) {
                // Aquí iría la lógica para calcular el precio de las opciones configurables
                // Por ahora, simulamos que cada opción agrega $5
                $optionsPrice += 5.0;
            }

            return $basePrice + $optionsPrice;

        } catch (\Exception $e) {
            Log::error('Error calculando precio del producto', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);
            return 0.0;
        }
    }

    /**
     * Buscar productos por término
     */
    public function searchProducts(string $searchTerm, array $filters = []): Collection
    {
        $query = Product::query()
            ->where('status', 'active')
            ->where('is_publicly_available', true)
            ->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('landing_page_description', 'like', '%' . $searchTerm . '%');
            })
            ->with(['pricings.billingCycle', 'productType']);

        // Aplicar filtros adicionales
        if (isset($filters['type_id'])) {
            $query->where('product_type_id', $filters['type_id']);
        }

        return $query->orderBy('display_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Obtener productos relacionados
     */
    public function getRelatedProducts(Product $product, int $limit = 5): Collection
    {
        return Product::where('product_type_id', $product->product_type_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->where('is_publicly_available', true)
            ->with(['pricings.billingCycle', 'productType'])
            ->orderBy('display_order', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtener productos más vendidos
     */
    public function getBestSellingProducts(int $limit = 10): Collection
    {
        // Por ahora, retornamos productos ordenados por display_order
        // En el futuro, esto debería basarse en estadísticas de ventas reales
        return Product::where('status', 'active')
            ->where('is_publicly_available', true)
            ->with(['pricings.billingCycle', 'productType'])
            ->orderBy('display_order', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtener productos por categoría/tipo específico
     */
    public function getProductsByCategory(string $category): Collection
    {
        return Product::whereHas('productType', function ($query) use ($category) {
                $query->where('name', 'like', '%' . $category . '%');
            })
            ->where('status', 'active')
            ->where('is_publicly_available', true)
            ->with(['pricings.billingCycle', 'productType'])
            ->orderBy('display_order', 'asc')
            ->get();
    }

    /**
     * Validar opciones configurables de un producto
     */
    public function validateConfigurableOptions(Product $product, array $options): array
    {
        $errors = [];

        try {
            // Obtener grupos de opciones configurables del producto
            $configurableGroups = $product->configurableOptionGroups ?? collect();

            foreach ($configurableGroups as $group) {
                // Verificar si el grupo es requerido
                if ($group->is_required && !isset($options[$group->id])) {
                    $errors[] = "La opción '{$group->name}' es requerida";
                    continue;
                }

                // Validar la opción seleccionada
                if (isset($options[$group->id])) {
                    $selectedOptionId = $options[$group->id];
                    $validOption = $group->options->where('id', $selectedOptionId)->first();

                    if (!$validOption) {
                        $errors[] = "Opción inválida para '{$group->name}'";
                    }
                }
            }

        } catch (\Exception $e) {
            Log::error('Error validando opciones configurables', [
                'product_id' => $product->id,
                'options' => $options,
                'error' => $e->getMessage()
            ]);
            $errors[] = 'Error al validar opciones configurables';
        }

        return $errors;
    }

    /**
     * Obtener productos con stock bajo
     */
    public function getLowStockProducts(int $threshold = 10): Collection
    {
        return Product::where('track_stock', true)
            ->where('stock_quantity', '<=', $threshold)
            ->where('status', 'active')
            ->with(['productType'])
            ->orderBy('stock_quantity', 'asc')
            ->get();
    }

    /**
     * Actualizar stock de un producto
     */
    public function updateStock(Product $product, int $quantity, string $operation = 'subtract'): bool
    {
        try {
            if (!$product->track_stock) {
                return true; // No se trackea stock, operación exitosa
            }

            $currentStock = $product->stock_quantity;

            if ($operation === 'subtract') {
                $newStock = $currentStock - $quantity;
                if ($newStock < 0) {
                    Log::warning('Intento de reducir stock por debajo de 0', [
                        'product_id' => $product->id,
                        'current_stock' => $currentStock,
                        'quantity_to_subtract' => $quantity
                    ]);
                    return false;
                }
            } elseif ($operation === 'add') {
                $newStock = $currentStock + $quantity;
            } else {
                Log::error('Operación de stock inválida', [
                    'operation' => $operation,
                    'product_id' => $product->id
                ]);
                return false;
            }

            return $product->update(['stock_quantity' => $newStock]);

        } catch (\Exception $e) {
            Log::error('Error actualizando stock del producto', [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'operation' => $operation,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}

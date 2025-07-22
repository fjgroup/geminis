<?php

namespace App\Contracts;

use App\Domains\Products\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface ProductRepositoryInterface
 *
 * Define el contrato para el manejo de productos
 * Abstrae el acceso a datos de productos
 */
interface ProductRepositoryInterface
{
    /**
     * Obtener todos los productos activos
     *
     * @return Collection
     */
    public function getActiveProducts(): Collection;

    /**
     * Obtener productos por tipo
     *
     * @param array $typeIds
     * @return Collection
     */
    public function getProductsByTypes(array $typeIds): Collection;

    /**
     * Obtener un producto por ID con sus relaciones
     *
     * @param int $productId
     * @param array $relations
     * @return Product|null
     */
    public function findWithRelations(int $productId, array $relations = []): ?Product;

    /**
     * Obtener productos paginados con filtros
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Verificar disponibilidad de un producto
     *
     * @param Product $product
     * @param int $quantity
     * @return bool
     */
    public function checkAvailability(Product $product, int $quantity = 1): bool;

    /**
     * Obtener precio de un producto con opciones configurables
     *
     * @param Product $product
     * @param array $configurableOptions
     * @param int $billingCycleId
     * @return float
     */
    public function calculateProductPrice(Product $product, array $configurableOptions = [], int $billingCycleId = null): float;

    /**
     * Buscar productos por término
     *
     * @param string $searchTerm
     * @param array $filters
     * @return Collection
     */
    public function searchProducts(string $searchTerm, array $filters = []): Collection;

    /**
     * Obtener productos relacionados
     *
     * @param Product $product
     * @param int $limit
     * @return Collection
     */
    public function getRelatedProducts(Product $product, int $limit = 5): Collection;

    /**
     * Obtener productos más vendidos
     *
     * @param int $limit
     * @return Collection
     */
    public function getBestSellingProducts(int $limit = 10): Collection;

    /**
     * Obtener productos por categoría/tipo específico
     *
     * @param string $category
     * @return Collection
     */
    public function getProductsByCategory(string $category): Collection;

    /**
     * Validar opciones configurables de un producto
     *
     * @param Product $product
     * @param array $options
     * @return array Array con errores de validación, vacío si todo está bien
     */
    public function validateConfigurableOptions(Product $product, array $options): array;

    /**
     * Obtener productos con stock bajo
     *
     * @param int $threshold
     * @return Collection
     */
    public function getLowStockProducts(int $threshold = 10): Collection;

    /**
     * Actualizar stock de un producto
     *
     * @param Product $product
     * @param int $quantity
     * @param string $operation 'add' o 'subtract'
     * @return bool
     */
    public function updateStock(Product $product, int $quantity, string $operation = 'subtract'): bool;
}

<?php

namespace App\Contracts;

use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Interface CartRepositoryInterface
 * 
 * Define el contrato para el manejo del carrito de compras
 * Permite implementaciones tanto para sesión como base de datos
 */
interface CartRepositoryInterface
{
    /**
     * Agregar un producto al carrito
     *
     * @param Product $product
     * @param int $quantity
     * @param array $options Opciones configurables del producto
     * @return bool
     */
    public function addItem(Product $product, int $quantity = 1, array $options = []): bool;

    /**
     * Remover un producto del carrito
     *
     * @param int $productId
     * @return bool
     */
    public function removeItem(int $productId): bool;

    /**
     * Actualizar la cantidad de un producto en el carrito
     *
     * @param int $productId
     * @param int $quantity
     * @return bool
     */
    public function updateQuantity(int $productId, int $quantity): bool;

    /**
     * Obtener todos los items del carrito
     *
     * @return array
     */
    public function getCart(): array;

    /**
     * Limpiar completamente el carrito
     *
     * @return bool
     */
    public function clearCart(): bool;

    /**
     * Obtener el total del carrito
     *
     * @return float
     */
    public function getCartTotal(): float;

    /**
     * Obtener la cantidad total de items en el carrito
     *
     * @return int
     */
    public function getCartCount(): int;

    /**
     * Verificar si un producto existe en el carrito
     *
     * @param int $productId
     * @return bool
     */
    public function hasItem(int $productId): bool;

    /**
     * Obtener un item específico del carrito
     *
     * @param int $productId
     * @return array|null
     */
    public function getItem(int $productId): ?array;

    /**
     * Migrar carrito de sesión a base de datos (para cuando el usuario se loguea)
     *
     * @param int $userId
     * @return bool
     */
    public function migrateSessionCartToDatabase(int $userId): bool;

    /**
     * Sincronizar carrito de base de datos a sesión (para cuando el usuario se loguea)
     *
     * @param int $userId
     * @return bool
     */
    public function syncDatabaseCartToSession(int $userId): bool;

    /**
     * Validar integridad del carrito (precios, disponibilidad, etc.)
     *
     * @return array Array con errores encontrados, vacío si todo está bien
     */
    public function validateCartIntegrity(): array;

    /**
     * Aplicar descuentos al carrito
     *
     * @param string $discountCode
     * @return bool
     */
    public function applyDiscount(string $discountCode): bool;

    /**
     * Remover descuento del carrito
     *
     * @return bool
     */
    public function removeDiscount(): bool;

    /**
     * Obtener información de descuento aplicado
     *
     * @return array|null
     */
    public function getAppliedDiscount(): ?array;
}

<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Log;

/**
 * Trait HasCart
 *
 * ⚠️ DEPRECATED - MARCADO PARA REFACTORIZACIÓN
 *
 * Este trait contiene lógica de negocio que debe ser movida a CartService.
 * Las funcionalidades de carrito ahora se manejan a través de:
 * - CartService (para lógica de negocio)
 * - CartRepository (para acceso a datos)
 * - CartController (para manejo HTTP)
 *
 * TODO: Migrar uso de este trait a CartService
 * Fecha de refactorización: 2025-01-22
 *
 * @deprecated Use CartService instead
 */
trait HasCart
{
    /**
     * Obtener el servicio de carrito para este usuario
     *
     * @return \App\Services\CartService
     */
    public function getCartService(): \App\Services\CartService
    {
        return app(\App\Services\CartService::class);
    }

    /**
     * Obtener resumen del carrito usando el servicio
     *
     * @return array
     * @deprecated Use CartService::getCartDetails() instead
     */
    public function getCartSummary(): array
    {
        try {
            $cartService = $this->getCartService();
            $cartDetails = $cartService->getCartDetails();

            if ($cartDetails['success']) {
                $cart = $cartDetails['data'];
                return [
                    'items_count' => $cart['items_count'] ?? 0,
                    'total_amount' => $cart['total_amount'] ?? 0.0,
                    'has_items' => ($cart['items_count'] ?? 0) > 0,
                    'last_updated' => $cart['last_updated'] ?? null
                ];
            }

            return [
                'items_count' => 0,
                'total_amount' => 0.0,
                'has_items' => false,
                'last_updated' => null
            ];

        } catch (\Exception $e) {
            Log::error('Error obteniendo resumen del carrito', [
                'user_id' => $this->id,
                'error' => $e->getMessage()
            ]);

            return [
                'items_count' => 0,
                'total_amount' => 0.0,
                'has_items' => false,
                'last_updated' => null
            ];
        }
    }

    /**
     * Verificar si el usuario tiene items en el carrito
     *
     * @return bool
     * @deprecated Use CartService::hasItems() instead
     */
    public function hasCartItems(): bool
    {
        try {
            $cartService = $this->getCartService();
            return $cartService->hasItems();
        } catch (\Exception $e) {
            Log::error('Error verificando items del carrito', [
                'user_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Obtener la cantidad total de items en el carrito
     *
     * @return int
     * @deprecated Use CartService::getItemsCount() instead
     */
    public function getCartItemsCount(): int
    {
        try {
            $cartService = $this->getCartService();
            $cartDetails = $cartService->getCartDetails();

            return $cartDetails['success'] ? ($cartDetails['data']['items_count'] ?? 0) : 0;
        } catch (\Exception $e) {
            Log::error('Error obteniendo cantidad de items del carrito', [
                'user_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Obtener el total del carrito
     *
     * @return float
     * @deprecated Use CartService::getCartTotal() instead
     */
    public function getCartTotal(): float
    {
        try {
            $cartService = $this->getCartService();
            $cartDetails = $cartService->getCartDetails();

            return $cartDetails['success'] ? ($cartDetails['data']['total_amount'] ?? 0.0) : 0.0;
        } catch (\Exception $e) {
            Log::error('Error obteniendo total del carrito', [
                'user_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return 0.0;
        }
    }

    /**
     * Limpiar el carrito del usuario
     *
     * @return bool
     * @deprecated Use CartService::clearCart() instead
     */
    public function clearCart(): bool
    {
        try {
            $cartService = $this->getCartService();
            $result = $cartService->clearCart();

            return $result['success'] ?? false;
        } catch (\Exception $e) {
            Log::error('Error limpiando carrito del usuario', [
                'user_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Verificar si un producto específico está en el carrito
     *
     * @param int $productId
     * @return bool
     * @deprecated Use CartService::hasProduct() instead
     */
    public function hasProductInCart(int $productId): bool
    {
        try {
            $cartService = $this->getCartService();
            return $cartService->hasProduct($productId);
        } catch (\Exception $e) {
            Log::error('Error verificando producto en carrito', [
                'user_id' => $this->id,
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}

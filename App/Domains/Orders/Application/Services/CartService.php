<?php

namespace App\Domains\Orders\Application\Services;

use App\Domains\Products\Infrastructure\Persistence\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Servicio de aplicación para manejo del carrito
 * 
 * Aplica Single Responsibility Principle - solo maneja operaciones del carrito
 * Ubicado en Application layer según arquitectura hexagonal
 */
class CartService
{
    /**
     * Inicializar carrito vacío
     */
    public function initializeCart(): array
    {
        return [
            'accounts' => [],
            'active_account_id' => null,
        ];
    }

    /**
     * Obtener detalles del carrito
     */
    public function getCartDetails(): array
    {
        try {
            $cart = session('cart', $this->initializeCart());

            if (!isset($cart['accounts']) || !isset($cart['active_account_id'])) {
                $cart = $this->initializeCart();
                session(['cart' => $cart]);
            }

            return [
                'success' => true,
                'data' => $cart,
                'errors' => []
            ];
        } catch (\Exception $e) {
            Log::error('Error obteniendo detalles del carrito', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return [
                'success' => false,
                'data' => $this->initializeCart(),
                'errors' => ['Error al obtener el carrito']
            ];
        }
    }

    /**
     * Agregar item al carrito
     */
    public function addItem(Product $product, int $quantity = 1, array $configurableOptions = []): array
    {
        try {
            $cart = session('cart', $this->initializeCart());

            // Generar UUID para la cuenta si no existe
            if (empty($cart['accounts'])) {
                $accountId = (string) Str::uuid();
                $cart['active_account_id'] = $accountId;
            } else {
                $accountId = $cart['active_account_id'] ?? (string) Str::uuid();
            }

            // Crear estructura del item
            $cartItemId = (string) Str::uuid();
            $newItem = [
                'cart_item_id' => $cartItemId,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'configurable_options' => $configurableOptions,
                'added_at' => now()->toISOString()
            ];

            // Buscar cuenta activa o crear nueva
            $accountIndex = $this->findAccountIndex($cart, $accountId);
            
            if ($accountIndex === null) {
                // Crear nueva cuenta
                $cart['accounts'][] = [
                    'account_id' => $accountId,
                    'domain_info' => null,
                    'primary_service' => $newItem,
                    'additional_services' => []
                ];
            } else {
                // Agregar a cuenta existente
                if (empty($cart['accounts'][$accountIndex]['primary_service'])) {
                    $cart['accounts'][$accountIndex]['primary_service'] = $newItem;
                } else {
                    $cart['accounts'][$accountIndex]['additional_services'][] = $newItem;
                }
            }

            session(['cart' => $cart]);

            Log::info('Item agregado al carrito', [
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'quantity' => $quantity,
                'account_id' => $accountId
            ]);

            return [
                'success' => true,
                'message' => 'Producto agregado al carrito exitosamente',
                'cart' => $cart
            ];

        } catch (\Exception $e) {
            Log::error('Error agregando item al carrito', [
                'error' => $e->getMessage(),
                'product_id' => $product->id,
                'user_id' => Auth::id()
            ]);

            return [
                'success' => false,
                'message' => 'Error al agregar producto al carrito',
                'cart' => session('cart', $this->initializeCart())
            ];
        }
    }

    /**
     * Actualizar cantidad de un item
     */
    public function updateQuantity(int $productId, int $quantity): array
    {
        try {
            $cart = session('cart', $this->initializeCart());

            if ($quantity <= 0) {
                return $this->removeItem($productId);
            }

            $updated = false;
            foreach ($cart['accounts'] as &$account) {
                // Verificar primary_service
                if (isset($account['primary_service']['product_id']) && 
                    $account['primary_service']['product_id'] == $productId) {
                    $account['primary_service']['quantity'] = $quantity;
                    $updated = true;
                    break;
                }

                // Verificar additional_services
                foreach ($account['additional_services'] as &$service) {
                    if (isset($service['product_id']) && $service['product_id'] == $productId) {
                        $service['quantity'] = $quantity;
                        $updated = true;
                        break 2;
                    }
                }
            }

            if ($updated) {
                session(['cart' => $cart]);
                
                return [
                    'success' => true,
                    'message' => 'Cantidad actualizada exitosamente',
                    'cart' => $cart
                ];
            }

            return [
                'success' => false,
                'message' => 'Producto no encontrado en el carrito',
                'cart' => $cart
            ];

        } catch (\Exception $e) {
            Log::error('Error actualizando cantidad en carrito', [
                'error' => $e->getMessage(),
                'product_id' => $productId,
                'user_id' => Auth::id()
            ]);

            return [
                'success' => false,
                'message' => 'Error al actualizar cantidad',
                'cart' => session('cart', $this->initializeCart())
            ];
        }
    }

    /**
     * Remover item del carrito
     */
    public function removeItem(int $productId): array
    {
        try {
            $cart = session('cart', $this->initializeCart());

            foreach ($cart['accounts'] as $accountIndex => &$account) {
                // Verificar primary_service
                if (isset($account['primary_service']['product_id']) && 
                    $account['primary_service']['product_id'] == $productId) {
                    $account['primary_service'] = null;
                    break;
                }

                // Verificar additional_services
                foreach ($account['additional_services'] as $serviceIndex => $service) {
                    if (isset($service['product_id']) && $service['product_id'] == $productId) {
                        unset($account['additional_services'][$serviceIndex]);
                        $account['additional_services'] = array_values($account['additional_services']);
                        break 2;
                    }
                }
            }

            session(['cart' => $cart]);

            return [
                'success' => true,
                'message' => 'Producto removido del carrito',
                'cart' => $cart
            ];

        } catch (\Exception $e) {
            Log::error('Error removiendo item del carrito', [
                'error' => $e->getMessage(),
                'product_id' => $productId,
                'user_id' => Auth::id()
            ]);

            return [
                'success' => false,
                'message' => 'Error al remover producto',
                'cart' => session('cart', $this->initializeCart())
            ];
        }
    }

    /**
     * Limpiar carrito
     */
    public function clearCart(): array
    {
        try {
            $cart = $this->initializeCart();
            session(['cart' => $cart]);

            Log::info('Carrito limpiado', [
                'user_id' => Auth::id()
            ]);

            return [
                'success' => true,
                'message' => 'Carrito limpiado exitosamente',
                'cart' => $cart
            ];

        } catch (\Exception $e) {
            Log::error('Error limpiando carrito', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return [
                'success' => false,
                'message' => 'Error al limpiar carrito',
                'cart' => session('cart', $this->initializeCart())
            ];
        }
    }

    /**
     * Buscar índice de cuenta por ID
     */
    private function findAccountIndex(array $cart, string $accountId): ?int
    {
        foreach ($cart['accounts'] as $index => $account) {
            if ($account['account_id'] === $accountId) {
                return $index;
            }
        }
        return null;
    }
}

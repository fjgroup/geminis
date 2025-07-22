<?php

namespace App\Services;

use App\Contracts\CartRepositoryInterface;
use App\Contracts\ProductRepositoryInterface;
use App\Domains\Products\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

/**
 * Class CartService
 *
 * Servicio principal para el manejo del carrito de compras
 * Centraliza toda la lógica de negocio relacionada con el carrito
 */
class CartService
{
    public function __construct(
        private CartRepositoryInterface $cartRepository,
        private ProductRepositoryInterface $productRepository
    ) {}

    /**
     * Agregar un producto al carrito con validaciones
     *
     * @param Product $product
     * @param int $quantity
     * @param array $configurableOptions
     * @return array
     */
    public function addItem(Product $product, int $quantity = 1, array $configurableOptions = []): array
    {
        try {
            // Validar entrada
            $this->validateAddItemInput($product, $quantity, $configurableOptions);

            // Verificar disponibilidad del producto
            if (!$this->productRepository->checkAvailability($product, $quantity)) {
                return $this->errorResponse('Producto no disponible en la cantidad solicitada');
            }

            // Validar opciones configurables
            $validationErrors = $this->productRepository->validateConfigurableOptions($product, $configurableOptions);
            if (!empty($validationErrors)) {
                return $this->errorResponse('Opciones configurables inválidas', $validationErrors);
            }

            // Calcular precio total del item
            $itemPrice = $this->productRepository->calculateProductPrice($product, $configurableOptions);

            // Preparar datos del item
            $itemData = [
                'product' => $product,
                'quantity' => $quantity,
                'configurable_options' => $configurableOptions,
                'unit_price' => $itemPrice,
                'total_price' => $itemPrice * $quantity,
                'added_at' => now()
            ];

            // Agregar al carrito
            $success = $this->cartRepository->addItem($product, $quantity, $itemData);

            if ($success) {
                Log::info('Producto agregado al carrito', [
                    'user_id' => Auth::id(),
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'total_price' => $itemData['total_price']
                ]);

                return $this->successResponse('Producto agregado al carrito exitosamente', [
                    'cart_count' => $this->cartRepository->getCartCount(),
                    'cart_total' => $this->cartRepository->getCartTotal()
                ]);
            }

            return $this->errorResponse('Error al agregar producto al carrito');

        } catch (\Exception $e) {
            Log::error('Error en CartService::addItem', [
                'error' => $e->getMessage(),
                'product_id' => $product->id,
                'user_id' => Auth::id()
            ]);

            return $this->errorResponse('Error interno del servidor');
        }
    }

    /**
     * Actualizar cantidad de un producto en el carrito
     *
     * @param int $productId
     * @param int $quantity
     * @return array
     */
    public function updateQuantity(int $productId, int $quantity): array
    {
        try {
            if ($quantity <= 0) {
                return $this->removeItem($productId);
            }

            $product = $this->productRepository->findWithRelations($productId);
            if (!$product) {
                return $this->errorResponse('Producto no encontrado');
            }

            if (!$this->productRepository->checkAvailability($product, $quantity)) {
                return $this->errorResponse('Cantidad no disponible');
            }

            $success = $this->cartRepository->updateQuantity($productId, $quantity);

            if ($success) {
                return $this->successResponse('Cantidad actualizada exitosamente', [
                    'cart_count' => $this->cartRepository->getCartCount(),
                    'cart_total' => $this->cartRepository->getCartTotal()
                ]);
            }

            return $this->errorResponse('Error al actualizar cantidad');

        } catch (\Exception $e) {
            Log::error('Error en CartService::updateQuantity', [
                'error' => $e->getMessage(),
                'product_id' => $productId,
                'quantity' => $quantity
            ]);

            return $this->errorResponse('Error interno del servidor');
        }
    }

    /**
     * Remover un producto del carrito
     *
     * @param int $productId
     * @return array
     */
    public function removeItem(int $productId): array
    {
        try {
            $success = $this->cartRepository->removeItem($productId);

            if ($success) {
                return $this->successResponse('Producto removido del carrito', [
                    'cart_count' => $this->cartRepository->getCartCount(),
                    'cart_total' => $this->cartRepository->getCartTotal()
                ]);
            }

            return $this->errorResponse('Error al remover producto del carrito');

        } catch (\Exception $e) {
            Log::error('Error en CartService::removeItem', [
                'error' => $e->getMessage(),
                'product_id' => $productId
            ]);

            return $this->errorResponse('Error interno del servidor');
        }
    }

    /**
     * Obtener el carrito completo con información detallada
     *
     * @return array
     */
    public function getCartDetails(): array
    {
        try {
            $cart = $this->cartRepository->getCart();
            $cartTotal = $this->cartRepository->getCartTotal();
            $cartCount = $this->cartRepository->getCartCount();
            $appliedDiscount = $this->cartRepository->getAppliedDiscount();

            // Validar integridad del carrito
            $integrityErrors = $this->cartRepository->validateCartIntegrity();

            return [
                'success' => true,
                'data' => [
                    'items' => $cart,
                    'total' => $cartTotal,
                    'count' => $cartCount,
                    'applied_discount' => $appliedDiscount,
                    'integrity_errors' => $integrityErrors,
                    'is_valid' => empty($integrityErrors)
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Error en CartService::getCartDetails', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return $this->errorResponse('Error al obtener detalles del carrito');
        }
    }

    /**
     * Limpiar el carrito completamente
     *
     * @return array
     */
    public function clearCart(): array
    {
        try {
            $success = $this->cartRepository->clearCart();

            if ($success) {
                return $this->successResponse('Carrito limpiado exitosamente');
            }

            return $this->errorResponse('Error al limpiar el carrito');

        } catch (\Exception $e) {
            Log::error('Error en CartService::clearCart', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return $this->errorResponse('Error interno del servidor');
        }
    }

    /**
     * Migrar carrito cuando el usuario se loguea
     *
     * @param int $userId
     * @return array
     */
    public function migrateCartOnLogin(int $userId): array
    {
        try {
            // Primero sincronizar carrito de BD a sesión
            $this->cartRepository->syncDatabaseCartToSession($userId);

            // Luego migrar todo a BD
            $success = $this->cartRepository->migrateSessionCartToDatabase($userId);

            if ($success) {
                Log::info('Carrito migrado exitosamente al login', ['user_id' => $userId]);
                return $this->successResponse('Carrito sincronizado exitosamente');
            }

            return $this->errorResponse('Error al sincronizar carrito');

        } catch (\Exception $e) {
            Log::error('Error en CartService::migrateCartOnLogin', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);

            return $this->errorResponse('Error al sincronizar carrito');
        }
    }

    /**
     * Verificar si el carrito tiene items
     *
     * @return bool
     */
    public function hasItems(): bool
    {
        try {
            $cartCount = $this->cartRepository->getCartCount();
            return $cartCount > 0;
        } catch (\Exception $e) {
            Log::error('Error verificando items del carrito', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Verificar si un producto específico está en el carrito
     *
     * @param int $productId
     * @return bool
     */
    public function hasProduct(int $productId): bool
    {
        try {
            $cart = $this->cartRepository->getCart();

            foreach ($cart as $item) {
                if (isset($item['product_id']) && $item['product_id'] == $productId) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Error verificando producto en carrito', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Obtener la cantidad de items en el carrito
     *
     * @return int
     */
    public function getItemsCount(): int
    {
        try {
            return $this->cartRepository->getCartCount();
        } catch (\Exception $e) {
            Log::error('Error obteniendo cantidad de items', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Obtener el total del carrito
     *
     * @return float
     */
    public function getCartTotal(): float
    {
        try {
            return $this->cartRepository->getCartTotal();
        } catch (\Exception $e) {
            Log::error('Error obteniendo total del carrito', ['error' => $e->getMessage()]);
            return 0.0;
        }
    }

    /**
     * Validar entrada para agregar item
     */
    private function validateAddItemInput(Product $product, int $quantity, array $configurableOptions): void
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('La cantidad debe ser mayor a 0');
        }

        if ($quantity > 100) { // Límite razonable
            throw new InvalidArgumentException('Cantidad máxima excedida');
        }

        if ($product->status !== 'active') {
            throw new InvalidArgumentException('El producto no está activo');
        }
    }

    /**
     * Respuesta de éxito estandarizada
     */
    private function successResponse(string $message, array $data = []): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];
    }

    /**
     * Respuesta de error estandarizada
     */
    private function errorResponse(string $message, array $errors = []): array
    {
        return [
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ];
    }
}

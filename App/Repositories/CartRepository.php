<?php
namespace App\Repositories;

use App\Contracts\CartRepositoryInterface;
use App\Models\Product;
use App\Models\ShoppingCart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * Class CartRepository
 *
 * Implementación unificada del repositorio de carrito
 * Maneja tanto carritos de sesión (usuarios anónimos) como de base de datos (usuarios logueados)
 */
class CartRepository implements CartRepositoryInterface
{
    private const SESSION_CART_KEY     = 'cart';
    private const SESSION_DISCOUNT_KEY = 'cart_discount';

    /**
     * Agregar un producto al carrito
     */
    public function addItem(Product $product, int $quantity = 1, array $options = []): bool
    {
        try {
            if (Auth::check()) {
                return $this->addItemToDatabase($product, $quantity, $options);
            }

            return $this->addItemToSession($product, $quantity, $options);

        } catch (\Exception $e) {
            Log::error('Error en CartRepository::addItem', [
                'error'      => $e->getMessage(),
                'product_id' => $product->id,
                'quantity'   => $quantity,
            ]);
            return false;
        }
    }

    /**
     * Remover un producto del carrito
     */
    public function removeItem(int $productId): bool
    {
        try {
            if (Auth::check()) {
                return $this->removeItemFromDatabase($productId);
            }

            return $this->removeItemFromSession($productId);

        } catch (\Exception $e) {
            Log::error('Error en CartRepository::removeItem', [
                'error'      => $e->getMessage(),
                'product_id' => $productId,
            ]);
            return false;
        }
    }

    /**
     * Actualizar cantidad de un producto
     */
    public function updateQuantity(int $productId, int $quantity): bool
    {
        try {
            if ($quantity <= 0) {
                return $this->removeItem($productId);
            }

            if (Auth::check()) {
                return $this->updateQuantityInDatabase($productId, $quantity);
            }

            return $this->updateQuantityInSession($productId, $quantity);

        } catch (\Exception $e) {
            Log::error('Error en CartRepository::updateQuantity', [
                'error'      => $e->getMessage(),
                'product_id' => $productId,
                'quantity'   => $quantity,
            ]);
            return false;
        }
    }

    /**
     * Obtener todos los items del carrito
     */
    public function getCart(): array
    {
        try {
            if (Auth::check()) {
                return $this->getCartFromDatabase();
            }

            return $this->getCartFromSession();

        } catch (\Exception $e) {
            Log::error('Error en CartRepository::getCart', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            return [];
        }
    }

    /**
     * Limpiar el carrito completamente
     */
    public function clearCart(): bool
    {
        try {
            if (Auth::check()) {
                $success = $this->clearCartFromDatabase();
            } else {
                $success = $this->clearCartFromSession();
            }

            // También limpiar descuentos
            $this->removeDiscount();

            return $success;

        } catch (\Exception $e) {
            Log::error('Error en CartRepository::clearCart', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            return false;
        }
    }

    /**
     * Obtener el total del carrito
     */
    public function getCartTotal(): float
    {
        try {
            $cart  = $this->getCart();
            $total = 0.0;

            foreach ($cart as $item) {
                $total += $item['total_price'] ?? 0;
            }

            // Aplicar descuento si existe
            $discount = $this->getAppliedDiscount();
            if ($discount) {
                $discountAmount = $this->calculateDiscountAmount($total, $discount);
                $total -= $discountAmount;
            }

            return max(0, $total); // Nunca negativo

        } catch (\Exception $e) {
            Log::error('Error en CartRepository::getCartTotal', [
                'error' => $e->getMessage(),
            ]);
            return 0.0;
        }
    }

    /**
     * Obtener la cantidad total de items
     */
    public function getCartCount(): int
    {
        try {
            $cart  = $this->getCart();
            $count = 0;

            foreach ($cart as $item) {
                $count += $item['quantity'] ?? 0;
            }

            return $count;

        } catch (\Exception $e) {
            Log::error('Error en CartRepository::getCartCount', [
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    /**
     * Verificar si un producto existe en el carrito
     */
    public function hasItem(int $productId): bool
    {
        $cart = $this->getCart();
        return isset($cart[$productId]);
    }

    /**
     * Obtener un item específico del carrito
     */
    public function getItem(int $productId): ?array
    {
        $cart = $this->getCart();
        return $cart[$productId] ?? null;
    }

    /**
     * Migrar carrito de sesión a base de datos
     */
    public function migrateSessionCartToDatabase(int $userId): bool
    {
        try {
            $sessionCart = $this->getCartFromSession();

            if (empty($sessionCart)) {
                return true; // No hay nada que migrar
            }

            $control = time();

            foreach ($sessionCart as $productId => $item) {
                ShoppingCart::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'post_id' => $productId,
                    ],
                    [
                        'count'                => $item['quantity'],
                        'control'              => $control,
                        'configurable_options' => json_encode($item['configurable_options'] ?? []),
                        'unit_price'           => $item['unit_price'] ?? 0,
                        'total_price'          => $item['total_price'] ?? 0,
                    ]
                );
            }

            // Limpiar items antiguos
            ShoppingCart::where('user_id', $userId)
                ->where('control', '!=', $control)
                ->delete();

            // Limpiar carrito de sesión
            $this->clearCartFromSession();

            Log::info('Carrito migrado de sesión a BD', [
                'user_id'        => $userId,
                'items_migrated' => count($sessionCart),
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Error en CartRepository::migrateSessionCartToDatabase', [
                'error'   => $e->getMessage(),
                'user_id' => $userId,
            ]);
            return false;
        }
    }

    /**
     * Sincronizar carrito de base de datos a sesión
     */
    public function syncDatabaseCartToSession(int $userId): bool
    {
        try {
            $dbCart = ShoppingCart::where('user_id', $userId)
                ->with('post')
                ->get();

            if ($dbCart->isEmpty()) {
                return true;
            }

            $sessionCart = [];
            foreach ($dbCart as $item) {
                $sessionCart[$item->post_id] = [
                    'product'              => $item->post,
                    'quantity'             => $item->count,
                    'configurable_options' => json_decode($item->configurable_options ?? '[]', true),
                    'unit_price'           => $item->unit_price ?? $item->post->price ?? 0,
                    'total_price'          => $item->total_price ?? ($item->count * ($item->post->price ?? 0)),
                    'added_at'             => $item->created_at,
                ];
            }

            Session::put(self::SESSION_CART_KEY, $sessionCart);

            Log::info('Carrito sincronizado de BD a sesión', [
                'user_id'      => $userId,
                'items_synced' => count($sessionCart),
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Error en CartRepository::syncDatabaseCartToSession', [
                'error'   => $e->getMessage(),
                'user_id' => $userId,
            ]);
            return false;
        }
    }

    /**
     * Validar integridad del carrito
     */
    public function validateCartIntegrity(): array
    {
        $errors = [];
        $cart   = $this->getCart();

        foreach ($cart as $productId => $item) {
            $product = Product::find($productId);

            if (! $product) {
                $errors[] = "Producto con ID {$productId} no encontrado";
                continue;
            }

            if ($product->status !== 'active') {
                $errors[] = "Producto '{$product->name}' no está disponible";
            }

            if ($product->track_stock && $product->stock_quantity < $item['quantity']) {
                $errors[] = "Stock insuficiente para '{$product->name}'";
            }

            // Validar precio actual vs precio en carrito
            $currentPrice = $product->price ?? 0;
            $cartPrice    = $item['unit_price'] ?? 0;

            if (abs($currentPrice - $cartPrice) > 0.01) { // Tolerancia de 1 centavo
                $errors[] = "El precio de '{$product->name}' ha cambiado";
            }
        }

        return $errors;
    }

    /**
     * Aplicar descuento al carrito
     */
    public function applyDiscount(string $discountCode): bool
    {
        try {
            // Aquí iría la lógica para validar el código de descuento
            // Por ahora, simulamos que es válido
            $discountData = [
                'code'       => $discountCode,
                'type'       => 'percentage', // o 'fixed'
                'value'      => 10,           // 10% o $10
                'applied_at' => now(),
            ];

            Session::put(self::SESSION_DISCOUNT_KEY, $discountData);
            return true;

        } catch (\Exception $e) {
            Log::error('Error en CartRepository::applyDiscount', [
                'error'         => $e->getMessage(),
                'discount_code' => $discountCode,
            ]);
            return false;
        }
    }

    /**
     * Remover descuento del carrito
     */
    public function removeDiscount(): bool
    {
        Session::forget(self::SESSION_DISCOUNT_KEY);
        return true;
    }

    /**
     * Obtener información de descuento aplicado
     */
    public function getAppliedDiscount(): ?array
    {
        return Session::get(self::SESSION_DISCOUNT_KEY);
    }

    /**
     * Agregar item a la base de datos
     */
    private function addItemToDatabase(Product $product, int $quantity, array $options): bool
    {
        $control = time();

        ShoppingCart::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'post_id' => $product->id,
            ],
            [
                'count'                => $quantity,
                'control'              => $control,
                'configurable_options' => json_encode($options['configurable_options'] ?? []),
                'unit_price'           => $options['unit_price'] ?? $product->price ?? 0,
                'total_price'          => $options['total_price'] ?? ($quantity * ($product->price ?? 0)),
            ]
        );

        return true;
    }

    /**
     * Agregar item a la sesión
     */
    private function addItemToSession(Product $product, int $quantity, array $options): bool
    {
        $cart = Session::get(self::SESSION_CART_KEY, []);

        $cart[$product->id] = [
            'product'              => $product,
            'quantity'             => $quantity,
            'configurable_options' => $options['configurable_options'] ?? [],
            'unit_price'           => $options['unit_price'] ?? $product->price ?? 0,
            'total_price'          => $options['total_price'] ?? ($quantity * ($product->price ?? 0)),
            'added_at'             => now(),
        ];

        Session::put(self::SESSION_CART_KEY, $cart);
        return true;
    }

    /**
     * Remover item de la base de datos
     */
    private function removeItemFromDatabase(int $productId): bool
    {
        return ShoppingCart::where('user_id', Auth::id())
            ->where('post_id', $productId)
            ->delete() > 0;
    }

    /**
     * Remover item de la sesión
     */
    private function removeItemFromSession(int $productId): bool
    {
        $cart = Session::get(self::SESSION_CART_KEY, []);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            Session::put(self::SESSION_CART_KEY, $cart);
            return true;
        }

        return false;
    }

    /**
     * Actualizar cantidad en base de datos
     */
    private function updateQuantityInDatabase(int $productId, int $quantity): bool
    {
        return ShoppingCart::where('user_id', Auth::id())
            ->where('post_id', $productId)
            ->update(['count' => $quantity]) > 0;
    }

    /**
     * Actualizar cantidad en sesión
     */
    private function updateQuantityInSession(int $productId, int $quantity): bool
    {
        $cart = Session::get(self::SESSION_CART_KEY, []);

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity']    = $quantity;
            $cart[$productId]['total_price'] = $quantity * $cart[$productId]['unit_price'];
            Session::put(self::SESSION_CART_KEY, $cart);
            return true;
        }

        return false;
    }

    /**
     * Obtener carrito de la base de datos
     */
    private function getCartFromDatabase(): array
    {
        $items = ShoppingCart::where('user_id', Auth::id())
            ->with('post')
            ->get();

        $cart = [];
        foreach ($items as $item) {
            $cart[$item->post_id] = [
                'product'              => $item->post,
                'quantity'             => $item->count,
                'configurable_options' => json_decode($item->configurable_options ?? '[]', true),
                'unit_price'           => $item->unit_price ?? $item->post->price ?? 0,
                'total_price'          => $item->total_price ?? ($item->count * ($item->post->price ?? 0)),
                'added_at'             => $item->created_at,
            ];
        }

        return $cart;
    }

    /**
     * Obtener carrito de la sesión
     */
    private function getCartFromSession(): array
    {
        return Session::get(self::SESSION_CART_KEY, []);
    }

    /**
     * Limpiar carrito de la base de datos
     */
    private function clearCartFromDatabase(): bool
    {
        return ShoppingCart::where('user_id', Auth::id())->delete() >= 0;
    }

    /**
     * Limpiar carrito de la sesión
     */
    private function clearCartFromSession(): bool
    {
        Session::forget(self::SESSION_CART_KEY);
        return true;
    }

    /**
     * Calcular monto de descuento
     */
    private function calculateDiscountAmount(float $total, array $discount): float
    {
        if ($discount['type'] === 'percentage') {
            return $total * ($discount['value'] / 100);
        }

        if ($discount['type'] === 'fixed') {
            return min($discount['value'], $total); // No puede ser mayor al total
        }

        return 0.0;
    }
}

<?php
namespace App\Domains\Orders\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartRequest;
use App\Domains\Products\Infrastructure\Persistence\Models\Product;
use App\Domains\Orders\Application\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Class CartController
 *
 * Controlador unificado para el manejo del carrito de compras
 * Reemplaza a Shop/CartController y Client/ClientCartController
 * Maneja tanto usuarios anónimos como logueados
 */
class CartController extends Controller
{
    public function __construct(
        private CartService $cartService
    ) {
        // Aplicar middleware de rate limiting para operaciones de carrito
        $this->middleware('cart.ratelimit:30,1')->except(['index', 'show']);
    }

    /**
     * Mostrar la página del carrito
     */
    public function index(): InertiaResponse
    {
        $cartDetails = $this->cartService->getCartDetails();

        return Inertia::render('Cart/Index', [
            'cart'   => $cartDetails['data'] ?? [],
            'errors' => $cartDetails['errors'] ?? [],
        ]);
    }

    /**
     * Obtener el carrito en formato JSON (para AJAX)
     */
    public function show(): JsonResponse
    {
        $cartDetails = $this->cartService->getCartDetails();

        return response()->json($cartDetails);
    }

    /**
     * Agregar un producto al carrito
     */
    public function store(AddToCartRequest $request): JsonResponse
    {
        try {
            $product             = Product::findOrFail($request->validated('product_id'));
            $quantity            = $request->validated('quantity', 1);
            $configurableOptions = $request->validated('configurable_options', []);

            $result = $this->cartService->addItem($product, $quantity, $configurableOptions);

            if ($result['success']) {
                Log::info('Producto agregado al carrito via API', [
                    'user_id'    => Auth::id(),
                    'product_id' => $product->id,
                    'quantity'   => $quantity,
                    'ip'         => $request->ip(),
                ]);

                return response()->json($result, 200);
            }

            return response()->json($result, 400);

        } catch (\Exception $e) {
            Log::error('Error en CartController::store', [
                'error'        => $e->getMessage(),
                'request_data' => $request->validated(),
                'user_id'      => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }

    /**
     * Agregar producto al carrito (método legacy para compatibilidad)
     */
    public function add(Product $product, int $count = 1): RedirectResponse
    {
        try {
            $result = $this->cartService->addItem($product, $count);

            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            }

            return redirect()->back()->withErrors(['error' => $result['message']]);

        } catch (\Exception $e) {
            Log::error('Error en CartController::add', [
                'error'      => $e->getMessage(),
                'product_id' => $product->id,
                'count'      => $count,
            ]);

            return redirect()->back()->withErrors(['error' => 'Error al agregar producto al carrito']);
        }
    }

    /**
     * Actualizar cantidad de un producto en el carrito
     */
    public function update(UpdateCartRequest $request): JsonResponse
    {
        try {
            $productId = $request->validated('product_id');
            $quantity  = $request->validated('quantity');

            $result = $this->cartService->updateQuantity($productId, $quantity);

            if ($result['success']) {
                Log::info('Cantidad actualizada en carrito', [
                    'user_id'      => Auth::id(),
                    'product_id'   => $productId,
                    'new_quantity' => $quantity,
                ]);

                return response()->json($result, 200);
            }

            return response()->json($result, 400);

        } catch (\Exception $e) {
            Log::error('Error en CartController::update', [
                'error'        => $e->getMessage(),
                'request_data' => $request->validated(),
                'user_id'      => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }

    /**
     * Remover un producto del carrito
     */
    public function destroy(Request $request): JsonResponse
    {
        try {
            $productId = $request->input('product_id');

            if (! $productId) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de producto requerido',
                ], 400);
            }

            $result = $this->cartService->removeItem($productId);

            if ($result['success']) {
                Log::info('Producto removido del carrito', [
                    'user_id'    => Auth::id(),
                    'product_id' => $productId,
                ]);

                return response()->json($result, 200);
            }

            return response()->json($result, 400);

        } catch (\Exception $e) {
            Log::error('Error en CartController::destroy', [
                'error'      => $e->getMessage(),
                'product_id' => $request->input('product_id'),
                'user_id'    => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }

    /**
     * Limpiar completamente el carrito
     */
    public function clear(): JsonResponse
    {
        try {
            $result = $this->cartService->clearCart();

            if ($result['success']) {
                Log::info('Carrito limpiado completamente', [
                    'user_id' => Auth::id(),
                ]);

                return response()->json($result, 200);
            }

            return response()->json($result, 400);

        } catch (\Exception $e) {
            Log::error('Error en CartController::clear', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }

    /**
     * Obtener resumen del carrito (para mostrar en header/navbar)
     */
    public function summary(): JsonResponse
    {
        try {
            $cartDetails = $this->cartService->getCartDetails();

            if ($cartDetails['success']) {
                return response()->json([
                    'success' => true,
                    'data'    => [
                        'count'     => $cartDetails['data']['count'],
                        'total'     => $cartDetails['data']['total'],
                        'has_items' => $cartDetails['data']['count'] > 0,
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'data'    => ['count' => 0, 'total' => 0, 'has_items' => false],
            ]);

        } catch (\Exception $e) {
            Log::error('Error en CartController::summary', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'data'    => ['count' => 0, 'total' => 0, 'has_items' => false],
            ]);
        }
    }

    /**
     * Validar integridad del carrito
     */
    public function validateCart(): JsonResponse
    {
        try {
            $cartDetails = $this->cartService->getCartDetails();

            return response()->json([
                'success'  => true,
                'is_valid' => $cartDetails['data']['is_valid'] ?? false,
                'errors'   => $cartDetails['data']['integrity_errors'] ?? [],
            ]);

        } catch (\Exception $e) {
            Log::error('Error en CartController::validate', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success'  => false,
                'is_valid' => false,
                'errors'   => ['Error al validar carrito'],
            ], 500);
        }
    }

    /**
     * Migrar carrito cuando el usuario se loguea (llamado desde AuthenticatedSessionController)
     */
    public function migrateOnLogin(int $userId): JsonResponse
    {
        try {
            $result = $this->cartService->migrateCartOnLogin($userId);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Error en CartController::migrateOnLogin', [
                'error'   => $e->getMessage(),
                'user_id' => $userId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al migrar carrito',
            ], 500);
        }
    }
}

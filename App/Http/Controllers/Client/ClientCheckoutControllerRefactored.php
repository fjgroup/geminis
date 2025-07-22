<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ClientCheckoutService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Controlador refactorizado para el checkout del cliente
 * 
 * Aplicando el Principio de Responsabilidad Única:
 * - Solo maneja HTTP requests/responses
 * - Delega toda la lógica de negocio a ClientCheckoutService
 */
class ClientCheckoutControllerRefactored extends Controller
{
    public function __construct(
        private ClientCheckoutService $clientCheckoutService
    ) {}

    /**
     * Show the product checkout page.
     */
    public function showProductCheckoutPage(Product $product): InertiaResponse
    {
        $product->load(['pricings.billingCycle', 'configurableOptionGroups.options', 'productType']);
        
        return Inertia::render('Client/Checkout/ProductCheckoutPage', [
            'product' => $product,
        ]);
    }

    /**
     * Submit current order from cart.
     */
    public function submitCurrentOrder(Request $request): RedirectResponse
    {
        $client = Auth::user();
        if (!$client) {
            return redirect()->route('login')
                ->with('error', 'Por favor, inicia sesión para continuar.');
        }

        $validatedData = $request->validate([
            'notes_to_client' => 'nullable|string|max:2000',
            'payment_method_slug' => 'nullable|string',
        ]);

        $additionalData = [
            'notes_to_client' => $validatedData['notes_to_client'] ?? null,
            'payment_method_slug' => $validatedData['payment_method_slug'] ?? null,
            'ip_address' => $request->ip(),
        ];

        try {
            $result = $this->clientCheckoutService->processCurrentOrder($client, $additionalData);

            if ($result['success']) {
                return redirect()->route('client.invoices.show', $result['data']->id)
                    ->with('success', $result['message']);
            }

            return redirect()->route('client.checkout.confirm')
                ->with('error', $result['message']);

        } catch (ValidationException $e) {
            Log::warning("ValidationException en submitCurrentOrder: " . $e->getMessage(), [
                'errors' => $e->errors()
            ]);
            
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (Exception $e) {
            Log::error("Error al procesar el pedido para el cliente ID {$client->id}", [
                'client_id' => $client->id,
                'error' => $e->getMessage(),
            ]);
            
            return redirect()->route('client.checkout.confirm')
                ->with('error', 'Ocurrió un error inesperado al procesar su pedido: ' . $e->getMessage());
        }
    }

    /**
     * Show select domain page.
     */
    public function showSelectDomainPage(Request $request): InertiaResponse
    {
        $result = $this->clientCheckoutService->getDomainSelectionData();

        if (!$result['success']) {
            Log::error('Error cargando página de selección de dominio', [
                'error' => $result['message']
            ]);
            
            // Datos por defecto en caso de error
            $result['data'] = [
                'genericDomainProductId' => null,
                'genericDomainPricingId' => null,
            ];
        }

        return Inertia::render('Client/Checkout/SelectDomainPage', $result['data']);
    }

    /**
     * Show select services page.
     */
    public function showSelectServicesPage(Request $request): InertiaResponse|RedirectResponse
    {
        // Obtener datos del carrito usando ClientCartController
        $cartController = app(\App\Http\Controllers\Client\ClientCartController::class);
        $cartData = $cartController->getCartData($request);

        $result = $this->clientCheckoutService->getServicesSelectionData($cartData);

        if (!$result['success']) {
            if (isset($result['redirect'])) {
                return redirect()->route($result['redirect'])
                    ->with('info', $result['message']);
            }

            Log::error('Error cargando página de selección de servicios', [
                'error' => $result['message']
            ]);
            
            return redirect()->route('client.checkout.selectDomain')
                ->with('error', $result['message']);
        }

        return Inertia::render('Client/Checkout/SelectServicesPage', $result['data']);
    }

    /**
     * Show confirm order page.
     */
    public function showConfirmOrderPage(Request $request): InertiaResponse|RedirectResponse
    {
        // Obtener datos del carrito
        $cartController = app(\App\Http\Controllers\Client\ClientCartController::class);
        $cartData = $cartController->getCartData($request);

        $result = $this->clientCheckoutService->getConfirmationData($cartData);

        if (!$result['success']) {
            if (isset($result['redirect'])) {
                return redirect()->route($result['redirect'])
                    ->with('info', $result['message']);
            }

            Log::error('Error cargando página de confirmación', [
                'error' => $result['message']
            ]);
            
            return redirect()->route('client.checkout.selectDomain')
                ->with('error', $result['message']);
        }

        return Inertia::render('Client/Checkout/ConfirmOrderPage', $result['data']);
    }

    /**
     * Get cart summary for AJAX requests.
     */
    public function getCartSummary(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $cartController = app(\App\Http\Controllers\Client\ClientCartController::class);
            $cartData = $cartController->getCartData($request);

            $result = $this->clientCheckoutService->getConfirmationData($cartData);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result['data']['summary'] ?? []
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);

        } catch (\Exception $e) {
            Log::error('Error obteniendo resumen del carrito', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener resumen del carrito'
            ], 500);
        }
    }

    /**
     * Validate cart for checkout.
     */
    public function validateCart(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $cartController = app(\App\Http\Controllers\Client\ClientCartController::class);
            $cartData = $cartController->getCartData($request);

            $cart = $cartData['cart'] ?? null;

            if (empty($cart) || empty($cart['accounts'])) {
                return response()->json([
                    'valid' => false,
                    'message' => 'El carrito está vacío'
                ]);
            }

            return response()->json([
                'valid' => true,
                'message' => 'Carrito válido'
            ]);

        } catch (\Exception $e) {
            Log::error('Error validando carrito', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'valid' => false,
                'message' => 'Error al validar el carrito'
            ], 500);
        }
    }

    /**
     * Clear checkout session data.
     */
    public function clearCheckoutSession(): RedirectResponse
    {
        try {
            session()->forget(['cart', 'purchase_context']);
            
            Log::info('Sesión de checkout limpiada', [
                'user_id' => Auth::id()
            ]);

            return redirect()->route('client.checkout.selectDomain')
                ->with('success', 'Sesión de checkout reiniciada');

        } catch (\Exception $e) {
            Log::error('Error limpiando sesión de checkout', [
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Error al limpiar la sesión');
        }
    }

    /**
     * Get checkout progress for progress indicator.
     */
    public function getCheckoutProgress(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $cartController = app(\App\Http\Controllers\Client\ClientCartController::class);
            $cartData = $cartController->getCartData($request);
            $cart = $cartData['cart'] ?? null;

            $progress = [
                'domain_selected' => false,
                'services_selected' => false,
                'ready_for_checkout' => false,
            ];

            if (!empty($cart['accounts'])) {
                $progress['domain_selected'] = true;
                
                foreach ($cart['accounts'] as $account) {
                    if (!empty($account['services'])) {
                        $progress['services_selected'] = true;
                        $progress['ready_for_checkout'] = true;
                        break;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'progress' => $progress
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo progreso del checkout', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener progreso'
            ], 500);
        }
    }
}

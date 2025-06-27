<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductPricing;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use App\Actions\Client\PlaceOrderAction;
use App\Http\Controllers\Client\ClientCartController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class ClientCheckoutController extends Controller
{
    public function showProductCheckoutPage(Product $product): InertiaResponse
    {
        $product->load(['pricings.billingCycle', 'configurableOptionGroups.options', 'productType']);
        return Inertia::render('Client/Checkout/ProductCheckoutPage', [
            'product' => $product,
        ]);
    }

    public function submitCurrentOrder(Request $request, PlaceOrderAction $placeOrderAction): RedirectResponse
    {
        $client = Auth::user();
        if (!$client) {
            return redirect()->route('login')->with('error', 'Por favor, inicia sesión para continuar.');
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
            $invoice = $placeOrderAction->execute($client, $additionalData);

            if (!$invoice) {
                Log::error("PlaceOrderAction returned null for client ID {$client->id} with cart.");
                return redirect()->route('client.checkout.confirm')
                                    ->with('error', 'No se pudo procesar su pedido. Por favor, inténtelo de nuevo.');
            }

            return redirect()->route('client.invoices.show', $invoice->id)
                                ->with('success', '¡Pedido realizado con éxito! Factura generada.');

        } catch (ValidationException $e) {
            Log::warning("ValidationException en submitCurrentOrder: " . $e->getMessage(), ['errors' => $e->errors()]);
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            Log::error("Error al procesar el pedido desde el carrito para el cliente ID {$client->id}: " . $e->getMessage(), [
                'client_id' => $client->id,
                'exception_trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('client.checkout.confirm')
                                ->with('error', 'Ocurrió un error inesperado al procesar su pedido: ' . $e->getMessage());
        }
    }

    public function showSelectDomainPage(Request $request): InertiaResponse
    {
        $genericDomainProductId = 4;
        $genericDomainPricingId = 8;

        Log::info('Usando IDs de dominio genéricos para SelectDomainPage', [
            'genericDomainProductId' => $genericDomainProductId,
            'genericDomainPricingId' => $genericDomainPricingId
        ]);

        $productExists = Product::where('id', $genericDomainProductId)
                                ->where('product_type_id', 3)
                                ->exists();
        $pricingExists = ProductPricing::where('id', $genericDomainPricingId)
                                ->where('product_id', $genericDomainProductId)
                                ->exists();

        $finalProductId = null;
        $finalPricingId = null;

        if (!$productExists || !$pricingExists) {
            Log::error('El ID del Producto de Dominio Genérico o el ID del Pricing Genérico no se encontraron en la BD, o no son del tipo/producto correcto.', [
                'genericDomainProductId_val' => $genericDomainProductId,
                'genericDomainPricingId_val' => $genericDomainPricingId,
                'productExists' => $productExists,
                'pricingExists' => $pricingExists
            ]);
        } else {
            $finalProductId = $genericDomainProductId;
            $finalPricingId = $genericDomainPricingId;
        }

        return Inertia::render('Client/Checkout/SelectDomainPage', [
            'genericDomainProductId' => $finalProductId,
            'genericDomainPricingId' => $finalPricingId,
        ]);
    }

    public function showSelectServicesPage(Request $request): InertiaResponse|RedirectResponse
    {
        Log::debug('ClientCheckoutController@showSelectServicesPage: Carrito al cargar la página.', [
            'session_cart_on_load' => session('cart', 'No cart in session')
        ]);

        $cartController = app(ClientCartController::class);
        $cartData = $cartController->getCart($request)->getData(true);
        $cart = $cartData['cart'] ?? null;

        if (empty($cart) || empty($cart['accounts']) || empty($cart['active_account_id'])) {
            Log::warning('ClientCheckoutController@showSelectServicesPage: Carrito vacío o sin cuenta activa. Redirigiendo a selectDomain.', ['cart' => $cart]);
            return redirect()->route('client.checkout.selectDomain')->with('info', 'Por favor, selecciona o configura un dominio primero.');
        }

        $activeAccount = null;
        $activeAccountIdFromSession = $cart['active_account_id']; // Guardar antes del bucle para loguear
        foreach($cart['accounts'] as $account) {
            if($account['account_id'] === $activeAccountIdFromSession) {
                $activeAccount = $account;
                break;
            }
        }

        if (!$activeAccount || empty($activeAccount['domain_info']['domain_name'])) {
             Log::warning('ClientCheckoutController@showSelectServicesPage: Cuenta activa no encontrada o sin nombre de dominio. Redirigiendo a selectDomain.', [
                'active_account_id_from_session' => $activeAccountIdFromSession,
                'active_account_found_in_loop' => $activeAccount,
                'cart' => $cart
             ]);
             return redirect()->route('client.checkout.selectDomain')->with('info', 'Por favor, configura un dominio para la cuenta activa antes de seleccionar servicios.');
        }

        $mainServiceTypeIds = [1, 2, 7];
        $sslTypeIds = [4];
        $licenseTypeIds = [6];

        $mainServiceProducts = Product::whereIn('product_type_id', $mainServiceTypeIds)
            ->where('status', 'active')->with(['pricings.billingCycle', 'productType', 'configurableOptionGroups.options'])->orderBy('display_order')->get();

        $sslProducts = Product::whereIn('product_type_id', $sslTypeIds)
            ->where('status', 'active')->with(['pricings.billingCycle', 'productType'])->orderBy('display_order')->get();

        $licenseProducts = Product::whereIn('product_type_id', $licenseTypeIds)
            ->where('status', 'active')->with(['pricings.billingCycle', 'productType'])->orderBy('display_order')->get();

        return Inertia::render('Client/Checkout/SelectServicesPage', [
            'initialCart' => $cart,
            'mainServiceProducts' => $mainServiceProducts,
            'sslProducts' => $sslProducts,
            'licenseProducts' => $licenseProducts,
        ]);
    }

    public function showConfirmOrderPage(Request $request): InertiaResponse|RedirectResponse
    {
        // Añadir log similar para esta página también
        Log::debug('ClientCheckoutController@showConfirmOrderPage: Carrito al cargar la página.', [
            'session_cart_on_load' => session('cart', 'No cart in session')
        ]);

        $cartController = app(ClientCartController::class);
        $cartData = $cartController->getCart($request)->getData(true);
        $cart = $cartData['cart'] ?? null;

        if (empty($cart) || empty($cart['accounts'])) {
             Log::warning('ClientCheckoutController@showConfirmOrderPage: Carrito vacío. Redirigiendo al dashboard.', ['cart' => $cart]);
            return redirect()->route('client.dashboard')->with('info', 'Tu carrito está vacío. Por favor, añade productos antes de confirmar el pedido.');
        }

        $hasBillableItem = false;
        foreach($cart['accounts'] as $account) {
            if (!empty($account['domain_info']['product_id']) || !empty($account['primary_service']) || !empty($account['additional_services'])) {
                $hasBillableItem = true;
                break;
            }
        }
        if (!$hasBillableItem && !$this->cartHasOnlyDomainRegistrationWithoutProduct($cart)) {
             Log::warning('ClientCheckoutController@showConfirmOrderPage: Carrito sin items facturables. Redirigiendo a selectServices.', ['cart' => $cart]);
             return redirect()->route('client.checkout.selectServices')->with('info', 'No hay servicios seleccionados en tu carrito.');
        }

        return Inertia::render('Client/Checkout/ConfirmOrderPage', [
            'initialCart' => $cart,
        ]);
    }

    private function cartHasOnlyDomainRegistrationWithoutProduct(array $cart): bool
    {
        if (count($cart['accounts']) === 1) {
            $account = $cart['accounts'][0];
            if (empty($account['domain_info']['product_id']) &&
                empty($account['primary_service']) &&
                empty($account['additional_services'])) {
                return true;
            }
        }
        return false;
    }
}

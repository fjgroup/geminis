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
use Illuminate\Database\Eloquent\ModelNotFoundException; // Aunque no se usa directamente, es bueno tenerlo por si acaso en el futuro.
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
            // Se comenta la validación de payment_method_slug para que no sea obligatoria si el cliente no elige uno explícitamente
            // y se pueda procesar el pedido para generar una factura que luego pagará.
            // Si se requiere un método de pago en este punto, se debe descomentar y ajustar la lógica.
            // 'payment_method_slug' => 'nullable|string|exists:payment_methods,slug',
            'payment_method_slug' => 'nullable|string', // Permitir cualquier string o null por ahora
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

    public function showSelectDomainPage(): InertiaResponse
    {
        return Inertia::render('Client/Checkout/SelectDomainPage');
    }

    public function showSelectServicesPage(Request $request): InertiaResponse|RedirectResponse
    {
        $cartController = app(ClientCartController::class);
        $cartData = $cartController->getCart($request)->getData(true);
        $cart = $cartData['cart'] ?? null;

        // Validar que haya una cuenta activa y un dominio configurado
        if (empty($cart) || empty($cart['accounts']) || empty($cart['active_account_id'])) {
            return redirect()->route('client.checkout.selectDomain')->with('info', 'Por favor, selecciona o configura un dominio primero.');
        }
        $activeAccount = null;
        foreach($cart['accounts'] as $account) {
            if($account['account_id'] === $cart['active_account_id']) {
                $activeAccount = $account;
                break;
            }
        }
        if (!$activeAccount || empty($activeAccount['domain_info']['domain_name'])) {
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
        $cartController = app(ClientCartController::class);
        $cartData = $cartController->getCart($request)->getData(true);
        $cart = $cartData['cart'] ?? null;

        if (empty($cart) || empty($cart['accounts'])) {
            return redirect()->route('client.dashboard')->with('info', 'Tu carrito está vacío. Por favor, añade productos antes de confirmar el pedido.');
        }
        // Adicionalmente, verificar si hay al menos un ítem facturable en el carrito
        $hasBillableItem = false;
        foreach($cart['accounts'] as $account) {
            if (!empty($account['domain_info']['product_id']) || !empty($account['primary_service']) || !empty($account['additional_services'])) {
                $hasBillableItem = true;
                break;
            }
        }
        if (!$hasBillableItem && !$this->cartHasOnlyDomainRegistrationWithoutProduct($cart, $cartController)) { // Pasar $cartController o $request
             return redirect()->route('client.checkout.selectServices')->with('info', 'No hay servicios seleccionados en tu carrito.');
        }


        return Inertia::render('Client/Checkout/ConfirmOrderPage', [
            'initialCart' => $cart,
            // 'paymentMethods' => \App\Models\PaymentMethod::where('is_active', true)->orderBy('name')->get(['name', 'slug']),
        ]);
    }

    // Helper para showConfirmOrderPage, similar al de PlaceOrderAction pero adaptado
    private function cartHasOnlyDomainRegistrationWithoutProduct(array $cart, ClientCartController $cartController): bool
    {
        // Esta función es para el caso donde solo hay un `domain_name` en `domain_info`
        // pero NO hay `product_id` ni `pricing_id` asociados a él, ni otros servicios.
        // Es un caso especial de "solo reservar dominio" que podría no generar factura inmediatamente.
        // La lógica original en PlaceOrderAction es:
        // if (count($cart['accounts']) !== 1) return false;
        // $account = $cart['accounts'][0];
        // return !empty($account['domain_info']['domain_name']) &&
        //        empty($account['domain_info']['product_id']) &&
        //        empty($account['primary_service']) &&
        //        empty($account['additional_services']);
        // Para el contexto de mostrar la página de confirmación, si el único "item" es un nombre de dominio
        // sin producto asociado, probablemente no deberíamos llegar a la confirmación de "pedido"
        // a menos que el acto de registrar un nombre de dominio sin producto sea en sí mismo un pedido.
        // Si ese no es el caso, entonces esto debería considerarse un carrito "efectivamente vacío" para facturación.

        // Simplificamos: si no hay product_id en domain_info, y no hay otros servicios, no es facturable aun.
        if (count($cart['accounts']) === 1) {
            $account = $cart['accounts'][0];
            if (empty($account['domain_info']['product_id']) &&
                empty($account['primary_service']) &&
                empty($account['additional_services'])) {
                return true; // Es solo un nombre de dominio sin producto/precio.
            }
        }
        return false;
    }
}

<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\ConfigurableOptionGroup;
use App\Models\ConfigurableOption;
use App\Models\ConfigurableOptionPricing;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse; // Importar para RedirectResponse

class ClientCartController extends Controller
{
    private function initializeCart()
    {
        return [
            'accounts' => [],
            'active_account_id' => null,
        ];
    }

    public function getCart(Request $request)
    {
        $cart = $request->session()->get('cart', $this->initializeCart());
        if (!isset($cart['accounts']) || !isset($cart['active_account_id'])) {
            $cart = $this->initializeCart();
        }

        foreach ($cart['accounts'] as &$account) {
            if (isset($account['domain_info']['product_id'], $account['domain_info']['pricing_id'])) {
                $product = Product::find($account['domain_info']['product_id']);
                $pricing = ProductPricing::find($account['domain_info']['pricing_id']);
                if ($product && $pricing && $pricing->product_id == $product->id) {
                    $productName = $product->name;
                    if (isset($account['domain_info']['tld_extension']) && $product->product_type_id == 3) {
                        // $productName = "Registro Dominio ." . $account['domain_info']['tld_extension'];
                    }
                    $account['domain_info']['product_name'] = $productName;
                    $account['domain_info']['price'] = isset($account['domain_info']['override_price']) ? (float)$account['domain_info']['override_price'] : (float)$pricing->price;
                    $account['domain_info']['currency_code'] = $pricing->currency_code;
                } else {
                    $account['domain_info']['product_name'] = 'Información no disponible';
                    $account['domain_info']['price'] = 0.00;
                    $account['domain_info']['currency_code'] = config('app.currency_code', 'USD');
                }
            } elseif (isset($account['domain_info']['domain_name']) && !isset($account['domain_info']['product_id'])) {
                $account['domain_info']['product_name'] = 'Dominio (Nombre Reservado)';
                $account['domain_info']['price'] = isset($account['domain_info']['override_price']) ? (float)$account['domain_info']['override_price'] : 0.00;
                $account['domain_info']['currency_code'] = config('app.currency_code', 'USD');
            }

            if (isset($account['primary_service']['product_id'], $account['primary_service']['pricing_id'])) {
                $product = Product::find($account['primary_service']['product_id']);
                $pricing = ProductPricing::find($account['primary_service']['pricing_id']);

                if ($product && $pricing && $pricing->product_id == $product->id) {
                    $account['primary_service']['product_name'] = $product->name;
                    $basePrice = (float) $pricing->price;
                    $optionsPriceAdjustment = 0.0;
                    $account['primary_service']['currency_code'] = $pricing->currency_code;

                    if (isset($account['primary_service']['configurable_options']) && is_array($account['primary_service']['configurable_options'])) {
                        $enrichedOptions = [];
                        foreach ($account['primary_service']['configurable_options'] as $groupId => $optionId) {
                            $group = ConfigurableOptionGroup::find($groupId);
                            $option = ConfigurableOption::find($optionId);
                            if ($group && $option && $option->group_id == $group->id) {
                                $enrichedOptions[] = ['group_id' => $group->id, 'group_name' => $group->name, 'option_id' => $option->id, 'option_name' => $option->name];
                                $optionPricing = ConfigurableOptionPricing::where('configurable_option_id', $option->id)
                                    ->where('billing_cycle_id', $pricing->billing_cycle_id)->first();
                                if ($optionPricing) { $optionsPriceAdjustment += (float) $optionPricing->price; }
                            } else {
                                $enrichedOptions[] = ['group_id' => $groupId, 'group_name' => "ID Grupo: {$groupId}", 'option_id' => $optionId, 'option_name' => "ID Opción: {$optionId}"];
                            }
                        }
                        $account['primary_service']['configurable_options_details'] = $enrichedOptions;
                    }
                    $account['primary_service']['price'] = $basePrice + $optionsPriceAdjustment;
                } else {
                    $account['primary_service']['product_name'] = 'Servicio primario no disponible';
                    $account['primary_service']['price'] = 0.00;
                    $account['primary_service']['currency_code'] = config('app.currency_code', 'USD');
                }
            }

            if (isset($account['additional_services']) && is_array($account['additional_services'])) {
                foreach ($account['additional_services'] as &$item) {
                    if (isset($item['product_id'], $item['pricing_id'])) {
                        $product = Product::find($item['product_id']);
                        $pricing = ProductPricing::find($item['pricing_id']);
                        if ($product && $pricing && $pricing->product_id == $product->id) {
                            $item['product_name'] = $product->name; $item['price'] = (float) $pricing->price; $item['currency_code'] = $pricing->currency_code;
                        } else {
                            $item['product_name'] = 'Servicio adicional no disponible'; $item['price'] = 0.00; $item['currency_code'] = config('app.currency_code', 'USD');
                        }
                    }
                }
                unset($item);
            }
        }
        unset($account);
        return response()->json(['status' => 'success', 'cart' => $cart]);
    }

    private function findAccount(Request $request, $accountId) { /* ... sin cambios ... */ }
    private function getActiveAccountIndex(Request $request) { /* ... sin cambios ... */ }
    private function findItemInAccount(&$account, $cartItemId) { /* ... sin cambios ... */ }

    public function setDomainForAccount(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'domain_name' => 'required|string|max:255',
            'override_price' => 'nullable|numeric|min:0',
            'tld_extension' => 'required|string|max:10',
            'product_id' => 'required|integer|exists:products,id',
            'pricing_id' => 'required|integer|exists:product_pricings,id',
        ]);

        $cart = $request->session()->get('cart', $this->initializeCart());
        $activeIndex = $this->getActiveAccountIndex($request);

        $genericProduct = Product::find($validated['product_id']);
        $genericPricing = ProductPricing::find($validated['pricing_id']);

        if (!$genericProduct || $genericProduct->product_type_id != 3) {
            // Este error no debería ocurrir si el frontend envía los IDs correctos pasados por el controlador
            return back()->withInput()->withErrors(['product_id' => 'El producto de dominio genérico configurado no es válido.']);
        }
        if (!$genericPricing || $genericPricing->product_id != $genericProduct->id) {
            return back()->withInput()->withErrors(['pricing_id' => 'La configuración de precios para el dominio genérico no es válida.']);
        }

        $domainInfo = [
            'domain_name' => $validated['domain_name'],
            'product_id' => $validated['product_id'],
            'pricing_id' => $validated['pricing_id'],
            'override_price' => $validated['override_price'] ?? null,
            'tld_extension' => $validated['tld_extension'],
            'cart_item_id' => (string) Str::uuid(),
        ];

        $newAccountId = null;
        if ($activeIndex === null) {
            $newAccountId = (string) Str::uuid();
            $newAccount = ['account_id' => $newAccountId, 'domain_info' => $domainInfo, 'primary_service' => null, 'additional_services' => []];
            $cart['accounts'][] = $newAccount;
            $cart['active_account_id'] = $newAccountId;
        } else {
            if (!empty($cart['accounts'][$activeIndex]['domain_info'])) {
                // En lugar de error JSON, redirigir con error de formulario
                return back()->withInput()->withErrors(['domain_name' => 'La cuenta activa ya tiene información de dominio. Para cambiarla, primero elimine la existente o cree una nueva cuenta.']);
            }
            $cart['accounts'][$activeIndex]['domain_info'] = $domainInfo;
            $newAccountId = $cart['accounts'][$activeIndex]['account_id'];
        }
        $request->session()->put('cart', $cart);

        // En lugar de JSON, redirigir a la siguiente página del flujo
        return redirect()->route('client.checkout.selectServices')
                       ->with('success', 'Dominio configurado en el carrito.');
    }

    // setPrimaryServiceForAccount y addItem deberían seguir devolviendo JSON
    // ya que SelectServicesPage.vue está en la misma página y espera una respuesta JSON
    // para actualizar CartSummary mediante evento sin recargar la página completa.
    public function setPrimaryServiceForAccount(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'pricing_id' => 'required|integer|exists:product_pricings,id',
            'configurable_options' => 'nullable|array',
        ]);

        $cart = $request->session()->get('cart', $this->initializeCart());
        $activeIndex = $this->getActiveAccountIndex($request);

        if ($activeIndex === null) {
            return response()->json(['status' => 'error', 'message' => 'No hay una cuenta activa para añadir el servicio.'], 400);
        }
        $account = &$cart['accounts'][$activeIndex];

        if (empty($account['domain_info'])) {
            return response()->json(['status' => 'error', 'message' => 'La cuenta activa debe tener información de dominio configurada.'], 400);
        }
        if (!empty($account['primary_service'])) {
            return response()->json(['status' => 'error', 'message' => 'La cuenta activa ya tiene un servicio principal.'], 409);
        }

        $product = Product::with('configurableOptionGroups.options')->find($validated['product_id']);
        $pricing = ProductPricing::find($validated['pricing_id']);

        if ($pricing->product_id != $product->id) {
            return response()->json(['status' => 'error', 'message' => 'La configuración de precio no corresponde al producto seleccionado.'], 422);
        }

        $allowedPrimaryServiceTypes = [1, 2, 7];
        if (!in_array($product->product_type_id, $allowedPrimaryServiceTypes)) {
            return response()->json(['status' => 'error', 'message' => 'Este tipo de producto no puede ser un servicio principal.'], 422);
        }

        $primaryServiceData = [
            'cart_item_id' => (string) Str::uuid(),
            'product_id' => $product->id, 'pricing_id' => $pricing->id,
        ];

        if (!empty($validated['configurable_options'])) {
            $validConfigOptions = [];
            $productConfigGroups = $product->configurableOptionGroups->keyBy('id');
            foreach ($validated['configurable_options'] as $groupId => $optionId) {
                if (!is_numeric($groupId) || !is_numeric($optionId)) {
                     return response()->json(['status' => 'error', 'message' => "ID de grupo u opción inválido: {$groupId} -> {$optionId}."], 422);
                }
                if (!isset($productConfigGroups[$groupId])) {
                    return response()->json(['status' => 'error', 'message' => "Grupo de opción configurable inválido: ID {$groupId}."], 422);
                }
                $group = $productConfigGroups[$groupId];
                if (!$group->options->contains('id', $optionId)) {
                    return response()->json(['status' => 'error', 'message' => "Opción configurable inválida: ID {$optionId} para el grupo '{$group->name}'."], 422);
                }
                $validConfigOptions[(int)$groupId] = (int)$optionId;
            }
            $primaryServiceData['configurable_options'] = $validConfigOptions;
        } else {
            $primaryServiceData['configurable_options'] = null;
        }

        $account['primary_service'] = $primaryServiceData;
        $request->session()->put('cart', $cart);
        // Devuelve el carrito enriquecido para que el frontend pueda actualizar CartSummary
        return response()->json(['status' => 'success', 'message' => 'Servicio principal añadido.', 'cart' => $this->getCart($request)->getData(true)['cart']]);
    }

    public function addItem(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'pricing_id' => 'required|integer|exists:product_pricings,id',
        ]);

        $cart = $request->session()->get('cart', $this->initializeCart());
        $activeIndex = $this->getActiveAccountIndex($request);

        if ($activeIndex === null) {
            return response()->json(['status' => 'error', 'message' => 'No hay una cuenta activa para añadir servicios adicionales.'], 400);
        }
        $account = &$cart['accounts'][$activeIndex];

        if (empty($account['domain_info'])) {
            return response()->json(['status' => 'error', 'message' => 'La cuenta activa debe tener información de dominio configurada antes de añadir servicios adicionales.'], 400);
        }

        $product = Product::find($validated['product_id']);
        $pricing = ProductPricing::find($validated['pricing_id']);

        if ($pricing->product_id != $product->id) {
            return response()->json(['status' => 'error', 'message' => 'La configuración de precio no corresponde al producto seleccionado.'], 422);
        }

        $allowedAdditionalServiceTypes = [4, 6];
        if (!in_array($product->product_type_id, $allowedAdditionalServiceTypes)) {
            return response()->json(['status' => 'error', 'message' => 'Este tipo de producto no puede ser añadido como servicio adicional de esta manera.'], 422);
        }

        $additionalServiceData = [
            'cart_item_id' => (string) Str::uuid(),
            'product_id' => $product->id, 'pricing_id' => $pricing->id,
        ];

        $account['additional_services'][] = $additionalServiceData;
        $request->session()->put('cart', $cart);
        return response()->json(['status' => 'success', 'message' => 'Servicio adicional añadido.', 'cart' => $this->getCart($request)->getData(true)['cart']]);
    }

    public function updateItem(Request $request) { /* ... sin cambios, ya devuelve JSON ... */ }
    public function removeItem(Request $request) { /* ... sin cambios, ya devuelve JSON ... */ }
    public function clearCart(Request $request) { /* ... sin cambios, ya devuelve JSON ... */ }
    public function setActiveAccount(Request $request) { /* ... sin cambios, ya devuelve JSON ... */ }
}

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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class ClientCartController extends Controller
{
    private function initializeCart()
    {
        return [
            'accounts' => [],
            'active_account_id' => null,
        ];
    }

    public function getCart(Request $request): JsonResponse
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

    private function findAccount(Request $request, $accountId): ?int
    {
        $cart = $request->session()->get('cart', $this->initializeCart());
        foreach ($cart['accounts'] as $index => $account) {
            if ($account['account_id'] === $accountId) {
                return $index;
            }
        }
        return null;
    }

    private function getActiveAccountIndex(Request $request): ?int
    {
        $cart = $request->session()->get('cart', $this->initializeCart());
        if (!$cart || !isset($cart['active_account_id'])) { // Añadida verificación para $cart
            Log::debug('ClientCartController@getActiveAccountIndex: Carrito no encontrado o active_account_id no establecido.', ['cart_in_session' => $cart]);
            return null;
        }
        return $this->findAccount($request, $cart['active_account_id']);
    }

    // No se necesita getActiveAccountData, se puede obtener el índice y luego los datos
    // private function getActiveAccountData(Request $request) { ... }


    private function findItemInAccount(?array &$account, string $cartItemId): ?array
    {
        if (!$account) return null;

        if (isset($account['domain_info']['cart_item_id']) && $account['domain_info']['cart_item_id'] === $cartItemId) {
            return ['type' => 'domain_info', 'item' => &$account['domain_info']];
        }
        if (isset($account['primary_service']['cart_item_id']) && $account['primary_service']['cart_item_id'] === $cartItemId) {
            return ['type' => 'primary_service', 'item' => &$account['primary_service']];
        }
        if (isset($account['additional_services'])) {
            foreach ($account['additional_services'] as $key => &$service) {
                if (isset($service['cart_item_id']) && $service['cart_item_id'] === $cartItemId) {
                    return ['type' => 'additional_service', 'index' => $key, 'item' => &$service];
                }
            }
        }
        return null;
    }

    public function setDomainForAccount(Request $request): RedirectResponse
    {
        // ... (validaciones y lógica principal) ...
        $validated = $request->validate([ /* ... */ ]);
        $cart = $request->session()->get('cart', $this->initializeCart());
        $activeIndex = $this->getActiveAccountIndex($request);
        // ... (resto de la lógica como estaba) ...

        $request->session()->put('cart', $cart);

        Log::info('ClientCartController@setDomainForAccount: Carrito después de configurar dominio y ANTES de redirección.', [
            'session_cart_final' => $request->session()->get('cart') // Loguear el carrito guardado
        ]);

        return redirect()->route('client.checkout.selectServices')
                       ->with('success', 'Dominio configurado en el carrito.');
    }

    public function setPrimaryServiceForAccount(Request $request): JsonResponse
    {
        Log::debug('ClientCartController@setPrimaryServiceForAccount: Iniciando.', [
            'session_cart_initial' => $request->session()->get('cart', 'No cart in session'),
            'request_payload' => $request->all()
        ]);

        $validated = $request->validate([ /* ... */ ]);
        $cart = $request->session()->get('cart', $this->initializeCart());
        $activeIndex = $this->getActiveAccountIndex($request);
        $activeAccountData = null;
        if ($activeIndex !== null && isset($cart['accounts'][$activeIndex])) {
            $activeAccountData = $cart['accounts'][$activeIndex];
        }

        Log::debug('ClientCartController@setPrimaryServiceForAccount: Estado de cuenta activa.', [
            'active_account_id_from_session' => $cart['active_account_id'] ?? 'Not set',
            'retrieved_active_account_data_by_index' => $activeAccountData,
            'current_cart_accounts' => $cart['accounts'] ?? []
        ]);

        if ($activeIndex === null) {
            Log::warning('ClientCartController@setPrimaryServiceForAccount: No se encontró índice de cuenta activa válido.', [
                'session_cart_on_failure' => $request->session()->get('cart')
            ]);
            return response()->json(['message' => 'No hay una cuenta activa para añadir el servicio.'], 422);
        }
        // Asegurar que la cuenta realmente exista en el array (activeIndex podría ser un índice fuera de rango si el carrito se modificó incorrectamente)
        if(!isset($cart['accounts'][$activeIndex])) {
            Log::error('ClientCartController@setPrimaryServiceForAccount: activeIndex está seteado pero la cuenta no existe en el array.', [
                'activeIndex' => $activeIndex,
                'session_cart_on_failure' => $request->session()->get('cart')
            ]);
            // Invalidar active_account_id y pedir al usuario que reintente o reinicie el proceso.
            $cart['active_account_id'] = null;
            $request->session()->put('cart', $cart);
            return response()->json(['message' => 'Error de consistencia en el carrito. Por favor, reinicia el proceso de compra.'], 500);
        }

        $account = &$cart['accounts'][$activeIndex];
        // ... (resto de la lógica como estaba, con sus retornos de error 422/409) ...

        $request->session()->put('cart', $cart);
        return response()->json(['status' => 'success', 'message' => 'Servicio principal añadido.', 'cart' => $this->getCart($request)->getData(true)['cart']]);
    }

    public function addItem(Request $request): JsonResponse
    {
        Log::debug('ClientCartController@addItem: Iniciando.', [
            'session_cart_initial' => $request->session()->get('cart', 'No cart in session'),
            'request_payload' => $request->all()
        ]);

        $validated = $request->validate([ /* ... */ ]);
        $cart = $request->session()->get('cart', $this->initializeCart());
        $activeIndex = $this->getActiveAccountIndex($request);
        $activeAccountData = null;
        if ($activeIndex !== null && isset($cart['accounts'][$activeIndex])) {
            $activeAccountData = $cart['accounts'][$activeIndex];
        }

        Log::debug('ClientCartController@addItem: Estado de cuenta activa.', [
            'active_account_id_from_session' => $cart['active_account_id'] ?? 'Not set',
            'retrieved_active_account_data_by_index' => $activeAccountData,
            'current_cart_accounts' => $cart['accounts'] ?? []
        ]);

        if ($activeIndex === null) {
            Log::warning('ClientCartController@addItem: No se encontró índice de cuenta activa válido.', [
                'session_cart_on_failure' => $request->session()->get('cart')
            ]);
            return response()->json(['message' => 'No hay una cuenta activa para añadir servicios adicionales.'], 422);
        }
         if(!isset($cart['accounts'][$activeIndex])) {
            Log::error('ClientCartController@addItem: activeIndex está seteado pero la cuenta no existe en el array.', [
                'activeIndex' => $activeIndex,
                'session_cart_on_failure' => $request->session()->get('cart')
            ]);
            $cart['active_account_id'] = null;
            $request->session()->put('cart', $cart);
            return response()->json(['message' => 'Error de consistencia en el carrito. Por favor, reinicia el proceso de compra.'], 500);
        }
        $account = &$cart['accounts'][$activeIndex];
        // ... (resto de la lógica como estaba, con sus retornos de error 422) ...

        $request->session()->put('cart', $cart);
        return response()->json(['status' => 'success', 'message' => 'Servicio adicional añadido.', 'cart' => $this->getCart($request)->getData(true)['cart']]);
    }

    // Los siguientes métodos no necesitan logging adicional para este subtask específico,
    // a menos que se sospeche que active_account_id se corrompe en ellos.
    public function updateItem(Request $request): JsonResponse { /* ... como estaba ... */ }
    public function removeItem(Request $request): JsonResponse { /* ... como estaba ... */ }
    public function clearCart(Request $request): JsonResponse { /* ... como estaba ... */ }
    public function setActiveAccount(Request $request): JsonResponse
    {
        Log::debug('ClientCartController@setActiveAccount: Iniciando.', [
            'session_cart_initial' => $request->session()->get('cart', 'No cart in session'),
            'requested_account_id' => $request->input('account_id')
        ]);

        $validated = $request->validate(['account_id' => 'nullable|string']);
        $cart = $request->session()->get('cart', $this->initializeCart());
        $accountIdToActivate = $validated['account_id'] ?? null;

        if ($accountIdToActivate === null) {
            $cart['active_account_id'] = null;
             Log::info('ClientCartController@setActiveAccount: Cuenta activa establecida a null.');
        } else {
            $accountIndex = $this->findAccount($request, $accountIdToActivate);
            if ($accountIndex === null) {
                 Log::warning('ClientCartController@setActiveAccount: Cuenta no encontrada para activar.', ['target_id' => $accountIdToActivate, 'cart' => $cart]);
                return response()->json(['message' => 'Cuenta no encontrada.'], 404);
            }
            $cart['active_account_id'] = $cart['accounts'][$accountIndex]['account_id'];
            Log::info('ClientCartController@setActiveAccount: Cuenta activa establecida.', ['active_id' => $cart['active_account_id'], 'cart' => $cart]);
        }

        $request->session()->put('cart', $cart);
        return response()->json(['status' => 'success', 'message' => 'Cuenta activa establecida.', 'cart' => $this->getCart($request)->getData(true)['cart']]);
    }
}

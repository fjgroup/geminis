<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ConfigurableOption;
use App\Models\ConfigurableOptionGroup;
use App\Models\ConfigurableOptionPricing;
use App\Models\Product;
use App\Models\ProductPricing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ClientCartController extends Controller
{
    private function initializeCart()
    {
        return [
            'accounts'          => [],
            'active_account_id' => null,
        ];
    }

    public function getCart(Request $request): JsonResponse
    {
        $cart = $request->session()->get('cart', $this->initializeCart());
        if (! isset($cart['accounts']) || ! isset($cart['active_account_id'])) {
            $cart = $this->initializeCart();
        }

        foreach ($cart['accounts'] as &$account) {
            if (isset($account['domain_info']['product_id'], $account['domain_info']['pricing_id'])) {
                $product = Product::find($account['domain_info']['product_id']);
                $pricing = ProductPricing::find($account['domain_info']['pricing_id']);
                if ($product && $pricing && $pricing->product_id == $product->id) {
                    $productName = $product->name;
                    if (isset($account['domain_info']['tld_extension']) && $product->product_type_id == 3) {
                    }
                    $account['domain_info']['product_name']  = $productName;
                    $account['domain_info']['price']         = isset($account['domain_info']['override_price']) ? (float) $account['domain_info']['override_price'] : (float) $pricing->price;
                    $account['domain_info']['currency_code'] = $pricing->currency_code;
                } else {
                    $account['domain_info']['product_name']  = 'Información no disponible';
                    $account['domain_info']['price']         = 0.00;
                    $account['domain_info']['currency_code'] = config('app.currency_code', 'USD');
                }
            } elseif (isset($account['domain_info']['domain_name']) && ! isset($account['domain_info']['product_id'])) {
                $account['domain_info']['product_name']  = 'Dominio (Nombre Reservado)';
                $account['domain_info']['price']         = isset($account['domain_info']['override_price']) ? (float) $account['domain_info']['override_price'] : 0.00;
                $account['domain_info']['currency_code'] = config('app.currency_code', 'USD');
            }

            if (isset($account['primary_service']['product_id'], $account['primary_service']['pricing_id'])) {
                $product = Product::find($account['primary_service']['product_id']);
                $pricing = ProductPricing::find($account['primary_service']['pricing_id']);

                if ($product && $pricing && $pricing->product_id == $product->id) {
                    $account['primary_service']['product_name']  = $product->name;
                    $basePrice                                   = (float) $pricing->price;
                    $optionsPriceAdjustment                      = 0.0;
                    $account['primary_service']['currency_code'] = $pricing->currency_code;

                    if (isset($account['primary_service']['configurable_options']) && is_array($account['primary_service']['configurable_options'])) {
                        $enrichedOptions = [];
                        foreach ($account['primary_service']['configurable_options'] as $groupId => $optionId) {
                            $group  = ConfigurableOptionGroup::find($groupId);
                            $option = ConfigurableOption::find($optionId);
                            if ($group && $option && $option->group_id == $group->id) {
                                $enrichedOptions[] = ['group_id' => $group->id, 'group_name' => $group->name, 'option_id' => $option->id, 'option_name' => $option->name];
                                $optionPricing     = ConfigurableOptionPricing::where('configurable_option_id', $option->id)
                                    ->where('billing_cycle_id', $pricing->billing_cycle_id)->first();
                                if ($optionPricing) {$optionsPriceAdjustment += (float) $optionPricing->price;}
                            } else {
                                $enrichedOptions[] = ['group_id' => $groupId, 'group_name' => "ID Grupo: {$groupId}", 'option_id' => $optionId, 'option_name' => "ID Opción: {$optionId}"];
                            }
                        }
                        $account['primary_service']['configurable_options_details'] = $enrichedOptions;
                    }
                    $account['primary_service']['price'] = $basePrice + $optionsPriceAdjustment;
                } else {
                    $account['primary_service']['product_name']  = 'Servicio primario no disponible';
                    $account['primary_service']['price']         = 0.00;
                    $account['primary_service']['currency_code'] = config('app.currency_code', 'USD');
                }
            }

            if (isset($account['additional_services']) && is_array($account['additional_services'])) {
                foreach ($account['additional_services'] as &$item) {
                    if (isset($item['product_id'], $item['pricing_id'])) {
                        $product = Product::find($item['product_id']);
                        $pricing = ProductPricing::find($item['pricing_id']);
                        if ($product && $pricing && $pricing->product_id == $product->id) {
                            $item['product_name']  = $product->name;
                            $item['price']         = (float) $pricing->price;
                            $item['currency_code'] = $pricing->currency_code;
                        } else {
                            $item['product_name']  = 'Servicio adicional no disponible';
                            $item['price']         = 0.00;
                            $item['currency_code'] = config('app.currency_code', 'USD');
                        }
                    }
                }
                unset($item);
            }
        }
        unset($account);
        return response()->json(['status' => 'success', 'cart' => $cart]);
    }

    private function findAccount($accountId): ?int
    {
        $cart = session('cart', $this->initializeCart());
        if (! isset($cart['accounts']) || ! is_array($cart['accounts'])) {
            Log::warning('ClientCartController@findAccount: "accounts" no es un array o no existe en el carrito.', ['cart_in_session' => $cart]);
            return null;
        }
        foreach ($cart['accounts'] as $index => $account) {
            if (is_array($account) && isset($account['account_id']) && $account['account_id'] === $accountId) {
                return $index;
            }
        }
        return null;
    }

    private function getActiveAccountIndex(): ?int
    {
        $cart = session('cart');

        if (! $cart || ! isset($cart['active_account_id']) || is_null($cart['active_account_id'])) {
            Log::debug('ClientCartController@getActiveAccountIndex: Carrito no encontrado o active_account_id no establecido o es null.', [
                'cart_in_session_active_account_id' => $cart['active_account_id'] ?? 'Not set or cart is null',
            ]);
            return null;
        }

        $activeAccountId = $cart['active_account_id'];
        if (! isset($cart['accounts']) || ! is_array($cart['accounts'])) {
            Log::error('ClientCartController@getActiveAccountIndex: "accounts" no es un array o no existe en el carrito, pero active_account_id sí estaba seteado.', [
                'active_account_id_was' => $activeAccountId,
                'cart_in_session'       => $cart,
            ]);
            return null;
        }

        $foundIndex = $this->findAccount($activeAccountId);

        if ($foundIndex === null) {
            Log::warning('ClientCartController@getActiveAccountIndex: active_account_id no fue encontrado en el array de cuentas.', [
                'active_account_id_searched' => $activeAccountId,
                'cart_accounts'              => $cart['accounts'] ?? 'No accounts array',
            ]);
            return null;
        }

        return $foundIndex;
    }

    private function findItemInAccount( ? array &$account, string $cartItemId): ?array
    { /* ... sin cambios ... */}

    public function setDomainForAccount(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'domain_name'    => 'required|string|max:255',
            'override_price' => 'nullable|numeric|min:0',
            'tld_extension'  => 'required|string|max:10',
            'product_id'     => 'required|integer|exists:products,id',
            'pricing_id'     => 'required|integer|exists:product_pricings,id',
        ]);
        $cart         = session('cart', $this->initializeCart()); // Uso session()
        $activeIndex  = $this->getActiveAccountIndex();           // Sin $request
        $newAccountId = null;

        $genericProduct = Product::find($validatedData['product_id']);
        $genericPricing = ProductPricing::find($validatedData['pricing_id']);

        if (! $genericProduct || $genericProduct->product_type_id != 3) {
            return back()->withInput()->withErrors(['product_id' => 'El producto de dominio genérico configurado no es válido.']);
        }
        if (! $genericPricing || $genericPricing->product_id != $genericProduct->id) {
            return back()->withInput()->withErrors(['pricing_id' => 'La configuración de precios para el dominio genérico no es válida.']);
        }
        $domainInfo = [
            'domain_name'    => $validatedData['domain_name'],
            'product_id'     => $validatedData['product_id'],
            'pricing_id'     => $validatedData['pricing_id'],
            'override_price' => $validatedData['override_price'] ?? null,
            'tld_extension'  => $validatedData['tld_extension'],
            'cart_item_id'   => (string) Str::uuid(),
        ];
        if ($activeIndex === null) {
            $newAccountId              = (string) Str::uuid();
            $newAccount                = ['account_id' => $newAccountId, 'domain_info' => $domainInfo, 'primary_service' => null, 'additional_services' => []];
            $cart['accounts'][]        = $newAccount;
            $cart['active_account_id'] = $newAccountId;
        } else {
            if (isset($cart['accounts'][$activeIndex]) && ! empty($cart['accounts'][$activeIndex]['domain_info'])) {
                return back()->withInput()->withErrors(['domain_name' => 'La cuenta activa ya tiene información de dominio. Para cambiarla, primero elimine la existente o cree una nueva cuenta.']);
            }
            if (isset($cart['accounts'][$activeIndex])) {
                $cart['accounts'][$activeIndex]['domain_info'] = $domainInfo;
                $newAccountId                                  = $cart['accounts'][$activeIndex]['account_id'];
            } else {
                Log::warning('ClientCartController@setDomainForAccount: activeIndex definido pero la cuenta no existe. Creando nueva cuenta.', ['activeIndex' => $activeIndex, 'cart' => $cart]);
                $newAccountId              = (string) Str::uuid();
                $newAccount                = ['account_id' => $newAccountId, 'domain_info' => $domainInfo, 'primary_service' => null, 'additional_services' => []];
                $cart['accounts'][]        = $newAccount;
                $cart['active_account_id'] = $newAccountId;
            }
        }

        $request->session()->put('cart', $cart);
        Log::info('ClientCartController@setDomainForAccount: Carrito después de configurar dominio y ANTES de redirección.', [
            'session_cart_final' => $request->session()->get('cart'),
        ]);
        return redirect()->route('client.checkout.selectServices')->with('success', 'Dominio configurado en el carrito.');
    }

    public function setPrimaryServiceForAccount(Request $request): RedirectResponse
    {
        Log::info('ClientCartController@setPrimaryServiceForAccount: MÉTODO INVOCADO.');

        $validatedData = $request->validate([
            'product_id'           => 'required|integer|exists:products,id',
            'pricing_id'           => 'required|integer|exists:product_pricings,id',
            'configurable_options' => 'nullable|array',
            // Se podrían añadir reglas más específicas para el contenido de configurable_options aquí,
            // por ejemplo, 'configurable_options.*' => 'integer|exists:configurable_options,id'
            // si se espera que los valores sean IDs de opciones existentes.
        ]);

        Log::debug('ClientCartController@setPrimaryServiceForAccount: Iniciando.', [
            'session_cart_initial' => session('cart', 'No cart in session'), // Usar session()
            'request_payload'      => $request->all(),
            'validated_data'       => $validatedData,
        ]);

        $cart              = session('cart', $this->initializeCart()); // Usar session()
        $activeIndex       = $this->getActiveAccountIndex();           // Sin $request
        $activeAccountData = null;
        if ($activeIndex !== null && isset($cart['accounts'][$activeIndex])) {
            $activeAccountData = $cart['accounts'][$activeIndex];
        }

        Log::debug('ClientCartController@setPrimaryServiceForAccount: Estado de cuenta activa.', [
            'active_account_id_from_session'         => $cart['active_account_id'] ?? 'Not set',
            'retrieved_active_account_data_by_index' => $activeAccountData,
            'current_cart_accounts_count'            => count($cart['accounts'] ?? [])
        ]);

        if ($activeIndex === null) {
            Log::warning('ClientCartController@setPrimaryServiceForAccount: No se encontró índice de cuenta activa válido.', ['session_cart_on_failure' => session('cart')]);
            return back()->with('error', 'No hay una cuenta activa para añadir el servicio.');
        }
        if (! isset($cart['accounts'][$activeIndex])) {
            Log::error('ClientCartController@setPrimaryServiceForAccount: activeIndex está seteado pero la cuenta no existe.', ['activeIndex' => $activeIndex, 'session_cart_on_failure' => session('cart')]);
            $cart['active_account_id'] = null;
            $request->session()->put('cart', $cart);
            return back()->with('error', 'Error de consistencia en el carrito. Por favor, reinicia el proceso de compra.');
        }
        $account = &$cart['accounts'][$activeIndex];
        if (empty($account['domain_info'])) {
            return back()->withInput()->withErrors(['general_error' => 'La cuenta activa debe tener información de dominio configurada.']);
        }
        if (! empty($account['primary_service'])) {
            return back()->withInput()->withErrors(['general_error' => 'La cuenta activa ya tiene un servicio principal.']);
        }
        $product = Product::with('configurableOptionGroups.options')->find($validatedData['product_id']);
        $pricing = ProductPricing::find($validatedData['pricing_id']);
        if ($pricing->product_id != $product->id) {return back()->withInput()->withErrors(['pricing_id' => 'La configuración de precio no corresponde al producto seleccionado.']);}
        $allowedPrimaryServiceTypes = [1, 2, 7];
        if (! in_array($product->product_type_id, $allowedPrimaryServiceTypes)) {return back()->withInput()->withErrors(['product_id' => 'Este tipo de producto no puede ser un servicio principal.']);}

        $primaryServiceData = [
            'cart_item_id' => (string) Str::uuid(),
            'product_id'   => $product->id,
            'pricing_id'   => $pricing->id,
        ];
        if (! empty($validatedData['configurable_options'])) {
            $validConfigOptions  = [];
            $productConfigGroups = $product->configurableOptionGroups->keyBy('id');
            foreach ($validatedData['configurable_options'] as $groupId => $optionId) {
                // Validar que los IDs de grupo y opción sean numéricos antes de usarlos para buscar en la BD
                if (! is_numeric($groupId) || ! is_numeric($optionId)) {
                    return back()->withInput()->withErrors(['configurable_options' => "ID de grupo u opción inválido (no numérico): Grupo {$groupId} -> Opción {$optionId}."]);
                }
                if (! isset($productConfigGroups[$groupId])) {
                    return back()->withInput()->withErrors(['configurable_options' => "Grupo de opción configurable inválido: ID {$groupId}."]);
                }
                $group = $productConfigGroups[$groupId];
                if (! $group->options->contains('id', $optionId)) {
                    return back()->withInput()->withErrors(['configurable_options.' . $groupId => "Opción configurable inválida: ID {$optionId} para el grupo '{$group->name}'."]);
                }
                $validConfigOptions[(int) $groupId] = (int) $optionId;
            }
            $primaryServiceData['configurable_options'] = $validConfigOptions;
        } else {
            $primaryServiceData['configurable_options'] = null;
        }

        $account['primary_service'] = $primaryServiceData;
        $request->session()->put('cart', $cart);
        return back()->with('success', 'Servicio principal añadido al carrito.');
    }

    public function removeDomainFromAccount(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'account_id' => 'required|string',
        ]);

        $cart = session('cart', $this->initializeCart());

        // Buscar la cuenta por account_id
        $accountIndex = null;
        foreach ($cart['accounts'] as $index => $account) {
            if ($account['account_id'] === $validatedData['account_id']) {
                $accountIndex = $index;
                break;
            }
        }

        if ($accountIndex === null) {
            return back()->withErrors(['account_id' => 'Cuenta no encontrada en el carrito.']);
        }

        // Eliminar la información del dominio
        if (isset($cart['accounts'][$accountIndex]['domain_info'])) {
            unset($cart['accounts'][$accountIndex]['domain_info']);
        }

        // Si la cuenta no tiene servicios, eliminarla completamente
        $account = $cart['accounts'][$accountIndex];
        if (empty($account['primary_service']) && empty($account['additional_services'])) {
            unset($cart['accounts'][$accountIndex]);
            $cart['accounts'] = array_values($cart['accounts']); // Reindexar array

            // Si era la cuenta activa, cambiar a la primera disponible o null
            if ($cart['active_account_id'] === $validatedData['account_id']) {
                $cart['active_account_id'] = ! empty($cart['accounts']) ? $cart['accounts'][0]['account_id'] : null;
            }
        }

        session(['cart' => $cart]);

        return back()->with('success', 'Dominio eliminado del carrito.');
    }

    public function addItem(Request $request): RedirectResponse
    { /* ... con return back() ... */}
    public function updateItem(Request $request): RedirectResponse
    { /* ... con return back() ... */}
    public function removeItem(Request $request): RedirectResponse
    { /* ... con return back() ... */}
    public function clearCart(Request $request): RedirectResponse
    { /* ... con return back() ... */}
    public function setActiveAccount(Request $request): RedirectResponse
    { /* ... con return back() ... */}
}

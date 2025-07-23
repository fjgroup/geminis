<?php
namespace App\Domains\Orders\Infrastructure\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\CartOperationRequest;
use App\Http\Traits\ClientSecurityTrait;
use App\Domains\Products\Infrastructure\Persistence\Models\ConfigurableOption;
use App\Domains\Products\Infrastructure\Persistence\Models\ConfigurableOptionPricing;
use App\Domains\Orders\Infrastructure\Persistence\Models\OrderConfigurableOption;
use App\Domains\Products\Infrastructure\Persistence\Models\Product;
use App\Domains\Products\Infrastructure\Persistence\Models\ProductPricing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ClientCartController extends Controller
{
    use ClientSecurityTrait;
    private function initializeCart()
    {
        return [
            'accounts'          => [],
            'active_account_id' => null,
        ];
    }

    public function getCart(Request $request): JsonResponse
    {
        $cartData = $this->getCartData($request);
        return response()->json($cartData);
    }

    /**
     * Get cart data without JSON wrapper (for internal use)
     */
    public function getCartData(Request $request): array
    {
        $cart = $request->session()->get('cart', $this->initializeCart());

        // 🔍 DEBUG: Log acceso al carrito
        Log::info('🛒 ACCESO AL CARRITO', [
            'user_id'      => Auth::id(),
            'has_cart'     => ! ! $cart,
            'cart_summary' => $cart ? [
                'accounts_count'    => count($cart['accounts'] ?? []),
                'active_account_id' => $cart['active_account_id'] ?? null,
            ] : null,
            'session_id'   => session()->getId(),
        ]);

        if (! isset($cart['accounts']) || ! isset($cart['active_account_id'])) {
            $cart = $this->initializeCart();
            Log::info('🔄 CARRITO INICIALIZADO (estaba vacío o malformado)', [
                'user_id' => Auth::id(),
            ]);
        }

        foreach ($cart['accounts'] as &$account) {
            if (isset($account['domain_info']['product_id'], $account['domain_info']['pricing_id'])) {
                $product = Product::find($account['domain_info']['product_id']);
                $pricing = ProductPricing::find($account['domain_info']['pricing_id']);
                if ($product && $pricing && $pricing->product_id == $product->id) {
                    $productName = $product->name;
                    if (isset($account['domain_info']['tld_extension']) && $product->productType && $product->productType->name === 'Domain') {
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
                    $account['primary_service']['currency_code'] = $pricing->currency_code;

                    // Agregar información del ciclo de facturación
                    if ($pricing->billingCycle) {
                        $account['primary_service']['billing_cycle_name'] = $pricing->billingCycle->name;
                    }

                    // Usar el precio calculado dinámicamente si está disponible
                    if (isset($account['primary_service']['calculated_price']) && is_numeric($account['primary_service']['calculated_price'])) {
                        $account['primary_service']['price'] = (float) $account['primary_service']['calculated_price'];
                        Log::info('ClientCartController@getCartData: Usando precio calculado dinámicamente.', [
                            'product_id'       => $product->id,
                            'calculated_price' => $account['primary_service']['calculated_price'],
                        ]);
                    } else {
                        // Fallback: calcular precio usando método tradicional
                        $basePrice              = (float) $pricing->price;
                        $optionsPriceAdjustment = 0.0;

                        if (isset($account['primary_service']['configurable_options']) && is_array($account['primary_service']['configurable_options'])) {
                            foreach ($account['primary_service']['configurable_options'] as $groupId => $optionId) {
                                $optionPricing = ConfigurableOptionPricing::where('configurable_option_id', $optionId)
                                    ->where('billing_cycle_id', $pricing->billing_cycle_id)->first();
                                if ($optionPricing) {$optionsPriceAdjustment += (float) $optionPricing->price;}
                            }
                        }
                        $account['primary_service']['price'] = $basePrice + $optionsPriceAdjustment;

                        Log::info('ClientCartController@getCartData: Usando cálculo tradicional de precio.', [
                            'product_id'         => $product->id,
                            'base_price'         => $basePrice,
                            'options_adjustment' => $optionsPriceAdjustment,
                            'final_price'        => $account['primary_service']['price'],
                        ]);
                    }

                    // Enriquecer detalles de opciones configurables con precios y cantidades
                    if (isset($account['primary_service']['configurable_options']) && is_array($account['primary_service']['configurable_options'])) {
                        $enrichedOptions = [];
                        $billingCycleId  = $account['primary_service']['billing_cycle_id'] ?? $pricing->billing_cycle_id;

                        foreach ($account['primary_service']['configurable_options'] as $optionId => $optionData) {
                            $option = ConfigurableOption::with('group')->find($optionId);

                            if ($option && $option->group) {
                                // Obtener precio de la opción
                                $optionPricing = ConfigurableOptionPricing::where('configurable_option_id', $option->id)
                                    ->where('billing_cycle_id', 1) // Usar precio mensual como base
                                    ->first();

                                               // Determinar cantidad desde la estructura guardada
                                $quantity = 1; // Por defecto
                                if (is_array($optionData)) {
                                    // Nueva estructura: ['option_id' => X, 'group_id' => Y, 'value' => Z, 'quantity' => Q]
                                    $quantity = isset($optionData['quantity']) ? (int) $optionData['quantity'] : 1;
                                } else {
                                    // Estructura simple: optionId => value
                                    $quantity = is_numeric($optionData) ? (int) $optionData : 1;
                                }

                                $unitPrice  = $optionPricing ? (float) $optionPricing->price : 0.0;
                                $totalPrice = $unitPrice * $quantity;

                                $enrichedOptions[] = [
                                    'group_id'      => $option->group->id,
                                    'group_name'    => $option->group->name,
                                    'option_id'     => $option->id,
                                    'option_name'   => $option->name,
                                    'quantity'      => $quantity,
                                    'unit_price'    => $unitPrice,
                                    'total_price'   => $totalPrice,
                                    'currency_code' => $pricing->currency_code,
                                ];

                                Log::debug('ClientCartController@getCartData: Opción configurada enriquecida.', [
                                    'option_name' => $option->name,
                                    'quantity'    => $quantity,
                                    'unit_price'  => $unitPrice,
                                    'total_price' => $totalPrice,
                                ]);
                            } else {
                                $enrichedOptions[] = [
                                    'group_id'      => 'unknown',
                                    'group_name'    => "Opción no encontrada",
                                    'option_id'     => $optionId,
                                    'option_name'   => "ID: {$optionId}",
                                    'quantity'      => 1,
                                    'unit_price'    => 0.0,
                                    'total_price'   => 0.0,
                                    'currency_code' => $pricing->currency_code,
                                ];
                            }
                        }
                        $account['primary_service']['configurable_options_details'] = $enrichedOptions;

                        Log::info('ClientCartController@getCartData: Opciones configurables procesadas desde carrito.', [
                            'product_id'    => $product->id,
                            'total_options' => count($enrichedOptions),
                        ]);
                    }

                    // Intentar cargar opciones configurables desde la tabla dedicada (nueva implementación)
                    if (isset($account['primary_service']['cart_item_id'])) {
                        $configurableOptions = OrderConfigurableOption::where('cart_item_id', $account['primary_service']['cart_item_id'])
                            ->where('is_active', true)
                            ->get();

                        if ($configurableOptions->isNotEmpty()) {
                            $enrichedOptionsFromDB = [];

                            foreach ($configurableOptions as $configOption) {
                                $enrichedOptionsFromDB[] = [
                                    'group_id'      => $configOption->configurable_option_group_id,
                                    'group_name'    => $configOption->group_name,
                                    'option_id'     => $configOption->configurable_option_id,
                                    'option_name'   => $configOption->option_name,
                                    'quantity'      => $configOption->quantity,
                                    'unit_price'    => $configOption->unit_price,
                                    'total_price'   => $configOption->total_price,
                                    'currency_code' => $configOption->currency_code,
                                ];
                            }

                            // Sobrescribir con datos de la BD (más confiables)
                            $account['primary_service']['configurable_options_details'] = $enrichedOptionsFromDB;

                            Log::info('ClientCartController@getCartData: Opciones configurables cargadas desde BD.', [
                                'product_id'    => $product->id,
                                'cart_item_id'  => $account['primary_service']['cart_item_id'],
                                'total_options' => count($enrichedOptionsFromDB),
                            ]);
                        }
                    }
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

        return ['status' => 'success', 'cart' => $cart];
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

    // Método findItemInAccount se implementará cuando sea necesario para operaciones específicas de items

    public function setDomainForAccount(CartOperationRequest $request): RedirectResponse
    {
        // Aplicar rate limiting
        $this->applyRateLimit('set_domain', 20, 1);

        // Verificar que el usuario puede realizar la acción
        $this->ensureUserCanPerformAction();

        $validatedData = $request->validated();
        $cart          = session('cart', $this->initializeCart()); // Uso session()

        // Validar integridad del carrito
        if (! $this->validateCartPriceIntegrity($cart)) {
            $this->logUserActivity('cart_integrity_violation', ['action' => 'set_domain']);
            return back()->withErrors(['error' => 'Se detectó una inconsistencia en el carrito. Por favor, inténtalo de nuevo.']);
        }

        $activeIndex  = $this->getActiveAccountIndex(); // Sin $request
        $newAccountId = null;

        $genericProduct = Product::find($validatedData['product_id']);
        $genericPricing = ProductPricing::find($validatedData['pricing_id']);

        if (! $genericProduct || ! $genericProduct->productType || $genericProduct->productType->name !== 'Domain') {
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

    public function setPrimaryServiceForAccount(CartOperationRequest $request): RedirectResponse
    {
        // Aplicar rate limiting
        $this->applyRateLimit('set_service', 15, 1);

        // Debug: Log de datos recibidos
        Log::info('🔍 setPrimaryServiceForAccount - Datos recibidos:', [
            'all_request_data' => $request->all(),
            'service_notes'    => $request->input('service_notes'),
            'calculated_price' => $request->input('calculated_price'),
            'billing_cycle_id' => $request->input('billing_cycle_id'),
        ]);

        // Verificar que el usuario puede realizar la acción
        $this->ensureUserCanPerformAction();

        Log::info('ClientCartController@setPrimaryServiceForAccount: MÉTODO INVOCADO.');

        $validatedData = $request->validated();

        Log::debug('ClientCartController@setPrimaryServiceForAccount: Iniciando.', [
            'session_cart_initial' => session('cart', 'No cart in session'), // Usar session()
            'request_payload'      => $request->all(),
            'validated_data'       => $validatedData,
        ]);

        // Debug específico para opciones configurables
        Log::info('🔍 DEBUGGING - Opciones configurables recibidas:', [
            'configurable_options_raw'   => $validatedData['configurable_options'] ?? 'No recibidas',
            'configurable_options_count' => count($validatedData['configurable_options'] ?? []),
            'configurable_options_keys'  => array_keys($validatedData['configurable_options'] ?? [])
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
        // Si ya existe un servicio principal, lo reemplazamos
        if (! empty($account['primary_service'])) {
            Log::info('Reemplazando servicio principal existente', [
                'user_id'        => Auth::id(),
                'account_id'     => $cart['active_account_id'],
                'old_product_id' => $account['primary_service']['product_id'] ?? null,
                'new_product_id' => $validatedData['product_id'],
            ]);
        }
        $product = Product::with('configurableOptionGroups.options')->find($validatedData['product_id']);
        $pricing = ProductPricing::find($validatedData['pricing_id']);
        if ($pricing->product_id != $product->id) {return back()->withInput()->withErrors(['pricing_id' => 'La configuración de precio no corresponde al producto seleccionado.']);}
        $allowedPrimaryServiceTypes = [1, 2, 7];
        if (! in_array($product->product_type_id, $allowedPrimaryServiceTypes)) {return back()->withInput()->withErrors(['product_id' => 'Este tipo de producto no puede ser un servicio principal.']);}

        $primaryServiceData = [
            'cart_item_id'     => (string) Str::uuid(),
            'product_id'       => $product->id,
            'pricing_id'       => $pricing->id,
            'calculated_price' => $validatedData['calculated_price'] ?? null,
            'billing_cycle_id' => $validatedData['billing_cycle_id'] ?? null,
            'service_notes'    => $validatedData['service_notes'] ?? 'Configuración estándar',
        ];
        // SOLUCIÓN CREATIVA: Procesar opciones configurables desde JSON
        $validConfigOptions      = [];
        $configurableOptionsData = null;

        // Intentar obtener opciones desde el campo JSON (nueva implementación)
        if (! empty($validatedData['configurable_options_json'])) {
            try {
                $configurableOptionsData = json_decode($validatedData['configurable_options_json'], true);
                Log::info('🎯 Opciones configurables decodificadas desde JSON:', [
                    'json_raw'      => $validatedData['configurable_options_json'],
                    'decoded_data'  => $configurableOptionsData,
                    'decoded_count' => is_array($configurableOptionsData) ? count($configurableOptionsData) : 0,
                ]);
            } catch (\Exception $e) {
                Log::error('Error decodificando opciones configurables JSON:', ['error' => $e->getMessage()]);
            }
        }

        // Fallback: usar el método original si no hay JSON
        if (empty($configurableOptionsData) && ! empty($validatedData['configurable_options'])) {
            $configurableOptionsData = $validatedData['configurable_options'];
            Log::info('🔄 Usando opciones configurables del método original');
        }

        if (! empty($configurableOptionsData)) {
            $productConfigGroups = $product->configurableOptionGroups->keyBy('id');

            foreach ($configurableOptionsData as $optionKey => $value) {
                // Saltar claves de cantidad (terminan en _quantity)
                if (str_ends_with($optionKey, '_quantity')) {
                    continue;
                }

                $optionId = (int) $optionKey;
                $option   = ConfigurableOption::with('group')->find($optionId);

                if (! $option) {
                    continue; // Saltar opciones que no existen
                }

                $group = $option->group;

                // Verificar que el grupo pertenece al producto
                if (! $productConfigGroups->contains('id', $group->id)) {
                    continue; // Saltar opciones de grupos que no pertenecen al producto
                }

                // Procesar según el tipo de opción
                switch ($option->option_type) {
                    case 'checkbox':
                        // Para checkbox, solo guardar si está marcado
                        if ($value) {
                            $validConfigOptions[$optionId] = [
                                'option_id' => $option->id,
                                'group_id'  => $group->id,
                                'value'     => true,
                                'quantity'  => 1,
                            ];
                        }
                        break;

                    case 'quantity':
                        // Para quantity, buscar la cantidad en la clave separada
                        if ($value) {
                            $quantityKey = $optionId . '_quantity';
                            $quantity    = isset($validatedData['configurable_options'][$quantityKey])
                            ? (int) $validatedData['configurable_options'][$quantityKey]
                            : 1;

                            if ($quantity > 0) {
                                if ($option->min_value && $quantity < $option->min_value) {
                                    return back()->withInput()->withErrors([
                                        'configurable_options.' . $optionId => "La cantidad mínima para '{$option->name}' es {$option->min_value}.",
                                    ]);
                                }
                                if ($option->max_value && $quantity > $option->max_value) {
                                    return back()->withInput()->withErrors([
                                        'configurable_options.' . $optionId => "La cantidad máxima para '{$option->name}' es {$option->max_value}.",
                                    ]);
                                }

                                $validConfigOptions[$optionId] = [
                                    'option_id' => $option->id,
                                    'group_id'  => $group->id,
                                    'value'     => $quantity,
                                    'quantity'  => $quantity,
                                ];
                            }
                        }
                        break;

                    case 'radio':
                    case 'dropdown':
                        // Para radio/dropdown, guardar la selección
                        if ($value) {
                            $validConfigOptions[$optionId] = [
                                'option_id' => $option->id,
                                'group_id'  => $group->id,
                                'value'     => $value,
                                'quantity'  => 1,
                            ];
                        }
                        break;

                    default:
                        // Para otros tipos, guardar el valor tal como viene
                        if ($value) {
                            $validConfigOptions[$optionId] = [
                                'option_id' => $option->id,
                                'group_id'  => $group->id,
                                'value'     => $value,
                                'quantity'  => 1,
                            ];
                        }
                        break;
                }
            }

            // Verificar opciones obligatorias
            foreach ($productConfigGroups as $group) {
                if ($group->is_required) {
                    $hasRequiredOption = false;
                    foreach ($group->options as $option) {
                        if (isset($validConfigOptions[$option->id])) {
                            $hasRequiredOption = true;
                            break;
                        }
                    }

                    if (! $hasRequiredOption) {
                        return back()->withInput()->withErrors([
                            'configurable_options' => "Debes seleccionar una opción para el grupo obligatorio '{$group->name}'.",
                        ]);
                    }
                }
            }

            $primaryServiceData['configurable_options'] = $validConfigOptions;
        } else {
            $primaryServiceData['configurable_options'] = null;
        }

        $account['primary_service'] = $primaryServiceData;

        // Guardar opciones configurables en la tabla dedicada
        if (! empty($validConfigOptions)) {
            $this->saveConfigurableOptionsToDatabase($validConfigOptions, $primaryServiceData, $pricing, $request);
        }

        // Log del precio calculado para verificación
        Log::info('ClientCartController@setPrimaryServiceForAccount: Servicio guardado.', [
            'product_id'       => $product->id,
            'pricing_id'       => $pricing->id,
            'calculated_price' => $validatedData['calculated_price'] ?? 'No enviado',
            'billing_cycle_id' => $validatedData['billing_cycle_id'] ?? 'No enviado',
            'service_notes'    => $validatedData['service_notes'] ?? 'Sin notas',
        ]);

        $request->session()->put('cart', $cart);
        return back()->with('success', 'Servicio principal añadido al carrito.');
    }

    public function removeDomainFromAccount(CartOperationRequest $request): RedirectResponse
    {
        // Aplicar rate limiting
        $this->applyRateLimit('remove_domain', 10, 1);

        // Verificar que el usuario puede realizar la acción
        $this->ensureUserCanPerformAction();

        $validatedData = $request->validated();

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

    public function removePrimaryServiceFromAccount(CartOperationRequest $request): RedirectResponse
    {
        // Aplicar rate limiting
        $this->applyRateLimit('remove_service', 10, 1);

        // Verificar que el usuario puede realizar la acción
        $this->ensureUserCanPerformAction();

        $validatedData = $request->validated();

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

        // Eliminar el servicio principal
        if (isset($cart['accounts'][$accountIndex]['primary_service'])) {
            unset($cart['accounts'][$accountIndex]['primary_service']);
        }

        session(['cart' => $cart]);

        return back()->with('success', 'Servicio principal eliminado del carrito.');
    }

    // Métodos adicionales del carrito se implementarán según necesidades futuras

    /**
     * Guardar opciones configurables en la tabla dedicada
     */
    private function saveConfigurableOptionsToDatabase(array $validConfigOptions, array $primaryServiceData, $pricing, $request): void
    {
        try {
            // Limpiar opciones anteriores para este cart_item_id
            OrderConfigurableOption::where('cart_item_id', $primaryServiceData['cart_item_id'])->delete();

            foreach ($validConfigOptions as $optionId => $optionData) {
                $option = ConfigurableOption::with('group')->find($optionId);

                if (! $option || ! $option->group) {
                    continue;
                }

                // Obtener precio unitario
                $optionPricing = ConfigurableOptionPricing::where('configurable_option_id', $option->id)
                    ->where('billing_cycle_id', 1) // Usar precio mensual como base
                    ->first();

                $unitPrice  = $optionPricing ? (float) $optionPricing->price : 0.0;
                $quantity   = (float) $optionData['quantity'];
                $totalPrice = $unitPrice * $quantity;

                // Crear registro en la tabla
                OrderConfigurableOption::create([
                    'cart_item_id'                 => $primaryServiceData['cart_item_id'],
                    'product_id'                   => $primaryServiceData['product_id'],
                    'client_email'                 => Auth::user()->email ?? 'guest@example.com',
                    'configurable_option_id'       => $option->id,
                    'configurable_option_group_id' => $option->group->id,
                    'option_name'                  => $option->name,
                    'group_name'                   => $option->group->name,
                    'quantity'                     => $quantity,
                    'option_value'                 => $optionData['value'],
                    'unit_price'                   => $unitPrice,
                    'total_price'                  => $totalPrice,
                    'currency_code'                => $pricing->currency_code,
                    'billing_cycle_id'             => $pricing->billing_cycle_id,
                    'is_active'                    => true,
                    'metadata'                     => [
                        'cart_session_id' => $request->session()->getId(),
                        'created_at_cart' => now()->toISOString(),
                    ],
                ]);

                Log::info('OrderConfigurableOption creada:', [
                    'option_name' => $option->name,
                    'group_name'  => $option->group->name,
                    'quantity'    => $quantity,
                    'unit_price'  => $unitPrice,
                    'total_price' => $totalPrice,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error guardando opciones configurables en BD:', [
                'error'        => $e->getMessage(),
                'cart_item_id' => $primaryServiceData['cart_item_id'],
            ]);
        }
    }
}

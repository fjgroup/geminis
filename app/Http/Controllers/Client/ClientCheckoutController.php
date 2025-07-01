<?php
namespace App\Http\Controllers\Client;

use App\Actions\Client\PlaceOrderAction;
use App\Http\Controllers\Client\ClientCartController;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductPricing;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

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
        if (! $client) {
            return redirect()->route('login')->with('error', 'Por favor, inicia sesión para continuar.');
        }

        $validatedData = $request->validate([
            'notes_to_client'     => 'nullable|string|max:2000',
            'payment_method_slug' => 'nullable|string',
        ]);

        $additionalData = [
            'notes_to_client'     => $validatedData['notes_to_client'] ?? null,
            'payment_method_slug' => $validatedData['payment_method_slug'] ?? null,
            'ip_address'          => $request->ip(),
        ];

        try {
            $invoice = $placeOrderAction->execute($client, $additionalData);

            if (! $invoice) {
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
                'client_id'       => $client->id,
                'exception_trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('client.checkout.confirm')
                ->with('error', 'Ocurrió un error inesperado al procesar su pedido: ' . $e->getMessage());
        }
    }

    public function showSelectDomainPage(Request $request): InertiaResponse
    {
        // 🔍 DEBUG: Log al entrar a selectDomain - verificar purchase_context
        $purchaseContext = session('purchase_context');
        $cart            = session('cart');

        Log::info('🔍 ENTRADA A selectDomain - verificando contextos', [
            'user_id'              => Auth::id(),
            'has_purchase_context' => ! ! $purchaseContext,
            'purchase_context'     => $purchaseContext,
            'has_cart'             => ! ! $cart,
            'cart_summary'         => $cart ? [
                'accounts_count'    => count($cart['accounts'] ?? []),
                'active_account_id' => $cart['active_account_id'] ?? null,
            ] : null,
            'session_id'           => session()->getId(),
        ]);

        // 🔧 TRANSFERIR purchase_context al carrito si existe
        if ($purchaseContext && (! $cart || empty($cart['accounts']))) {
            Log::info('🔄 TRANSFIRIENDO purchase_context al carrito', [
                'user_id'          => Auth::id(),
                'purchase_context' => $purchaseContext,
            ]);

            $cart = $this->transferPurchaseContextToCart($purchaseContext);
            session(['cart' => $cart]);

            Log::info('✅ PURCHASE_CONTEXT transferido al carrito', [
                'user_id'          => Auth::id(),
                'new_cart_summary' => [
                    'accounts_count'    => count($cart['accounts'] ?? []),
                    'active_account_id' => $cart['active_account_id'] ?? null,
                ],
            ]);

            // Limpiar purchase_context ya que se transfirió al carrito
            session()->forget('purchase_context');
        }

        $genericDomainProductId = 1;
        $genericDomainPricingId = 1; // Ambos IDs son 1

        Log::info('Usando IDs de dominio genéricos para SelectDomainPage', [
            'genericDomainProductId' => $genericDomainProductId,
            'genericDomainPricingId' => $genericDomainPricingId,
        ]);

        // Buscar el producto de dominio por tipo en lugar de ID hardcodeado
        $domainProduct = Product::with('productType')
            ->whereHas('productType', function ($query) {
                $query->where('name', 'Domain');
            })
            ->where('id', $genericDomainProductId)
            ->first();

        $productExists = $domainProduct !== null;
        $pricingExists = ProductPricing::where('id', $genericDomainPricingId)
            ->where('product_id', $genericDomainProductId)
            ->exists();

        $finalProductId = null;
        $finalPricingId = null;

        if (! $productExists || ! $pricingExists) {
            Log::error('El ID del Producto de Dominio Genérico o el ID del Pricing Genérico no se encontraron en la BD, o no son del tipo/producto correcto.', [
                'genericDomainProductId_val' => $genericDomainProductId,
                'genericDomainPricingId_val' => $genericDomainPricingId,
                'productExists'              => $productExists,
                'pricingExists'              => $pricingExists,
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

    public function showSelectServicesPage(Request $request): InertiaResponse | RedirectResponse
    {
        Log::debug('ClientCheckoutController@showSelectServicesPage: Carrito al cargar la página.', [
            'session_cart_on_load' => session('cart', 'No cart in session'),
        ]);

        $cartController = app(ClientCartController::class);
        $cartData       = $cartController->getCartData($request);
        $cart           = $cartData['cart'] ?? null;

        if (empty($cart) || empty($cart['accounts']) || empty($cart['active_account_id'])) {
            Log::warning('ClientCheckoutController@showSelectServicesPage: Carrito vacío o sin cuenta activa. Redirigiendo a selectDomain.', ['cart' => $cart]);
            return redirect()->route('client.checkout.selectDomain')->with('info', 'Por favor, selecciona o configura un dominio primero.');
        }

        $activeAccount              = null;
        $activeAccountIdFromSession = $cart['active_account_id']; // Guardar antes del bucle para loguear
        foreach ($cart['accounts'] as $account) {
            if ($account['account_id'] === $activeAccountIdFromSession) {
                $activeAccount = $account;
                break;
            }
        }

        if (! $activeAccount || empty($activeAccount['domain_info']['domain_name'])) {
            Log::warning('ClientCheckoutController@showSelectServicesPage: Cuenta activa no encontrada o sin nombre de dominio. Redirigiendo a selectDomain.', [
                'active_account_id_from_session' => $activeAccountIdFromSession,
                'active_account_found_in_loop'   => $activeAccount,
                'cart'                           => $cart,
            ]);
            return redirect()->route('client.checkout.selectDomain')->with('info', 'Por favor, configura un dominio para la cuenta activa antes de seleccionar servicios.');
        }

        $mainServiceTypeIds = [1, 2, 7];
        $sslTypeIds         = [4];
        $licenseTypeIds     = [6];

        $mainServiceProducts = Product::whereIn('product_type_id', $mainServiceTypeIds)
            ->where('status', 'active')
            ->with([
                'pricings.billingCycle',
                'productType',
                'configurableOptionGroups' => function ($query) {
                    $query->withPivot('base_quantity', 'display_order', 'is_required')
                        ->orderBy('product_configurable_option_groups.display_order');
                },
                'configurableOptionGroups.options.pricings.billingCycle',
            ])
            ->orderBy('display_order')
            ->get()
            ->map(function ($product) {
                return [
                    'id'                         => $product->id,
                    'name'                       => $product->name,
                    'description'                => $product->description,
                    'landing_page_slug'          => $product->landing_page_slug,
                    'features_list'              => $product->features_list,
                    'call_to_action_text'        => $product->call_to_action_text,
                    // Recursos base dinámicos
                    'base_resources'             => $product->base_resources ?? [],
                    // Precios y opciones
                    'pricings'                   => $product->pricings->map(function ($pricing) {
                        return [
                            'id'            => $pricing->id,
                            'price'         => $pricing->price,
                            'setup_fee'     => $pricing->setup_fee,
                            'currency_code' => $pricing->currency_code,
                            'billing_cycle' => [
                                'id'   => $pricing->billingCycle->id,
                                'name' => $pricing->billingCycle->name,
                                'days' => $pricing->billingCycle->days,
                                // 'discount_percentage' => $pricing->billingCycle->discount_percentage, // TODO: Implementar descuentos
                            ],
                        ];
                    }),
                    'configurable_option_groups' => $product->configurableOptionGroups->map(function ($group) {
                        return [
                            'id'            => $group->id,
                            'name'          => $group->name,
                            'description'   => $group->description,
                            'is_required'   => $group->pivot ? $group->pivot->is_required : false, // Desde la tabla pivote
                            'display_order' => $group->pivot ? $group->pivot->display_order : 0,   // Desde la tabla pivote
                            'base_quantity' => $group->pivot ? $group->pivot->base_quantity : 0,   // Desde la tabla pivote
                            'options'       => $group->options->map(function ($option) {
                                return [
                                    'id'            => $option->id,
                                    'name'          => $option->name,
                                    'description'   => $option->description,
                                    'option_type'   => $option->option_type,
                                    'value'         => $option->value,
                                    'is_required'   => $option->is_required,
                                    'min_value'     => $option->min_value,
                                    'max_value'     => $option->max_value,
                                    'display_order' => $option->display_order,
                                    'pricings'      => $option->pricings->map(function ($pricing) {
                                        return [
                                            'id'            => $pricing->id,
                                            'price'         => $pricing->price,
                                            'setup_fee'     => $pricing->setup_fee,
                                            'currency_code' => $pricing->currency_code,
                                            'billing_cycle' => [
                                                'id'   => $pricing->billingCycle->id,
                                                'name' => $pricing->billingCycle->name,
                                                'days' => $pricing->billingCycle->days,
                                                // 'discount_percentage' => $pricing->billingCycle->discount_percentage, // TODO: Implementar descuentos
                                            ],
                                        ];
                                    }),
                                ];
                            }),
                        ];
                    }),
                ];
            });

        $sslProducts = Product::whereIn('product_type_id', $sslTypeIds)
            ->where('status', 'active')->with(['pricings.billingCycle', 'productType'])->orderBy('display_order')->get()
            ->map(function ($product) {
                return [
                    'id'          => $product->id,
                    'name'        => $product->name,
                    'description' => $product->description,
                    'pricings'    => $product->pricings->map(function ($pricing) {
                        return [
                            'id'            => $pricing->id,
                            'price'         => $pricing->price,
                            'setup_fee'     => $pricing->setup_fee,
                            'currency_code' => $pricing->currency_code,
                            'billing_cycle' => [
                                'id'   => $pricing->billingCycle->id,
                                'name' => $pricing->billingCycle->name,
                                'days' => $pricing->billingCycle->days,
                                // 'discount_percentage' => $pricing->billingCycle->discount_percentage, // TODO: Implementar descuentos
                            ],
                        ];
                    }),
                ];
            });

        $licenseProducts = Product::whereIn('product_type_id', $licenseTypeIds)
            ->where('status', 'active')->with(['pricings.billingCycle', 'productType'])->orderBy('display_order')->get()
            ->map(function ($product) {
                return [
                    'id'          => $product->id,
                    'name'        => $product->name,
                    'description' => $product->description,
                    'pricings'    => $product->pricings->map(function ($pricing) {
                        return [
                            'id'            => $pricing->id,
                            'price'         => $pricing->price,
                            'setup_fee'     => $pricing->setup_fee,
                            'currency_code' => $pricing->currency_code,
                            'billing_cycle' => [
                                'id'   => $pricing->billingCycle->id,
                                'name' => $pricing->billingCycle->name,
                                'days' => $pricing->billingCycle->days,
                                // 'discount_percentage' => $pricing->billingCycle->discount_percentage, // TODO: Implementar descuentos
                            ],
                        ];
                    }),
                ];
            });

        // Log para debug de productos cargados
        Log::debug('ClientCheckoutController@showSelectServicesPage: Productos cargados.', [
            'mainServiceProducts_count' => count($mainServiceProducts),
            'sslProducts_count'         => count($sslProducts),
            'licenseProducts_count'     => count($licenseProducts),
            'activeAccount'             => $activeAccount,
            'first_product_sample'      => $mainServiceProducts->first(),
        ]);

        // Obtener datos para cálculos en el frontend
        $discountPercentages = \App\Models\DiscountPercentage::with(['product', 'billingCycle'])
            ->where('is_active', true)
            ->get()
            ->map(function ($discount) {
                return [
                    'product_id'       => $discount->product_id,
                    'billing_cycle_id' => $discount->billing_cycle_id,
                    'percentage'       => (float) $discount->percentage,
                    'name'             => $discount->name,
                    'description'      => $discount->description,
                ];
            })
            ->keyBy(function ($discount) {
                return "{$discount['product_id']}-{$discount['billing_cycle_id']}";
            });

        // Obtener precios de opciones configurables (SOLO MENSUALES - ciclo 1)
        $configurableOptionPrices = \App\Models\ConfigurableOptionPricing::with(['option', 'billingCycle'])
            ->where('is_active', true)
            ->where('billing_cycle_id', 1) // Solo precios mensuales
            ->get()
            ->map(function ($pricing) {
                return [
                    'option_id'        => $pricing->configurable_option_id,
                    'billing_cycle_id' => $pricing->billing_cycle_id,
                    'price'            => (float) $pricing->price,
                    'setup_fee'        => (float) $pricing->setup_fee,
                    'currency_code'    => $pricing->currency_code,
                ];
            })
            ->keyBy('option_id')
            ->map(function ($pricing) {
                // Crear estructura simple: option_id => {1: pricing_data}
                return [1 => $pricing];
            });

        try {
            return Inertia::render('Client/Checkout/SelectServicesPage', [
                'initialCart'              => $cart,
                'mainServiceProducts'      => $mainServiceProducts,
                'sslProducts'              => $sslProducts,
                'licenseProducts'          => $licenseProducts,
                'discountPercentages'      => $discountPercentages,
                'configurableOptionPrices' => $configurableOptionPrices,
            ]);
        } catch (\Exception $e) {
            Log::error('ClientCheckoutController@showSelectServicesPage: Error al renderizar página.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('client.checkout.selectDomain')
                ->with('error', 'Error al cargar la página de servicios. Por favor, inténtalo de nuevo.');
        }
    }

    public function showConfirmOrderPage(Request $request): InertiaResponse | RedirectResponse
    {
        // Añadir log similar para esta página también
        Log::debug('ClientCheckoutController@showConfirmOrderPage: Carrito al cargar la página.', [
            'session_cart_on_load' => session('cart', 'No cart in session'),
        ]);

        $cartController = app(ClientCartController::class);
        $cartData       = $cartController->getCart($request)->getData(true);
        $cart           = $cartData['cart'] ?? null;

        if (empty($cart) || empty($cart['accounts'])) {
            Log::warning('ClientCheckoutController@showConfirmOrderPage: Carrito vacío. Redirigiendo al dashboard.', ['cart' => $cart]);
            return redirect()->route('client.dashboard')->with('info', 'Tu carrito está vacío. Por favor, añade productos antes de confirmar el pedido.');
        }

        $hasBillableItem = false;
        foreach ($cart['accounts'] as $account) {
            if (! empty($account['domain_info']['product_id']) || ! empty($account['primary_service']) || ! empty($account['additional_services'])) {
                $hasBillableItem = true;
                break;
            }
        }
        if (! $hasBillableItem && ! $this->cartHasOnlyDomainRegistrationWithoutProduct($cart)) {
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

    /**
     * Transfer purchase_context to cart structure
     */
    private function transferPurchaseContextToCart(array $purchaseContext): array
    {
        // Buscar el producto basado en el product_slug del purchase_context
        $product = Product::where('slug', $purchaseContext['product_slug'])->first();

        if (! $product) {
            Log::error('Producto no encontrado para purchase_context', [
                'product_slug'     => $purchaseContext['product_slug'],
                'purchase_context' => $purchaseContext,
            ]);
            // Retornar carrito vacío si no se encuentra el producto
            return [
                'accounts'          => [],
                'active_account_id' => null,
            ];
        }

        // Buscar el primer pricing disponible para el producto
        $pricing = $product->pricings()->first();

        if (! $pricing) {
            Log::error('No se encontró pricing para el producto', [
                'product_id'   => $product->id,
                'product_slug' => $product->slug,
            ]);
            // Retornar carrito vacío si no hay pricing
            return [
                'accounts'          => [],
                'active_account_id' => null,
            ];
        }

        // Generar UUID para la cuenta
        $accountId = (string) \Illuminate\Support\Str::uuid();

        // Crear estructura del carrito con el producto del purchase_context
        $cart = [
            'accounts'          => [
                [
                    'account_id'          => $accountId,
                    'domain_info'         => null, // Se configurará en selectDomain
                    'primary_service'     => [
                        'cart_item_id'         => (string) \Illuminate\Support\Str::uuid(),
                        'product_id'           => $product->id,
                        'pricing_id'           => $pricing->id,
                        'calculated_price'     => null,
                        'billing_cycle_id'     => $pricing->billing_cycle_id,
                        'service_notes'        => 'Servicio seleccionado desde landing page',
                        'configurable_options' => [],
                    ],
                    'additional_services' => [],
                ],
            ],
            'active_account_id' => $accountId,
        ];

        Log::info('Carrito creado desde purchase_context', [
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'pricing_id'   => $pricing->id,
            'account_id'   => $accountId,
        ]);

        return $cart;
    }
}

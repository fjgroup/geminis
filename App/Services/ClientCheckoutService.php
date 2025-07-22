<?php

namespace App\Services;

use App\Actions\Client\PlaceOrderAction;
use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * Servicio para la gestión del checkout del cliente
 *
 * Extrae la lógica de negocio del ClientCheckoutController aplicando el SRP
 * Reutiliza servicios existentes para evitar duplicidad
 */
class ClientCheckoutService
{
    public function __construct(
        private PlaceOrderAction $placeOrderAction,
        private ProductService $productService
    ) {}

    /**
     * Procesar orden actual del cliente
     */
    public function processCurrentOrder(User $client, array $data): array
    {
        try {
            $additionalData = [
                'notes_to_client' => $data['notes_to_client'] ?? null,
                'payment_method_slug' => $data['payment_method_slug'] ?? null,
                'ip_address' => $data['ip_address'] ?? null,
            ];

            $invoice = $this->placeOrderAction->execute($client, $additionalData);

            if (!$invoice) {
                Log::error("PlaceOrderAction returned null for client ID {$client->id} with cart.");

                return [
                    'success' => false,
                    'message' => 'No se pudo procesar su pedido. Por favor, inténtelo de nuevo.'
                ];
            }

            Log::info('Orden procesada exitosamente', [
                'client_id' => $client->id,
                'invoice_id' => $invoice->id
            ]);

            return [
                'success' => true,
                'data' => $invoice,
                'message' => '¡Pedido realizado con éxito! Factura generada.'
            ];

        } catch (\Exception $e) {
            Log::error("Error al procesar el pedido para el cliente ID {$client->id}", [
                'client_id' => $client->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Ocurrió un error inesperado al procesar su pedido: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener datos para la página de selección de dominio
     */
    public function getDomainSelectionData(): array
    {
        try {
            // Transferir purchase_context al carrito si existe
            $this->transferPurchaseContextToCartIfNeeded();

            $genericDomainProductId = 1;
            $genericDomainPricingId = 1;

            // Buscar el producto de dominio por tipo
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

            if (!$productExists || !$pricingExists) {
                Log::error('El ID del Producto de Dominio Genérico o el ID del Pricing Genérico no se encontraron', [
                    'genericDomainProductId' => $genericDomainProductId,
                    'genericDomainPricingId' => $genericDomainPricingId,
                    'productExists' => $productExists,
                    'pricingExists' => $pricingExists,
                ]);

                return [
                    'success' => false,
                    'message' => 'Error al cargar datos de dominio'
                ];
            }

            return [
                'success' => true,
                'data' => [
                    'genericDomainProductId' => $genericDomainProductId,
                    'genericDomainPricingId' => $genericDomainPricingId,
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Error obteniendo datos de selección de dominio', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error al cargar la página de selección de dominio'
            ];
        }
    }

    /**
     * Obtener datos para la página de selección de servicios
     */
    public function getServicesSelectionData(array $cartData): array
    {
        try {
            $cart = $cartData['cart'] ?? null;

            // Validar carrito
            $validation = $this->validateCartForServices($cart);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'redirect' => 'client.checkout.selectDomain',
                    'message' => $validation['message']
                ];
            }

            $activeAccount = $validation['activeAccount'];

            // Obtener productos por categorías específicas del checkout
            $mainServiceProducts = $this->getProductsByTypes([1, 2, 7]);
            $sslProducts = $this->getProductsByTypes([4]);
            $licenseProducts = $this->getProductsByTypes([6]);

            // Obtener datos adicionales específicos del checkout
            $discountPercentages = $this->getDiscountPercentages();
            $configurableOptionPrices = $this->getConfigurableOptionPrices();

            return [
                'success' => true,
                'data' => [
                    'activeAccount' => $activeAccount,
                    'mainServiceProducts' => $mainServiceProducts,
                    'sslProducts' => $sslProducts,
                    'licenseProducts' => $licenseProducts,
                    'discountPercentages' => $discountPercentages,
                    'configurableOptionPrices' => $configurableOptionPrices,
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Error obteniendo datos de selección de servicios', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error al cargar la página de selección de servicios'
            ];
        }
    }

    /**
     * Obtener datos para la página de confirmación
     */
    public function getConfirmationData(array $cartData): array
    {
        try {
            $cart = $cartData['cart'] ?? null;

            if (empty($cart) || empty($cart['accounts'])) {
                return [
                    'success' => false,
                    'redirect' => 'client.checkout.selectDomain',
                    'message' => 'El carrito está vacío. Por favor, selecciona productos primero.'
                ];
            }

            // Procesar datos del carrito para confirmación
            $processedCart = $this->processCartForConfirmation($cart);

            return [
                'success' => true,
                'data' => [
                    'cart' => $processedCart,
                    'summary' => $this->calculateCartSummary($processedCart),
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Error obteniendo datos de confirmación', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error al cargar la página de confirmación'
            ];
        }
    }

    /**
     * Transferir purchase_context al carrito si es necesario
     */
    private function transferPurchaseContextToCartIfNeeded(): void
    {
        $purchaseContext = Session::get('purchase_context');
        $cart = Session::get('cart');

        if ($purchaseContext && (!$cart || empty($cart['accounts']))) {
            Log::info('Transfiriendo purchase_context al carrito');

            $cart = $this->transferPurchaseContextToCart($purchaseContext);
            Session::put('cart', $cart);
            Session::forget('purchase_context');

            Log::info('Purchase_context transferido al carrito exitosamente');
        }
    }

    /**
     * Transferir contexto de compra al carrito
     */
    private function transferPurchaseContextToCart(array $purchaseContext): array
    {
        // Lógica para transferir el contexto de compra al formato de carrito
        return [
            'accounts' => [
                [
                    'account_id' => uniqid(),
                    'domain_info' => $purchaseContext['domain_info'] ?? [],
                    'services' => $purchaseContext['services'] ?? [],
                ]
            ],
            'active_account_id' => uniqid(),
        ];
    }

    /**
     * Validar carrito para selección de servicios
     */
    private function validateCartForServices(?array $cart): array
    {
        if (empty($cart) || empty($cart['accounts']) || empty($cart['active_account_id'])) {
            return [
                'valid' => false,
                'message' => 'Por favor, selecciona o configura un dominio primero.'
            ];
        }

        $activeAccount = null;
        foreach ($cart['accounts'] as $account) {
            if ($account['account_id'] === $cart['active_account_id']) {
                $activeAccount = $account;
                break;
            }
        }

        if (!$activeAccount || empty($activeAccount['domain_info']['domain_name'])) {
            return [
                'valid' => false,
                'message' => 'Por favor, configura un dominio para la cuenta activa antes de seleccionar servicios.'
            ];
        }

        return [
            'valid' => true,
            'activeAccount' => $activeAccount
        ];
    }

    /**
     * Obtener productos por tipos con sus relaciones
     */
    private function getProductsByTypes(array $typeIds): Collection
    {
        return Product::whereIn('product_type_id', $typeIds)
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
                return $this->formatProductForFrontend($product);
            });
    }

    /**
     * Formatear producto para el frontend
     */
    private function formatProductForFrontend($product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'landing_page_slug' => $product->landing_page_slug,
            'features_list' => $product->features_list,
            'call_to_action_text' => $product->call_to_action_text,
            'base_resources' => $product->base_resources ?? [],
            'pricings' => $product->pricings->map(function ($pricing) {
                return [
                    'id' => $pricing->id,
                    'price' => $pricing->price,
                    'setup_fee' => $pricing->setup_fee,
                    'currency_code' => $pricing->currency_code,
                    'billing_cycle' => [
                        'id' => $pricing->billingCycle->id,
                        'name' => $pricing->billingCycle->name,
                        'days' => $pricing->billingCycle->days,
                    ],
                ];
            }),
            'configurable_option_groups' => $product->configurableOptionGroups->map(function ($group) {
                return $this->formatConfigurableOptionGroup($group);
            }),
        ];
    }

    /**
     * Formatear grupo de opciones configurables
     */
    private function formatConfigurableOptionGroup($group): array
    {
        return [
            'id' => $group->id,
            'name' => $group->name,
            'description' => $group->description,
            'is_required' => $group->pivot ? $group->pivot->is_required : false,
            'display_order' => $group->pivot ? $group->pivot->display_order : 0,
            'base_quantity' => $group->pivot ? $group->pivot->base_quantity : 0,
            'options' => $group->options->map(function ($option) {
                return [
                    'id' => $option->id,
                    'name' => $option->name,
                    'description' => $option->description,
                    'option_type' => $option->option_type,
                    'value' => $option->value,
                    'is_required' => $option->is_required,
                    'min_value' => $option->min_value,
                    'max_value' => $option->max_value,
                    'display_order' => $option->display_order,
                    'pricings' => $option->pricings->map(function ($pricing) {
                        return [
                            'id' => $pricing->id,
                            'price' => $pricing->price,
                            'currency_code' => $pricing->currency_code,
                            'billing_cycle' => [
                                'id' => $pricing->billingCycle->id,
                                'name' => $pricing->billingCycle->name,
                                'days' => $pricing->billingCycle->days,
                            ],
                        ];
                    }),
                ];
            }),
        ];
    }

    /**
     * Procesar carrito para confirmación
     */
    private function processCartForConfirmation(array $cart): array
    {
        // Procesar y formatear datos del carrito para la página de confirmación
        return $cart;
    }

    /**
     * Obtener porcentajes de descuento activos
     */
    private function getDiscountPercentages(): Collection
    {
        return \App\Models\DiscountPercentage::with(['product', 'billingCycle'])
            ->where('is_active', true)
            ->get()
            ->map(function ($discount) {
                return [
                    'product_id' => $discount->product_id,
                    'billing_cycle_id' => $discount->billing_cycle_id,
                    'percentage' => (float) $discount->percentage,
                    'name' => $discount->name,
                    'description' => $discount->description,
                ];
            })
            ->keyBy(function ($discount) {
                return "{$discount['product_id']}-{$discount['billing_cycle_id']}";
            });
    }

    /**
     * Obtener precios de opciones configurables (solo mensuales)
     */
    private function getConfigurableOptionPrices(): Collection
    {
        return \App\Models\ConfigurableOptionPricing::with(['option', 'billingCycle'])
            ->where('is_active', true)
            ->where('billing_cycle_id', 1) // Solo precios mensuales
            ->get()
            ->map(function ($pricing) {
                return [
                    'option_id' => $pricing->configurable_option_id,
                    'billing_cycle_id' => $pricing->billing_cycle_id,
                    'price' => $pricing->price,
                    'currency_code' => $pricing->currency_code,
                ];
            })
            ->keyBy(function ($pricing) {
                return "{$pricing['option_id']}-{$pricing['billing_cycle_id']}";
            });
    }

    /**
     * Calcular resumen del carrito
     */
    private function calculateCartSummary(array $cart): array
    {
        $subtotal = 0;
        $setupFees = 0;
        $taxes = 0;

        // Calcular totales basados en los items del carrito
        foreach ($cart['accounts'] ?? [] as $account) {
            foreach ($account['services'] ?? [] as $service) {
                $subtotal += $service['price'] ?? 0;
                $setupFees += $service['setup_fee'] ?? 0;
            }
        }

        return [
            'subtotal' => $subtotal,
            'setup_fees' => $setupFees,
            'taxes' => $taxes,
            'total' => $subtotal + $setupFees + $taxes,
        ];
    }
}

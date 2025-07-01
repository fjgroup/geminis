<?php
namespace App\Services;

use App\Models\ConfigurableOptionPricing;
use App\Models\DiscountPercentage;
use App\Models\Product;
use App\Models\ProductPricing;
use Illuminate\Support\Facades\Log;

class PricingCalculatorService
{
    /**
     * Calcular el precio total de un producto con opciones configurables y descuentos
     *
     * @param int $productId ID del producto
     * @param int $billingCycleId ID del ciclo de facturación
     * @param array $configurableOptions Array de opciones configurables [option_id => quantity]
     * @return array Desglose completo del precio
     */
    public function calculateProductPrice(int $productId, int $billingCycleId, array $configurableOptions = []): array
    {
        Log::info('PricingCalculatorService: Calculando precio', [
            'product_id'           => $productId,
            'billing_cycle_id'     => $billingCycleId,
            'configurable_options' => $configurableOptions,
        ]);

        // 1. Obtener precio base del producto
        $basePrice = $this->getBasePrice($productId, $billingCycleId);

        // 2. Calcular precio de recursos base incluidos
        $baseResourcesPrice = $this->calculateBaseResourcesPrice($productId, $billingCycleId);

        // 3. Calcular precio de opciones configurables adicionales
        $optionsPrice = $this->calculateConfigurableOptionsPrice($configurableOptions, $billingCycleId);

        // 4. Calcular subtotal (antes de descuentos)
        $subtotal = $basePrice['price'] + $baseResourcesPrice['total'] + $optionsPrice['total'];

        // 5. Obtener descuento aplicable
        $discount = $this->getDiscount($productId, $billingCycleId);

        // 6. Calcular descuento en dinero
        $discountAmount = $subtotal * ($discount['percentage'] / 100);

        // 7. Calcular total final
        $total = $subtotal - $discountAmount;

        $result = [
            'product_id'           => $productId,
            'billing_cycle_id'     => $billingCycleId,
            'base_price'           => $basePrice,
            'base_resources'       => $baseResourcesPrice,
            'configurable_options' => $optionsPrice,
            'subtotal'             => round($subtotal, 2),
            'discount'             => $discount,
            'discount_amount'      => round($discountAmount, 2),
            'total'                => round($total, 2),
            'currency_code'        => $basePrice['currency_code'],
        ];

        Log::info('PricingCalculatorService: Precio calculado', $result);

        return $result;
    }

    /**
     * Obtener el precio base de un producto para un ciclo específico
     */
    private function getBasePrice(int $productId, int $billingCycleId): array
    {
        $pricing = ProductPricing::where('product_id', $productId)
            ->where('billing_cycle_id', $billingCycleId)
            ->first();

        if (! $pricing) {
            Log::warning('PricingCalculatorService: Precio base no encontrado', [
                'product_id'       => $productId,
                'billing_cycle_id' => $billingCycleId,
            ]);

            return [
                'price'         => 0,
                'setup_fee'     => 0,
                'currency_code' => 'USD',
            ];
        }

        return [
            'price'         => (float) $pricing->price,
            'setup_fee'     => (float) $pricing->setup_fee,
            'currency_code' => $pricing->currency_code,
        ];
    }

    /**
     * Calcular el precio de los recursos base incluidos en el producto
     */
    private function calculateBaseResourcesPrice(int $productId, int $billingCycleId): array
    {
        $product = Product::with(['configurableOptionGroups' => function ($query) {
            $query->withPivot('base_quantity', 'display_order', 'is_required')
                ->orderBy('product_configurable_option_groups.display_order');
        }, 'configurableOptionGroups.options.pricings' => function ($query) use ($billingCycleId) {
            $query->where('billing_cycle_id', $billingCycleId);
        }])->find($productId);

        if (! $product) {
            return ['details' => [], 'total' => 0];
        }

        $baseResourcesDetails = [];
        $totalPrice           = 0;

        foreach ($product->configurableOptionGroups as $group) {
            $baseQuantity = (float) $group->pivot->base_quantity;

            if ($baseQuantity <= 0) {
                continue; // Saltar grupos sin cantidad base
            }

            // Obtener la primera opción del grupo (asumiendo que cada grupo tiene una opción principal)
            $option = $group->options->first();

            if (! $option) {
                continue;
            }

            // Obtener el precio de la opción para este ciclo de facturación
            $optionPricing = $option->pricings->where('billing_cycle_id', $billingCycleId)->first();

            if (! $optionPricing) {
                Log::warning('PricingCalculatorService: Precio de recurso base no encontrado', [
                    'product_id'       => $productId,
                    'group_id'         => $group->id,
                    'option_id'        => $option->id,
                    'billing_cycle_id' => $billingCycleId,
                ]);
                continue;
            }

            $unitPrice = (float) $optionPricing->price;
            $lineTotal = $unitPrice * $baseQuantity;
            $totalPrice += $lineTotal;

            $baseResourcesDetails[] = [
                'group_id'      => $group->id,
                'group_name'    => $group->name,
                'option_id'     => $option->id,
                'option_name'   => $option->name,
                'base_quantity' => $baseQuantity,
                'unit_price'    => $unitPrice,
                'line_total'    => round($lineTotal, 2),
                'currency_code' => $optionPricing->currency_code,
                'display_text'  => $this->formatResourceDisplay($group->name, $baseQuantity),
            ];
        }

        return [
            'details' => $baseResourcesDetails,
            'total'   => round($totalPrice, 2),
        ];
    }

    /**
     * Calcular el precio total de opciones configurables adicionales
     */
    private function calculateConfigurableOptionsPrice(array $configurableOptions, int $billingCycleId): array
    {
        $optionsDetails = [];
        $totalPrice     = 0;

        foreach ($configurableOptions as $optionId => $quantity) {
            if ($quantity <= 0) {
                continue; // Saltar opciones con cantidad 0 o negativa
            }

            $optionPricing = ConfigurableOptionPricing::where('configurable_option_id', $optionId)
                ->where('billing_cycle_id', $billingCycleId)
                ->with('option')
                ->first();

            if (! $optionPricing) {
                Log::warning('PricingCalculatorService: Precio de opción no encontrado', [
                    'option_id'        => $optionId,
                    'billing_cycle_id' => $billingCycleId,
                ]);
                continue;
            }

            $optionPrice = (float) $optionPricing->price;
            $lineTotal   = $optionPrice * $quantity;
            $totalPrice += $lineTotal;

            $optionsDetails[] = [
                'option_id'     => $optionId,
                'option_name'   => $optionPricing->option->name ?? "Opción ID {$optionId}",
                'quantity'      => $quantity,
                'unit_price'    => $optionPrice,
                'line_total'    => round($lineTotal, 2),
                'currency_code' => $optionPricing->currency_code,
            ];
        }

        return [
            'details' => $optionsDetails,
            'total'   => round($totalPrice, 2),
        ];
    }

    /**
     * Obtener el descuento aplicable para un producto y ciclo
     */
    private function getDiscount(int $productId, int $billingCycleId): array
    {
        $discount = DiscountPercentage::where('product_id', $productId)
            ->where('billing_cycle_id', $billingCycleId)
            ->first();

        if (! $discount) {
            Log::info('PricingCalculatorService: Sin descuento aplicable', [
                'product_id'       => $productId,
                'billing_cycle_id' => $billingCycleId,
            ]);

            return [
                'percentage'  => 0,
                'name'        => 'Sin descuento',
                'description' => 'No hay descuento aplicable',
            ];
        }

        return [
            'percentage'  => (float) $discount->percentage,
            'name'        => $discount->name,
            'description' => $discount->description,
        ];
    }

    /**
     * Calcular precio de múltiples productos (para carrito completo)
     */
    public function calculateCartTotal(array $cartItems): array
    {
        $cartDetails        = [];
        $cartSubtotal       = 0;
        $cartDiscountAmount = 0;
        $cartTotal          = 0;

        foreach ($cartItems as $item) {
            $itemCalculation = $this->calculateProductPrice(
                $item['product_id'],
                $item['billing_cycle_id'],
                $item['configurable_options'] ?? []
            );

            $cartDetails[] = $itemCalculation;
            $cartSubtotal += $itemCalculation['subtotal'];
            $cartDiscountAmount += $itemCalculation['discount_amount'];
            $cartTotal += $itemCalculation['total'];
        }

        return [
            'items'                 => $cartDetails,
            'subtotal'              => round($cartSubtotal, 2),
            'total_discount_amount' => round($cartDiscountAmount, 2),
            'total'                 => round($cartTotal, 2),
            'currency_code'         => $cartDetails[0]['currency_code'] ?? 'USD',
        ];
    }

    /**
     * Calcular precio para usuarios no logueados (incluye recursos base del plan)
     * Usa precio base + recursos base configurados por el admin, sin opciones adicionales
     */
    public function calculateSimpleProductPrice(int $productId, int $billingCycleId): array
    {
        Log::info('PricingCalculatorService: Calculando precio para usuario no logueado', [
            'product_id'       => $productId,
            'billing_cycle_id' => $billingCycleId,
        ]);

        // 1. Obtener precio base del producto (cPanel)
        $basePrice = $this->getBasePrice($productId, $billingCycleId);

        if (! $basePrice) {
            throw new \Exception('Precio base no encontrado para el producto');
        }

        // 2. Calcular precio de recursos base incluidos (RAM, CPU, espacio)
        $baseResourcesPrice = $this->calculateBaseResourcesPrice($productId, $billingCycleId);

        // 3. Calcular subtotal (precio base + recursos base)
        $subtotal = $basePrice['price'] + $baseResourcesPrice['total'];

        // 4. Aplicar descuento si existe
        $discount       = $this->getDiscount($productId, $billingCycleId);
        $discountAmount = $subtotal * ($discount['percentage'] / 100);
        $total          = $subtotal - $discountAmount;

        $result = [
            'product_id'        => $productId,
            'billing_cycle_id'  => $billingCycleId,
            'base_price'        => $basePrice,
            'base_resources'    => $baseResourcesPrice,
            'subtotal'          => round($subtotal, 2),
            'discount'          => $discount,
            'discount_amount'   => round($discountAmount, 2),
            'total'             => round($total, 2),
            'currency_code'     => $basePrice['currency_code'],
            'is_simple_pricing' => true, // Indicador de que es precio simplificado
        ];

        Log::info('PricingCalculatorService: Precio calculado para usuario no logueado', [
            'result'                 => $result,
            'base_resources_details' => $baseResourcesPrice['details'] ?? [],
        ]);

        return $result;
    }

    /**
     * Obtener información de recursos base de un producto
     */
    public function getProductBaseResources(int $productId): array
    {
        $product = Product::with(['configurableOptionGroups' => function ($query) {
            $query->withPivot('base_quantity', 'display_order', 'is_required')
                ->orderBy('product_configurable_option_groups.display_order');
        }])->find($productId);

        if (! $product) {
            return [];
        }

        $baseResources = [];

        foreach ($product->configurableOptionGroups as $group) {
            $baseQuantity = $group->pivot->base_quantity;

            if ($baseQuantity > 0) {
                $baseResources[] = [
                    'group_id'      => $group->id,
                    'group_name'    => $group->name,
                    'base_quantity' => $baseQuantity,
                    'unit'          => $this->getResourceUnit($group->name),
                    'display_text'  => $this->formatResourceDisplay($group->name, $baseQuantity),
                ];
            }
        }

        return $baseResources;
    }

    /**
     * Obtener la unidad de medida para un recurso
     */
    private function getResourceUnit(string $resourceName): string
    {
        $units = [
            'Espacio en Disco' => 'GB',
            'vCPU'             => 'cores',
            'vRam'             => 'GB',
            'Memoria RAM'      => 'GB',
            'Transferencia'    => 'GB',
        ];

        return $units[$resourceName] ?? 'unidades';
    }

    /**
     * Formatear la visualización de un recurso con mensajes de marketing
     */
    private function formatResourceDisplay(string $resourceName, float $quantity): string
    {
        // Formatear cantidad (sin decimales si es entero)
        $formattedQuantity = $quantity == (int) $quantity ? (int) $quantity : $quantity;

        // Mensajes de marketing atractivos
        $marketingDisplays = [
            'Espacio en Disco'      => "{$formattedQuantity} GB de Espacio para Crecer 🚀",
            '🚀 Espacio para Crecer' => "{$formattedQuantity} GB de Espacio para Crecer 🚀",
            'vCPU'                  => "{$formattedQuantity} Núcleos de Potencia Turbo ⚡",
            '⚡ Potencia Turbo'      => "{$formattedQuantity} Núcleos de Potencia Turbo ⚡",
            'vRam'                  => "{$formattedQuantity} GB de Memoria Inteligente 🧠",
            '🧠 Memoria Inteligente' => "{$formattedQuantity} GB de Memoria Inteligente 🧠",
            'Memoria RAM'           => "{$formattedQuantity} GB de Memoria Inteligente 🧠",
            'Transferencia'         => "{$formattedQuantity} GB de Transferencia Ilimitada 🌐",
        ];

        // Si hay un mensaje de marketing específico, usarlo
        if (isset($marketingDisplays[$resourceName])) {
            return $marketingDisplays[$resourceName];
        }

        // Fallback al formato original
        $unit = $this->getResourceUnit($resourceName);
        return "{$formattedQuantity} {$unit} de {$resourceName}";
    }
}

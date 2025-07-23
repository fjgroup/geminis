<?php

namespace App\Domains\Products\Application\Services;

use App\Domains\Products\Infrastructure\Persistence\Models\Product;
use App\Domains\Products\Infrastructure\Persistence\Models\ProductPricing;
use App\Domains\Products\Infrastructure\Persistence\Models\ConfigurableOption;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de aplicación para cálculo de precios de productos
 * 
 * Cumple con Single Responsibility Principle - solo maneja cálculos de precios
 * Ubicado en Application layer según arquitectura hexagonal
 */
class ProductPricingService
{
    /**
     * Calculate product price including configurable options
     *
     * @param int $productId
     * @param int $billingCycleId
     * @param array $configurableOptions
     * @return array
     */
    public function calculateProductPrice(int $productId, int $billingCycleId, array $configurableOptions = []): array
    {
        try {
            $product = Product::with(['pricings.billingCycle'])->findOrFail($productId);
            
            // Get base pricing for the product and billing cycle
            $basePricing = $product->pricings()
                ->where('billing_cycle_id', $billingCycleId)
                ->where('is_active', true)
                ->first();

            if (!$basePricing) {
                throw new \Exception("No pricing found for product {$productId} with billing cycle {$billingCycleId}");
            }

            $basePrice = $basePricing->price;
            $setupFee = $basePricing->setup_fee ?? 0;
            $optionsTotal = 0;
            $optionsDetails = [];

            // Calculate configurable options pricing
            if (!empty($configurableOptions)) {
                foreach ($configurableOptions as $optionData) {
                    $optionId = $optionData['option_id'] ?? $optionData;
                    $quantity = $optionData['quantity'] ?? 1;

                    $option = ConfigurableOption::with(['pricings' => function ($query) use ($billingCycleId) {
                        $query->where('billing_cycle_id', $billingCycleId)
                              ->where('is_active', true);
                    }])->find($optionId);

                    if ($option && $option->pricings->isNotEmpty()) {
                        $optionPricing = $option->pricings->first();
                        $optionPrice = $optionPricing->price * $quantity;
                        $optionsTotal += $optionPrice;

                        $optionsDetails[] = [
                            'option_id' => $option->id,
                            'name' => $option->name,
                            'quantity' => $quantity,
                            'unit_price' => $optionPricing->price,
                            'total_price' => $optionPrice,
                        ];
                    }
                }
            }

            $subtotal = $basePrice + $optionsTotal;
            $total = $subtotal + $setupFee;

            return [
                'success' => true,
                'product_id' => $productId,
                'billing_cycle_id' => $billingCycleId,
                'base_price' => $basePrice,
                'setup_fee' => $setupFee,
                'options_total' => $optionsTotal,
                'options_details' => $optionsDetails,
                'subtotal' => $subtotal,
                'total' => $total,
                'currency' => 'USD', // TODO: Get from configuration
                'billing_cycle' => $basePricing->billingCycle->name ?? 'Unknown',
            ];

        } catch (\Exception $e) {
            Log::error("Error calculating product price: " . $e->getMessage(), [
                'product_id' => $productId,
                'billing_cycle_id' => $billingCycleId,
                'configurable_options' => $configurableOptions,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'product_id' => $productId,
                'billing_cycle_id' => $billingCycleId,
            ];
        }
    }

    /**
     * Get available billing cycles for a product
     *
     * @param int $productId
     * @return array
     */
    public function getAvailableBillingCycles(int $productId): array
    {
        $product = Product::with(['pricings.billingCycle' => function ($query) {
            $query->where('is_active', true);
        }])->find($productId);

        if (!$product) {
            return [];
        }

        return $product->pricings
            ->where('is_active', true)
            ->map(function ($pricing) {
                return [
                    'id' => $pricing->billingCycle->id,
                    'name' => $pricing->billingCycle->name,
                    'days' => $pricing->billingCycle->days,
                    'price' => $pricing->price,
                    'setup_fee' => $pricing->setup_fee,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Validate pricing calculation request
     *
     * @param array $data
     * @return array
     */
    public function validatePricingRequest(array $data): array
    {
        $errors = [];

        if (empty($data['product_id'])) {
            $errors[] = 'Product ID is required';
        }

        if (empty($data['billing_cycle_id'])) {
            $errors[] = 'Billing cycle ID is required';
        }

        if (!empty($data['configurable_options']) && !is_array($data['configurable_options'])) {
            $errors[] = 'Configurable options must be an array';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}

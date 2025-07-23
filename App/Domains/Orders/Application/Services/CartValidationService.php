<?php

namespace App\Domains\Orders\Application\Services;

use App\Domains\Products\Infrastructure\Persistence\Models\Product;
use App\Domains\Products\Infrastructure\Persistence\Models\ProductPricing;
use App\Domains\Products\Infrastructure\Persistence\Models\BillingCycle;
use App\Domains\Orders\Infrastructure\Persistence\Models\OrderConfigurableOption;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Servicio de aplicación para validación de carrito
 * 
 * Aplica Single Responsibility Principle - solo valida items del carrito
 * Ubicado en Application layer según arquitectura hexagonal
 */
class CartValidationService
{
    /**
     * Validar disponibilidad de todos los items del carrito
     *
     * @param array $cart
     * @throws Exception
     */
    public function validateCartItemsAvailability(array $cart): void
    {
        if (empty($cart['accounts'])) {
            throw new Exception("El carrito está vacío o no tiene cuentas.");
        }

        foreach ($cart['accounts'] as $accountIndex => $account) {
            if (empty($account['account_id'])) {
                throw new Exception("Cuenta inválida en el carrito (índice {$accountIndex}): falta account_id.");
            }

            $accountIdentifierForError = $account['domain_info']['domain_name'] ?? "Cuenta ID: {$account['account_id']}";

            // Validar domain_info
            if (isset($account['domain_info']) && !empty($account['domain_info']['product_id'])) {
                $domainPricing = ProductPricing::with('billingCycle')->find($account['domain_info']['pricing_id']);
                if (!$domainPricing || !$domainPricing->billingCycle) {
                    throw new Exception("Ciclo de facturación no encontrado para el servicio de dominio en '{$accountIdentifierForError}'.");
                }
                $this->validateCartItem(
                    $account['domain_info'], 
                    'domain_info', 
                    $domainPricing->billingCycle, 
                    false, 
                    isset($account['domain_info']['override_price'])
                );
            }

            // Validar primary_service
            if (isset($account['primary_service']) && !empty($account['primary_service']['product_id'])) {
                $primaryServicePricing = ProductPricing::with('billingCycle')->find($account['primary_service']['pricing_id']);
                if (!$primaryServicePricing || !$primaryServicePricing->billingCycle) {
                    throw new Exception("Ciclo de facturación no encontrado para el servicio principal en '{$accountIdentifierForError}'.");
                }
                $this->validateCartItem(
                    $account['primary_service'], 
                    'primary_service', 
                    $primaryServicePricing->billingCycle, 
                    true, 
                    false
                );
            }

            // Validar additional_services
            if (isset($account['additional_services']) && is_array($account['additional_services'])) {
                foreach ($account['additional_services'] as $additionalServiceIndex => $additionalService) {
                    if (empty($additionalService['product_id']) || empty($additionalService['pricing_id'])) {
                        Log::warning("Servicio adicional malformado omitido en validación.", ['service_data' => $additionalService]);
                        continue;
                    }
                    $additionalServicePricing = ProductPricing::with('billingCycle')->find($additionalService['pricing_id']);
                    if (!$additionalServicePricing || !$additionalServicePricing->billingCycle) {
                        $serviceName = $additionalService['product_name'] ?? "ID: {$additionalService['product_id']}";
                        throw new Exception("Ciclo de facturación no encontrado para el servicio adicional '{$serviceName}' en '{$accountIdentifierForError}'.");
                    }
                    $this->validateCartItem(
                        $additionalService, 
                        "additional_service[{$additionalServiceIndex}]", 
                        $additionalServicePricing->billingCycle, 
                        false, 
                        false
                    );
                }
            }
        }
    }

    /**
     * Validar un item específico del carrito
     */
    private function validateCartItem(
        ?array $item, 
        string $itemKeyInAccount, 
        BillingCycle $itemBillingCycle, 
        bool $checkConfigOptions = false, 
        bool $hasOverridePrice = false
    ): void {
        if (empty($item) || !isset($item['product_id']) || !isset($item['pricing_id'])) {
            Log::warning("CartValidationService: Ítem inválido o faltan product_id/pricing_id.", [
                'item' => $item, 
                'item_key' => $itemKeyInAccount
            ]);
            throw new Exception("Un ítem en el carrito es inválido ({$itemKeyInAccount}). Contacte a soporte.");
        }

        $product = Product::find($item['product_id']);
        $productNameForError = $item['product_name'] ?? "ID Prod:{$item['product_id']}";
        
        if (isset($item['domain_name']) && $itemKeyInAccount === 'domain_info') {
            $productNameForError = $item['domain_name'];
        }

        if (!$product || $product->status !== 'active') {
            throw new Exception("El producto '{$productNameForError}' ({$itemKeyInAccount}) ya no está disponible.");
        }

        $pricing = ProductPricing::find($item['pricing_id']);
        if (!$pricing || $pricing->product_id !== $product->id) {
            throw new Exception("La configuración de precio para '{$productNameForError}' ({$itemKeyInAccount}) es inválida.");
        }
        
        if ($pricing->billing_cycle_id !== $itemBillingCycle->id) {
            Log::error("Discrepancia de BillingCycle en validateCartItem", [
                'item_pricing_id' => $pricing->id,
                'item_bc_id' => $pricing->billing_cycle_id,
                'expected_bc_id' => $itemBillingCycle->id,
                'item_key' => $itemKeyInAccount,
            ]);
            throw new Exception("Error de consistencia en el ciclo de facturación para '{$productNameForError}' ({$itemKeyInAccount}).");
        }

        if ($itemKeyInAccount === 'domain_info' && $hasOverridePrice) {
            if (!isset($item['override_price']) || !is_numeric($item['override_price']) || (float) $item['override_price'] < 0) {
                throw new Exception("El precio de registro para el dominio {$productNameForError} es inválido.");
            }
        }

        // Validar opciones configurables si es necesario
        if ($itemKeyInAccount === 'primary_service' && $checkConfigOptions && isset($item['cart_item_id'])) {
            $cartItemId = $item['cart_item_id'];
            $configurableOptions = OrderConfigurableOption::where('cart_item_id', $cartItemId)
                ->where('is_active', true)
                ->get();

            if ($configurableOptions->isNotEmpty()) {
                Log::debug("CartValidationService: Encontradas {$configurableOptions->count()} opciones configurables válidas para '{$productNameForError}'.");
            }
        }
    }
}

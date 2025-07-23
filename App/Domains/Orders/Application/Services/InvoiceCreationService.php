<?php

namespace App\Domains\Orders\Application\Services;

use App\Domains\Users\Infrastructure\Persistence\Models\User;
use App\Domains\Invoices\Infrastructure\Persistence\Models\Invoice;
use App\Domains\Invoices\Infrastructure\Persistence\Models\InvoiceItem;
use App\Domains\Products\Infrastructure\Persistence\Models\Product;
use App\Domains\Products\Infrastructure\Persistence\Models\ProductPricing;
use App\Domains\Products\Infrastructure\Persistence\Models\ConfigurableOption;
use App\Domains\Products\Application\Services\ProductPricingService;
use App\Domains\Orders\Infrastructure\Persistence\Models\OrderConfigurableOption;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de aplicación para creación de facturas
 * 
 * Aplica Single Responsibility Principle - solo crea facturas e items
 * Ubicado en Application layer según arquitectura hexagonal
 */
class InvoiceCreationService
{
    public function __construct(
        private ProductPricingService $pricingService
    ) {}

    /**
     * Crear factura completa desde el carrito
     */
    public function createInvoiceFromCart(User $client, array $cart, array $additionalData = []): Invoice
    {
        $notesToClient = $additionalData['notes_to_client'] ?? null;
        $ipAddress = $additionalData['ip_address'] ?? request()->ip();
        $paymentGatewaySlug = $additionalData['payment_gateway_slug'] ?? null;

        $invoiceCurrencyCode = $this->determineCurrencyCode($cart);

        // Crear factura base
        $invoice = new Invoice([
            'client_id' => $client->id,
            'reseller_id' => $client->reseller_id,
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'requested_date' => Carbon::now(),
            'issue_date' => Carbon::now()->toDateString(),
            'due_date' => Carbon::now()->addDays(config('invoicing.due_days', 7))->toDateString(),
            'status' => 'unpaid',
            'currency_code' => $invoiceCurrencyCode,
            'subtotal' => 0,
            'tax1_rate' => $client->tax_rate_1 ?? 0,
            'tax1_description' => $client->tax_description_1 ?? 'Tax 1',
            'tax1_amount' => 0,
            'tax2_rate' => $client->tax_rate_2 ?? 0,
            'tax2_description' => $client->tax_description_2 ?? 'Tax 2',
            'tax2_amount' => 0,
            'total_amount' => 0,
            'notes_to_client' => $notesToClient,
            'ip_address' => $ipAddress,
            'payment_gateway_slug' => $paymentGatewaySlug,
        ]);

        $invoiceItemsCollection = [];
        $currentSubtotal = 0;

        // Procesar cada cuenta del carrito
        foreach ($cart['accounts'] as $account) {
            $domainNameForService = $account['domain_info']['domain_name'] ?? null;

            // Procesar domain_info
            if (isset($account['domain_info']['product_id'], $account['domain_info']['pricing_id'])) {
                $items = $this->processDomainInfo($account['domain_info']);
                $invoiceItemsCollection = array_merge($invoiceItemsCollection, $items['items']);
                $currentSubtotal += $items['subtotal'];
            }

            // Procesar primary_service
            if (!empty($account['primary_service'])) {
                $items = $this->processPrimaryService($account['primary_service'], $domainNameForService);
                $invoiceItemsCollection = array_merge($invoiceItemsCollection, $items['items']);
                $currentSubtotal += $items['subtotal'];
            }

            // Procesar additional_services
            if (!empty($account['additional_services'])) {
                foreach ($account['additional_services'] as $additionalService) {
                    $items = $this->processAdditionalService($additionalService, $domainNameForService);
                    $invoiceItemsCollection = array_merge($invoiceItemsCollection, $items['items']);
                    $currentSubtotal += $items['subtotal'];
                }
            }
        }

        // Calcular totales
        $invoice->subtotal = $currentSubtotal;
        if ($invoice->tax1_rate > 0) {
            $invoice->tax1_amount = round($currentSubtotal * ($invoice->tax1_rate / 100), 2);
        }
        if ($invoice->tax2_rate > 0) {
            $invoice->tax2_amount = round($currentSubtotal * ($invoice->tax2_rate / 100), 2);
        }
        $invoice->total_amount = round($currentSubtotal + $invoice->tax1_amount + $invoice->tax2_amount, 2);

        // Guardar factura e items
        $invoice->save();
        if (!empty($invoiceItemsCollection)) {
            $invoice->items()->saveMany($invoiceItemsCollection);
        }

        Log::info('Invoice created successfully', [
            'invoice_id' => $invoice->id,
            'subtotal' => $invoice->subtotal,
            'tax1_amount' => $invoice->tax1_amount,
            'tax2_amount' => $invoice->tax2_amount,
            'total_amount' => $invoice->total_amount,
            'items_count' => count($invoiceItemsCollection),
        ]);

        return $invoice;
    }

    /**
     * Procesar información de dominio
     */
    private function processDomainInfo(array $item): array
    {
        $productModel = Product::find($item['product_id']);
        $pricingModel = ProductPricing::with('billingCycle')->find($item['pricing_id']);

        if (!$productModel || !$pricingModel) {
            throw new \Exception("Error al procesar el ítem de dominio: producto o precio no encontrado.");
        }

        $unitPrice = (isset($item['override_price']) && is_numeric($item['override_price']))
            ? (float) $item['override_price']
            : (float) $pricingModel->price;

        $setupFee = $pricingModel->setup_fee ?? 0.0;
        $itemTotalPrice = $unitPrice + $setupFee;

        $description = $productModel->name;
        if ($pricingModel->billingCycle) {
            $description .= ' (' . $pricingModel->billingCycle->name . ')';
        }
        $description .= ' - ' . $item['domain_name'];

        $invoiceItem = new InvoiceItem([
            'product_id' => $productModel->id,
            'product_pricing_id' => $pricingModel->id,
            'description' => $description,
            'quantity' => 1,
            'unit_price' => $unitPrice,
            'setup_fee' => $setupFee,
            'total_price' => $itemTotalPrice,
            'taxable' => $productModel->taxable ?? true,
            'domain_name' => $item['domain_name'],
            'item_type' => $productModel->productType?->slug ?? 'domain_registration',
        ]);

        return [
            'items' => [$invoiceItem],
            'subtotal' => $itemTotalPrice
        ];
    }

    /**
     * Procesar servicio principal
     */
    private function processPrimaryService(array $item, ?string $domainNameForService): array
    {
        $productModel = Product::with(['productType', 'configurableOptionGroups.options.pricings'])
            ->find($item['product_id']);
        $pricingModel = ProductPricing::with('billingCycle')->find($item['pricing_id']);

        if (!$productModel || !$pricingModel) {
            throw new \Exception("Error al procesar el servicio principal: producto o precio no encontrado.");
        }

        $quantity = $item['quantity'] ?? 1;
        
        // Usar ProductPricingService para calcular precio con opciones configurables
        $configurableOptionsForCalculation = $this->getConfigurableOptionsForCalculation($item);
        
        $priceCalculation = $this->pricingService->calculateProductPrice(
            $productModel->id,
            $pricingModel->billing_cycle_id,
            $configurableOptionsForCalculation
        );

        if (!$priceCalculation['success']) {
            throw new \Exception("Error calculando precio: " . $priceCalculation['error']);
        }

        $finalUnitPrice = $priceCalculation['total'];
        $currentSetupFee = (float) ($pricingModel->setup_fee ?? 0.0);
        $itemTotalPrice = ($finalUnitPrice * $quantity) + $currentSetupFee;

        // Crear descripción con opciones configurables
        $configurableOptionsDescriptionArray = $this->getConfigurableOptionsDescriptions($item);
        
        $description = $productModel->name;
        if ($pricingModel->billingCycle) {
            $description .= ' (' . $pricingModel->billingCycle->name . ')';
        }
        if (!empty($configurableOptionsDescriptionArray)) {
            $description .= ' - Opciones: ' . implode('; ', $configurableOptionsDescriptionArray);
        }
        if ($domainNameForService) {
            $description .= ' - ' . $domainNameForService;
        }

        $invoiceItem = new InvoiceItem([
            'product_id' => $productModel->id,
            'product_pricing_id' => $pricingModel->id,
            'description' => $description,
            'quantity' => $quantity,
            'unit_price' => $finalUnitPrice,
            'setup_fee' => $currentSetupFee,
            'total_price' => $itemTotalPrice,
            'taxable' => $productModel->taxable ?? true,
            'domain_name' => $domainNameForService,
            'item_type' => $productModel->productType?->slug ?? 'hosting_service',
        ]);

        return [
            'items' => [$invoiceItem],
            'subtotal' => $itemTotalPrice
        ];
    }

    /**
     * Procesar servicio adicional
     */
    private function processAdditionalService(array $item, ?string $domainNameForService): array
    {
        $productModel = Product::with('productType')->find($item['product_id']);
        $pricingModel = ProductPricing::with('billingCycle')->find($item['pricing_id']);

        if (!$productModel || !$pricingModel) {
            throw new \Exception("Error al procesar un servicio adicional: producto o precio no encontrado.");
        }

        $quantity = $item['quantity'] ?? 1;
        $unitPrice = (float) $pricingModel->price;
        $setupFee = (float) ($pricingModel->setup_fee ?? 0.0);
        $itemTotalPrice = ($unitPrice * $quantity) + $setupFee;

        $description = $productModel->name;
        if ($pricingModel->billingCycle) {
            $description .= ' (' . $pricingModel->billingCycle->name . ')';
        }
        if ($domainNameForService) {
            $description .= ' - Associated with ' . $domainNameForService;
        }

        $invoiceItem = new InvoiceItem([
            'product_id' => $productModel->id,
            'product_pricing_id' => $pricingModel->id,
            'description' => $description,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'setup_fee' => $setupFee,
            'total_price' => $itemTotalPrice,
            'taxable' => $productModel->taxable ?? true,
            'domain_name' => null,
            'item_type' => $productModel->productType?->slug ?? 'additional_service',
        ]);

        return [
            'items' => [$invoiceItem],
            'subtotal' => $itemTotalPrice
        ];
    }

    /**
     * Obtener opciones configurables para cálculo
     */
    private function getConfigurableOptionsForCalculation(array $item): array
    {
        $configurableOptionsForCalculation = [];
        
        $cartItemId = $item['cart_item_id'] ?? null;
        if ($cartItemId) {
            $configurableOptions = OrderConfigurableOption::where('cart_item_id', $cartItemId)
                ->where('is_active', true)
                ->get();

            foreach ($configurableOptions as $configOption) {
                $configurableOptionsForCalculation[$configOption->option_id] = $configOption->quantity;
            }
        }

        return $configurableOptionsForCalculation;
    }

    /**
     * Obtener descripciones de opciones configurables
     */
    private function getConfigurableOptionsDescriptions(array $item): array
    {
        $descriptions = [];
        
        $cartItemId = $item['cart_item_id'] ?? null;
        if ($cartItemId) {
            $configurableOptions = OrderConfigurableOption::where('cart_item_id', $cartItemId)
                ->where('is_active', true)
                ->get();

            foreach ($configurableOptions as $configOption) {
                $description = $configOption->group_name . ': ' . $configOption->option_name;
                if ($configOption->quantity > 1) {
                    $description .= " (Cantidad: {$configOption->quantity})";
                }
                $descriptions[] = $description;
            }
        }

        return $descriptions;
    }

    /**
     * Determinar código de moneda desde el carrito
     */
    private function determineCurrencyCode(array $cart): string
    {
        if (isset($cart['accounts']) && count($cart['accounts']) > 0) {
            foreach ($cart['accounts'] as $account) {
                $itemsToScan = [];
                
                // Recolectar pricing_id de todos los items
                if (isset($account['domain_info']) && !empty($account['domain_info']['pricing_id'])) {
                    $itemsToScan[] = $account['domain_info']['pricing_id'];
                }
                if (isset($account['primary_service']) && !empty($account['primary_service']['pricing_id'])) {
                    $itemsToScan[] = $account['primary_service']['pricing_id'];
                }
                if (isset($account['additional_services']) && is_array($account['additional_services'])) {
                    foreach ($account['additional_services'] as $additionalItem) {
                        if (isset($additionalItem['pricing_id'])) {
                            $itemsToScan[] = $additionalItem['pricing_id'];
                        }
                    }
                }

                foreach ($itemsToScan as $pricingId) {
                    $pricing = ProductPricing::find($pricingId);
                    if ($pricing && !empty($pricing->currency_code) && is_string($pricing->currency_code)) {
                        return $pricing->currency_code;
                    }
                }
            }
        }

        return config('app.currency_code', 'USD');
    }
}

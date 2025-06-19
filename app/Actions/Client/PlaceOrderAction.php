<?php

namespace App\Actions\Client;

use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Models\ClientService;
use App\Models\BillingCycle;
use App\Models\ConfigurableOptionGroup;
use App\Models\ConfigurableOption;
use App\Models\ConfigurableOptionPricing;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Exception; // Usar la clase base de Exception para capturar todo tipo de excepciones

class PlaceOrderAction
{
    public function execute(?User $client = null, array $additionalData = []): Invoice
    {
        $client = $client ?? Auth::user();
        if (!$client) {
            throw new Exception("Cliente no proporcionado o no autenticado.");
        }

        $cart = session()->get('cart');

        if (!$cart || empty($cart['accounts'])) {
            throw new Exception("El carrito está vacío o es inválido.");
        }

        // **Paso de Verificación Previa (antes de la transacción)**
        $this->validateCartItemsAvailability($cart);

        DB::beginTransaction();

        try {
            $notesToClient = $additionalData['notes_to_client'] ?? null;
            $ipAddress = $additionalData['ip_address'] ?? request()->ip();
            $paymentGatewaySlug = $additionalData['payment_gateway_slug'] ?? null;

            $currencyCode = $this->determineCurrencyCode($cart);

            $invoice = new Invoice([
                'client_id' => $client->id,
                'reseller_id' => $client->reseller_id,
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'requested_date' => Carbon::now(),
                'issue_date' => Carbon::now()->toDateString(),
                'due_date' => Carbon::now()->addDays(config('invoicing.due_days', 7))->toDateString(),
                'status' => 'unpaid',
                'currency_code' => $currencyCode,
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
            $clientServicesCollection = [];
            $currentSubtotal = 0;

            foreach ($cart['accounts'] as $account) {
                $domainNameForService = $account['domain_info']['domain_name'] ?? null;

                // Procesar Registro de Dominio
                if (!empty($account['domain_info']['product_id']) && !empty($account['domain_info']['pricing_id'])) {
                    $item = $account['domain_info'];
                    $productModel = Product::find($item['product_id']); // Ya validado en pre-verificación
                    $pricingModel = ProductPricing::with('billingCycle')->find($item['pricing_id']); // Ya validado

                    $unitPrice = $item['price'];
                    $setupFee = $pricingModel->setup_fee ?? 0;
                    $itemTotalPrice = $unitPrice + $setupFee;
                    $description = $item['product_name'] . ' (' . $pricingModel->billingCycle->name . ') - ' . $item['domain_name'];

                    $invoiceItemsCollection[] = new InvoiceItem([
                        'product_id' => $item['product_id'],
                        'product_pricing_id' => $item['pricing_id'],
                        'description' => $description, 'quantity' => 1, 'unit_price' => $unitPrice,
                        'setup_fee' => $setupFee, 'total_price' => $itemTotalPrice,
                        'taxable' => $productModel->taxable ?? true, 'domain_name' => $item['domain_name'],
                        'item_type' => $productModel->productType?->slug ?? 'domain_registration',
                    ]);
                    $currentSubtotal += $itemTotalPrice;
                    $clientServicesCollection[] = new ClientService([
                        'client_id' => $client->id, 'product_id' => $item['product_id'],
                        'product_pricing_id' => $item['pricing_id'], 'billing_cycle_id' => $pricingModel->billing_cycle_id,
                        'domain_name' => $item['domain_name'], 'status' => 'Pending',
                        'registration_date' => Carbon::now(), 'next_due_date' => $this->calculateNextDueDate($pricingModel->billingCycle),
                    ]);
                }

                // Procesar Servicio Principal
                if (!empty($account['primary_service'])) {
                    $item = $account['primary_service'];
                    $productModel = Product::with('productType')->find($item['product_id']); // Ya validado
                    $pricingModel = ProductPricing::with('billingCycle')->find($item['pricing_id']); // Ya validado

                    $quantity = $item['quantity'] ?? 1;
                    $baseUnitPriceFromModel = $pricingModel->price;
                    $currentSetupFee = $pricingModel->setup_fee ?? 0; // Setup fee del producto base

                    $configurableOptionsDescriptionArray = [];
                    $configurableOptionsPriceAdjustment = 0.0;
                    $configurableOptionsForServiceNotes = [];

                    if (isset($item['configurable_options']) && is_array($item['configurable_options'])) {
                        foreach ($item['configurable_options'] as $groupId => $optionId) {
                            $group = ConfigurableOptionGroup::find($groupId); // Ya validado en pre-verificación (indirectamente)
                            $option = ConfigurableOption::find($optionId);   // Ya validado en pre-verificación

                            if ($group && $option) { // No es necesario $option->group_id == $group->id porque ya se validó en controller
                                $configurableOptionsDescriptionArray[] = $group->name . ': ' . $option->name;
                                $configurableOptionsForServiceNotes[] = $group->name . ': ' . $option->name;
                                $optionPricing = ConfigurableOptionPricing::where('configurable_option_id', $option->id)
                                    ->where('billing_cycle_id', $pricingModel->billing_cycle_id)->first(); // Ya validado
                                if ($optionPricing) {
                                    $configurableOptionsPriceAdjustment += $optionPricing->price;
                                    $currentSetupFee += $optionPricing->setup_fee ?? 0; // Sumar setup_fee de la opción
                                }
                            }
                        }
                    }

                    $finalUnitPrice = $baseUnitPriceFromModel + $configurableOptionsPriceAdjustment;
                    $itemTotalPrice = ($finalUnitPrice * $quantity) + $currentSetupFee;
                    $description = $item['product_name'] . ' (' . $pricingModel->billingCycle->name . ')';
                    if (!empty($configurableOptionsDescriptionArray)) {
                        $description .= ' - Opciones: ' . implode('; ', $configurableOptionsDescriptionArray);
                    }
                    if ($domainNameForService) { $description .= ' - ' . $domainNameForService; }

                    $invoiceItemsCollection[] = new InvoiceItem([
                        'product_id' => $item['product_id'], 'product_pricing_id' => $item['pricing_id'],
                        'description' => $description, 'quantity' => $quantity, 'unit_price' => $finalUnitPrice,
                        'setup_fee' => $currentSetupFee, 'total_price' => $itemTotalPrice,
                        'taxable' => $productModel->taxable ?? true, 'domain_name' => $domainNameForService,
                        'item_type' => $productModel->productType?->slug ?? 'hosting_service',
                    ]);
                    $currentSubtotal += $itemTotalPrice;
                    $clientServicesCollection[] = new ClientService([
                        'client_id' => $client->id, 'product_id' => $item['product_id'],
                        'product_pricing_id' => $item['pricing_id'], 'billing_cycle_id' => $pricingModel->billing_cycle_id,
                        'domain_name' => $domainNameForService, 'status' => 'Pending',
                        'registration_date' => Carbon::now(), 'next_due_date' => $this->calculateNextDueDate($pricingModel->billingCycle),
                        'notes' => implode("\n", $configurableOptionsForServiceNotes),
                    ]);
                }

                // Procesar Servicios Adicionales
                if (!empty($account['additional_services'])) {
                    foreach ($account['additional_services'] as $item) {
                        $productModel = Product::with('productType')->find($item['product_id']); // Ya validado
                        $pricingModel = ProductPricing::with('billingCycle')->find($item['pricing_id']); // Ya validado
                        $quantity = $item['quantity'] ?? 1;
                        $unitPrice = $item['price'];
                        $setupFee = $pricingModel->setup_fee ?? 0;
                        $itemTotalPrice = ($unitPrice * $quantity) + $setupFee;
                        $description = $item['product_name'] . ' (' . $pricingModel->billingCycle->name . ')' . ($domainNameForService ? ' - Associated with ' . $domainNameForService : '');

                        $invoiceItemsCollection[] = new InvoiceItem([
                            'product_id' => $item['product_id'], 'product_pricing_id' => $item['pricing_id'],
                            'description' => $description, 'quantity' => $quantity, 'unit_price' => $unitPrice,
                            'setup_fee' => $setupFee, 'total_price' => $itemTotalPrice,
                            'taxable' => $productModel->taxable ?? true, 'domain_name' => null,
                            'item_type' => $productModel->productType?->slug ?? 'additional_service',
                        ]);
                        $currentSubtotal += $itemTotalPrice;
                        $clientServicesCollection[] = new ClientService([
                            'client_id' => $client->id, 'product_id' => $item['product_id'],
                            'product_pricing_id' => $item['pricing_id'], 'billing_cycle_id' => $pricingModel->billing_cycle_id,
                            'domain_name' => $domainNameForService, 'status' => 'Pending',
                            'registration_date' => Carbon::now(), 'next_due_date' => $this->calculateNextDueDate($pricingModel->billingCycle),
                        ]);
                    }
                }
            }

            $invoice->subtotal = $currentSubtotal;
            if ($invoice->tax1_rate > 0) { $invoice->tax1_amount = round($currentSubtotal * ($invoice->tax1_rate / 100), 2); }
            if ($invoice->tax2_rate > 0) { $invoice->tax2_amount = round($currentSubtotal * ($invoice->tax2_rate / 100), 2); }
            $invoice->total_amount = round($currentSubtotal + $invoice->tax1_amount + $invoice->tax2_amount, 2);

            $invoice->save();
            if (!empty($invoiceItemsCollection)) { $invoice->items()->saveMany($invoiceItemsCollection); }
            foreach ($clientServicesCollection as $service) { $service->save(); }

            DB::commit();
            session()->forget('cart');
            return $invoice;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error placing order in PlaceOrderAction for client ID {$client->id}: " . $e->getMessage(), [
                'cart' => $cart, 'additional_data' => $additionalData, 'exception_trace' => $e->getTraceAsString()
            ]);
            throw $e; // Re-lanzar para que el controlador lo maneje
        }
    }

    private function validateCartItemsAvailability(array $cart): void
    {
        foreach ($cart['accounts'] as $account) {
            $this->validateCartItem($account['domain_info'] ?? null);
            $this->validateCartItem($account['primary_service'] ?? null, true); // true para validar opciones configurables
            if (!empty($account['additional_services'])) {
                foreach ($account['additional_services'] as $additionalService) {
                    $this->validateCartItem($additionalService);
                }
            }
        }
    }

    private function validateCartItem(?array $item, bool $checkConfigOptions = false): void
    {
        if (empty($item) || empty($item['product_id']) || empty($item['pricing_id'])) {
            // Ítem sin producto/precio (ej. solo nombre de dominio) es válido para no lanzar error aquí.
            // PlaceOrderAction lo omitirá si no tiene product_id.
            return;
        }

        $product = Product::find($item['product_id']);
        if (!$product || $product->status !== 'active') {
            throw new Exception("El producto '{$item['product_name']}' (ID: {$item['product_id']}) ya no está disponible o fue desactivado.");
        }

        $pricing = ProductPricing::find($item['pricing_id']);
        if (!$pricing || $pricing->product_id != $product->id) { // Podría añadirse chequeo de status para pricing si existe
            throw new Exception("La opción de precio seleccionada para '{$item['product_name']}' ya no es válida.");
        }

        if ($checkConfigOptions && isset($item['configurable_options']) && is_array($item['configurable_options'])) {
            foreach ($item['configurable_options'] as $groupId => $optionId) {
                $group = ConfigurableOptionGroup::find($groupId);
                $option = ConfigurableOption::find($optionId);
                if (!$group || !$option || $option->group_id != $group->id) {
                    throw new Exception("La opción configurable seleccionada (Grupo ID: {$groupId}, Opción ID: {$optionId}) para '{$item['product_name']}' ya no es válida.");
                }
                // Validar precio de la opción configurable
                $optionPricing = ConfigurableOptionPricing::where('configurable_option_id', $option->id)
                    ->where('billing_cycle_id', $pricing->billing_cycle_id)
                    ->first();
                if (!$optionPricing) { // Asumiendo que todas las opciones seleccionadas deben tener un precio para el ciclo de facturación
                    throw new Exception("El precio para la opción configurable '{$option->name}' del grupo '{$group->name}' para el ciclo de facturación seleccionado ya no es válido.");
                }
            }
        }
    }

    private function determineCurrencyCode(array $cart): string
    {
        foreach ($cart['accounts'] as $account) {
            $itemsToCheck = array_filter([
                $account['domain_info'] ?? null,
                $account['primary_service'] ?? null,
            ]);
            if (!empty($account['additional_services'])) {
                $itemsToCheck = array_merge($itemsToCheck, $account['additional_services']);
            }
            foreach ($itemsToCheck as $item) {
                if (!empty($item['currency_code'])) {
                    return $item['currency_code'];
                }
            }
        }
        return config('app.currency_code', 'USD'); // Fallback
    }

    private function calculateNextDueDate(BillingCycle $billingCycle): Carbon
    {
        $today = Carbon::today();
        switch ($billingCycle->cycle_unit) {
            case 'month': return $today->addMonths($billingCycle->cycle_multiplier);
            case 'year': return $today->addYears($billingCycle->cycle_multiplier);
            case 'day': return $today->addDays($billingCycle->cycle_multiplier);
            default:
                Log::warning("Unrecognized billing cycle unit: {$billingCycle->cycle_unit} for cycle ID {$billingCycle->id}. Defaulting to 1 month.");
                return $today->addMonth();
        }
    }

    // El método cartHasOnlyDomainRegistrationWithoutProduct ya no es necesario aquí, se elimina.
    // El método createInvoiceWithItems fue integrado y refactorizado dentro de execute, se elimina.
}

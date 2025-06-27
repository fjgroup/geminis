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
use Exception;

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
                'subtotal' => 0, 'tax1_rate' => $client->tax_rate_1 ?? 0,
                'tax1_description' => $client->tax_description_1 ?? 'Tax 1', 'tax1_amount' => 0,
                'tax2_rate' => $client->tax_rate_2 ?? 0,
                'tax2_description' => $client->tax_description_2 ?? 'Tax 2', 'tax2_amount' => 0,
                'total_amount' => 0, 'notes_to_client' => $notesToClient,
                'ip_address' => $ipAddress, 'payment_gateway_slug' => $paymentGatewaySlug,
            ]);

            $invoiceItemsCollection = [];
            $clientServicesCollection = [];
            $currentSubtotal = 0;

            foreach ($cart['accounts'] as $account) {
                $domainNameForService = $account['domain_info']['domain_name'] ?? null;

                // Procesar Registro de Dominio
                // $item['product_id'] y $item['pricing_id'] son ahora los IDs genéricos.
                // $item['tld_extension'] tiene la extensión real (ej. "com").
                // $item['override_price'] tiene el precio de NameSilo.
                if (isset($account['domain_info']['product_id'], $account['domain_info']['pricing_id'], $account['domain_info']['tld_extension'])) {
                    $item = $account['domain_info'];
                    $productModel = Product::find($item['product_id']); // Producto genérico de dominio
                    $pricingModel = ProductPricing::with('billingCycle')->find($item['pricing_id']); // Pricing genérico de dominio

                    $unitPrice = (isset($item['override_price']) && is_numeric($item['override_price']))
                                 ? (float) $item['override_price']
                                 : (float) $item['price']; // $item['price'] ya debería ser el override_price si se seteó en getCart

                    $setupFee = $pricingModel->setup_fee ?? 0;
                    $itemTotalPrice = $unitPrice + $setupFee;

                    // Descripción más específica usando la tld_extension
                    $description = "Registro de Dominio: {$item['domain_name']} ({$pricingModel->billingCycle->name})";
                    // O usar el nombre del producto genérico: $productModel->name ...

                    $invoiceItemsCollection[] = new InvoiceItem([
                        'product_id' => $item['product_id'], // ID del producto genérico
                        'product_pricing_id' => $item['pricing_id'], // ID del pricing genérico
                        'description' => $description, 'quantity' => 1, 'unit_price' => $unitPrice,
                        'setup_fee' => $setupFee, 'total_price' => $itemTotalPrice,
                        'taxable' => $productModel->taxable ?? true,
                        'domain_name' => $item['domain_name'], // FQDN
                        'item_type' => $productModel->productType?->slug ?? 'domain_registration',
                    ]);
                    $currentSubtotal += $itemTotalPrice;

                    $clientServicesCollection[] = new ClientService([
                        'client_id' => $client->id,
                        'product_id' => $item['product_id'], // ID del producto genérico
                        'product_pricing_id' => $item['pricing_id'], // ID del pricing genérico
                        'billing_cycle_id' => $pricingModel->billing_cycle_id,
                        'domain_name' => $item['domain_name'], 'status' => 'Pending',
                        'registration_date' => Carbon::now(),
                        'next_due_date' => $this->calculateNextDueDate($pricingModel->billingCycle),
                        // Podríamos guardar la tld_extension en 'notes' o un campo dedicado si ClientService lo tuviera
                        'notes' => "Extensión: .{$item['tld_extension']}",
                        'first_payment_amount' => $unitPrice, // Guardar el precio real pagado
                    ]);
                }

                // Procesar Servicio Principal
                if (!empty($account['primary_service'])) {
                    $item = $account['primary_service'];
                    $productModel = Product::with('productType', 'configurableOptionGroups.options.pricings') // Asegurar carga para validación y precios de opciones
                                        ->find($item['product_id']);
                    $pricingModel = ProductPricing::with('billingCycle')->find($item['pricing_id']);

                    $quantity = $item['quantity'] ?? 1;
                    $baseUnitPriceFromModel = $pricingModel->price;
                    $currentSetupFee = $pricingModel->setup_fee ?? 0;

                    $configurableOptionsDescriptionArray = [];
                    $configurableOptionsPriceAdjustment = 0.0;
                    $configurableOptionsForServiceNotes = [];

                    if (isset($item['configurable_options']) && is_array($item['configurable_options'])) {
                        foreach ($item['configurable_options'] as $groupId => $optionId) {
                            $group = $productModel->configurableOptionGroups->find($groupId);
                            $option = $group ? $group->options->find($optionId) : null;

                            if ($group && $option) {
                                $configurableOptionsDescriptionArray[] = $group->name . ': ' . $option->name;
                                $configurableOptionsForServiceNotes[] = $group->name . ': ' . $option->name;

                                // Usar la relación cargada para buscar el precio de la opción
                                $optionPricing = $option->pricings
                                    ->where('billing_cycle_id', $pricingModel->billing_cycle_id)
                                    ->first();

                                if ($optionPricing) {
                                    $configurableOptionsPriceAdjustment += $optionPricing->price;
                                    $currentSetupFee += $optionPricing->setup_fee ?? 0;
                                }
                            }
                        }
                    }

                    $finalUnitPrice = $baseUnitPriceFromModel + $configurableOptionsPriceAdjustment;
                    $itemTotalPrice = ($finalUnitPrice * $quantity) + $currentSetupFee;
                    // $item['product_name'] ya está enriquecido por getCart
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
                        'first_payment_amount' => $finalUnitPrice, // Guardar el precio real pagado
                    ]);
                }

                // Procesar Servicios Adicionales
                if (!empty($account['additional_services'])) {
                    foreach ($account['additional_services'] as $item) {
                        $productModel = Product::with('productType')->find($item['product_id']);
                        $pricingModel = ProductPricing::with('billingCycle')->find($item['pricing_id']);
                        $quantity = $item['quantity'] ?? 1;
                        $unitPrice = $item['price']; // Ya enriquecido por getCart
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
                            'first_payment_amount' => $unitPrice,
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
            throw $e;
        }
    }

    private function validateCartItemsAvailability(array $cart): void
    {
        foreach ($cart['accounts'] as $account) {
             if (isset($account['domain_info']['product_id']) && isset($account['domain_info']['pricing_id'])) {
                // Para dominios, product_name en el carrito podría ser el nombre del producto genérico.
                // La validación de `override_price` asegura que el precio de NameSilo esté presente si se espera.
                $this->validateCartItem($account['domain_info'], false, isset($account['domain_info']['override_price']));
            }

            $this->validateCartItem($account['primary_service'] ?? null, true);
            if (!empty($account['additional_services'])) {
                foreach ($account['additional_services'] as $additionalService) {
                    $this->validateCartItem($additionalService);
                }
            }
        }
    }

    private function validateCartItem(?array $item, bool $checkConfigOptions = false, bool $hasOverridePrice = false): void
    {
        if (empty($item) || empty($item['product_id']) || empty($item['pricing_id'])) {
            return;
        }

        $product = Product::find($item['product_id']);
        $productNameForError = $item['product_name'] ?? "Producto ID: {$item['product_id']}";
        // Si es un dominio con tld_extension, usar el FQDN para el mensaje de error
        if (isset($item['domain_name']) && isset($item['tld_extension'])) {
            $productNameForError = $item['domain_name'];
        }


        if (!$product || $product->status !== 'active') {
            throw new Exception("El producto '{$productNameForError}' ya no está disponible o fue desactivado.");
        }

        $pricing = ProductPricing::find($item['pricing_id']);
        if (!$pricing || $pricing->product_id != $product->id) {
            throw new Exception("La opción de precio seleccionada para '{$productNameForError}' ya no es válida.");
        }

        if ($hasOverridePrice) {
            if (!isset($item['override_price']) || !is_numeric($item['override_price'])) {
                 throw new Exception("Se esperaba un precio de registro especial para '{$productNameForError}' pero no se encontró o no es válido.");
            }
        }

        if ($checkConfigOptions && isset($item['configurable_options']) && is_array($item['configurable_options'])) {
            // Cargar opciones del producto para una validación más eficiente
            $productWithOptions = Product::with('configurableOptionGroups.options.pricings')->find($item['product_id']);

            foreach ($item['configurable_options'] as $groupId => $optionId) {
                $group = $productWithOptions->configurableOptionGroups->find($groupId);
                $option = $group ? $group->options->find($optionId) : null;

                if (!$group || !$option) { // No es necesario $option->group_id != $group->id porque ya se valida por la estructura
                    throw new Exception("La opción configurable seleccionada (Grupo ID: {$groupId}, Opción ID: {$optionId}) para '{$productNameForError}' ya no es válida.");
                }

                $optionPricing = $option->pricings->where('billing_cycle_id', $pricing->billing_cycle_id)->first();
                if (!$optionPricing) {
                    throw new Exception("El precio para la opción configurable '{$option->name}' del grupo '{$group->name}' para el ciclo de facturación seleccionado ya no es válido para '{$productNameForError}'.");
                }
            }
        }
    }

    private function determineCurrencyCode(array $cart): string { /* ... sin cambios ... */ }
    private function calculateNextDueDate(BillingCycle $billingCycle): Carbon { /* ... sin cambios ... */ }
}

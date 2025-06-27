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
                if (!empty($account['domain_info']['product_id']) && !empty($account['domain_info']['pricing_id'])) {
                    $item = $account['domain_info'];
                    $productModel = Product::find($item['product_id']);
                    $pricingModel = ProductPricing::with('billingCycle')->find($item['pricing_id']);

                    // Usar override_price si está disponible y es numérico; de lo contrario, usar el precio del item (que ya fue enriquecido)
                    $unitPrice = (isset($item['override_price']) && is_numeric($item['override_price']))
                                 ? (float) $item['override_price']
                                 : (float) $item['price'];

                    $setupFee = $pricingModel->setup_fee ?? 0; // El setup_fee para dominios suele ser 0 o incluido.
                    // Si override_price se considera que ya incluye cualquier "setup" de NameSilo, entonces setupFee podría ser forzado a 0 aquí.
                    // Por ahora, se mantiene el setup_fee del ProductPricing interno si override_price no lo cubre.

                    $itemTotalPrice = $unitPrice + $setupFee;
                    $description = $item['product_name'] . ' (' . $pricingModel->billingCycle->name . ') - ' . $item['domain_name'];

                    $invoiceItemsCollection[] = new InvoiceItem([
                        'product_id' => $item['product_id'],
                        'product_pricing_id' => $item['pricing_id'],
                        'description' => $description, 'quantity' => 1, 'unit_price' => $unitPrice,
                        'setup_fee' => $setupFee, 'total_price' => $itemTotalPrice,
                        'taxable' => $productModel->taxable ?? true, 'domain_name' => $item['domain_name'],
                        'item_type' => $productModel->productType?->slug ?? 'domain_registration',
                        // Guardar el override_price en el invoice_item para referencia/auditoría si es necesario
                        // Esto requeriría añadir una columna 'override_price' a la tabla 'invoice_items'.
                        // 'override_price' => (isset($item['override_price']) && is_numeric($item['override_price'])) ? (float)$item['override_price'] : null,
                    ]);
                    $currentSubtotal += $itemTotalPrice;
                    $clientServicesCollection[] = new ClientService([
                        'client_id' => $client->id, 'product_id' => $item['product_id'],
                        'product_pricing_id' => $item['pricing_id'], 'billing_cycle_id' => $pricingModel->billing_cycle_id,
                        'domain_name' => $item['domain_name'], 'status' => 'Pending',
                        'registration_date' => Carbon::now(), 'next_due_date' => $this->calculateNextDueDate($pricingModel->billingCycle),
                         // El precio de registro real (override_price) podría guardarse en 'first_payment_amount' en ClientService si existe tal campo.
                        // 'first_payment_amount' => $unitPrice,
                    ]);
                }

                // Procesar Servicio Principal (sin cambios en esta parte respecto a override_price)
                if (!empty($account['primary_service'])) {
                    $item = $account['primary_service'];
                    $productModel = Product::with('productType')->find($item['product_id']);
                    $pricingModel = ProductPricing::with('billingCycle')->find($item['pricing_id']);

                    $quantity = $item['quantity'] ?? 1;
                    $baseUnitPriceFromModel = $pricingModel->price; // Precio base del ProductPricing
                    $currentSetupFee = $pricingModel->setup_fee ?? 0;

                    $configurableOptionsDescriptionArray = [];
                    $configurableOptionsPriceAdjustment = 0.0;
                    $configurableOptionsForServiceNotes = [];

                    if (isset($item['configurable_options']) && is_array($item['configurable_options'])) {
                        foreach ($item['configurable_options'] as $groupId => $optionId) {
                            $group = ConfigurableOptionGroup::find($groupId);
                            $option = ConfigurableOption::find($optionId);

                            if ($group && $option) {
                                $configurableOptionsDescriptionArray[] = $group->name . ': ' . $option->name;
                                $configurableOptionsForServiceNotes[] = $group->name . ': ' . $option->name;
                                $optionPricing = ConfigurableOptionPricing::where('configurable_option_id', $option->id)
                                    ->where('billing_cycle_id', $pricingModel->billing_cycle_id)->first();
                                if ($optionPricing) {
                                    $configurableOptionsPriceAdjustment += $optionPricing->price;
                                    $currentSetupFee += $optionPricing->setup_fee ?? 0;
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

                // Procesar Servicios Adicionales (sin cambios)
                if (!empty($account['additional_services'])) {
                    foreach ($account['additional_services'] as $item) {
                        $productModel = Product::with('productType')->find($item['product_id']);
                        $pricingModel = ProductPricing::with('billingCycle')->find($item['pricing_id']);
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
            throw $e;
        }
    }

    private function validateCartItemsAvailability(array $cart): void
    {
        foreach ($cart['accounts'] as $account) {
            // Validar domain_info, incluyendo override_price si existe
             if (isset($account['domain_info']['product_id']) && isset($account['domain_info']['pricing_id'])) {
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
        if (!$product || $product->status !== 'active') {
            $productName = $item['product_name'] ?? "ID: {$item['product_id']}";
            throw new Exception("El producto '{$productName}' ya no está disponible o fue desactivado.");
        }

        $pricing = ProductPricing::find($item['pricing_id']);
        // Si hay override_price, el pricing interno es solo para referencia de ciclo/renovación, no para el precio inicial.
        // Pero aún así debe ser válido y pertenecer al producto.
        if (!$pricing || $pricing->product_id != $product->id) {
            $productName = $item['product_name'] ?? $product->name;
            throw new Exception("La opción de precio seleccionada para '{$productName}' ya no es válida.");
        }

        // Si hay un override_price, no necesitamos validar el precio del $pricing interno contra el $item['price']
        // porque el $item['price'] ya DEBERÍA ser el override_price (actualizado por getCart).
        // Pero sí validamos que el override_price sea numérico si existe en el item.
        if ($hasOverridePrice) {
            if (!isset($item['override_price']) || !is_numeric($item['override_price'])) {
                 $productName = $item['product_name'] ?? $product->name;
                 throw new Exception("Se esperaba un precio de registro especial para '{$productName}' pero no se encontró o no es válido.");
            }
        }


        if ($checkConfigOptions && isset($item['configurable_options']) && is_array($item['configurable_options'])) {
            foreach ($item['configurable_options'] as $groupId => $optionId) {
                $group = ConfigurableOptionGroup::find($groupId);
                $option = ConfigurableOption::find($optionId);
                $productName = $item['product_name'] ?? $product->name;
                if (!$group || !$option || $option->group_id != $group->id) {
                    throw new Exception("La opción configurable seleccionada (Grupo ID: {$groupId}, Opción ID: {$optionId}) para '{$productName}' ya no es válida.");
                }
                $optionPricing = ConfigurableOptionPricing::where('configurable_option_id', $option->id)
                    ->where('billing_cycle_id', $pricing->billing_cycle_id)
                    ->first();
                if (!$optionPricing) {
                    throw new Exception("El precio para la opción configurable '{$option->name}' del grupo '{$group->name}' para el ciclo de facturación seleccionado ya no es válido para '{$productName}'.");
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
        return config('app.currency_code', 'USD');
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
}

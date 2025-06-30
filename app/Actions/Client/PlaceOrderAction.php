<?php
namespace App\Actions\Client;

use App\Models\BillingCycle;
use App\Models\ClientService;
use App\Models\ConfigurableOption;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\OrderConfigurableOption;
use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\User;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlaceOrderAction
{
    public function execute(?User $client = null, array $additionalData = []): Invoice
    {
        $client = $client ?? Auth::user();
        if (! $client) {
            throw new Exception("Cliente no proporcionado o no autenticado.");
        }

        $cart = session()->get('cart');

        if (! $cart || empty($cart['accounts'])) {
            throw new Exception("El carrito está vacío o es inválido.");
        }

        $this->validateCartItemsAvailability($cart);

        DB::beginTransaction();

        try {
            $notesToClient      = $additionalData['notes_to_client'] ?? null;
            $ipAddress          = $additionalData['ip_address'] ?? request()->ip();
            $paymentGatewaySlug = $additionalData['payment_gateway_slug'] ?? null;

            $invoiceCurrencyCode = $this->determineCurrencyCode($cart);

            $invoice = new Invoice([
                'client_id'        => $client->id,
                'reseller_id'      => $client->reseller_id,
                'invoice_number'   => Invoice::generateInvoiceNumber(),
                'requested_date'   => Carbon::now(),
                'issue_date'       => Carbon::now()->toDateString(),
                'due_date'         => Carbon::now()->addDays(config('invoicing.due_days', 7))->toDateString(),
                'status'           => 'unpaid',
                'currency_code'    => $invoiceCurrencyCode,
                'subtotal'         => 0, 'tax1_rate'                                       => $client->tax_rate_1 ?? 0,
                'tax1_description' => $client->tax_description_1 ?? 'Tax 1', 'tax1_amount' => 0,
                'tax2_rate'        => $client->tax_rate_2 ?? 0,
                'tax2_description' => $client->tax_description_2 ?? 'Tax 2', 'tax2_amount' => 0,
                'total_amount'     => 0, 'notes_to_client'                                 => $notesToClient,
                'ip_address'       => $ipAddress, 'payment_gateway_slug'                   => $paymentGatewaySlug,
            ]);

            $invoiceItemsCollection   = [];
            $clientServicesCollection = [];
            $currentSubtotal          = 0;

            foreach ($cart['accounts'] as $account) {
                $domainNameForService = $account['domain_info']['domain_name'] ?? null;

                if (isset($account['domain_info']['product_id'], $account['domain_info']['pricing_id'], $account['domain_info']['tld_extension'])) {
                    $item         = $account['domain_info'];
                    $productModel = Product::find($item['product_id']);
                    $pricingModel = ProductPricing::with('billingCycle')->find($item['pricing_id']);

                    if (! $productModel || ! $pricingModel) {
                        Log::error("PlaceOrderAction: Producto o Pricing no encontrado para domain_info.", ['item' => $item]);
                        throw new Exception("Error al procesar el ítem de dominio: producto o precio no encontrado.");
                    }

                    $unitPrice = (isset($item['override_price']) && is_numeric($item['override_price']))
                    ? (float) $item['override_price']
                    : (float) $pricingModel->price;

                    $setupFee       = $pricingModel->setup_fee ?? 0.0;
                    $itemTotalPrice = $unitPrice + $setupFee;

                    $description = $productModel->name;
                    if ($pricingModel->billingCycle) {
                        $description .= ' (' . $pricingModel->billingCycle->name . ')';
                    }
                    $description .= ' - ' . $item['domain_name'];

                    $invoiceItemsCollection[] = new InvoiceItem([
                        'product_id'         => $productModel->id,
                        'product_pricing_id' => $pricingModel->id,
                        'description'        => $description, 'quantity' => 1, 'unit_price' => $unitPrice,
                        'setup_fee'          => $setupFee, 'total_price' => $itemTotalPrice,
                        'taxable'            => $productModel->taxable ?? true,
                        'domain_name'        => $item['domain_name'],
                        'item_type'          => $productModel->productType?->slug ?? 'domain_registration',
                    ]);
                    $currentSubtotal += $itemTotalPrice;

                    $clientServicesCollection[] = new ClientService([
                        'client_id'            => $client->id, 'product_id'             => $productModel->id,
                        'product_pricing_id'   => $pricingModel->id, 'billing_cycle_id' => $pricingModel->billing_cycle_id,
                        'domain_name'          => $item['domain_name'], 'status'        => 'pending',
                        'registration_date'    => Carbon::now(),
                        'next_due_date'        => $this->calculateNextDueDate($pricingModel->billingCycle),
                        'notes'                => "Extensión: .{$item['tld_extension']}",
                        'first_payment_amount' => $unitPrice,
                        'billing_amount'       => $unitPrice, // Usar unitPrice que incluye override_price
                    ]);
                }

                if (! empty($account['primary_service'])) {
                    $item         = $account['primary_service'];
                    $productModel = Product::with(['productType', 'configurableOptionGroups.options.pricings'])
                        ->find($item['product_id']);
                    $pricingModel = ProductPricing::with('billingCycle')->find($item['pricing_id']);

                    if (! $productModel || ! $pricingModel) {
                        Log::error("PlaceOrderAction: Producto o Pricing no encontrado para primary_service.", ['item' => $item]);
                        throw new Exception("Error al procesar el servicio principal: producto o precio no encontrado.");
                    }

                    $quantity        = $item['quantity'] ?? 1;
                    $baseUnitPrice   = (float) $pricingModel->price;
                    $currentSetupFee = (float) ($pricingModel->setup_fee ?? 0.0);

                    $configurableOptionsDescriptionArray = [];
                    $configurableOptionsPriceAdjustment  = 0.0;
                    $configurableOptionsForServiceNotes  = [];

                    // Buscar opciones configurables desde la tabla order_configurable_options usando cart_item_id
                    $cartItemId = $item['cart_item_id'] ?? null;
                    if ($cartItemId) {
                        $configurableOptions = OrderConfigurableOption::where('cart_item_id', $cartItemId)
                            ->where('is_active', true)
                            ->get();

                        foreach ($configurableOptions as $configOption) {
                            $quantity   = $configOption->quantity;
                            $groupName  = $configOption->group_name;
                            $optionName = $configOption->option_name;
                            $totalPrice = $configOption->total_price;

                            // Generar descripción para la factura
                            $description = $groupName . ': ' . $optionName;
                            if ($quantity > 1) {
                                $description .= " (Cantidad: {$quantity})";
                            }
                            $configurableOptionsDescriptionArray[] = $description;

                            // Generar nota detallada para el servicio
                            $serviceNote = $this->generateDetailedServiceNote($groupName, $optionName, $quantity);
                            if ($serviceNote) {
                                $configurableOptionsForServiceNotes[] = $serviceNote;
                            }

                            // Sumar al ajuste de precio
                            $configurableOptionsPriceAdjustment += (float) $totalPrice;
                        }
                    }

                    // Fallback: procesar opciones desde la estructura del carrito si no hay cart_item_id
                    if (empty($configurableOptionsForServiceNotes) && isset($item['configurable_options']) && is_array($item['configurable_options'])) {
                        foreach ($item['configurable_options'] as $optionId => $optionData) {
                            // Manejar nueva estructura de opciones configurables con cantidades
                            if (is_array($optionData) && isset($optionData['option_id'], $optionData['group_id'])) {
                                $option = ConfigurableOption::with('group')->find($optionData['option_id']);
                                $group  = $option ? $option->group : null;

                                if ($group && $option) {
                                    $quantity = $optionData['quantity'] ?? 1;

                                    // Generar descripción detallada para factura
                                    $description = $group->name . ': ' . $option->name;
                                    if ($option->option_type === 'quantity' && $quantity > 1) {
                                        $description .= " (Cantidad: {$quantity})";
                                    }
                                    $configurableOptionsDescriptionArray[] = $description;

                                    // Generar nota detallada para el servicio
                                    $serviceNote = $this->generateDetailedServiceNote($group->name, $option->name, $quantity);
                                    if ($serviceNote) {
                                        $configurableOptionsForServiceNotes[] = $serviceNote;
                                    }

                                    // Calcular ajuste de precio
                                    $optionPricing = $option->pricings
                                        ->where('billing_cycle_id', $pricingModel->billing_cycle_id)
                                        ->first();
                                    if ($optionPricing) {
                                        $priceAdjustment = (float) $optionPricing->price * $quantity;
                                        $configurableOptionsPriceAdjustment += $priceAdjustment;
                                        $currentSetupFee += (float) ($optionPricing->setup_fee ?? 0.0);
                                    }
                                }
                            } else {
                                // Mantener compatibilidad con estructura antigua (groupId => optionId)
                                $group  = $productModel->configurableOptionGroups->find($optionId);
                                $option = $group ? $group->options->find($optionData) : null;

                                if ($group && $option) {
                                    $configurableOptionsDescriptionArray[] = $group->name . ': ' . $option->name;
                                    $configurableOptionsForServiceNotes[]  = $group->name . ': ' . $option->name;

                                    $optionPricing = $option->pricings
                                        ->where('billing_cycle_id', $pricingModel->billing_cycle_id)
                                        ->first();
                                    if ($optionPricing) {
                                        $configurableOptionsPriceAdjustment += (float) $optionPricing->price;
                                        $currentSetupFee += (float) ($optionPricing->setup_fee ?? 0.0);
                                    }
                                }
                            }
                        }
                    }

                    $finalUnitPrice = $baseUnitPrice + $configurableOptionsPriceAdjustment;
                    $itemTotalPrice = ($finalUnitPrice * $quantity) + $currentSetupFee;

                    $description = $productModel->name;
                    if ($pricingModel->billingCycle) {
                        $description .= ' (' . $pricingModel->billingCycle->name . ')';
                    }
                    if (! empty($configurableOptionsDescriptionArray)) {
                        $description .= ' - Opciones: ' . implode('; ', $configurableOptionsDescriptionArray);
                    }
                    if ($domainNameForService) {$description .= ' - ' . $domainNameForService;}

                    $invoiceItemsCollection[] = new InvoiceItem([
                        'product_id'  => $productModel->id, 'product_pricing_id'       => $pricingModel->id,
                        'description' => $description, 'quantity'                      => $quantity, 'unit_price' => $finalUnitPrice,
                        'setup_fee'   => $currentSetupFee, 'total_price'               => $itemTotalPrice,
                        'taxable'     => $productModel->taxable ?? true, 'domain_name' => $domainNameForService,
                        'item_type'   => $productModel->productType?->slug ?? 'hosting_service',
                    ]);
                    $currentSubtotal += $itemTotalPrice;
                    $clientServicesCollection[] = new ClientService([
                        'client_id'            => $client->id, 'product_id'             => $productModel->id,
                        'product_pricing_id'   => $pricingModel->id, 'billing_cycle_id' => $pricingModel->billing_cycle_id,
                        'domain_name'          => $domainNameForService, 'status'       => 'pending',
                        'registration_date'    => Carbon::now(), 'next_due_date'        => $this->calculateNextDueDate($pricingModel->billingCycle),
                        'notes'                => implode("\n", $configurableOptionsForServiceNotes),
                        'first_payment_amount' => $finalUnitPrice,
                        'billing_amount'       => $finalUnitPrice,
                    ]);
                }

                if (! empty($account['additional_services'])) {
                    foreach ($account['additional_services'] as $item) {
                        $productModel = Product::with('productType')->find($item['product_id']);
                        $pricingModel = ProductPricing::with('billingCycle')->find($item['pricing_id']);

                        if (! $productModel || ! $pricingModel) {
                            Log::error("PlaceOrderAction: Producto o Pricing no encontrado para additional_service.", ['item' => $item]);
                            throw new Exception("Error al procesar un servicio adicional: producto o precio no encontrado.");
                        }

                        $quantity       = $item['quantity'] ?? 1;
                        $unitPrice      = (float) $pricingModel->price;
                        $setupFee       = (float) ($pricingModel->setup_fee ?? 0.0);
                        $itemTotalPrice = ($unitPrice * $quantity) + $setupFee;

                        $description = $productModel->name;
                        if ($pricingModel->billingCycle) {
                            $description .= ' (' . $pricingModel->billingCycle->name . ')';
                        }
                        if ($domainNameForService) {$description .= ' - Associated with ' . $domainNameForService;}

                        $invoiceItemsCollection[] = new InvoiceItem([
                            'product_id'  => $productModel->id, 'product_pricing_id'       => $pricingModel->id,
                            'description' => $description, 'quantity'                      => $quantity, 'unit_price' => $unitPrice,
                            'setup_fee'   => $setupFee, 'total_price'                      => $itemTotalPrice,
                            'taxable'     => $productModel->taxable ?? true, 'domain_name' => null,
                            'item_type'   => $productModel->productType?->slug ?? 'additional_service',
                        ]);
                        $currentSubtotal += $itemTotalPrice;
                        $clientServicesCollection[] = new ClientService([
                            'client_id'            => $client->id, 'product_id'             => $productModel->id,
                            'product_pricing_id'   => $pricingModel->id, 'billing_cycle_id' => $pricingModel->billing_cycle_id,
                            'domain_name'          => $domainNameForService, 'status'       => 'pending',
                            'registration_date'    => Carbon::now(), 'next_due_date'        => $this->calculateNextDueDate($pricingModel->billingCycle),
                            'first_payment_amount' => $unitPrice,
                            'billing_amount'       => $unitPrice,
                        ]);
                    }
                }
            }

            $invoice->subtotal = $currentSubtotal;
            if ($invoice->tax1_rate > 0) {$invoice->tax1_amount = round($currentSubtotal * ($invoice->tax1_rate / 100), 2);}
            if ($invoice->tax2_rate > 0) {$invoice->tax2_amount = round($currentSubtotal * ($invoice->tax2_rate / 100), 2);}
            $invoice->total_amount = round($currentSubtotal + $invoice->tax1_amount + $invoice->tax2_amount, 2);

            $invoice->save();
            if (! empty($invoiceItemsCollection)) {$invoice->items()->saveMany($invoiceItemsCollection);}
            foreach ($clientServicesCollection as $service) {$service->save();}

            DB::commit();
            session()->forget('cart');
            return $invoice;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error placing order in PlaceOrderAction for client ID {$client->id}: " . $e->getMessage(), [
                'cart' => $cart, 'additional_data' => $additionalData, 'exception_trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    private function validateCartItemsAvailability(array $cart): void
    {
        if (empty($cart['accounts'])) {
            throw new Exception("El carrito está vacío o no tiene cuentas.");
        }

        foreach ($cart['accounts'] as $accountIndex => $account) {
            if (empty($account['account_id'])) {
                throw new Exception("Cuenta inválida en el carrito (índice {$accountIndex}): falta account_id.");
            }

            $accountIdentifierForError = $account['domain_info']['domain_name'] ?? "Cuenta ID: {$account['account_id']}";

            if (isset($account['domain_info']) && ! empty($account['domain_info']['product_id'])) {
                $domainPricing = ProductPricing::with('billingCycle')->find($account['domain_info']['pricing_id']);
                if (! $domainPricing || ! $domainPricing->billingCycle) {
                    throw new Exception("Ciclo de facturación no encontrado para el servicio de dominio en '{$accountIdentifierForError}'.");
                }
                $this->validateCartItem($account['domain_info'], 'domain_info', $domainPricing->billingCycle, false, isset($account['domain_info']['override_price']));
            }

            if (isset($account['primary_service']) && ! empty($account['primary_service']['product_id'])) {
                $primaryServicePricing = ProductPricing::with('billingCycle')->find($account['primary_service']['pricing_id']);
                if (! $primaryServicePricing || ! $primaryServicePricing->billingCycle) {
                    throw new Exception("Ciclo de facturación no encontrado para el servicio principal en '{$accountIdentifierForError}'.");
                }
                $this->validateCartItem($account['primary_service'], 'primary_service', $primaryServicePricing->billingCycle, true, false);
            }

            if (isset($account['additional_services']) && is_array($account['additional_services'])) {
                foreach ($account['additional_services'] as $additionalServiceIndex => $additionalService) {
                    if (empty($additionalService['product_id']) || empty($additionalService['pricing_id'])) {
                        Log::warning("Servicio adicional malformado omitido en validación.", ['service_data' => $additionalService]);
                        continue;
                    }
                    $additionalServicePricing = ProductPricing::with('billingCycle')->find($additionalService['pricing_id']);
                    if (! $additionalServicePricing || ! $additionalServicePricing->billingCycle) {
                        $serviceName = $additionalService['product_name'] ?? "ID: {$additionalService['product_id']}";
                        throw new Exception("Ciclo de facturación no encontrado para el servicio adicional '{$serviceName}' en '{$accountIdentifierForError}'.");
                    }
                    $this->validateCartItem($additionalService, "additional_service[{$additionalServiceIndex}]", $additionalServicePricing->billingCycle, false, false);
                }
            }
        }
    }

    private function validateCartItem(?array $item, string $itemKeyInAccount, BillingCycle $itemBillingCycle, bool $checkConfigOptions = false, bool $hasOverridePrice = false): void
    {
        if (empty($item) || ! isset($item['product_id']) || ! isset($item['pricing_id'])) {
            Log::warning("PlaceOrderAction@validateCartItem: Ítem inválido o faltan product_id/pricing_id.", ['item' => $item, 'item_key' => $itemKeyInAccount]);
            throw new Exception("Un ítem en el carrito es inválido ({$itemKeyInAccount}). Contacte a soporte.");
        }

        $product             = Product::find($item['product_id']);
        $productNameForError = $item['product_name'] ?? "ID Prod:{$item['product_id']}";
        if (isset($item['domain_name']) && $itemKeyInAccount === 'domain_info') {
            $productNameForError = $item['domain_name'];
        }

        if (! $product || $product->status !== 'active') {
            throw new Exception("El producto '{$productNameForError}' ({$itemKeyInAccount}) ya no está disponible.");
        }

        $pricing = ProductPricing::find($item['pricing_id']);
        if (! $pricing || $pricing->product_id !== $product->id) {
            throw new Exception("La configuración de precio para '{$productNameForError}' ({$itemKeyInAccount}) es inválida.");
        }
        if ($pricing->billing_cycle_id !== $itemBillingCycle->id) {
            Log::error("Discrepancia de BillingCycle en validateCartItem", [
                'item_pricing_id' => $pricing->id, 'item_bc_id'        => $pricing->billing_cycle_id,
                'expected_bc_id'  => $itemBillingCycle->id, 'item_key' => $itemKeyInAccount,
            ]);
            throw new Exception("Error de consistencia en el ciclo de facturación para '{$productNameForError}' ({$itemKeyInAccount}).");
        }

        if ($itemKeyInAccount === 'domain_info' && $hasOverridePrice) {
            if (! isset($item['override_price']) || ! is_numeric($item['override_price']) || (float) $item['override_price'] < 0) {
                throw new Exception("El precio de registro para el dominio {$productNameForError} es inválido.");
            }
        }

        // Las opciones configurables ahora se validan cuando se guardan en order_configurable_options
        // No necesitamos validarlas aquí desde el carrito de sesión
        if ($itemKeyInAccount === 'primary_service' && $checkConfigOptions && isset($item['cart_item_id'])) {
            // Validar que existan opciones configurables válidas en la tabla dedicada
            $cartItemId          = $item['cart_item_id'];
            $configurableOptions = OrderConfigurableOption::where('cart_item_id', $cartItemId)
                ->where('is_active', true)
                ->get();

            // Log para debug - las opciones configurables se validaron al guardarlas
            if ($configurableOptions->isNotEmpty()) {
                Log::debug("PlaceOrderAction@validateCartItem: Encontradas {$configurableOptions->count()} opciones configurables válidas para '{$productNameForError}'.");
            }
        }
    }

    private function determineCurrencyCode(array $cart): string
    {
        if (isset($cart['accounts']) && count($cart['accounts']) > 0) {
            foreach ($cart['accounts'] as $account) {
                $itemsToScan = [];
                // Recolectar pricing_id de todos los items que lo tengan
                if (isset($account['domain_info']) && ! empty($account['domain_info']['pricing_id'])) {
                    $itemsToScan[] = $account['domain_info']['pricing_id'];
                }
                if (isset($account['primary_service']) && ! empty($account['primary_service']['pricing_id'])) {
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
                    $pricing = ProductPricing::find($pricingId); // Consultar solo una vez
                    if ($pricing && ! empty($pricing->currency_code) && is_string($pricing->currency_code)) {
                        Log::debug('PlaceOrderAction@determineCurrencyCode: Moneda determinada desde el ítem del carrito.', [
                            'pricing_id_for_currency' => $pricingId,
                            'currency_code'           => $pricing->currency_code,
                        ]);
                        return $pricing->currency_code;
                    }
                }
            }
        }

        $defaultCurrency = config('app.currency_code', 'USD');
        Log::warning('PlaceOrderAction@determineCurrencyCode: No se pudo determinar el código de moneda de los ítems del carrito. Usando por defecto.', [
            'active_account_id'     => $cart['active_account_id'] ?? 'N/A',
            'num_accounts'          => count($cart['accounts'] ?? []),
            'default_currency_used' => $defaultCurrency
        ]);
        return $defaultCurrency;
    }

    private function calculateNextDueDate(BillingCycle $billingCycle): Carbon
    {
        $startDate = Carbon::today();

        $multiplier = (int) ($billingCycle->cycle_multiplier ?? 1);
        if ($multiplier < 1) {
            Log::warning('PlaceOrderAction@calculateNextDueDate: Multiplicador de ciclo inválido o nulo, usando 1 por defecto.', [
                'billing_cycle_id'    => $billingCycle->id,
                'original_multiplier' => $billingCycle->cycle_multiplier,
            ]);
            $multiplier = 1;
        }

        $unitInput = $billingCycle->cycle_unit ?? 'month';
        $unit      = strtolower(trim($unitInput));
        if (empty($unit)) {
            Log::warning('PlaceOrderAction@calculateNextDueDate: cycle_unit estaba vacío o solo espacios, usando "month" por defecto.', [
                'billing_cycle_id' => $billingCycle->id,
                'original_unit'    => $billingCycle->cycle_unit,
            ]);
            $unit = 'month';
        }

        switch ($unit) {
            case 'day':case 'days':return $startDate->addDays($multiplier);
            case 'week':case 'weeks':return $startDate->addWeeks($multiplier);
            case 'month':case 'months':return $startDate->addMonthsNoOverflow($multiplier);
            case 'quarter':case 'quarters':return $startDate->addMonthsNoOverflow($multiplier * 3);
            case 'year':case 'years':return $startDate->addYearsNoOverflow($multiplier);
            default:
                Log::error('PlaceOrderAction@calculateNextDueDate: Unidad de ciclo de facturación desconocida o no manejada.', [
                    'billing_cycle_id'          => $billingCycle->id,
                    'cycle_unit_received'       => $unitInput,
                    'cycle_multiplier_received' => $billingCycle->cycle_multiplier ?? 'N/A',
                ]);
                return $startDate->addMonthsNoOverflow(1);
        }
    }

    /**
     * Generar nota detallada para el servicio basada en las opciones configurables
     */
    private function generateDetailedServiceNote(string $groupName, string $optionName, float $quantity): string
    {
        // Mapear nombres de grupos a descripciones más específicas
        $groupMappings = [
            'Espacio en Disco' => 'GB de espacio web adicional',
            'vCPU'             => 'vCPU adicionales',
            'vRam'             => 'GB de RAM adicional',
            'Memoria RAM'      => 'GB de RAM adicional',
            'Transferencia'    => 'GB de transferencia adicional',
            'Seguridad Email'  => 'servicio de seguridad email',
            'SpamExperts'      => 'protección SpamExperts',
            'Backup'           => 'servicios de backup adicionales',
            'SSL'              => 'certificados SSL adicionales',
        ];

        // Obtener la descripción específica o usar el nombre del grupo como fallback
        $description = $groupMappings[$groupName] ?? strtolower($groupName);

        // Generar la nota según el tipo de recurso
        if ($quantity > 1) {
            // Para cantidades mayores a 1, mostrar la cantidad específica
            if (str_contains($description, 'GB') || str_contains($description, 'vCPU')) {
                return "{$quantity} {$description}";
            } else {
                return "{$quantity} unidades de {$description}";
            }
        } else {
            // Para cantidad 1, usar descripción simple
            if (str_contains($description, 'servicio') || str_contains($description, 'protección')) {
                return ucfirst($description) . " activado";
            } else {
                return "1 {$description}";
            }
        }
    }
}

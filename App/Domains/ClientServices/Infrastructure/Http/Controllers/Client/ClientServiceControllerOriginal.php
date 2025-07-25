<?php
namespace App\Domains\ClientServices\Infrastructure\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Domains\ClientServices\Infrastructure\Persistence\Models\ClientService;
use App\Domains\Invoices\Infrastructure\Persistence\Models\Invoice;
use App\Domains\Invoices\Infrastructure\Persistence\Models\InvoiceItem;
use App\Domains\Products\Infrastructure\Persistence\Models\Product;
use App\Domains\Products\Infrastructure\Persistence\Models\ProductPricing;
use App\Domains\BillingAndPayments\Infrastructure\Persistence\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ClientServiceController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $user           = $request->user();
        $clientServices = $user->clientServices()
            ->with(['product', 'productPricing', 'billingCycle'])
            ->get();

        // Procesar servicios para agregar información de configuraciones adicionales
        $processedServices = [];

        foreach ($clientServices as $service) {
            // Parsear opciones configurables si existen
            $configurableOptionsData = null;
            if (! empty($service->notes)) {
                $configurableOptionsData = $this->parseConfigurableOptionsWithPrices($service);
            }

            // Agregar información de configuraciones al servicio base
            $service->configurable_options_details = $configurableOptionsData['options'] ?? [];
            $service->configurable_options_total   = $configurableOptionsData['total_price'] ?? 0;
            $service->has_configurable_options     = ! empty($configurableOptionsData['options']);

            // Agregar el servicio base con toda la información
            $processedServices[] = $service;

            // Si tiene opciones configurables, crear un item separado para mostrar el desglose
            if (! empty($configurableOptionsData['options'])) {
                // Crear un pseudo-servicio para las configuraciones adicionales
                $additionalConfigService = (object) [
                    'id'                           => $service->id . '_config',
                    'domain_name'                  => $service->domain_name,
                    'product'                      => (object) ['name' => 'Configuraciones Adicionales'],
                    'billingCycle'                 => $service->billingCycle,
                    'next_due_date'                => $service->next_due_date,
                    'status'                       => $service->status,
                    'billing_amount'               => $configurableOptionsData['total_price'],
                    'configurable_options_details' => $configurableOptionsData['options'],
                    'is_additional_config'         => true,
                    'parent_service_id'            => $service->id,
                ];

                $processedServices[] = $additionalConfigService;
            }
        }

        $clientServices          = collect($processedServices);
        $actionableInvoicesCount = $user->invoices()
            ->whereIn('status', ['pending_activation', 'pending_confirmation', 'unpaid'])
            ->count();
        $unpaidInvoicesCount = $user->invoices()
            ->where('status', 'unpaid')
            ->count();
        return Inertia::render('Client/Services/Index', [
            'clientServices'          => $clientServices,
            'actionableInvoicesCount' => $actionableInvoicesCount,
            'unpaidInvoicesCount'     => $unpaidInvoicesCount,
            'accountBalance'          => $user->balance,
            'formattedAccountBalance' => $user->formatted_balance,
        ]);
    }

    public function requestCancellation(Request $request, ClientService $service): RedirectResponse
    {
        $this->authorize('requestCancellation', $service);
        if (! ($service->status && in_array(strtolower($service->status), ['active', 'suspended']))) {
            return redirect()->back()->with('error', 'This service cannot be cancelled at its current stage.');
        }
        DB::beginTransaction();
        try {
            $sourceInvoiceId = $request->input('source_invoice_id');
            $service->status = 'pending_cancellation';
            $service->save();
            if ($sourceInvoiceId) {
                $invoice = \App\Models\Invoice::where('id', $sourceInvoiceId)
                    ->where('client_id', $request->user()->id)
                    ->where('status', 'unpaid')
                    ->first();
                if ($invoice) {
                    $isRenewalForThisService = $invoice->items()->where('client_service_id', $service->id)
                        ->where('item_type', 'renewal')
                        ->exists();
                    if ($isRenewalForThisService) {
                        $invoice->status = 'cancelled';
                        $invoice->save();
                        Log::info("Source renewal invoice ID {$invoice->id} cancelled due to service ID {$service->id} cancellation request by user ID {$request->user()->id}.");
                    } else {
                        Log::warning("Service cancellation for service ID {$service->id}: Source invoice ID {$sourceInvoiceId} provided but it's not a valid renewal invoice for this service for user ID {$request->user()->id}.");
                    }
                } else {
                    Log::warning("Service cancellation for service ID {$service->id}: Source invoice ID {$sourceInvoiceId} provided but not found, not owned by user {$request->user()->id}, or not unpaid.");
                }
            }
            DB::commit();
            return redirect()->route('client.services.index')->with('success', 'Tu solicitud de cancelación ha sido recibida y está pendiente de revisión.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Service cancellation request failed: ' . $e->getMessage(), [
                'client_service_id' => $service->id,
                'user_id'           => $request->user()->id,
                'source_invoice_id' => $sourceInvoiceId ?? null,
                'exception_trace'   => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Could not process cancellation request. Please try again.');
        }
    }

    public function showUpgradeDowngradeOptions(Request $request, ClientService $service): InertiaResponse
    {
        $this->authorize('viewUpgradeDowngradeOptions', $service);
        $service->loadMissing(['product.productType', 'productPricing.billingCycle']);
        if (! $service->product || ! $service->product->productType) {
            Log::error("Servicio ID {$service->id} no tiene producto o tipo de producto asociado.");
            return redirect()->route('client.services.index')->with('error', 'No se pudo determinar el tipo de producto del servicio actual.');
        }
        $currentProductTypeId   = $service->product->product_type_id;
        $productIdsWithSameType = Product::where('product_type_id', $currentProductTypeId)->pluck('id');
        $availableOptions       = ProductPricing::whereIn('product_id', $productIdsWithSameType)
            ->with(['billingCycle', 'product:id,name'])
            ->orderBy('product_id')->orderBy('price')->get();
        $discountInfo = [
            12 => ['percentage' => 18, 'text' => 'Ahorra 18%'],
            24 => ['percentage' => 26, 'text' => 'Ahorra 26%'],
        ];
        return Inertia::render('Client/Services/UpgradeDowngradeOptions', [
            'service'          => $service,
            'availableOptions' => $availableOptions,
            'discountInfo'     => $discountInfo,
        ]);
    }

    public function processUpgradeDowngrade(Request $request, ClientService $service): RedirectResponse
    {
        // FORCED UPDATE CHECK - PROCESS UPGRADE DOWNGRADE - 20250618
        $this->authorize('processUpgradeDowngrade', $service);
        $validated           = $request->validate(['new_product_pricing_id' => 'required|exists:product_pricings,id']);
        $newProductPricingId = $validated['new_product_pricing_id'];

        DB::beginTransaction();
        try {
            $service->loadMissing(['product.productType', 'productPricing.billingCycle', 'client']);
            $originalNextDueDate = Carbon::parse($service->getOriginal('next_due_date'));
            // Log::debug("PUD Log - Service ID: {$service->id}, Original Next Due Date: {$originalNextDueDate->toDateString()}");

            $newProductPricing = ProductPricing::with(['product.productType', 'billingCycle'])
                ->findOrFail($newProductPricingId);
            // dd('processUpgradeDowngrade START', $service->toArray(), $newProductPricing->toArray());

            if (strtolower($service->status) !== 'active') {
                DB::rollBack();
                return redirect()->route('client.services.index')->with('error', 'El servicio debe estar activo para cambiar de plan.');
            }
            if ($newProductPricing->id === $service->product_pricing_id) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Ya estás en este plan.');
            }
            if ($newProductPricing->product->product_type_id !== $service->product->product_type_id) {
                Log::warning("PUD Log - User ID {$request->user()->id} attempt to change service ID {$service->id} from product type {$service->product->product_type_id} to {$newProductPricing->product->product_type_id}. Operation blocked.");
                DB::rollBack();
                return redirect()->route('client.services.index')->with('error', 'No puedes cambiar a un tipo de producto diferente.');
            }

            $currentPricingForCredit = $service->productPricing;
            $currentCycleForCredit   = $currentPricingForCredit->billingCycle;
            $billingAmountForCredit  = $service->billing_amount;

            // Log::debug("PUD Log - Current ProductPricing ID for credit: {$currentPricingForCredit->id}");
            // Log::debug("PUD Log - Current Billing Amount for credit: {$billingAmountForCredit}");

            if (! $currentCycleForCredit) {
                Log::error("PUD Log - Error: El objeto currentCycleForCredit es null para el servicio ID: {$service->id}.");
                DB::rollBack();
                return redirect()->route('client.services.index')->with('error', 'Error de configuración interna del ciclo de facturación (actual). Contacte a soporte.');
            }
            $daysInCurrentCycleRaw = $currentCycleForCredit->getAttributeValue('days');
            // Log::debug("PUD Log - Current Cycle ID for credit: {$currentCycleForCredit->id}, Days (raw): " . gettype($daysInCurrentCycleRaw) . " - " . print_r($daysInCurrentCycleRaw, true));
            if (! is_numeric($daysInCurrentCycleRaw) || $daysInCurrentCycleRaw <= 0) {
                Log::error("PUD Log - Error: Configuración inválida para BillingCycle ID: {$currentCycleForCredit->id} (actual) - 'days' es inválido. Raw: " . print_r($daysInCurrentCycleRaw, true));
                DB::rollBack();
                return redirect()->route('client.services.index')->with('error', 'Error de configuración interna del ciclo de facturación (actual). Contacte a soporte.');
            }
            $daysInCurrentCycleForCreditCalc = (int) $daysInCurrentCycleRaw;
            // Log::debug("PUD Log - Days in Current Cycle for credit (validated): {$daysInCurrentCycleForCreditCalc}");

            $fechaInicioCicloActual = $originalNextDueDate->copy()->subDays($daysInCurrentCycleForCreditCalc); // Usa originalNextDueDate aquí
                                                                                                               // Log::debug("PUD Log - Fecha Inicio Ciclo Actual (calculada): {$fechaInicioCicloActual->toDateString()}");

            $hoy                 = Carbon::now()->startOfDay();
            $inicioCicloParaDiff = $fechaInicioCicloActual->copy()->startOfDay();
            // Log::debug("PUD Log - Fecha de 'Hoy' (para diff): {$hoy->toDateString()}");
            // dd('processUpgradeDowngrade - Antes de diasUtilizados', [
            //     'hoy' => $hoy->toDateString(),
            //     'inicioCicloParaDiff' => $inicioCicloParaDiff->toDateString(),
            //     'originalNextDueDate' => $originalNextDueDate->toDateString(),
            //     'daysInCurrentCycleForCreditCalc' => $daysInCurrentCycleForCreditCalc,
            //     'billingAmountForCredit' => $billingAmountForCredit,
            //     'service_original_next_due_date' => $service->getOriginal('next_due_date')
            // ]);

            // Copia la misma lógica de cálculo de $diasUtilizadosPlanActual de calculateProration
            if ($hoy->lt($inicioCicloParaDiff)) {
                $diasUtilizadosPlanActual = 0;
                // Log::debug("PUD Log - Hoy ({$hoy->toDateString()}) es anterior al inicio del ciclo ({$inicioCicloParaDiff->toDateString()}). Días Utilizados inicial = 0.");
            } else {
                $diasTranscurridos = $hoy->diffInDays($inicioCicloParaDiff);
                // Log::debug("PUD Log - Días Transcurridos Brutos (diffInDays hoy e inicioCiclo): {$diasTranscurridos}");

                if ($hoy->gte($inicioCicloParaDiff) && $diasTranscurridos < 0) {
                    Log::warning("PUD Log - diffInDays retornó un valor negativo anómalo. Forzando a positivo.", ['raw_diff' => $diasTranscurridos]);
                    $diasTranscurridos = abs($diasTranscurridos);
                }

                $diasUtilizadosPlanActual = $diasTranscurridos + 1;
                // Log::debug("PUD Log - Días Utilizados después de sumar 1: {$diasUtilizadosPlanActual}");
            }

            $diasUtilizadosPlanActual = min($diasUtilizadosPlanActual, $daysInCurrentCycleForCreditCalc); // Usa $daysInCurrentCycleForCreditCalc aquí
                                                                                                          // Log::debug("PUD Log - Días Utilizados después de min(diasDelCiclo): {$diasUtilizadosPlanActual}");

                                                                                    // Esta condición 'max(1, ...)' ya estaba presente y es ligeramente diferente, la mantenemos así por ahora
                                                                                    // y vemos el resultado. La lógica original era:
                                                                                    // if ($hoy->gte($inicioCicloParaDiff) && $diasUtilizadosPlanActual < 1) {
                                                                                    //     $diasUtilizadosPlanActual = 1; ...}
                                                                                    // if ($hoy->gte($inicioCicloParaDiff)) { $diasUtilizadosPlanActual = max(1, $diasUtilizadosPlanActual); }
                                                                                    // La nueva lógica unificada de arriba ya cubre el ajuste a 1 si es necesario y el ciclo está activo.
                                                                                    // Así que la siguiente línea que ajustaba a max(1,..) podría ser redundante o necesitar revisión si el resultado no es el esperado.
                                                                                    // Por ahora, confiamos en la lógica copiada.
            if ($hoy->gte($inicioCicloParaDiff) && $diasUtilizadosPlanActual < 1) { // Re-aplicar la condición de ajuste a 1.
                $diasUtilizadosPlanActual = 1;
                // Log::debug("PUD Log - Días Utilizados ajustado a 1 porque hoy >= inicioCiclo y cálculo < 1 (después de min).");
            }
            // Log::debug("PUD Log - Días Utilizados Plan Actual (final refinado): {$diasUtilizadosPlanActual}");

            $tarifaDiariaPlanActual = ($daysInCurrentCycleForCreditCalc > 0 && $billingAmountForCredit > 0) ? ($billingAmountForCredit / $daysInCurrentCycleForCreditCalc) : 0;
            // Log::debug("PUD Log - Tarifa Diaria Plan Actual: {$tarifaDiariaPlanActual}");
            $costoUtilizadoPlanActual = $tarifaDiariaPlanActual * $diasUtilizadosPlanActual;
            // Log::debug("PUD Log - Costo Utilizado Plan Actual: {$costoUtilizadoPlanActual}");
            $creditoNoUtilizado = $billingAmountForCredit - $costoUtilizadoPlanActual;
            $creditoNoUtilizado = max(0, round($creditoNoUtilizado, 2));
            // Log::debug("PUD Log - Crédito No Utilizado: {$creditoNoUtilizado}");
            // dd('processUpgradeDowngrade - Despues de creditoNoUtilizado', [
            //     'diasUtilizadosPlanActual' => $diasUtilizadosPlanActual,
            //     'tarifaDiariaPlanActual' => $tarifaDiariaPlanActual,
            //     'costoUtilizadoPlanActual' => $costoUtilizadoPlanActual,
            //     'creditoNoUtilizado' => $creditoNoUtilizado,
            //     'billingAmountForCredit' => $billingAmountForCredit
            // ]);

            $newCycle = $newProductPricing->billingCycle;
            if (! $newCycle) {
                Log::error("PUD Log - Error: El objeto newCycle es null para ProductPricing ID: {$newProductPricing->id}.");
                DB::rollBack();return redirect()->route('client.services.index')->with('error', 'Error de configuración interna del ciclo de facturación (nuevo). Contacte a soporte.');
            }
            $daysRawValueNew = $newCycle->getAttributeValue('days');
            // Log::debug("PUD Log - New Cycle ID: {$newCycle->id}, Days (raw): " . gettype($daysRawValueNew) . " - " . print_r($daysRawValueNew, true));
            if (! is_numeric($daysRawValueNew) || $daysRawValueNew <= 0) {
                Log::error("PUD Log - Error: Configuración inválida para BillingCycle ID: {$newCycle->id} (nuevo) - 'days' es inválido. Raw: " . print_r($daysRawValueNew, true));
                DB::rollBack();return redirect()->route('client.services.index')->with('error', 'Error de configuración interna del ciclo de facturación (nuevo). Contacte a soporte.');
            }
            $daysInNewCycle = (int) $daysRawValueNew;

            $precioTotalNuevoPlan = $newProductPricing->price;
            // Log::debug("PUD Log - Precio Total Nuevo Plan: {$precioTotalNuevoPlan}");
            $montoFinal = $precioTotalNuevoPlan - $creditoNoUtilizado;
            $montoFinal = round($montoFinal, 2);
            // Log::debug("PUD Log - Monto Final [PrecioNuevo - CreditoNoUtilizado]: {$montoFinal}");

            $invoiceToPay           = null;
            $originalProductIdValue = $service->getOriginal('product_id');
            $old_product_name       = $service->product->name;
            $old_cycle_name         = $currentCycleForCredit->name;

            // Define original NDD string for notes
            $note_original_next_due_date_str = $originalNextDueDate->format('Y-m-d');

            $notesForService = "Actualización completa de plan de '{$old_product_name} ({$old_cycle_name})' a '{$newProductPricing->product->name} ({$newCycle->name})' el " . Carbon::now()->toDateTimeString() . ".";
            $notesForService .= " Fecha de vencimiento original del servicio: " . $note_original_next_due_date_str . ".";
            $notesForService .= " Crédito por tiempo no utilizado del plan anterior: {$creditoNoUtilizado} {$currentPricingForCredit->currency_code}.";
            $notesForService .= " Costo del nuevo plan (primer ciclo): {$precioTotalNuevoPlan} {$newProductPricing->currency_code}.";

            if ($montoFinal > 0) {
                $invoiceNumber = 'INV-UPGRADE-' . strtoupper(Str::random(8));
                $newInvoice    = Invoice::create([
                    'client_id'       => $service->client_id, 'reseller_id' => $service->client->reseller_id,
                    'invoice_number'  => $invoiceNumber, 'issue_date'       => Carbon::now(), 'due_date'   => Carbon::now(),
                    'status'          => 'unpaid', 'subtotal'               => $montoFinal, 'total_amount' => $montoFinal,
                    'currency_code'   => $newProductPricing->currency_code,
                    'notes_to_client' => "Cargo por actualización de plan de '{$old_product_name}' a '{$newProductPricing->product->name}'.",
                ]);
                InvoiceItem::create([
                    'invoice_id'         => $newInvoice->id,
                    'client_service_id'  => $service->id,
                    'product_id'         => $newProductPricing->product_id, // Added
                    'product_pricing_id' => $newProductPricing->id,         // Added
                    'description'        => "Cargo por actualización a {$newProductPricing->product->name} ({$newCycle->name})",
                    'quantity'           => 1,
                    'unit_price'         => $montoFinal,
                    'total_price'        => $montoFinal,
                    'taxable'            => $newProductPricing->product->taxable ?? false,
                ]);
                $invoiceToPay = $newInvoice;
                $notesForService .= " Se generó la factura {$invoiceNumber} por {$montoFinal} {$newProductPricing->currency_code}.";
            } elseif ($montoFinal < 0) {
                $creditToBalance = abs($montoFinal);
                $client          = $service->client;
                $client->balance += $creditToBalance;
                $client->save();

                Transaction::create([
                    'client_id'              => $service->client_id,
                    'reseller_id'            => $service->client->reseller_id,
                    'invoice_id'             => null,
                    'payment_method_id'      => null,
                    'gateway_slug'           => 'system_credit',
                    'gateway_transaction_id' => 'CREDIT-' . strtoupper(Str::random(10)),
                    'type'                   => 'credit_added',
                    'amount'                 => $creditToBalance,
                    'currency_code'          => $currentPricingForCredit->currency_code,
                    'status'                 => 'completed',
                    'description'            => "Crédito por actualización de plan de '{$old_product_name} ({$old_cycle_name})' a '{$newProductPricing->product->name} ({$newCycle->name})'.",
                    'transaction_date'       => Carbon::now(),
                ]);

                $notesForService .= " Se acreditó " . abs($montoFinal) . " {$currentPricingForCredit->currency_code} al balance del cliente. (Transacción de crédito registrada).";
            } else {
                $notesForService .= " La actualización no generó costos adicionales ni créditos inmediatos.";
            }

            $newEffectiveNextDueDate = Carbon::now()->startOfDay()->addDays($daysInNewCycle);
            $notesForService .= " Nueva fecha de vencimiento del servicio: " . $newEffectiveNextDueDate->format('Y-m-d') . ".";

            $service->product_id         = $newProductPricing->product_id;
            $service->product_pricing_id = $newProductPricing->id;
            $service->billing_cycle_id   = $newProductPricing->billing_cycle_id;
            $service->billing_amount     = $newProductPricing->price;

            $service->next_due_date = $newEffectiveNextDueDate;

            $service->notes = ($service->getOriginal('notes') ? trim($service->getOriginal('notes')) . "\n" : '') . trim($notesForService);

            if ($originalProductIdValue !== $newProductPricing->product_id && $service->status !== 'pending_configuration') {
                $service->status = 'pending_configuration';
            }

            $service->save();
            DB::commit();

            $successMessage = "Plan actualizado de '{$old_product_name} ({$old_cycle_name})' a '{$newProductPricing->product->name} ({$newCycle->name})'.";
            $successMessage .= " Tu nueva fecha de vencimiento es el " . $newEffectiveNextDueDate->format('d/m/Y') . ".";

            // Determinar la ruta de redirección y el mensaje específico
            if ($invoiceToPay) {
                // Si se generó una factura
                $successMessage .= " Se generó la factura {$invoiceToPay->invoice_number} por {$invoiceToPay->total_amount_formatted} para la actualización.";
                if ($service->status === 'pending_configuration') {
                    $successMessage .= " El servicio requiere configuración adicional por un administrador.";
                }
                return redirect()->route('client.invoices.show', $invoiceToPay->id)->with('success', $successMessage);
            } elseif ($montoFinal < 0) {
                // Si se acreditó saldo
                // $creditToBalance is already defined if $montoFinal < 0
                $successMessage .= " Se acreditó " . abs($montoFinal) . " {$currentPricingForCredit->currency_code} a tu balance.";
                if ($service->status === 'pending_configuration') {
                    $successMessage .= " El servicio requiere configuración adicional por un administrador.";
                }
                return redirect()->route('client.transactions.index')->with('success', $successMessage);
            } else {
                // Si no hubo costo ni crédito
                $successMessage .= " La actualización no tuvo costo adicional inmediato.";
                if ($service->status === 'pending_configuration') {
                    $successMessage .= " El servicio requiere configuración adicional por un administrador.";
                }
                return redirect()->route('client.services.index')->with('success', $successMessage);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PUD Log - Error en processUpgradeDowngrade para servicio ID ' . ($service->id ?? 'desconocido') . ': ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return redirect()->route('client.services.index')->with('error', 'No se pudo procesar tu solicitud de cambio de plan. Inténtalo de nuevo.');
        }
    }

    public function calculateProration(Request $request, ClientService $service): JsonResponse
    {
        // FORCED UPDATE CHECK - CALCULATE PRORATION - 20250618
        $this->authorize('view', $service);
        $validated           = $request->validate(['new_product_pricing_id' => 'required|exists:product_pricings,id']);
        $newProductPricingId = $validated['new_product_pricing_id'];

        try {
            $service->loadMissing(['product.productType', 'productPricing.billingCycle', 'client']);
            $newProductPricingLoaded = ProductPricing::with(['product.productType', 'billingCycle'])
                ->findOrFail($newProductPricingId);

            if (strtolower($service->status) !== 'active') {
                return response()->json(['error' => 'El servicio debe estar activo para calcular el prorrateo.'], 422);
            }
            if ($newProductPricingLoaded->id === $service->product_pricing_id) {
                return response()->json(['error' => 'Esta selección es tu plan actual.'], 422);
            }
            if ($newProductPricingLoaded->product->product_type_id !== $service->product->product_type_id) {
                return response()->json(['error' => 'No puedes cambiar a un tipo de producto diferente.'], 422);
            }

            // Log::debug("PRORATE_CALC_DEBUG: --- Iniciando Cálculo de Prorrateo para Service ID: {$service->id} ---");
            // Log::debug("PRORATE_CALC_DEBUG: Hoy (Carbon::now()->startOfDay()): " . Carbon::now()->startOfDay()->toDateString());
            // Log::debug("PRORATE_CALC_DEBUG: Service Current ProductPricing ID: {$service->product_pricing_id}");
            // Log::debug("PRORATE_CALC_DEBUG: Service Current Billing Amount (PrecioPlanActual para crédito): {$service->billing_amount}");
            // Log::debug("PRORATE_CALC_DEBUG: Service Current Next Due Date (raw): {$service->next_due_date}");

            $currentPricing                = $service->productPricing;
            $currentCycle                  = $currentPricing->billingCycle;
            $originalNextDueDateForPreview = Carbon::parse($service->next_due_date);
            // Log::debug("PRORATE_CALC_DEBUG: Original Next Due Date (para preview y base de cálculo, Carbon parsed): {$originalNextDueDateForPreview->toDateString()}");

            if (! $currentCycle) {
                Log::error("PRORATE_CALC_DEBUG: Error - El objeto currentCycle es null para el servicio ID: {$service->id}.");
                return response()->json(['error' => 'Error de configuración interna del ciclo de facturación (actual). Contacte a soporte.'], 500);
            }
            $daysInCurrentCycleRaw = $currentCycle->getAttributeValue('days');
            // Log::debug("PRORATE_CALC_DEBUG: Current Cycle ID: {$currentCycle->id}, Days (raw from getAttributeValue): " . gettype($daysInCurrentCycleRaw) . " - " . print_r($daysInCurrentCycleRaw, true));
            if (! is_numeric($daysInCurrentCycleRaw) || $daysInCurrentCycleRaw <= 0) {
                Log::error("PRORATE_CALC_DEBUG: Error - Configuración inválida para BillingCycle ID: {$currentCycle->id} (actual) - 'days' es inválido. Raw: " . print_r($daysInCurrentCycleRaw, true));
                return response()->json(['error' => 'Error de configuración interna del ciclo de facturación (actual). Contacte a soporte.'], 500);
            }
            $daysInCurrentCycle = (int) $daysInCurrentCycleRaw;
            // Log::debug("PRORATE_CALC_DEBUG: Days in Current Cycle (usado para cálculo): {$daysInCurrentCycle}");

            $fechaInicioCicloActual = $originalNextDueDateForPreview->copy()->subDays($daysInCurrentCycle);
            // Log::debug("PRORATE_CALC_DEBUG: Fecha Inicio Ciclo Actual (calculada): {$fechaInicioCicloActual->toDateString()}");

            $hoy                 = Carbon::now()->startOfDay();
            $inicioCicloParaDiff = $fechaInicioCicloActual->copy()->startOfDay();
            // Log::debug("PRORATE_CALC_DEBUG: Fecha de 'Hoy' (para diff): {$hoy->toDateString()}");
            // Log::debug('calculateProration - Antes de diasUtilizados', [
            //     'service_id' => $service->id,
            //     'new_product_pricing_id' => $newProductPricingId,
            //     'hoy' => $hoy->toDateString(),
            //     'inicioCicloParaDiff' => $inicioCicloParaDiff->toDateString(),
            //     'originalNextDueDateForPreview' => $originalNextDueDateForPreview->toDateString(),
            //     'daysInCurrentCycle' => $daysInCurrentCycle,
            //     'billing_amount' => $service->billing_amount
            // ]);

            if ($hoy->lt($inicioCicloParaDiff)) {
                $diasUtilizadosPlanActual = 0;
                // Log::debug("PRORATE_CALC_DEBUG: Hoy ({$hoy->toDateString()}) es anterior al inicio del ciclo ({$inicioCicloParaDiff->toDateString()}). Días Utilizados inicial = 0.");
            } else {
                // Calcular días transcurridos. diffInDays debería devolver un valor positivo
                // ya que $abs = true por defecto.
                $diasTranscurridos = $hoy->diffInDays($inicioCicloParaDiff);

                // Log para verificar el valor crudo de diffInDays
                // Log::debug("PRORATE_CALC_DEBUG: Días Transcurridos Brutos (diffInDays entre hoy e inicioCiclo): {$diasTranscurridos}");

                // Salvaguarda: Si $hoy es posterior o igual a inicioCicloParaDiff,
                // diasTranscurridos no debería ser negativo. Si lo es, es anómalo, pero lo corregimos.
                if ($hoy->gte($inicioCicloParaDiff) && $diasTranscurridos < 0) {
                    Log::warning("PRORATE_CALC_DEBUG: diffInDays retornó un valor negativo anómalo. Forzando a positivo.", ['raw_diff' => $diasTranscurridos]);
                    $diasTranscurridos = abs($diasTranscurridos);
                }

                // Se considera que el día actual es un día completo de uso.
                // Por ejemplo, si el ciclo empezó hoy, hoy es 1 día de uso.
                // Si el ciclo empezó ayer, y hoy es el cambio, son 2 días de uso.
                // diffInDays entre hoy y ayer es 1. 1+1 = 2.
                $diasUtilizadosPlanActual = $diasTranscurridos + 1;
                // Log::debug("PRORATE_CALC_DEBUG: Días Utilizados después de sumar 1 (considerando día actual): {$diasUtilizadosPlanActual}");
            }

            // Esta lógica de min y ajuste a 1 si es < 1 (y el ciclo está vigente) se mantiene:
            $diasUtilizadosPlanActual = min($diasUtilizadosPlanActual, $daysInCurrentCycle);
            // Log::debug("PRORATE_CALC_DEBUG: Días Utilizados después de min(diasDelCiclo): {$diasUtilizadosPlanActual}");

            if ($hoy->gte($inicioCicloParaDiff) && $diasUtilizadosPlanActual < 1) {
                $diasUtilizadosPlanActual = 1;
                // Log::debug("PRORATE_CALC_DEBUG: Días Utilizados ajustado a 1 porque hoy >= inicioCiclo y cálculo < 1.");
            }
            // Fin de la lógica de $diasUtilizadosPlanActual
            // Log::debug("PRORATE_CALC_DEBUG: Días Utilizados Plan Actual (final refinado): {$diasUtilizadosPlanActual}");

            $tarifaDiariaPlanActual = ($daysInCurrentCycle > 0 && $service->billing_amount > 0) ? ($service->billing_amount / $daysInCurrentCycle) : 0;
            // Log::debug("PRORATE_CALC_DEBUG: Tarifa Diaria Plan Actual (calculada): {$tarifaDiariaPlanActual}");
            $costoUtilizadoPlanActual = $tarifaDiariaPlanActual * $diasUtilizadosPlanActual;
            // Log::debug("PRORATE_CALC_DEBUG: Costo Utilizado Plan Actual (calculado): {$costoUtilizadoPlanActual}");
            $creditoNoUtilizado = $service->billing_amount - $costoUtilizadoPlanActual;
            $creditoNoUtilizado = max(0, round($creditoNoUtilizado, 2));
            // Log::debug("PRORATE_CALC_DEBUG: Crédito No Utilizado (calculado): {$creditoNoUtilizado}");
            // Log::debug('calculateProration - Despues de creditoNoUtilizado', [
            //     'service_id' => $service->id,
            //     'diasUtilizadosPlanActual' => $diasUtilizadosPlanActual,
            //     'tarifaDiariaPlanActual' => $tarifaDiariaPlanActual,
            //     'costoUtilizadoPlanActual' => $costoUtilizadoPlanActual,
            //     'creditoNoUtilizado' => $creditoNoUtilizado,
            //     'service_billing_amount' => $service->billing_amount
            // ]);

            $newCycle = $newProductPricingLoaded->billingCycle;
            // Log::debug("PRORATE_CALC_DEBUG: New ProductPricing ID: {$newProductPricingLoaded->id}");
            // Log::debug("PRORATE_CALC_DEBUG: Precio Total Nuevo Plan: {$newProductPricingLoaded->price}");

            if (! $newCycle) {
                Log::error("PRORATE_CALC_DEBUG: Error - El objeto newCycle es null para ProductPricing ID: {$newProductPricingLoaded->id}.");
                return response()->json(['error' => 'Error de configuración interna del ciclo de facturación (nuevo). Contacte a soporte.'], 500);
            }
            $daysInNewCycleRaw = $newCycle->getAttributeValue('days');
            // Log::debug("PRORATE_CALC_DEBUG: New Cycle ID: {$newCycle->id}, Days (raw from getAttributeValue): " . gettype($daysInNewCycleRaw) . " - " . print_r($daysInNewCycleRaw, true));
            if (! is_numeric($daysInNewCycleRaw) || $daysInNewCycleRaw <= 0) {
                Log::error("PRORATE_CALC_DEBUG: Error - Configuración inválida para BillingCycle ID: {$newCycle->id} (nuevo) - 'days' es inválido. Raw: " . print_r($daysInNewCycleRaw, true));
                return response()->json(['error' => 'Error de configuración interna del ciclo de facturación (nuevo). Contacte a soporte.'], 500);
            }
            $daysInNewCycle = (int) $daysInNewCycleRaw;

            $precioTotalNuevoPlan = $newProductPricingLoaded->price;
            $montoFinal           = $precioTotalNuevoPlan - $creditoNoUtilizado;
            $montoFinal           = round($montoFinal, 2);
            // Log::debug("PRORATE_CALC_DEBUG: Monto Final (calculado) [PrecioNuevo - CreditoNoUtilizado]: {$montoFinal}");

            $newNextDueDatePreview       = Carbon::now()->startOfDay()->addDays($daysInNewCycle);
            $newNextDueDatePreviewString = $newNextDueDatePreview->toDateString();
            // Log::debug("PRORATE_CALC_DEBUG: New Next Due Date Preview (calculada: hoy + {$daysInNewCycle} days): {$newNextDueDatePreviewString}");

            $message = $montoFinal > 0 ? 'Monto a pagar para la actualización completa.' : ($montoFinal < 0 ? 'Crédito a tu balance por la actualización.' : 'Actualización completa sin costo adicional inmediato.');
            $message .= " Tu nueva fecha de vencimiento será el " . $newNextDueDatePreview->format('d/m/Y') . "."; // Updated message
                                                                                                                    // Log::debug("PRORATE_CALC_DEBUG: --- Fin Cálculo de Prorrateo ---");

            return response()->json([
                'prorated_amount'           => $montoFinal,
                'currency_code'             => $newProductPricingLoaded->currency_code ?? $currentPricing->currency_code,
                'message'                   => $message,
                'new_next_due_date_preview' => $newNextDueDatePreviewString,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('PRORATE_CALC_DEBUG: Error - ModelNotFoundException. new_product_pricing_id: ' . $newProductPricingId . ' - ' . $e->getMessage());
            return response()->json(['error' => 'El plan seleccionado no es válido.'], 404);
        } catch (\Exception $e) {
            Log::error('PRORATE_CALC_DEBUG: Error en calculateProration para el servicio ID ' . ($service->id ?? 'desconocido') . ': ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'No se pudo calcular el monto de prorrateo.'], 500);
        }
    }

    public function updatePassword(Request $request, ClientService $service): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('updatePassword', $service);
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password'     => ['required', 'string', Password::min(12)->mixedCase()->numbers()->symbols()->uncompromised(), 'confirmed'],
        ]);
        DB::beginTransaction();
        try {
            if (! Hash::check($validated['current_password'], $service->password_encrypted)) {
                throw ValidationException::withMessages(['current_password' => __('La contraseña actual proporcionada es incorrecta.')]);
            }
            $service->password_encrypted = Hash::make($validated['new_password']);
            $service->save();
            Log::info("Password updated for service ID {$service->id} by user ID {$request->user()->id}.");
            DB::commit();
            return redirect()->back()->with('success', 'Contraseña actualizada con éxito.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            DB::rollBack();
            Log::warning('Password update authorization failed: ' . $e->getMessage(), ['client_service_id' => $service->id, 'user_id' => $request->user()->id]);
            return redirect()->back()->with('error', 'No tienes permiso para realizar esta acción.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Password update failed: . Controller: ' . __CLASS__ . '. Method: ' . __METHOD__ . '. Line: ' . __LINE__ . 'Service ID: ' . ($service->id ?? 'N/A') . ' Error: ' . $e->getMessage(), ['client_service_id' => $service->id ?? null, 'user_id' => $request->user()->id ?? null, 'exception_trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Error al actualizar la contraseña. Por favor, inténtalo de nuevo más tarde.');
        }
    }

    /**
     * Parsear opciones configurables desde las notas del servicio y obtener precios reales
     */
    private function parseConfigurableOptionsWithPrices($service)
    {
        $options    = [];
        $totalPrice = 0;

        if (empty($service->notes)) {
            return ['options' => $options, 'total_price' => $totalPrice];
        }

        $notes = explode("\n", $service->notes);

        foreach ($notes as $note) {
            $note = trim($note);
            if (empty($note)) {
                continue;
            }

            $optionData = $this->parseIndividualOption($note, $service->billing_cycle_id);
            if ($optionData) {
                $options[] = $optionData;
                $totalPrice += $optionData['price'];
            }
        }

        return ['options' => $options, 'total_price' => $totalPrice];
    }

    /**
     * Parsear una opción individual y obtener su precio desde la base de datos
     */
    private function parseIndividualOption($noteText, $billingCycleId)
    {
        // Patrones para extraer información de las notas
        $patterns = [
            // "X GB de espacio web adicional"
            '/(\d+(?:\.\d+)?)\s+GB\s+de\s+espacio\s+web\s+adicional/i' => [
                'slug' => 'espacio-en-disco',
                'name' => 'Espacio en Disco',
                'unit' => 'GB',
            ],
            // "X vCPU adicionales"
            '/(\d+(?:\.\d+)?)\s+vCPU\s+adicionales/i'                  => [
                'slug' => 'vcpu',
                'name' => 'vCPU',
                'unit' => 'vCPU',
            ],
            // "X GB de RAM adicional"
            '/(\d+(?:\.\d+)?)\s+GB\s+de\s+RAM\s+adicional/i'           => [
                'slug' => 'vram',
                'name' => 'vRAM',
                'unit' => 'GB',
            ],
            // "Servicio de seguridad email activado"
            '/servicio\s+de\s+seguridad\s+email\s+activado/i'          => [
                'slug' => 'spamexperts',
                'name' => 'SpamExperts',
                'unit' => 'servicio',
            ],
        ];

        foreach ($patterns as $pattern => $config) {
            if (preg_match($pattern, $noteText, $matches)) {
                $quantity = isset($matches[1]) ? (float) $matches[1] : 1;

                // Buscar la opción configurable y su precio
                $option = \App\Models\ConfigurableOption::where('slug', $config['slug'])->first();
                if ($option) {
                    $pricing = \App\Models\ConfigurableOptionPricing::where('configurable_option_id', $option->id)
                        ->where('billing_cycle_id', $billingCycleId)
                        ->first();

                    if ($pricing) {
                        return [
                            'name'        => $config['name'],
                            'quantity'    => $quantity,
                            'unit'        => $config['unit'],
                            'unit_price'  => (float) $pricing->price,
                            'price'       => $quantity * (float) $pricing->price,
                            'description' => $noteText,
                        ];
                    }
                }

                // Fallback con precios estimados si no se encuentra en la BD
                $fallbackPrices = [
                    'espacio-en-disco' => 0.50,
                    'vcpu'             => 5.00,
                    'vram'             => 1.00,
                    'spamexperts'      => 3.00,
                ];

                $unitPrice = $fallbackPrices[$config['slug']] ?? 0;
                return [
                    'name'        => $config['name'],
                    'quantity'    => $quantity,
                    'unit'        => $config['unit'],
                    'unit_price'  => $unitPrice,
                    'price'       => $quantity * $unitPrice,
                    'description' => $noteText,
                ];
            }
        }

        return null;
    }
}

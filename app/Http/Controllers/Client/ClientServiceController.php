<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientService;
// use App\Models\User; // Removed
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
// use App\Models\OrderActivity; // Removed
use Illuminate\Support\Facades\Auth; // Keep if Auth::id() is used, or if user() is typehinted User
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\ProductPricing; // Added for fetching pricing options
use Inertia\Inertia; // Added for Inertia response
use Inertia\Response as InertiaResponse; // Added for type hinting
use App\Models\Invoice; // Added for creating renewal invoice
use App\Models\InvoiceItem; // Added for creating invoice items
use Carbon\Carbon; // Added for dates
use Illuminate\Support\Str; // Added for generating invoice number
use Illuminate\Support\Facades\Hash; // For hashing passwords
use Illuminate\Validation\Rules\Password; // For password validation rules
use Illuminate\Validation\ValidationException; // Added for throwing validation exceptions
use App\Models\Product; // Added for querying Product model
use Illuminate\Http\JsonResponse; // Added for JSON response

class ClientServiceController extends Controller
{
    /**
     * Display a listing of the client's services.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function index(Request $request): InertiaResponse
    {
        $user = $request->user();

        $clientServices = $user->clientServices()
                                ->with(['product', 'productPricing', 'billingCycle'])
                                ->get();

        // Contar facturas que están pagadas y en proceso de activación o confirmación
        $actionableInvoicesCount = $user->invoices()
                                     ->whereIn('status', ['pending_activation', 'pending_confirmation', 'unpaid']) // Incluimos unpaid para "acción requerida"
                                     ->count();

        $unpaidInvoicesCount = $user->invoices()
                                    ->where('status', 'unpaid')
                                    ->count();

        // Assuming there's a view for client services list, not the full dashboard
        return Inertia::render('Client/Services/Index', [ // NOTE: Assuming the view is Client/Services/Index
            'clientServices' => $clientServices,
            'actionableInvoicesCount' => $actionableInvoicesCount, // Nombre de variable actualizado
            'unpaidInvoicesCount' => $unpaidInvoicesCount,
            'accountBalance' => $user->balance,
            'formattedAccountBalance' => $user->formatted_balance,
        ]);
    }

    /**
     * Request cancellation for the specified service.
     *
     * @param  Request  $request
     * @param  ClientService  $service
     * @return RedirectResponse
     */
    public function requestCancellation(Request $request, ClientService $service): RedirectResponse
    {
        $this->authorize('requestCancellation', $service);

        // Allow cancellation for 'Active' or 'Suspended' services.
        // Note: ClientServicePolicy@requestCancellation might also need adjustment.
        if (!($service->status && in_array(strtolower($service->status), ['active', 'suspended']))) {
            return redirect()->back()->with('error', 'This service cannot be cancelled at its current stage.');
        }

        DB::beginTransaction();

        try {
            $sourceInvoiceId = $request->input('source_invoice_id');

            $originalStatus = $service->status;
            $service->status = 'pending_cancellation'; // Usar el nuevo estado ENUM
            $service->save();

            if ($sourceInvoiceId) {
                $invoice = \App\Models\Invoice::where('id', $sourceInvoiceId)
                                    ->where('client_id', $request->user()->id) // Ensure invoice belongs to user
                                    ->where('status', 'unpaid')
                                    ->first();

                if ($invoice) {
                    // Further check: ensure this invoice is indeed for the service being cancelled.
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
                'user_id' => $request->user()->id, // Changed Auth::id() to $request->user()->id for consistency
                'source_invoice_id' => $sourceInvoiceId ?? null,
                'exception' => $e
            ]);
            return redirect()->back()->with('error', 'Could not process cancellation request. Please try again.');
        }
    }

    /**
     * Show options for upgrading or downgrading the specified service.
     *
     * @param  Request  $request
     * @param  ClientService  $service
     * @return InertiaResponse
     */
    public function showUpgradeDowngradeOptions(Request $request, ClientService $service): InertiaResponse
    {
        $this->authorize('viewUpgradeDowngradeOptions', $service);

        // Ensure product, its productType, and current product pricing are loaded
        $service->loadMissing(['product.productType', 'productPricing.billingCycle']);

        Log::debug("Debugging showUpgradeDowngradeOptions for Service ID: {$service->id}");
        Log::debug("Service product_id: " . ($service->product_id ?? 'null'));

        if ($service->product) {
            Log::debug('Product data loaded: ', $service->product->toArray());
            Log::debug('Product product_type_id: ' . ($service->product->product_type_id ?? 'null'));
            if ($service->product->productType) {
                Log::debug('ProductType data loaded: ', $service->product->productType->toArray());
            } else {
                Log::debug('ProductType relation is null on product.');
            }
        } else {
            Log::debug('Product relation is null on service.');
        }

        if (!$service->product || !$service->product->productType) {
            Log::error("Servicio ID {$service->id} no tiene producto o tipo de producto asociado.");
            return redirect()->route('client.services.index')->with('error', 'No se pudo determinar el tipo de producto del servicio actual.');
        }

        $currentProductTypeId = $service->product->product_type_id;

        $productIdsWithSameType = Product::where('product_type_id', $currentProductTypeId)
                                        ->pluck('id');

        $availableOptions = ProductPricing::whereIn('product_id', $productIdsWithSameType)
            ->with(['billingCycle', 'product:id,name'])
            ->orderBy('product_id')
            ->orderBy('price')
            ->get();

        $discountInfo = [
            12 => ['percentage' => 18, 'text' => 'Ahorra 18%'],
            24 => ['percentage' => 26, 'text' => 'Ahorra 26%'],
        ];

        return Inertia::render('Client/Services/UpgradeDowngradeOptions', [
            'service' => $service,
            'availableOptions' => $availableOptions,
            'discountInfo' => $discountInfo,
        ]);
    }

    /**
     * Process the upgrade or downgrade for the specified service.
     *
     * @param  Request  $request
     * @param  ClientService  $service
     * @return RedirectResponse
     */
    public function processUpgradeDowngrade(Request $request, ClientService $service): RedirectResponse
    {
        $this->authorize('processUpgradeDowngrade', $service);

        $validated = $request->validate([
            'new_product_pricing_id' => 'required|exists:product_pricings,id',
        ]);

        $service->loadMissing(['product.productType', 'productPricing.billingCycle', 'client']);
        $newProductPricing = ProductPricing::with(['product.productType', 'billingCycle'])
            ->findOrFail($validated['new_product_pricing_id']);

        if (!($service->status && strtolower($service->status) === 'active')) {
            return redirect()->route('client.services.index')->with('error', 'El servicio debe estar activo para cambiar de plan.');
        }
        if ($newProductPricing->id === $service->product_pricing_id) {
            return redirect()->back()->with('error', 'Ya estás en este plan.');
        }

        $currentProductTypeId = $service->product->product_type_id;
        $newProductTypeId = $newProductPricing->product->product_type_id;

        if ($newProductTypeId !== $currentProductTypeId) {
            Log::warning("User ID {$request->user()->id} attempt to change service ID {$service->id} from product type {$currentProductTypeId} to {$newProductTypeId}. Operation blocked.");
            return redirect()->route('client.services.index')->with('error', 'No puedes cambiar a un tipo de producto diferente.');
        }

        DB::beginTransaction();
        try {
            $currentPricing = $service->productPricing;
            $currentCycle = $currentPricing->billingCycle;

            if (!$currentCycle) {
                Log::error("ClientServiceController: El objeto currentCycle es null para el servicio ID: {$service->id} en processUpgradeDowngrade.");
                DB::rollBack();
                return redirect()->route('client.services.index')->with('error', 'Error de configuración interna del ciclo de facturación (actual). Contacte a soporte.');
            }

            $daysRawValueCurrent = $currentCycle->getAttributeValue('days');
            Log::debug("ClientServiceController PUD: Para BillingCycle ID: " . ($currentCycle->id ?? 'desconocido') . " (actual), valor raw de 'days' obtenido con getAttributeValue(): " . gettype($daysRawValueCurrent) . " - " . print_r($daysRawValueCurrent, true));

            if (!is_numeric($daysRawValueCurrent) || $daysRawValueCurrent <= 0) {
                Log::error("ClientServiceController PUD: Configuración inválida para BillingCycle ID: " . ($currentCycle->id ?? 'desconocido') . " (actual) - el valor 'days' es inválido o no numérico. Valor raw: " . print_r($daysRawValueCurrent, true));
                DB::rollBack();
                return redirect()->route('client.services.index')->with('error', 'Error de configuración interna del ciclo de facturación (actual). Contacte a soporte.');
            }
            $daysInCurrentCycle = (int) $daysRawValueCurrent;

            $currentNextDueDate = Carbon::parse($service->next_due_date);
            $fechaInicioCicloActual = $currentNextDueDate->copy()->subDays($daysInCurrentCycle);

            $diasUtilizadosPlanActual = Carbon::now()->diffInDaysFiltered(fn(Carbon $date) => true, $fechaInicioCicloActual, false);
            if ($diasUtilizadosPlanActual < 1 && Carbon::now()->isSameDay($fechaInicioCicloActual)) {
                $diasUtilizadosPlanActual = 1;
            }
            $diasUtilizadosPlanActual = max(1, min($diasUtilizadosPlanActual, $daysInCurrentCycle));

            $tarifaDiariaPlanActual = ($daysInCurrentCycle > 0 && $service->billing_amount > 0) ? ($service->billing_amount / $daysInCurrentCycle) : 0;
            $costoUtilizadoPlanActual = $tarifaDiariaPlanActual * $diasUtilizadosPlanActual;
            $creditoNoUtilizado = $service->billing_amount - $costoUtilizadoPlanActual;
            $creditoNoUtilizado = max(0, round($creditoNoUtilizado, 2));

            $newCycle = $newProductPricing->billingCycle;
            if (!$newCycle) {
                Log::error("ClientServiceController: El objeto newCycle es null para ProductPricing ID: {$newProductPricing->id} en processUpgradeDowngrade.");
                DB::rollBack();
                return redirect()->route('client.services.index')->with('error', 'Error de configuración interna del ciclo de facturación (nuevo). Contacte a soporte.');
            }

            $daysRawValueNew = $newCycle->getAttributeValue('days');
            Log::debug("ClientServiceController PUD: Para BillingCycle ID: " . ($newCycle->id ?? 'desconocido') . " (nuevo), valor raw de 'days' obtenido con getAttributeValue(): " . gettype($daysRawValueNew) . " - " . print_r($daysRawValueNew, true));

            if (!is_numeric($daysRawValueNew) || $daysRawValueNew <= 0) {
                Log::error("ClientServiceController PUD: Configuración inválida para BillingCycle ID: " . ($newCycle->id ?? 'desconocido') . " (nuevo) - el valor 'days' es inválido o no numérico. Valor raw: " . print_r($daysRawValueNew, true));
                DB::rollBack();
                return redirect()->route('client.services.index')->with('error', 'Error de configuración interna del ciclo de facturación (nuevo). Contacte a soporte.');
            }
            $daysInNewCycle = (int) $daysRawValueNew;

            $precioTotalNuevoPlan = $newProductPricing->price;
            $montoFinal = $precioTotalNuevoPlan - $creditoNoUtilizado;
            $montoFinal = round($montoFinal, 2);

            $invoiceToPay = null;
            $originalProductId = $service->product_id;
            $old_product_name = $service->product->name;
            $old_cycle_name = $currentCycle->name;

            $notesForService = "Actualización completa de plan de '{$old_product_name} ({$old_cycle_name})' a '{$newProductPricing->product->name} ({$newCycle->name})' el " . Carbon::now()->toDateTimeString() . ".";
            $notesForService .= " Antigua fecha de vencimiento: " . $currentNextDueDate->format('Y-m-d') . ".";
            $notesForService .= " Crédito por tiempo no utilizado: {$creditoNoUtilizado} {$currentPricing->currency_code}.";
            $notesForService .= " Costo nuevo plan: {$precioTotalNuevoPlan} {$newProductPricing->currency_code}.";

            if ($montoFinal > 0) {
                $invoiceNumber = 'INV-UPGRADE-' . strtoupper(Str::random(8));
                $newInvoice = Invoice::create([
                    'client_id' => $service->client_id,
                    'reseller_id' => $service->client->reseller_id,
                    'invoice_number' => $invoiceNumber,
                    'issue_date' => Carbon::now(),
                    'due_date' => Carbon::now(),
                    'status' => 'unpaid',
                    'subtotal' => $montoFinal,
                    'total_amount' => $montoFinal,
                    'currency_code' => $newProductPricing->currency_code,
                    'notes_to_client' => "Cargo por actualización de plan de '{$old_product_name}' a '{$newProductPricing->product->name}'.",
                ]);

                InvoiceItem::create([
                    'invoice_id' => $newInvoice->id,
                    'client_service_id' => $service->id,
                    'description' => "Cargo por actualización a {$newProductPricing->product->name} ({$newCycle->name})",
                    'quantity' => 1,
                    'unit_price' => $montoFinal,
                    'total_price' => $montoFinal,
                    'taxable' => $newProductPricing->product->taxable ?? false,
                ]);
                $invoiceToPay = $newInvoice;
                $notesForService .= " Se generó la factura {$invoiceNumber} por {$montoFinal} {$newProductPricing->currency_code} para cubrir la diferencia.";
            } elseif ($montoFinal < 0) {
                $creditToBalance = abs($montoFinal);
                $client = $service->client;
                $client->balance += $creditToBalance;
                $client->save();
                $notesForService .= " Se acreditó {$creditToBalance} {$currentPricing->currency_code} al balance del cliente por la diferencia.";
            } else {
                $notesForService .= " La actualización no generó costos adicionales ni créditos inmediatos.";
            }

            $service->product_id = $newProductPricing->product_id;
            $service->product_pricing_id = $newProductPricing->id;
            $service->billing_cycle_id = $newProductPricing->billing_cycle_id;
            $service->billing_amount = $newProductPricing->price;
            $service->next_due_date = Carbon::now()->addDays($daysInNewCycle);
            $service->notes = ($service->notes ? trim($service->notes) . "\n" : '') . trim($notesForService);

            if ($service->isDirty('product_id') && $service->status !== 'pending_configuration') {
                $service->status = 'pending_configuration';
            }

            $service->save();
            DB::commit();

            $successMessage = "Plan actualizado de '{$old_product_name} ({$old_cycle_name})' a '{$newProductPricing->product->name} ({$newCycle->name})'.";
            $successMessage .= " Próximo vencimiento: " . Carbon::parse($service->next_due_date)->format('Y-m-d') . ".";

            if ($invoiceToPay) {
                $successMessage .= " Se generó la factura {$invoiceToPay->invoice_number} por {$invoiceToPay->total_amount_formatted} para la actualización.";
            } elseif ($montoFinal < 0) {
                $successMessage .= " Se acreditó " . abs($montoFinal) . " {$currentPricing->currency_code} a tu balance.";
            } else {
                $successMessage .= " La actualización no tuvo costo adicional inmediato.";
            }

            if ($service->status === 'pending_configuration') {
                $successMessage .= " El servicio requiere configuración adicional por un administrador.";
            }

            return redirect()->route('client.services.index')->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Service plan change failed: ' . $e->getMessage(), [
                'client_service_id' => $service->id,
                'new_product_pricing_id' => $validated['new_product_pricing_id'],
                'user_id' => $request->user()->id,
                'exception_trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('client.services.index')->with('error', 'No se pudo procesar tu solicitud de cambio de plan. Inténtalo de nuevo.');
        }
    }

    public function calculateProration(Request $request, ClientService $service): JsonResponse
    {
        $this->authorize('view', $service);

        $validated = $request->validate([
            'new_product_pricing_id' => 'required|exists:product_pricings,id',
        ]);
        $newProductPricingId = $validated['new_product_pricing_id'];

        try {
            $service->loadMissing(['product.productType', 'productPricing.billingCycle', 'client']);
            $newProductPricing = ProductPricing::with(['product.productType', 'billingCycle'])
                ->findOrFail($newProductPricingId);

            if (strtolower($service->status) !== 'active') {
                return response()->json(['error' => 'El servicio debe estar activo para calcular el prorrateo.'], 422);
            }
            if ((int)$newProductPricingId === $service->product_pricing_id) {
                return response()->json(['error' => 'Esta selección es tu plan actual.'], 422);
            }
            if ($newProductPricing->product->product_type_id !== $service->product->product_type_id) {
                return response()->json(['error' => 'No puedes cambiar a un tipo de producto diferente.'], 422);
            }

            $currentPricing = $service->productPricing;
            $currentCycle = $currentPricing->billingCycle;

            if (!$currentCycle) {
                Log::error("ClientServiceController: El objeto currentCycle es null para el servicio ID: {$service->id} en calculateProration.");
                return response()->json(['error' => 'Error de configuración interna del ciclo de facturación (actual). Contacte a soporte.'], 500);
            }

            $daysRawValueCurrent = $currentCycle->getAttributeValue('days');
            Log::debug("ClientServiceController CP: Para BillingCycle ID: " . ($currentCycle->id ?? 'desconocido') . " (actual), valor raw de 'days' obtenido con getAttributeValue(): " . gettype($daysRawValueCurrent) . " - " . print_r($daysRawValueCurrent, true));

            if (!is_numeric($daysRawValueCurrent) || $daysRawValueCurrent <= 0) {
                Log::error("ClientServiceController CP: Configuración inválida para BillingCycle ID: " . ($currentCycle->id ?? 'desconocido') . " (actual) - el valor 'days' es inválido o no numérico. Valor raw: " . print_r($daysRawValueCurrent, true));
                return response()->json(['error' => 'Error de configuración interna del ciclo de facturación (actual). Contacte a soporte.'], 500);
            }
            $daysInCurrentCycle = (int) $daysRawValueCurrent;

            $currentNextDueDate = Carbon::parse($service->next_due_date);
            $fechaInicioCicloActual = $currentNextDueDate->copy()->subDays($daysInCurrentCycle);

            $diasUtilizadosPlanActual = Carbon::now()->diffInDaysFiltered(fn(Carbon $date) => true, $fechaInicioCicloActual, false);
            if ($diasUtilizadosPlanActual < 1 && Carbon::now()->isSameDay($fechaInicioCicloActual)) {
                $diasUtilizadosPlanActual = 1;
            }
            $diasUtilizadosPlanActual = max(1, min($diasUtilizadosPlanActual, $daysInCurrentCycle));

            $tarifaDiariaPlanActual = ($daysInCurrentCycle > 0 && $service->billing_amount > 0) ? ($service->billing_amount / $daysInCurrentCycle) : 0;
            $costoUtilizadoPlanActual = $tarifaDiariaPlanActual * $diasUtilizadosPlanActual;
            $creditoNoUtilizado = $service->billing_amount - $costoUtilizadoPlanActual;
            $creditoNoUtilizado = max(0, round($creditoNoUtilizado, 2));

            $newCycle = $newProductPricing->billingCycle;
            if (!$newCycle) {
                Log::error("ClientServiceController: El objeto newCycle es null para ProductPricing ID: {$newProductPricing->id} en calculateProration.");
                return response()->json(['error' => 'Error de configuración interna del ciclo de facturación (nuevo). Contacte a soporte.'], 500);
            }

            $daysRawValueNew = $newCycle->getAttributeValue('days');
            Log::debug("ClientServiceController CP: Para BillingCycle ID: " . ($newCycle->id ?? 'desconocido') . " (nuevo), valor raw de 'days' obtenido con getAttributeValue(): " . gettype($daysRawValueNew) . " - " . print_r($daysRawValueNew, true));

            if (!is_numeric($daysRawValueNew) || $daysRawValueNew <= 0) {
                Log::error("ClientServiceController CP: Configuración inválida para BillingCycle ID: " . ($newCycle->id ?? 'desconocido') . " (nuevo) - el valor 'days' es inválido o no numérico. Valor raw: " . print_r($daysRawValueNew, true));
                return response()->json(['error' => 'Error de configuración interna del ciclo de facturación (nuevo). Contacte a soporte.'], 500);
            }

            $precioTotalNuevoPlan = $newProductPricing->price;
            $montoFinal = $precioTotalNuevoPlan - $creditoNoUtilizado;
            $montoFinal = round($montoFinal, 2);

            $message = $montoFinal > 0 ? 'Monto a pagar para la actualización completa.' : ($montoFinal < 0 ? 'Crédito a tu balance por la actualización.' : 'Actualización completa sin costo adicional inmediato.');

            return response()->json([
                'prorated_amount' => $montoFinal,
                'currency_code' => $newProductPricing->currency_code ?? $service->productPricing->currency_code,
                'message' => $message,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Calculate Proration: Model not found for new_product_pricing_id: ' . $newProductPricingId . ' - ' . $e->getMessage());
            return response()->json(['error' => 'El plan seleccionado no es válido.'], 404);
        } catch (\Exception $e) {
            Log::error('Error en calculateProration para el servicio ID ' . $service->id . ': ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'No se pudo calcular el monto de prorrateo.'], 500);
        }
    }

    public function requestRenewal(Request $request, ClientService $service): RedirectResponse
    {
        $this->authorize('renewService', $service);

        if (!($service->status && in_array(strtolower($service->status), ['active', 'suspended']))) {
            return redirect()->back()->with('error', 'This service cannot be renewed at its current stage.');
        }

        $existingUnpaidRenewalInvoice = Invoice::where('client_id', $service->client_id)
            ->where('status', 'unpaid')
            ->whereHas('items', function ($query) use ($service) {
                $query->where('client_service_id', $service->id)
                      ->where('description', 'like', 'Renewal:%');
            })
            ->first();

        if ($existingUnpaidRenewalInvoice) {
            return redirect()->route('client.invoices.show', $existingUnpaidRenewalInvoice->id)
                             ->with('info', 'An unpaid renewal invoice already exists for this service. Please complete payment.');
        }

        DB::beginTransaction();
        try {
            $service->loadMissing(['product', 'productPricing', 'billingCycle', 'client']);

            if (!$service->productPricing || !$service->billingCycle || !$service->product) {
                 Log::error("Service {$service->id} is missing pricing, cycle, or product details for renewal.");
                 return redirect()->back()->with('error', 'Service configuration error. Cannot generate renewal invoice.');
            }

            $invoiceNumber = 'INV-RENEW-' . strtoupper(Str::random(8));
            $renewalAmount = $service->billing_amount;
            $currencyCode = $service->productPricing->currency_code;
            $description = "Renewal: {$service->product->name} - {$service->domain_name} ({$service->billingCycle->name})";
            $notesToClient = "Renewal for service: {$service->product->name} - {$service->domain_name} for billing cycle {$service->billingCycle->name}";

            $newInvoice = Invoice::create([
                'client_id' => $service->client_id,
                'reseller_id' => $service->client->reseller_id,
                'invoice_number' => $invoiceNumber,
                'issue_date' => Carbon::now(),
                'due_date' => Carbon::now(),
                'status' => 'unpaid',
                'subtotal' => $renewalAmount,
                'total_amount' => $renewalAmount,
                'currency_code' => $currencyCode,
                'notes_to_client' => $notesToClient,
            ]);

            InvoiceItem::create([
                'invoice_id' => $newInvoice->id,
                'client_service_id' => $service->id,
                'description' => $description,
                'quantity' => 1,
                'unit_price' => $renewalAmount,
                'total_price' => $renewalAmount,
                'taxable' => $service->product->taxable ?? false,
            ]);

            DB::commit();

            return redirect()->route('client.invoices.show', $newInvoice->id)
                             ->with('success', 'Renewal invoice generated successfully. Please proceed with payment.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Service renewal request failed: ' . $e->getMessage(), [
                'client_service_id' => $service->id,
                'user_id' => Auth::id(),
                'exception' => $e,
            ]);
            return redirect()->back()->with('error', 'Could not generate renewal invoice. Please try again.');
        }
    }

    public function updatePassword(Request $request, ClientService $service): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('updatePassword', $service);

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', Password::min(12)->mixedCase()->numbers()->symbols()->uncompromised(), 'confirmed'],
        ]);

        DB::beginTransaction();
        try {
            if (!Hash::check($validated['current_password'], $service->password_encrypted)) {
                throw ValidationException::withMessages([
                    'current_password' => __('La contraseña actual proporcionada es incorrecta.'),
                ]);
            }

            $service->password_encrypted = Hash::make($validated['new_password']);
            $service->save();

            Log::info("Password updated for service ID {$service->id} by user ID {$request->user()->id}.");
            DB::commit();

            return redirect()->back()->with('success', 'Contraseña actualizada con éxito.');

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            DB::rollBack();
            Log::warning('Password update authorization failed: ' . $e->getMessage(), [
                'client_service_id' => $service->id,
                'user_id' => $request->user()->id,
            ]);
            return redirect()->back()->with('error', 'No tienes permiso para realizar esta acción.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        }
        catch (\Exception $e) {
            DB::rollBack();
            Log::error('Password update failed: ' . $e->getMessage(), [
                'client_service_id' => $service->id,
                'user_id' => $request->user()->id,
                'exception' => $e,
            ]);
            return redirect()->back()->with('error', 'Error al actualizar la contraseña. Por favor, inténtalo de nuevo más tarde.');
        }
    }
}

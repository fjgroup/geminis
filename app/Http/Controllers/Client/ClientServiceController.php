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

        // La condición original del if que causa el problema:
        // if (!$service->product || !$service->product->productType) { ... }
        if (!$service->product || !$service->product->productType) {
            Log::error("Servicio ID {$service->id} no tiene producto o tipo de producto asociado.");
            return redirect()->route('client.services.index')->with('error', 'No se pudo determinar el tipo de producto del servicio actual.');
        }

        $currentProductTypeId = $service->product->product_type_id;

        // Get IDs of all products belonging to this product_type_id
        $productIdsWithSameType = Product::where('product_type_id', $currentProductTypeId)
                                        // ->where('status', 'active') // Optional: consider only active products for upgrade/downgrade
                                        ->pluck('id');

        // Fetch all ProductPricing tiers for products of the same type.
        // The frontend will handle differentiating the current plan from others.
        $availableOptions = ProductPricing::whereIn('product_id', $productIdsWithSameType)
            ->with(['billingCycle', 'product:id,name']) // Eager load billingCycle and product name
            ->orderBy('product_id') // Group by product first
            ->orderBy('price')      // Then by price
            ->get();

        // Prepare discount information to be passed to the frontend
        // This is based on the subtask's fixed discount percentages.
        $discountInfo = [
            12 => ['percentage' => 18, 'text' => 'Ahorra 18%'], // 12 months
            24 => ['percentage' => 26, 'text' => 'Ahorra 26%'], // 24 months
        ];

        return Inertia::render('Client/Services/UpgradeDowngradeOptions', [
            'service' => $service, // Contains current product, productPricing, and billingCycle
            'availableOptions' => $availableOptions, // All pricing options for this product
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

        // 1. Load relations
        $service->loadMissing(['product.productType', 'productPricing.billingCycle', 'client']);
        $newProductPricing = ProductPricing::with(['product.productType', 'billingCycle'])
            ->findOrFail($validated['new_product_pricing_id']);

        // 2. Initial Validation
        if (!($service->status && strtolower($service->status) === 'active')) {
            return redirect()->route('client.services.index')->with('error', 'El servicio debe estar activo para cambiar de plan.');
        }
        if ($newProductPricing->id === $service->product_pricing_id) {
            return redirect()->back()->with('error', 'Ya estás en este plan.');
        }

        // 3. Product Type Validation
        $currentProductTypeId = $service->product->product_type_id;
        $newProductTypeId = $newProductPricing->product->product_type_id;

        if ($newProductTypeId !== $currentProductTypeId) {
            Log::warning("User ID {$request->user()->id} attempt to change service ID {$service->id} from product type {$currentProductTypeId} to {$newProductTypeId}. Operation blocked.");
            return redirect()->route('client.services.index')->with('error', 'No puedes cambiar a un tipo de producto diferente.');
        }

        DB::beginTransaction();
        try {
            // 4. Proration Calculation
            $currentPricing = $service->productPricing;
            $currentCycle = $currentPricing->billingCycle;
            // Assuming BillingCycle has duration_in_days. Fallback to duration_in_months * 30 if not.
            $daysInCurrentCycle = $currentCycle->duration_in_days ?? ($currentCycle->duration_in_months * 30);
            if ($daysInCurrentCycle <= 0) { // Prevent division by zero
                Log::error("Service ID {$service->id}: Current cycle duration is zero or negative.");
                DB::rollBack();
                return redirect()->route('client.services.index')->with('error', 'Error de configuración del ciclo de facturación actual.');
            }

            $remainingDays = Carbon::now()->diffInDays($service->next_due_date, false);
            $pricePerDayCurrent = $currentPricing->price / $daysInCurrentCycle;
            $creditAmount = ($remainingDays > 0) ? $pricePerDayCurrent * $remainingDays : 0;

            $newCycle = $newProductPricing->billingCycle;
            // Assuming BillingCycle has duration_in_days. Fallback to duration_in_months * 30 if not.
            $daysInNewCycle = $newCycle->duration_in_days ?? ($newCycle->duration_in_months * 30);
            if ($daysInNewCycle <= 0) { // Prevent division by zero
                Log::error("Service ID {$service->id}: New cycle duration is zero or negative for pricing ID {$newProductPricing->id}.");
                DB::rollBack();
                return redirect()->route('client.services.index')->with('error', 'Error de configuración del nuevo ciclo de facturación.');
            }

            $pricePerDayNew = $newProductPricing->price / $daysInNewCycle;
            $costForRemainingPeriod = ($remainingDays > 0) ? $pricePerDayNew * $remainingDays : 0;

            // Ensure precision for currency calculations
            $creditAmount = round($creditAmount, 2);
            $costForRemainingPeriod = round($costForRemainingPeriod, 2);
            $proratedDifference = round($costForRemainingPeriod - $creditAmount, 2);

            // 5. Handle Difference and Update Service
            $invoiceToPay = null;
            $notesForService = "Cambio de plan procesado el " . Carbon::now()->toDateTimeString() . ".";
            $originalProductId = $service->product_id; // For checking if product itself changed

            if ($proratedDifference > 0) {
                $invoiceNumber = 'INV-UPGRADE-' . strtoupper(Str::random(8));
                $newInvoice = Invoice::create([
                    'client_id' => $service->client_id,
                    'reseller_id' => $service->client->reseller_id,
                    'invoice_number' => $invoiceNumber,
                    'issue_date' => Carbon::now(),
                    'due_date' => Carbon::now(),
                    'status' => 'unpaid',
                    'subtotal' => $proratedDifference,
                    'total_amount' => $proratedDifference,
                    'currency_code' => $newProductPricing->currency_code,
                    'notes_to_client' => "Cargo por cambio de plan de '{$service->product->name}' a '{$newProductPricing->product->name}'.",
                ]);

                InvoiceItem::create([
                    'invoice_id' => $newInvoice->id,
                    'client_service_id' => $service->id,
                    'description' => "Prorrateo por cambio a {$newProductPricing->product->name} ({$newProductPricing->billingCycle->name})",
                    'quantity' => 1,
                    'unit_price' => $proratedDifference,
                    'total_price' => $proratedDifference,
                    'taxable' => $newProductPricing->product->taxable ?? false,
                ]);
                $invoiceToPay = $newInvoice;
                $notesForService .= " Se generó la factura {$invoiceNumber} por {$proratedDifference} {$newProductPricing->currency_code}.";
            } elseif ($proratedDifference < 0) {
                $creditToBalance = abs($proratedDifference);
                $client = $service->client;
                $client->balance += $creditToBalance;
                $client->save();
                $notesForService .= " Se acreditó {$creditToBalance} {$currentPricing->currency_code} al balance del cliente.";
            } else {
                $notesForService .= " El cambio no generó costos adicionales ni créditos por el período restante.";
            }

            // Update ClientService
            $old_product_name = $service->product->name;
            $old_cycle_name = $service->productPricing->billingCycle->name; // Access through productPricing for current cycle

            $service->product_id = $newProductPricing->product_id;
            $service->product_pricing_id = $newProductPricing->id;
            $service->billing_cycle_id = $newProductPricing->billing_cycle_id;
            $service->billing_amount = $newProductPricing->price;
            $service->notes = ($service->notes ? $service->notes . "\n" : '') . $notesForService;

            if ($service->product_id !== $originalProductId && $service->status !== 'pending_configuration') {
                $service->status = 'pending_configuration';
            }
            $service->save();

            DB::commit();

            // 6. Redirection and Message
            $successMessage = "Plan cambiado de '{$old_product_name} ({$old_cycle_name})' a '{$newProductPricing->product->name} ({$newProductPricing->billingCycle->name})'.";
            if ($invoiceToPay) {
                // Message updated to reflect immediate change and pending payment for proration.
                $successMessage .= " El plan ha sido actualizado. Se generó la factura {$invoiceToPay->invoice_number} por {$invoiceToPay->total_amount_formatted} correspondiente al prorrateo.";
                 // Consider redirecting to invoice if payment is mandatory before change is effective
                 // For now, redirecting to services index as per current plan.
                return redirect()->route('client.services.index')->with('success', $successMessage);
            } elseif ($proratedDifference < 0) {
                $successMessage .= " Se ha acreditado " . abs($proratedDifference) . " {$currentPricing->currency_code} a tu balance.";
            } else {
                $successMessage .= " El plan se ha actualizado sin costo adicional por el período actual.";
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
                'user_id' => $request->user()->id, // Changed from Auth::id()
                'exception_trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('client.services.index')->with('error', 'No se pudo procesar tu solicitud de cambio de plan. Inténtalo de nuevo.');
        }
    }

    /**
     * Request renewal for the specified service and generate an invoice.
     *
     * @param  Request  $request
     * @param  ClientService  $service
     * @return RedirectResponse
     */
    public function requestRenewal(Request $request, ClientService $service): RedirectResponse
    {
        $this->authorize('renewService', $service);

        // Validation: Check if service is in a renewable state
        if (!($service->status && in_array(strtolower($service->status), ['active', 'suspended']))) {
            return redirect()->back()->with('error', 'This service cannot be renewed at its current stage.');
        }

        // Validation: Check for existing unpaid renewal invoice for this service
        // This assumes InvoiceItem has a client_service_id column.
        $existingUnpaidRenewalInvoice = Invoice::where('client_id', $service->client_id)
            ->where('status', 'unpaid')
            ->whereHas('items', function ($query) use ($service) {
                $query->where('client_service_id', $service->id)
                      ->where('description', 'like', 'Renewal:%'); // Match description
            })
            ->first();

        if ($existingUnpaidRenewalInvoice) {
            return redirect()->route('client.invoices.show', $existingUnpaidRenewalInvoice->id)
                             ->with('info', 'An unpaid renewal invoice already exists for this service. Please complete payment.');
        }

        DB::beginTransaction();
        try {
            // Ensure all necessary relationships are loaded for invoice creation
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

            // Create Invoice
            $newInvoice = Invoice::create([
                'client_id' => $service->client_id,
                'reseller_id' => $service->client->reseller_id, // Assuming client has reseller_id
                'invoice_number' => $invoiceNumber,
                'issue_date' => Carbon::now(),
                'due_date' => Carbon::now(), // Or Carbon::now()->addDays(config('billing.renewal_due_days', 0))
                'status' => 'unpaid',
                'subtotal' => $renewalAmount,
                'total_amount' => $renewalAmount,
                'currency_code' => $currencyCode,
                'notes_to_client' => $notesToClient,
            ]);

            // Create InvoiceItem
            InvoiceItem::create([
                'invoice_id' => $newInvoice->id,
                'client_service_id' => $service->id, // Direct link to the service
                'description' => $description,
                'quantity' => 1,
                'unit_price' => $renewalAmount,
                'total_price' => $renewalAmount,
                'taxable' => $service->product->taxable ?? false, // Assuming product has a taxable attribute
            ]);

            // OrderActivity::create([...]) // ELIMINADO
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

    /**
     * Update the password for the specified service.
     *
     * @param  Request  $request
     * @param  ClientService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request, ClientService $service): \Illuminate\Http\RedirectResponse
    {
        // Authorization: Ensure the authenticated user owns this service
        // This will throw a 403 error if the policy check fails.
        $this->authorize('updatePassword', $service); // Assumes 'updatePassword' method exists in ClientServicePolicy

        // Validate the request
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', Password::min(12)->mixedCase()->numbers()->symbols()->uncompromised(), 'confirmed'],
        ]);

        DB::beginTransaction();
        try {
            // Verify the current password
            if (!Hash::check($validated['current_password'], $service->password_encrypted)) {
                throw ValidationException::withMessages([
                    'current_password' => __('La contraseña actual proporcionada es incorrecta.'),
                ]);
            }

            // IMPORTANT: This is a simplified password update.
            // In a real-world scenario with external provisioning (cPanel, Plesk, etc.),
            // this would involve calling an external API via a ServiceProvisioningService.
            // For now, we'll assume the password (or a hash) is stored directly on the ClientService model
            // or its related configuration. This is generally NOT recommended for actual service credentials.

            // Example: If storing a hash directly on ClientService model (ensure field exists and is fillable)
            // $service->service_password = Hash::make($validated['new_password']);

            // Update the 'password_encrypted' field directly
            $service->password_encrypted = Hash::make($validated['new_password']);
            // Log::debug("Attempting to save new password_encrypted for service ID: {$service->id}"); // Optional temporary log
            $service->save();
            // Log::debug("Save operation complete for service ID: {$service->id}"); // Optional temporary log

            // Optionally, log this activity
            Log::info("Password updated for service ID {$service->id} by user ID {$request->user()->id}.");

            DB::commit();

            return redirect()->back()->with('success', 'Contraseña actualizada con éxito.');

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            DB::rollBack();
            Log::warning('Password update authorization failed: ' . $e->getMessage(), [
                'client_service_id' => $service->id,
                'user_id' => $request->user()->id,
            ]);
            // Re-throw is an option, or redirect back with error for user feedback on the form page.
            // throw $e;
            return redirect()->back()->with('error', 'No tienes permiso para realizar esta acción.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            // This block might be redundant if $request->validate() is used as it throws
            // an exception that Laravel's handler converts to an Inertia-compatible response.
            // However, if reached, redirect back with errors.
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

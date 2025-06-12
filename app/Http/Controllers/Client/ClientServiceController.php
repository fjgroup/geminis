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
        if (!in_array($service->status, ['Active', 'Suspended'])) {
            return redirect()->back()->with('error', 'This service cannot be cancelled at its current stage.');
        }

        DB::beginTransaction();

        try {
            $sourceInvoiceId = $request->input('source_invoice_id');

            $originalStatus = $service->status;
            $service->status = 'Cancellation Requested'; // Consistent status value
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

            return redirect()->route('client.services.index')->with('success', 'Service cancellation requested successfully. Any associated unpaid renewal invoice has also been cancelled.');

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

        // Ensure product and current product pricing are loaded
        $service->load(['product', 'productPricing.billingCycle']);

        if (!$service->product) {
            // This should ideally not happen if data integrity is maintained
            abort(404, 'Product associated with this service not found.');
        }

        // Fetch all other ProductPricing tiers for the current service's product
        // Eager load billingCycle for display
        $availableOptions = ProductPricing::where('product_id', $service->product_id)
            ->where('id', '!=', $service->product_pricing_id) // Exclude current pricing
            ->with('billingCycle')
            ->orderBy('price') // Optionally order by price or some other attribute
            ->get();

        // You might also want to filter these options based on status (e.g., only 'active' pricings)
        // or if they are marked as "allow_upgrades_downgrades" etc. (future enhancement)

        return Inertia::render('Client/Services/UpgradeDowngradeOptions', [
            'service' => $service,
            'availableOptions' => $availableOptions,
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

        // Validation
        $validated = $request->validate([
            'new_product_pricing_id' => 'required|exists:product_pricings,id',
        ]);

        $newProductPricing = ProductPricing::findOrFail($validated['new_product_pricing_id']);

        // Further validation
        if ($service->status !== 'Active') {
            return redirect()->back()->with('error', 'This service is not active and cannot be changed.');
        }
        if ($newProductPricing->product_id !== $service->product_id) {
            return redirect()->back()->with('error', 'Invalid plan selected. It does not belong to the same product.');
        }
        if ($newProductPricing->id === $service->product_pricing_id) {
            return redirect()->back()->with('error', 'This is already your current plan.');
        }

        DB::beginTransaction();
        try {
            $old_product_pricing_id = $service->product_pricing_id;
            $old_billing_amount = $service->billing_amount;
            // Load old pricing details for logging if not already available on $service model
            $service->loadMissing('productPricing.billingCycle');
            $old_billing_cycle_name = $service->productPricing?->billingCycle?->name ?? 'N/A';


            $service->product_pricing_id = $newProductPricing->id;
            $service->billing_cycle_id = $newProductPricing->billing_cycle_id; // Ensure this is updated
            $service->billing_amount = $newProductPricing->price;
            // Add a note about the change, this is optional
            $service->notes = ($service->notes ? $service->notes . "\n" : '') .
                              "User requested plan change to pricing ID {$newProductPricing->id} on " . now()->toDateTimeString() .
                              ". Changes apply on next renewal.";
            $service->save();

            // OrderActivity::create([...]) // ELIMINADO
            DB::commit();

            return redirect()->route('client.services.index')
                             ->with('success', 'Your plan has been updated. Changes will apply on your next renewal date: ' . ($service->next_due_date ? $service->next_due_date->format('Y-m-d') : 'N/A') . '.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Service plan change failed: ' . $e->getMessage(), [
                'client_service_id' => $service->id,
                'new_product_pricing_id' => $validated['new_product_pricing_id'],
                'user_id' => Auth::id(),
                'exception' => $e,
            ]);
            return redirect()->back()->with('error', 'Could not process your plan change request. Please try again.');
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
        if (!in_array($service->status, ['Active', 'Suspended'])) {
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
}

<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\User;

// Removed App\Models\OrderActivity; as it's no longer used here
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Exception; // Added for general \Exception
use App\Models\ClientService; // Added for ClientService creation
use Illuminate\Support\Facades\Log; // Ensure Log is imported

class ClientInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);

        $invoices = $request->user()->invoices()->with('items')->paginate(10); // Asumiendo una relación 'invoices' en el modelo User

        return Inertia::render('Client/Invoices/Index', [
            'invoices' => $invoices,
        ]);
    }
    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice) // Removed Request $request
    {
        $this->authorize('view', $invoice);

        $invoice->load([
            'client',
            'reseller',
            'items.clientService',
            // 'items.orderItem.product', // orderItem will be removed from InvoiceItem model
            'items.product', // Direct relation from InvoiceItem to Product
            'items.productPricing.billingCycle', // Direct relation
            // 'order', // order relation on Invoice will be removed
            'transactions' => function ($query) {
                $query->where('status', 'completed')
                      ->with('paymentMethod')
                      ->latest('transaction_date');
            }
        ]);

        $userResource = null;
        $authUser = Auth::user();
        if ($authUser) {
            $userResource = [
                'id' => $authUser->id,
                'name' => $authUser->name,
                'email' => $authUser->email,
                'balance' => $authUser->balance,
                'formatted_balance' => $authUser->formatted_balance,
            ];
        }

        return Inertia::render('Client/Invoices/Show', [
            'invoice' => $invoice,
            'auth' => ['user' => $userResource]
        ]);
    }

    /**
     * Process payment of the specified invoice using user's balance.
     *
     * @param  Request  $request
     * @param  Invoice  $invoice
     * @return RedirectResponse
     */
    public function payWithBalance(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('payWithBalance', $invoice);

        if ($invoice->status !== 'unpaid') {
            return redirect()->route('client.invoices.show', $invoice->id)
                             ->with('error', 'This invoice is not awaiting payment.');
        }

        $user = Auth::user();

        if ($user->balance < $invoice->total_amount) {
            return redirect()->route('client.invoices.show', $invoice->id)
                             ->with('error', 'Insufficient balance to pay this invoice.');
        }

        DB::beginTransaction();

        try {

            $user->decrement('balance', $invoice->total_amount);

            Transaction::create([
                'invoice_id' => $invoice->id,
                'client_id' => $user->id,
                'reseller_id' => $user->reseller_id,
                'gateway_slug' => 'balance',
                'gateway_transaction_id' => 'BAL-' . strtoupper(Str::random(10)),
                'type' => 'payment',
                'amount' => $invoice->total_amount,
                'currency_code' => $invoice->currency_code,
                'status' => 'completed',
                'description' => "Payment for Invoice #{$invoice->invoice_number} using account balance.",
                'transaction_date' => Carbon::now(),
            ]);

            $invoice->status = 'paid';
            $invoice->paid_date = Carbon::now();
            $invoice->save();

            // Create Client Services from Invoice Items
            $invoice->loadMissing(['items.product', 'items.productPricing.billingCycle', 'client']);

            foreach ($invoice->items as $invoiceItem) {
                if ($invoiceItem->client_service_id && ClientService::find($invoiceItem->client_service_id)) {
                    Log::info("ClientService ID {$invoiceItem->client_service_id} already exists and is linked to InvoiceItem ID {$invoiceItem->id}. Skipping creation.");
                    continue;
                }

                if (!$invoiceItem->product || !$invoiceItem->productPricing || !$invoiceItem->productPricing->billingCycle) {
                    Log::error("InvoiceItem ID {$invoiceItem->id} is missing product, productPricing, or billingCycle details. Skipping ClientService creation.");
                    continue;
                }

                $registrationDate = Carbon::now();
                $nextDueDate = $registrationDate->copy();
                $billingCycle = $invoiceItem->productPricing->billingCycle;

                if (isset($billingCycle->period_unit) && isset($billingCycle->period_amount) && is_numeric($billingCycle->period_amount) && $billingCycle->period_amount > 0) {
                    switch (strtolower($billingCycle->period_unit)) {
                        case 'day':
                        case 'days':
                            $nextDueDate->addDays($billingCycle->period_amount);
                            break;
                        case 'month':
                        case 'months':
                            $nextDueDate->addMonthsNoOverflow($billingCycle->period_amount);
                            break;
                        case 'year':
                        case 'years':
                            $nextDueDate->addYearsNoOverflow($billingCycle->period_amount);
                            break;
                        default:
                            Log::warning("Unknown billing cycle unit '{$billingCycle->period_unit}' for ProductPricing ID: {$invoiceItem->product_pricing_id} on InvoiceItem ID: {$invoiceItem->id}. Defaulting next_due_date to 1 month.");
                            $nextDueDate->addMonth();
                    }
                } elseif (isset($billingCycle->days) && is_numeric($billingCycle->days) && $billingCycle->days > 0) { // Fallback for 'days' attribute
                     $nextDueDate->addDays((int)$billingCycle->days);
                } else {
                    Log::warning("BillingCycle period information not found or invalid for ProductPricing ID: {$invoiceItem->product_pricing_id} on InvoiceItem ID: {$invoiceItem->id}. Defaulting next_due_date to 100 years (error indicator).");
                    $nextDueDate->addYears(100);
                }

                $clientService = ClientService::create([
                    'client_id' => $invoice->client_id,
                    'reseller_id' => $invoice->client->reseller_id,
                    'product_id' => $invoiceItem->product_id,
                    'product_pricing_id' => $invoiceItem->product_pricing_id,
                    'domain_name' => $invoiceItem->domain_name,
                    'status' => 'pending',
                    'registration_date' => $registrationDate->toDateString(),
                    'next_due_date' => $nextDueDate->toDateString(),
                    'billing_amount' => $invoiceItem->total_price,
                    'currency_code' => $invoice->currency_code,
                    'notes' => 'Servicio creado automáticamente desde Factura #' . $invoice->invoice_number,
                    // 'invoice_item_id' => $invoiceItem->id, // Uncomment if/when this field is added to client_services table
                ]);

                $invoiceItem->client_service_id = $clientService->id;
                $invoiceItem->save();
            }

            DB::commit();

            return redirect()->route('client.invoices.show', $invoice->id)
                             ->with('success', 'Invoice paid successfully using your account balance. Services are being provisioned.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error processing payment for invoice ID {$invoice->id} with balance: " . $e->getMessage(), ['exception' => $e->getTraceAsString()]);
            return redirect()->route('client.invoices.show', $invoice->id)
                             ->with('error', 'An error occurred while processing your payment. Please try again.');
        }
    }
}

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
use Illuminate\Support\Facades\Redirect; // Ensure Redirect facade is available if used directly

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
                $query->where('type', 'payment')
                      ->with('paymentMethod')
                      ->latest('transaction_date')
                      ->limit(1);
            }
        ]);

        return Inertia::render('Client/Invoices/Show', [
            'invoice' => $invoice,
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
            $invoice->loadMissing(['items.product.productType', 'items.productPricing.billingCycle', 'client']);

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

                // All services are initially set to 'pending' to be picked up by the job or manual activation.
                $serviceStatus = 'pending';

                $clientService = ClientService::create([
                    'client_id' => $invoice->client_id,
                    'reseller_id' => $invoice->client->reseller_id,
                    'product_id' => $invoiceItem->product_id,
                    'product_pricing_id' => $invoiceItem->product_pricing_id,
                    'domain_name' => $invoiceItem->domain_name,
                    'status' => $serviceStatus, // Consistently 'pending'
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

            // Update Invoice status: if any item is expected to create a service, set to 'pending_activation'.
            // Otherwise, it remains 'paid'.
            $invoice->loadMissing('items.product.productType'); // Ensure productType is loaded for the check below

            $hasAnyServiceToCreate = false;
            foreach ($invoice->items as $item) {
                if ($item->product && $item->product->productType && $item->product->productType->creates_service_instance) {
                    $hasAnyServiceToCreate = true;
                    break;
                }
            }

            if ($hasAnyServiceToCreate) {
                $invoice->status = 'pending_activation';
            }
            // If !$hasAnyServiceToCreate, invoice status remains 'paid' as set earlier.
            $invoice->save();

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

    /**
     * Allows a client to cancel their previously reported manual payment
     * if the invoice is still pending confirmation.
     */
    public function cancelPaymentReport(Invoice $invoice): RedirectResponse
    {
        // Authorization: Ensure the authenticated user owns this invoice
        // Using 'update' policy assuming clients can update (which includes this action) invoices they own.
        // Adjust if a more specific policy like 'cancelPaymentReport' is created.
        $this->authorize('cancelPaymentReport', $invoice);

        if ($invoice->status !== 'pending_confirmation') {
            Log::warning("User " . Auth::id() . " attempted to cancel payment report for invoice {$invoice->id} which is not in 'pending_confirmation' status. Current status: {$invoice->status}");
            return Redirect::route('client.invoices.show', $invoice->id)
                             ->with('error', 'Este reporte de pago no puede ser anulado porque la factura no está pendiente de confirmación.');
        }

        DB::beginTransaction();
        try {
            // Find the latest pending payment transaction for this invoice
            // This assumes that when a client reports a payment, a 'payment' type transaction is created with 'pending' status.
            $paymentTransaction = $invoice->transactions()
                ->where('type', 'payment')
                ->where('status', 'pending')
                ->latest('created_at') // Get the most recent one if multiple somehow exist
                ->first();

            if ($paymentTransaction) {
                $paymentTransaction->status = 'client_cancelled'; // This is the line to ensure is correct
                $paymentTransaction->save();
                Log::info("Payment transaction ID {$paymentTransaction->id} for invoice {$invoice->id} was cancelled by client " . Auth::id());
            } else {
                // This case might indicate an inconsistency, as an invoice 'pending_confirmation'
                // should ideally have a corresponding 'pending' payment transaction.
                Log::warning("No pending payment transaction found for invoice {$invoice->id} (status 'pending_confirmation') when client " . Auth::id() . " tried to cancel payment report.");
            }

            $invoice->status = 'unpaid';
            // Optionally, clear paid_date if it was set, though for pending_confirmation it usually wouldn't be.
            // $invoice->paid_date = null;
            $invoice->save();

            DB::commit();

            return Redirect::route('client.invoices.show', $invoice->id)
                             ->with('success', 'Tu reporte de pago ha sido anulado. La factura está nuevamente marcada como no pagada.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error when client " . Auth::id() . " tried to cancel payment report for invoice {$invoice->id}: " . $e->getMessage(), ['exception' => $e]);
            return Redirect::route('client.invoices.show', $invoice->id)
                             ->with('error', 'Ocurrió un error al intentar anular tu reporte de pago. Por favor, inténtalo de nuevo.');
        }
    }

    /**
     * Request cancellation of an invoice and its associated pending services.
     *
     * @param Invoice $invoice
     * @return RedirectResponse
     */
    public function requestInvoiceCancellation(Invoice $invoice): RedirectResponse
    {
        $this->authorize('requestCancellationForNewServiceInvoice', $invoice);

        DB::beginTransaction();

        try {
            $invoice->status = 'cancelled';

            // Load items and their related client services if not already loaded
            $invoice->loadMissing('items.clientService');

            foreach ($invoice->items as $item) {
                // Check if the item has a related client service and if that service is pending
                if ($item->clientService && $item->clientService->status === 'pending') {
                    $item->clientService->status = 'cancelled';
                    $item->clientService->save();
                }
            }

            $invoice->save();

            DB::commit();

            return redirect()->route('client.invoices.show', $invoice)
                             ->with('success', 'La factura y los servicios pendientes asociados han sido cancelados.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error requesting invoice cancellation for Invoice ID {$invoice->id}: " . $e->getMessage(), [
                'invoice_id' => $invoice->id,
                'user_id' => Auth::id(),
                'exception' => $e
            ]);
            return redirect()->route('client.invoices.show', $invoice)
                             ->with('error', 'Ocurrió un error al intentar cancelar la factura.');
        }
    }
}

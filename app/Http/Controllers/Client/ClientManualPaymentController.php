<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Requests\Client\StoreManualPaymentRequest; // Will be created next
use Illuminate\Support\Facades\Redirect;

class ClientManualPaymentController extends Controller
{
    /**
     * Show the form for initiating a manual payment for an invoice.
     */
    public function showPaymentForm(Request $request, Invoice $invoice)
    {
        $this->authorize('pay', $invoice);

        if ($invoice->status !== 'unpaid') {
            return Redirect::route('client.invoices.show', $invoice->id)
                ->with('error', 'Esta factura no está pendiente de pago.');
        }

        $paymentMethods = PaymentMethod::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'account_holder_name', 'account_number', 'bank_name', 'branch_name', 'swift_code', 'iban', 'instructions', 'logo_url']);

        return Inertia::render('Client/Payments/ManualPaymentForm', [
            'invoice' => $invoice->load('order:id,order_number'), // Eager load necessary order details
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * Process the manual payment submission from the client.
     */
    public function processManualPayment(StoreManualPaymentRequest $request, Invoice $invoice)
    {
        $this->authorize('pay', $invoice);

        if ($invoice->status !== 'unpaid') {
            return Redirect::route('client.invoices.show', $invoice->id)
                ->with('error', 'Esta factura no está pendiente de pago o ya ha sido pagada.');
        }

        $validated = $request->validated();

        Transaction::create([
            'client_id' => Auth::id(),
            'invoice_id' => $invoice->id,
            'order_id' => $invoice->order_id, // Assumes invoice always has an order_id if it's for an order
            'payment_method_id' => $validated['payment_method_id'],
            'gateway_slug' => 'manual_payment', // Specific slug for these types of transactions
            'gateway_transaction_id' => $validated['reference_number'], // Client's reference
            'amount' => $invoice->total_amount,
            'currency_code' => $invoice->currency_code,
            'status' => 'pending', // Pending confirmation by admin
            'type' => 'payment',
            'transaction_date' => $validated['payment_date'],
            'description' => "Pago manual iniciado por cliente para factura #{$invoice->invoice_number}",
            'fees_amount' => 0, // Typically no fees for manual payment recording itself
        ]);

        // Add these lines:
        $invoice->status = 'pending_confirmation';
        $invoice->save();

        return Redirect::route('client.invoices.show', $invoice->id)
            ->with('success', 'Tu información de pago ha sido enviada y está pendiente de confirmación.');
    }
}

<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request; // Keep for potential future use, though not directly used now
use Inertia\Inertia;
use App\Http\Requests\Client\StoreFundAdditionRequest; // Will be created next
use Illuminate\Support\Facades\Redirect;

class ClientFundAdditionController extends Controller
{
    /**
     * Show the form for adding funds.
     */
    public function showAddFundsForm()
    {
        $paymentMethods = PaymentMethod::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'account_holder_name', 'account_number', 'bank_name', 'branch_name', 'swift_code', 'iban', 'instructions', 'logo_url']);
        
        $client = Auth::user();
        $currencyCode = $client->currency_code ?? 'USD'; // Default to USD if not set

        return Inertia::render('Client/Funds/AddForm', [
            'paymentMethods' => $paymentMethods,
            'currencyCode' => $currencyCode,
        ]);
    }

    /**
     * Process the fund addition request from the client.
     */
    public function processFundAddition(StoreFundAdditionRequest $request)
    {
        $validated = $request->validated();
        $client = Auth::user();

        Transaction::create([
            'client_id' => $client->id,
            'invoice_id' => null,
            'order_id' => null,
            'payment_method_id' => $validated['payment_method_id'],
            'gateway_slug' => 'manual_fund_addition', // Specific slug for these types of transactions
            'gateway_transaction_id' => $validated['reference_number'], // Client's reference
            'amount' => $validated['amount'],
            'currency_code' => $client->currency_code ?? 'USD',
            'status' => 'pending', // Pending confirmation by admin
            'type' => 'fund_addition',
            'transaction_date' => $validated['payment_date'],
            'description' => "Solicitud de adición de fondos por cliente.",
            'fees_amount' => 0, // Typically no fees for manual fund addition recording itself
        ]);

        return Redirect::route('client.transactions.index') // Or client.dashboard if more appropriate
            ->with('success', 'Tu solicitud para agregar fondos ha sido enviada y está pendiente de confirmación.');
    }
}

<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderActivity;
use App\Models\Transaction;
use App\Models\User; // Assuming User model is in App\Models
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class InvoiceController extends Controller
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
            'client', // Already loaded by policy check if using $invoice->client_id for auth
            'reseller', 
            'items',
            'items.orderItem.product', // Example: load product through orderItem
            'items.clientService', // If applicable
            'order' // Load the associated order if it exists
        ]);
        
        // Get authenticated user and explicitly include balance and formatted_balance
        $authUser = Auth::user();
        $userResource = null;
        if ($authUser) {
            $userResource = [
                'id' => $authUser->id,
                'name' => $authUser->name,
                'email' => $authUser->email,
                'balance' => $authUser->balance, // Assuming 'balance' is a direct attribute or casted
                'formatted_balance' => $authUser->formatted_balance, // Accessor
            ];
        }


        return Inertia::render('Client/Invoices/Show', [
            'invoice' => $invoice,
            'auth' => ['user' => $userResource] // Pass necessary auth user details
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
        // Authorization: Use the 'payWithBalance' policy.
        $this->authorize('payWithBalance', $invoice);

        // Validation: Check invoice status (already implicitly handled by policy, but explicit check is fine)
        if ($invoice->status !== 'unpaid') {
            return redirect()->route('client.invoices.show', $invoice->id)
                             ->with('error', 'This invoice is not awaiting payment.');
        }

        $user = Auth::user();

        // Validation: Check user balance
        if ($user->balance < $invoice->total_amount) {
            return redirect()->route('client.invoices.show', $invoice->id)
                             ->with('error', 'Insufficient balance to pay this invoice.');
        }

        DB::beginTransaction();

        try {
            // 1. Decrement user balance
            $user->balance -= $invoice->total_amount;
            $user->save();

            // 2. Create Transaction record
            $transaction = Transaction::create([
                'invoice_id' => $invoice->id,
                'client_id' => $user->id,
                'reseller_id' => $user->reseller_id, // Assuming reseller_id is on user model
                'gateway_slug' => 'balance',
                'gateway_transaction_id' => 'BAL-' . strtoupper(Str::random(10)),
                'type' => 'payment',
                'amount' => $invoice->total_amount,
                'currency_code' => $invoice->currency_code,
                'status' => 'completed',
                'description' => "Payment for Invoice #{$invoice->invoice_number} using account balance.",
                'transaction_date' => Carbon::now(),
            ]);

            // 3. Update Invoice status
            $invoice->status = 'paid';
            $invoice->paid_date = Carbon::now();
            $invoice->save();

            // 4. Update Associated Order
            $invoice->load('order.client'); // Ensure order and its client are loaded
            $order = $invoice->order;

            if ($order && $order->status === 'pending_payment') {
                $previous_order_status = $order->status;
                $order->status = 'paid_pending_execution';
                $order->save();

                OrderActivity::create([
                    'order_id' => $order->id,
                    'user_id' => $user->id, // Client user performing the action via balance
                    'type' => 'payment_confirmed_order_pending_execution',
                    'details' => json_encode([
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'payment_method' => 'account_balance',
                        'transaction_id' => $transaction->id,
                        'previous_order_status' => $previous_order_status,
                        'client_name' => $order->client->name, // Assuming client relationship exists and has name
                        'new_order_status' => $order->status,
                    ]),
                ]);
            }

            DB::commit();

            return redirect()->route('client.invoices.show', $invoice->id)
                             ->with('success', 'Invoice paid successfully using your account balance.');

        } catch (\Exception $e) {
            DB::rollBack();
            // Log the exception $e->getMessage()
            return redirect()->route('client.invoices.show', $invoice->id)
                             ->with('error', 'An error occurred while processing your payment. Please try again.');
        }
    }
}

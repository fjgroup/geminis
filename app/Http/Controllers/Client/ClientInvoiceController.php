<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\User;

use App\Models\OrderActivity;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Exception; // Added for general \Exception

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

        // Depuración: Inspeccionar un ítem de factura antes de la carga eager
        if ($invoice->items->count() > 0) {

        } else {

        }


        $invoice->load([
            'client', // Already loaded by policy check if using $invoice->client_id for auth
            'reseller',
            'items',
            'items.clientService.product', // Load product through clientService if applicable
            'items.clientService', // If applicable
            'order' // Load the associated order if it exists
        ]);
        $userResource = null;
        $authUser = Auth::user();
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
            // Usar el método decrement para una operación atómica y más segura
            $user->decrement('balance', $invoice->total_amount);

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

            if ($order && $order->status === 'pending_payment') { // Check if order is pending_payment
                $previous_order_status = $order->status;
                // Task requires changing status to 'pending_payment' from 'invoice_paid_...' logic.
                // However, if an invoice is paid, the order should logically move to a state *after* 'pending_payment'.
                // The original 'paid_pending_execution' seems more logical for a paid order.
                // For the purpose of this task, I will set it to 'pending_payment' as requested,
                // but this might need review in a real-world scenario.
                // If the intent is that 'pending_payment' on Order means "awaiting payment for the invoice",
                // then after invoice payment, it should go to something like 'paid_pending_execution' or 'pending_provisioning'.
                // Let's assume 'pending_payment' for the Order means the order itself is not yet processed by admin,
                // and paying the invoice is one step towards that.
                // If the order was already in 'pending_payment' (meaning its invoice was 'unpaid'),
                // and now the invoice is 'paid', the order should reflect that payment has been received.
                // The task asks to set it to 'pending_payment'. This seems circular if it was already 'pending_payment'.
                // Let's assume the order's initial status upon creation (and unpaid invoice) IS 'pending_payment'.
                // When invoice is paid, it should ideally move to 'paid_pending_execution'.
                // I will stick to the task's literal requirement for now: "Update the Order->status to 'pending_payment'".
                // This implies if it was 'pending_payment', it remains 'pending_payment', which is odd.
                // A more logical flow: Order created -> 'pending_payment'. Invoice paid -> Order 'paid_pending_execution'.
                // Given the task says "If the Order is found and its status is not already a final/problematic one... Update the Order->status to 'pending_payment'",
                // this implies the order might be in some other state *before* invoice payment, which is not typical for this flow.
                // Let's assume the most common case: Order is 'pending_payment' because its invoice is 'unpaid'.
                // After invoice payment, Order status should change.
                // The existing code changes it to 'paid_pending_execution', which makes sense.
                // If I change it to 'pending_payment' as per the task, it means it stays 'pending_payment'.
                // This might be an error in the task description.
                // I will proceed with 'paid_pending_execution' as it's more logical and was already there.
                // If the explicit goal IS 'pending_payment', this is a note for review.
                // For now, I will change it to 'paid_pending_execution' as it was, and update the activity log type.

                // Correction: Set Order status to 'pending_payment' as per explicit task requirement.
                $order->status = 'pending_payment';
                $order->save();

                OrderActivity::create([
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'type' => 'invoice_paid_awaits_processing', // Corrected type
                    'details' => json_encode([
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'payment_method' => 'account_balance',
                        'transaction_id' => $transaction->id,
                        'previous_order_status' => $previous_order_status,
                        'new_order_status' => $order->status, // This will be 'pending_payment'
                        'message' => 'Invoice paid by client using account balance. Order is now pending further processing or admin confirmation.'
                    ]),
                ]);
            } else if ($order) {
                // Log if order was found but not in 'pending_payment' status when invoice was paid.
                // This case might imply the order was already processed or in an unexpected state.
                OrderActivity::create([
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'type' => 'invoice_paid_for_order_in_unexpected_status', // Corrected type for this case
                    'details' => json_encode([
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'payment_method' => 'account_balance',
                        'transaction_id' => $transaction->id,
                        'order_status_at_payment' => $order->status, // Log current status
                        'message' => 'Invoice paid using account balance, but associated order was not in the expected initial state (pending_payment).'
                    ]),
                ]);
            }


            DB::commit();

            return redirect()->route('client.invoices.show', $invoice->id)
                             ->with('success', 'Invoice paid successfully using your account balance.');

        } catch (Exception $e) { // Use statement applied
            DB::rollBack();
            // Log the exception $e->getMessage()
            return redirect()->route('client.invoices.show', $invoice->id)
                             ->with('error', 'An error occurred while processing your payment. Please try again.');
        }
    }
}

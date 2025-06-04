<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\ConfirmManualTransactionAction; // Import the new Action
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTransactionRequest;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderActivity;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Import Log facade
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use InvalidArgumentException; // Import specific exception

class AdminTransactionController extends Controller
{
    /**
     * Store a newly created transaction in storage.
     */
    public function store(StoreTransactionRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('create', Transaction::class);
        $validatedData = $request->validated();
        $validatedData['client_id'] = $invoice->client_id;
        $validatedData['reseller_id'] = $invoice->reseller_id;

        $transaction = Transaction::create($validatedData);

        // This method will use the controller's private helper
        $this->updateInvoiceAndOrderStatusAfterStore($invoice, $transaction);

        return Redirect::route('admin.invoices.show', $invoice->id)
                         ->with('success', 'Payment registered successfully.');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): InertiaResponse // Add Request $request
    {
        $this->authorize('viewAny', Transaction::class);

        $query = Transaction::with(['invoice', 'client', 'reseller', 'paymentMethod'])
            ->latest('transaction_date');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('gateway_slug')) {
            $query->where('gateway_slug', $request->input('gateway_slug'));
        }

        // TODO: Add search filter if 'search' is filled, searching across relevant fields.
        // Example for search (can be expanded):
        // if ($request->filled('search')) {
        //     $searchTerm = $request->input('search');
        //     $query->where(function($q) use ($searchTerm) {
        //         $q->where('id', 'like', "%{$searchTerm}%")
        //           ->orWhere('gateway_transaction_id', 'like', "%{$searchTerm}%")
        //           ->orWhereHas('client', fn($cq) => $cq->where('name', 'like', "%{$searchTerm}%"));
        //     });
        // }


        $transactions = $query->paginate(15)->withQueryString(); // withQueryString to append filters to pagination

        return Inertia::render('Admin/Transactions/Index', [
            'transactions' => $transactions,
            'filters' => $request->only(['search', 'status', 'type', 'gateway_slug']), // Pass used filters
        ]);
    }

    /**
     * Confirm a pending transaction using the Action class.
     */
    public function confirm(Transaction $transaction, ConfirmManualTransactionAction $confirmAction): RedirectResponse
    {
        $this->authorize('confirm', $transaction);

        try {
            $success = $confirmAction->execute($transaction);
            if ($success) {
                return redirect()->route('admin.transactions.index')->with('success', 'Transacción confirmada exitosamente.');
            } else {
                // This path might not be reached if execute() throws exceptions for all failures
                return redirect()->route('admin.transactions.index')->with('error', 'No se pudo confirmar la transacción por una razón desconocida.');
            }
        } catch (InvalidArgumentException $e) {
            return redirect()->route('admin.transactions.index')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Error confirming transaction: ' . $e->getMessage(), [
                'transaction_id' => $transaction->id,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('admin.transactions.index')->with('error', 'Ocurrió un error al confirmar la transacción.');
        }
    }

    /**
     * Reject a pending transaction.
     */
    public function reject(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('reject', $transaction);

        if ($transaction->status !== 'pending') {
            return Redirect::route('admin.transactions.index')->with('error', 'Transaction is not pending and cannot be rejected.');
        }

        $transaction->status = 'failed'; // or 'rejected'
        // Optional: Add admin notes if the field exists and is sent in the request
        // if ($request->has('rejection_reason') && Schema::hasColumn('transactions', 'admin_notes')) {
        //     $transaction->admin_notes = $request->input('rejection_reason');
        // }
        $transaction->save();

        return Redirect::route('admin.transactions.index')->with('success', 'Transaction rejected successfully.');
    }

    /**
     * Helper method to update invoice and order status specifically for the store() method.
     * The logic inside ConfirmManualTransactionAction is similar but tailored for that action's context.
     */
    private function updateInvoiceAndOrderStatusAfterStore(Invoice $invoice, Transaction $transaction)
    {
        $invoice->loadMissing('transactions', 'order.client');

        $totalPaid = $invoice->transactions
                             ->where('status', 'completed')
                             ->sum('amount');

        $netPaid = $totalPaid;

        if (bccomp($netPaid, $invoice->total_amount, 2) >= 0) {
            if ($invoice->status !== 'paid') {
                $invoice->status = 'paid';
                $invoice->paid_date = $transaction->transaction_date ?? now();
            }
        } elseif (bccomp($netPaid, '0', 2) <= 0 && in_array($invoice->status, ['paid', 'overdue'])) {
            $invoice->status = 'unpaid';
            $invoice->paid_date = null;
        } elseif (bccomp($netPaid, '0', 2) > 0 && bccomp($netPaid, $invoice->total_amount, 2) < 0 && $invoice->status === 'paid') {
            $invoice->status = 'unpaid';
            $invoice->paid_date = null;
        }
        $invoice->save();

        $order = $invoice->order;
        if ($invoice->status === 'paid' && $order) {
            if ($order->status === 'pending_payment') { // Condition specific to store flow
                $previous_status = $order->status;
                $order->status = 'paid_pending_execution';
                $order->save();

                OrderActivity::create([
                    'order_id' => $order->id,
                    'user_id' => Auth::id(),
                    'type' => 'payment_confirmed_order_pending_execution', // Type can be more specific for store
                    'details' => json_encode([
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'payment_transaction_id' => $transaction->id,
                        'transaction_status_changed_to' => $transaction->status, // This is 'completed' from store
                        'previous_order_status' => $previous_status,
                        'client_name' => $order->client->name ?? 'N/A',
                        'new_order_status' => $order->status,
                    ]),
                ]);
            }
        }
    }
}

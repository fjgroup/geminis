<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTransactionRequest;
use App\Models\Invoice;
// use App\Models\Transaction; // Already imported for store, but ensure it's here
use Illuminate\Http\RedirectResponse;
// use Illuminate\Support\Facades\Auth; // Not used in store, check if needed for index
// use Illuminate\Support\Facades\DB; // Not used in store, check if needed for index

// Imports for the new index method
use App\Models\Transaction; // Explicitly ensuring Transaction model is imported
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class TransactionController extends Controller
{
    /**
     * Store a newly created transaction in storage.
     *
     * @param  StoreTransactionRequest  $request
     * @param  Invoice  $invoice  // Route model binding for the invoice
     * @return RedirectResponse
     */
    public function store(StoreTransactionRequest $request, Invoice $invoice): RedirectResponse
    {
        // Authorization is handled by StoreTransactionRequest's authorize() method returning true,
        // and then policy check here.
        $this->authorize('create', Transaction::class); // Uses TransactionPolicy@create

        // Validate request (already done by StoreTransactionRequest)
        $validatedData = $request->validated();

        // Add client_id and reseller_id from the invoice by default
        // Admin making the entry is Auth::id(), but transaction is for invoice's client
        $validatedData['client_id'] = $invoice->client_id;
        $validatedData['reseller_id'] = $invoice->reseller_id; // This might be null

        // Create the transaction
        $transaction = Transaction::create($validatedData);

        // Update Invoice Status
        $invoice->load('transactions'); // Ensure transactions are loaded for accurate sum

        $totalPaid = $invoice->transactions
                             ->where('status', 'completed')
                             ->where('type', 'payment')
                             ->sum('amount');
        
        $totalRefunded = $invoice->transactions
                                ->where('status', 'completed')
                                ->where('type', 'refund') // Assuming 'refund' type exists
                                ->sum('amount');

        $netPaid = $totalPaid - $totalRefunded;

        if ($netPaid >= $invoice->total_amount) {
            if ($invoice->status !== 'paid') { // Only update if not already paid
                $invoice->status = 'paid';
                $invoice->paid_date = $transaction->transaction_date; // Or use Carbon::now()
            }
        } elseif ($netPaid <= 0 && in_array($invoice->status, ['paid', 'overdue'])) { 
            // If it was paid or overdue and now effectively zero or less is paid (e.g. full refund)
            $invoice->status = 'unpaid'; // Or 'refunded' if that's a more appropriate status
            $invoice->paid_date = null;
        } elseif ($netPaid > 0 && $netPaid < $invoice->total_amount && $invoice->status === 'paid') {
            // If it was paid, but now a refund makes it partially paid
            $invoice->status = 'unpaid'; // Or potentially 'overdue' if due_date has passed
            $invoice->paid_date = null; // Remove paid_date as it's no longer fully paid
        }
        // If netPaid > 0 and < total_amount, and status was 'unpaid' or 'overdue', it remains so.
        // No 'partial' status is used.
        // Further refinement can be done if specific statuses like 'partially_paid' or 'refunded' are added to the ENUM.

        $invoice->save();

        // Redirect back to the invoice show page with a success message
        return redirect()->route('admin.invoices.show', $invoice->id)
                         ->with('success', 'Payment registered successfully.');
    }

    /**
     * Display a listing of the resource.
     *
     * @return InertiaResponse
     */
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', Transaction::class); // Uses TransactionPolicy@viewAny

        $transactions = Transaction::with(['invoice', 'client', 'reseller'])
            ->latest('transaction_date') // Order by most recent
            ->paginate(15); // Or your preferred pagination size

        return Inertia::render('Admin/Transactions/Index', [
            'transactions' => $transactions,
            'filters' => request()->all('search', 'status', 'type'), // For potential filtering later
        ]);
    }
}

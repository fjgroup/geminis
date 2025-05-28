<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
// use Illuminate\Http\Request; // Request parameter is not used in show method
use Illuminate\Support\Facades\Auth; // Importar Auth
use Inertia\Inertia; // Added for Inertia::render
use Inertia\Response as InertiaResponse; // Added for type hinting
use App\Http\Requests\Admin\UpdateOrderRequest; // New
use Illuminate\Http\RedirectResponse; // New
use Illuminate\Support\Facades\Log; // For logging errors
use Illuminate\Http\Request; // For existing index method
use App\Models\OrderActivity; // Added for new methods
use App\Models\Invoice; // Added for approveCancellationRequest
use App\Models\Transaction; // Added for approveCancellationRequest
use Illuminate\Support\Carbon; // Added for approveCancellationRequest
use Illuminate\Support\Str; // Added for approveCancellationRequest
use Illuminate\Support\Facades\DB; // Added for approveCancellationRequest

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): InertiaResponse // Add Request type hint
    {
        $this->authorize('viewAny', Order::class); // Assuming OrderPolicy@viewAny allows admins

        // $user = Auth::user(); // Not directly used in the new logic if admin sees all by default
        
        $query = Order::with([
            'client:id,name,email,balance,currency_code', // Specific columns for client
            'invoice', 
            'items'
        ])->latest('order_date');

        // Status Filtering
        if ($request->filled('status')) {
            $validStatuses = ['pending_payment', 'paid_pending_execution', 'cancellation_requested_by_client', 'active', 'completed', 'fraud', 'cancelled', 'pending_provisioning']; // All current ENUMs
            if (in_array($request->status, $validStatuses)) {
                $query->where('status', $request->status);
            }
        }
        
        // Search Filtering (Example - can be expanded)
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('order_number', 'like', "%{$searchTerm}%")
                  ->orWhereHas('client', function($clientQuery) use ($searchTerm) {
                      $clientQuery->where('name', 'like', "%{$searchTerm}%")
                                  ->orWhere('email', 'like', "%{$searchTerm}%");
                  })
                  ->orWhereHas('invoice', function($invoiceQuery) use ($searchTerm) {
                        $invoiceQuery->where('invoice_number', 'like', "%{$searchTerm}%");
                  });
            });
        }
        
        // Reseller Scoping: If the authenticated user is a reseller, scope orders to their clients.
        // This assumes admins do not have the 'reseller' role or that isAdmin() check in policy is primary.
        $authUser = Auth::user();
        if ($authUser->hasRole('reseller')) {
            $query->whereHas('client', function ($q) use ($authUser) {
                $q->where('reseller_id', $authUser->id);
            });
        }

        $orders = $query->paginate(15)->withQueryString(); // withQueryString to append filters to pagination links

        // Get distinct statuses from the orders table for filter dropdown
        // This can be resource-intensive on large tables. Consider a predefined list or caching.
        // For now, let's use the known list.
        $possibleStatuses = ['pending_payment', 'paid_pending_execution', 'cancellation_requested_by_client', 'active', 'completed', 'fraud', 'cancelled', 'pending_provisioning'];


        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'filters' => $request->only(['search', 'status']), // Pass current filters back to the view
            'possibleStatuses' => $possibleStatuses, // For the filter dropdown
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order): InertiaResponse // Removed Request $request, added InertiaResponse type hint
    {
        $this->authorize('view', $order); // Assuming OrderPolicy@view exists

        $order->load([
            'client:id,name,email', // Load specific columns for client
            'reseller:id,name,email', // Load specific columns for reseller
            'items.product:id,name', // For each order item, load its product
            'items.productPricing:id,price,billing_cycle_id', // And its pricing details (billing_cycle_name is not a direct column, billing_cycle_id is)
            'invoice:id,invoice_number,status,total_amount' // Load associated invoice
        ]);

        return Inertia::render('Admin/Orders/Show', [
            'order' => $order,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Order  $order
     * @return InertiaResponse
     */
    public function edit(Order $order): InertiaResponse
    {
        $this->authorize('update', $order); // Uses OrderPolicy@update

        // For status dropdown, use the same list as in UpdateOrderRequest or from Order model
        $possibleStatuses = ['pending_payment', 'pending_provisioning', 'active', 'fraud', 'cancelled', 'completed'];
        // It's better to get this from the Order model if possible, e.g., Order::getPossibleStatuses()

        return Inertia::render('Admin/Orders/Edit', [
            'order' => $order->load(['client:id,name', 'items.product']), // Load necessary data
            'possibleStatuses' => $possibleStatuses,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateOrderRequest  $request
     * @param  Order  $order
     * @return RedirectResponse
     */
    public function update(UpdateOrderRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order); // Uses OrderPolicy@update

        $validatedData = $request->validated();

        try {
            $order->status = $validatedData['status'];
            if (isset($validatedData['notes'])) {
                // Append to existing notes or replace, based on desired behavior.
                // For now, let's assume we append if notes are provided.
                // A more sophisticated approach might involve a separate notes/history table.
                $order->notes = ($order->notes ? $order->notes . "\n--- Admin Update ---\n" : '') . $validatedData['notes'];
            }
            
            // If status changed to 'completed' or 'active' and it wasn't before,
            // consider if any provisioning logic or events should be triggered.
            // (This is out of scope for current task but a point for future).

            $order->save();

            return redirect()->route('admin.orders.show', $order->id)
                             ->with('success', 'Order updated successfully.');

        } catch (\Illuminate\Database\QueryException $e) {
            // Check for ENUM constraint violation if 'completed' status isn't in DB yet
            if (str_contains($e->getMessage(), "Data truncated for column 'status'")) {
                 Log::error("Failed to update order status: Possible ENUM mismatch. Status tried: " . $validatedData['status'], ['error' => $e]);
                 return redirect()->back()
                                 ->with('error', 'Failed to update order: The status value is not valid. Please ensure the database schema is up to date.');
            }
            Log::error("Failed to update order: " . $e->getMessage(), ['error' => $e]);
            return redirect()->back()
                             ->with('error', 'Failed to update order due to a database error.');
        } catch (\Exception $e) {
            Log::error("Failed to update order: " . $e->getMessage(), ['error' => $e]);
            return redirect()->back()
                             ->with('error', 'An unexpected error occurred while updating the order.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Order  $order
     * @return RedirectResponse
     */
    public function destroy(Order $order): RedirectResponse
    {
        $this->authorize('delete', $order); // Uses OrderPolicy@delete

        try {
            // Check for related records that might prevent deletion, e.g., non-cancelled invoices
            // Ensure invoice relationship is loaded if not already by route model binding enhancements
            $order->loadMissing('invoice'); 
            if ($order->invoice && !in_array($order->invoice->status, ['cancelled', 'refunded'])) {
                 return redirect()->route('admin.orders.show', $order->id)
                                 ->with('error', 'Cannot delete order: It has an active or unpaid invoice. Please cancel or refund the invoice first.');
            }

            $order->delete(); // This will soft delete if the trait is used

            return redirect()->route('admin.orders.index')
                             ->with('success', 'Order successfully deleted (soft delete).');
        } catch (\Exception $e) {
            Log::error("Failed to delete order: " . $e->getMessage(), ['order_id' => $order->id, 'error' => $e]);
            return redirect()->route('admin.orders.show', $order->id)
                             ->with('error', 'An unexpected error occurred while deleting the order.');
        }
    }

    /**
     * Mark the order as being processed by admin.
     *
     * @param  Order  $order
     * @return RedirectResponse
     */
    public function startExecution(Order $order): RedirectResponse
    {
        $this->authorize('update', $order); // Or a more specific policy method like 'manageExecution'

        if ($order->status !== 'paid_pending_execution') {
            return redirect()->route('admin.orders.show', $order->id)
                             ->with('error', 'Order cannot be started at its current stage. Expected status: Paid, Pending Execution.');
        }

        try {
            $order->status = 'pending_provisioning'; // Or 'provisioning_in_progress', 'admin_processing'
                                                 // This 'pending_provisioning' was an original ENUM value.
            $order->save();

            OrderActivity::create([
                'order_id' => $order->id,
                'user_id' => Auth::id(), // Admin performing the action
                'type' => 'admin_started_provisioning', // From ENUM list
                'details' => ['previous_status' => 'paid_pending_execution']
            ]);

            return redirect()->route('admin.orders.show', $order->id)
                             ->with('success', 'Order status updated to: Processing by Admin.');
        } catch (\Exception $e) {
            Log::error("Error starting order execution for order ID: {$order->id}", ['error' => $e->getMessage()]);
            return redirect()->route('admin.orders.show', $order->id)
                             ->with('error', 'Failed to start order execution.');
        }
    }

    /**
     * Mark the order as completed/service activated.
     *
     * @param  Order  $order
     * @return RedirectResponse
     */
    public function completeExecution(Order $order): RedirectResponse
    {
        $this->authorize('update', $order); // Or 'manageExecution'

        // Typically, an order would be in 'pending_provisioning' or a similar active processing state
        if (!in_array($order->status, ['pending_provisioning', 'paid_pending_execution', 'active'])) {
             // Allow 'active' if it can be re-completed or if 'active' implies ongoing and 'completed' is final.
             // Allow 'paid_pending_execution' to skip 'startExecution' if admin wants to mark as directly active/completed.
            return redirect()->route('admin.orders.show', $order->id)
                             ->with('error', 'Order cannot be completed at its current stage.');
        }

        try {
            // Decide if it goes to 'active' first or directly to 'completed'
            // For a service that runs, 'active' is good.
            // If it's a one-time provisioning, 'completed' might be fine.
            // Let's use 'active' as a general "service is now usable" state.
            $previousStatus = $order->status;
            $order->status = 'active'; // Or 'completed' if that's the final state post-provisioning
            $order->save();

            OrderActivity::create([
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'type' => 'service_activated', // Or 'admin_completed_provisioning' from ENUM
                'details' => ['previous_status' => $previousStatus, 'new_status' => 'active']
            ]);

            // Potentially trigger other actions: e.g., create ClientService record, send notification
            // (These are out of scope for this specific sub-task)

            return redirect()->route('admin.orders.show', $order->id)
                             ->with('success', 'Order execution completed. Service is now active.');
        } catch (\Exception $e) {
            Log::error("Error completing order execution for order ID: {$order->id}", ['error' => $e->getMessage()]);
            return redirect()->route('admin.orders.show', $order->id)
                             ->with('error', 'Failed to complete order execution.');
        }
    }

    public function approveCancellationRequest(Order $order): RedirectResponse
    {
        $this->authorize('update', $order); // Or a more specific policy: 'approveCancellation', $order

        if ($order->status !== 'cancellation_requested_by_client') {
            return redirect()->route('admin.orders.show', $order->id)
                             ->with('error', 'Order is not awaiting cancellation approval.');
        }

        DB::beginTransaction();
        try {
            $previousStatus = $order->status;

            // 1. Update Order Status
            $order->status = 'cancelled'; // Final cancelled status
            $order->save();

            // 2. Update associated Invoice status (e.g., 'refunded' or 'cancelled')
            //    And potentially create a credit note / adjustment invoice (out of scope for now)
            if ($order->invoice) {
                $invoice = $order->invoice;
                if (in_array($invoice->status, ['paid', 'overdue'])) { // Only if it was actually paid
                    $invoice->status = 'refunded'; // Or 'cancelled_credited' if you add such ENUM
                                                  // 'refunded' is an existing ENUM for invoices
                    // $invoice->notes_to_client = ($invoice->notes_to_client ? $invoice->notes_to_client . "\n" : '') . "Order cancelled and amount credited to account.";
                    $invoice->save();

                    // 3. Create Financial Transaction for the credit issued to client
                    // This assumes client gets a credit to their account balance.
                    // The actual crediting of user's balance (e.g. User::balance column) is Step 10.
                    Transaction::create([
                        'invoice_id' => $invoice->id, // Link to original invoice for reference
                        'client_id' => $order->client_id,
                        'reseller_id' => $order->reseller_id,
                        'gateway_slug' => 'internal_credit', // Or 'account_credit'
                        'gateway_transaction_id' => 'CREDIT-' . strtoupper(Str::random(10)),
                        'type' => 'credit_added', // From ENUM in transactions migration
                        'amount' => $invoice->total_amount, // Assuming full amount is credited
                        'currency_code' => $invoice->currency_code,
                        'status' => 'completed',
                        'description' => 'Credit issued for cancelled Order #' . $order->order_number . ' / Invoice #' . $invoice->invoice_number,
                        'transaction_date' => Carbon::now(),
                    ]);

                    // 3.5. Update Client's Balance
                    $client = $order->client; // Assumes 'client' relationship is eager loaded or loaded via $order->load('client') if needed
                    if (!$client) { // Defensive check, ensure client is loaded
                        $order->load('client');
                        $client = $order->client;
                    }

                    if ($client) {
                        $creditAmount = $invoice->total_amount; // Amount from the invoice
                        if ($creditAmount > 0) {
                            $client->increment('balance', $creditAmount); // Atomically increments
                            // No need for $client->save() when using increment/decrement
                        }
                    } else {
                        // Log a warning if client not found on order, though this should ideally not happen
                        Log::warning("Client not found for order ID: {$order->id} during credit approval.");
                    }

                } else {
                    // If invoice wasn't 'paid' or 'overdue' but order was 'cancellation_requested_by_client'
                    // (shouldn't happen if previous logic is correct), just mark invoice as cancelled.
                    $invoice->status = 'cancelled';
                    $invoice->save();
                }
            }

            // 4. Create OrderActivity Log
            // Eager load client again to get the updated balance for logging, if $client was reloaded.
            // Or, if $client->increment() updates the model instance, this might not be needed.
            // $client->refresh(); // To be safe, or trust that $client->balance is updated.
            // For simplicity, we assume $client->balance reflects the new balance after increment.
            // If not, a $newBalance = $client->balance (before increment) + $creditAmount would be more direct for logging.
            // However, the increment method returns a boolean, not the new balance directly.
            // So, to log the new balance, we'd have to re-fetch or calculate.
            // Let's fetch the client again for the new balance if needed.
            $updatedClientForLog = $order->client()->first(); // Re-fetch the client for the latest balance

            OrderActivity::create([
                'order_id' => $order->id,
                'user_id' => Auth::id(), // Admin performing the action
                'type' => 'cancellation_approved_credit_issued', // From ENUM
                'details' => [
                    'previous_status' => $previousStatus,
                    'new_order_status' => 'cancelled',
                    'invoice_id' => $order->invoice_id,
                    'invoice_status_updated_to' => $order->invoice ? $order->invoice->status : null,
                    'credited_amount' => $order->invoice && isset($creditAmount) ? $creditAmount : 0, // Use $creditAmount if set
                    'client_new_balance' => $updatedClientForLog ? $updatedClientForLog->balance : null,
                ]
            ]);

            DB::commit();

            return redirect()->route('admin.orders.show', $order->id)
                             ->with('success', 'Client cancellation request approved. Order cancelled and credit issued.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error approving cancellation for order ID: {$order->id}", ['error' => $e->getMessage()]);
            return redirect()->route('admin.orders.show', $order->id)
                             ->with('error', 'Failed to approve cancellation request.');
        }
    }
}

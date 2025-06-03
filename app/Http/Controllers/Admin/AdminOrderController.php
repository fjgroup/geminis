<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderActivity;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\ClientService; // Added ClientService model
use App\Http\Requests\Admin\UpdateOrderRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Database\QueryException;
use Exception;
use App\Actions\Admin\ConfirmOrderPaymentAction;
use App\Actions\Admin\ApproveOrderCancellationAction;

class AdminOrderController extends Controller
{
    // ... (index, create, store, show, edit, update, confirmPayment, destroy, startExecution methods remain unchanged) ...
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

        } catch (QueryException $e) { // Use statement applied
            // Check for ENUM constraint violation if 'completed' status isn't in DB yet
            if (str_contains($e->getMessage(), "Data truncated for column 'status'")) {
                 Log::error("Failed to update order status: Possible ENUM mismatch. Status tried: " . $validatedData['status'], ['error' => $e]);
                 return redirect()->back()
                                 ->with('error', 'Failed to update order: The status value is not valid. Please ensure the database schema is up to date.');
            }
            Log::error("Failed to update order: " . $e->getMessage(), ['error' => $e]);
            return redirect()->back()
                             ->with('error', 'Failed to update order due to a database error.');
        } catch (Exception $e) { // Use statement applied
            Log::error("Failed to update order: " . $e->getMessage(), ['error' => $e]);
            return redirect()->back()
                             ->with('error', 'An unexpected error occurred while updating the order.');
        }
    }

    /**
     * Confirm payment for an order by an admin.
     *
     * @param  Order  $order
     * @return RedirectResponse
     */
    public function confirmPayment(Order $order, ConfirmOrderPaymentAction $confirmOrderPaymentAction): RedirectResponse
    {
        $this->authorize('update', $order); // Assuming 'update' policy covers this admin action

        if ($order->status !== 'pending_payment') {
            return redirect()->route('admin.orders.show', $order->id)
                             ->with('info', "This order is not awaiting payment confirmation (current status: {$order->status}) or is already processed.");
        }

        try {
            // The ConfirmOrderPaymentAction handles its own DB transaction.
            $confirmOrderPaymentAction->execute($order);

            return redirect()->route('admin.orders.show', $order->id)
                             ->with('success', 'Payment confirmed. Order status updated to Paid, Pending Execution.');
        } catch (Exception $e) { // Catches exceptions from the Action class or other issues.
            // The action class already rolls back its transaction on failure if it started one.
            // If DB::beginTransaction() was used here, we would DB::rollBack();
            Log::error("Error confirming payment for order ID {$order->id} by admin " . Auth::id() . " (via Action): " . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('admin.orders.show', $order->id)
                             ->with('error', 'An error occurred while confirming payment. Please try again. ' . $e->getMessage());
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
        } catch (Exception $e) { // Use statement applied
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
            $order->status = 'pending_provisioning';
            $order->save();

            OrderActivity::create([
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'type' => 'admin_started_provisioning',
                'details' => ['previous_status' => 'paid_pending_execution']
            ]);

            return redirect()->route('admin.orders.show', $order->id)
                             ->with('success', 'Order status updated to: Processing by Admin.');
        } catch (Exception $e) {
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
        $this->authorize('update', $order);

        if (!in_array($order->status, ['pending_provisioning', 'paid_pending_execution', 'active'])) {
            return redirect()->route('admin.orders.show', $order->id)
                             ->with('error', 'Order cannot be completed at its current stage.');
        }

        DB::beginTransaction();
        try {
            $previousStatus = $order->status;
            $order->status = 'active'; // Or 'completed'

            $order->loadMissing('items.product.productType', 'items.productPricing.billingCycle', 'client.reseller'); // Ensure client.reseller and productType for items

            $orderItem = $order->items->first();
            $clientService = null;
            $activityDetails = ['previous_status' => $previousStatus, 'new_status' => 'active'];

            // Check if a service instance should be created based on ProductType
            if ($orderItem && $orderItem->product && $orderItem->product->productType && $orderItem->product->productType->creates_service_instance) {
                $registrationDate = Carbon::now();
                $nextDueDate = $registrationDate->copy();
                $billingCycle = $orderItem->productPricing->billingCycle ?? null;

                if ($billingCycle) {
                    switch ($billingCycle->type) {
                        case 'day': $nextDueDate->addDays($billingCycle->multiplier); break;
                        case 'month': $nextDueDate->addMonthsNoOverflow($billingCycle->multiplier); break;
                        case 'year': $nextDueDate->addYearsNoOverflow($billingCycle->multiplier); break;
                        default:
                            Log::warning("Unknown billing cycle type '{$billingCycle->type}' for ProductPricing ID: {$orderItem->product_pricing_id}. Defaulting to 1 month.");
                            $nextDueDate->addMonth();
                    }
                } else {
                    Log::warning("BillingCycle not found for ProductPricing ID: {$orderItem->product_pricing_id}. Defaulting next due date to 1 month.");
                    $nextDueDate->addMonth();
                }

                $clientService = ClientService::create([
                    'client_id' => $order->client_id,
                    'reseller_id' => $order->client->reseller_id, // Access reseller_id from loaded client
                    'order_id' => $order->id,
                    'product_id' => $orderItem->product_id,
                    'product_pricing_id' => $orderItem->product_pricing_id,
                    'billing_cycle_id' => $billingCycle->id ?? null, // Save billing_cycle_id
                    'domain_name' => $orderItem->domain_name,
                    'status' => 'Active', // ClientService status
                    'registration_date' => $registrationDate->toDateString(),
                    'next_due_date' => $nextDueDate->toDateString(),
                    'billing_amount' => $orderItem->unit_price, // Assuming unit_price is the recurring amount
                    'notes' => "Servicio activado desde Pedido #" . $order->order_number,
                ]);

                $orderItem->client_service_id = $clientService->id;
                $orderItem->save();

                $activityDetails['client_service_id'] = $clientService->id;
                $activityDetails['domain_name'] = $clientService->domain_name;
            } elseif (!$orderItem) {
                Log::error("Order {$order->id} has no items, cannot create ClientService.");
            }

            $order->save(); // Save order status

            OrderActivity::create([
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'type' => 'service_activated',
                'details' => $activityDetails
            ]);

            DB::commit();

            return redirect()->route('admin.orders.show', $order->id)
                             ->with('success', 'Order execution completed. Service is now active.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error completing order execution for order ID: {$order->id}", ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->route('admin.orders.show', $order->id)
                             ->with('error', 'Failed to complete order execution. ' . $e->getMessage());
        }
    }

    public function approveCancellationRequest(Order $order, ApproveOrderCancellationAction $approveOrderCancellationAction): RedirectResponse
    {
        $this->authorize('update', $order);

        if ($order->status !== 'cancellation_requested_by_client') {
            return redirect()->route('admin.orders.show', $order->id)
                             ->with('error', 'Order is not awaiting cancellation approval.');
        }

        try {
            $approveOrderCancellationAction->execute($order);
            
            return redirect()->route('admin.orders.show', $order->id)
                             ->with('success', 'Client cancellation request approved. Order cancelled and credit issued.');

        } catch (Exception $e) {
            Log::error("Error approving cancellation for order ID: {$order->id} (via Action): " . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('admin.orders.show', $order->id)
                             ->with('error', 'Failed to approve cancellation request. ' . $e->getMessage());
        }
    }
}

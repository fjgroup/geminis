<?php
namespace App\Http\Controllers\Client;


use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderRequest; // New
use App\Http\Requests\Client\PlaceOrderRequest; // Added for Form Request
use App\Actions\Client\PlaceOrderAction; // Added for refactoring

use App\Models\Product; // Added for showOrderForm, placeOrder
use App\Models\ProductPricing; // Added for placeOrder
use App\Models\OrderItem; // Added for placeOrder
use App\Models\InvoiceItem; // Added for placeOrder
use App\Models\Order;
use App\Models\OrderActivity; // Added for new methods

use Illuminate\Support\Facades\Auth; // Importar Auth
use Illuminate\Support\Facades\DB; // Added for approveCancellationRequest and placeOrder
use Illuminate\Http\RedirectResponse; // New
use Illuminate\Support\Facades\Log; // For logging errors
use Illuminate\Http\Request; // For existing index method - Request is used in placeOrder, so it's fine.
use Illuminate\Validation\ValidationException; // For specific validation exceptions
use Illuminate\Database\QueryException; // For database query exceptions
use Illuminate\Database\Eloquent\ModelNotFoundException; // For model not found (e.g. findOrFail)
use Illuminate\Support\Facades\Validator; // For Validator::make()
use Exception; // For general \Exception

use Inertia\Response as InertiaResponse; // Added for type hinting
use Inertia\Inertia; // Added for Inertia::render

class ClientOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): InertiaResponse // Add Request type hint
    {
        $this->authorize('viewAny', Order::class); // Assuming OrderPolicy@viewAny allows clients to view their own orders

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

        // Ensure only the authenticated client's orders are fetched
        $orders = $query->where('client_id', Auth::id())->paginate(15)->withQueryString(); // withQueryString to append filters to pagination links

        // Get distinct statuses from the orders table for filter dropdown
        // This can be resource-intensive on large tables. Consider a predefined list or caching.
        // For now, let's use the known list.
        $possibleStatuses = ['pending_payment', 'paid_pending_execution', 'cancellation_requested_by_client', 'active', 'completed', 'fraud', 'cancelled', 'pending_provisioning'];


        return Inertia::render('Client/Orders/Index', [ // NOTE: This might need to be 'Client/Orders/Index'
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
            'items.clientService', // Added this line
            'invoice:id,invoice_number,status,total_amount' // Load associated invoice
        ]);

        return Inertia::render('Client/Orders/Show', [ // NOTE: This might need to be 'Client/Orders/Show'
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


        return Inertia::render('Client/Orders/Edit', [ // NOTE: This might need to be 'Client/Orders/Edit'
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

            return redirect()->route('client.orders.show', $order->id) // NOTE: This might need to be 'client.orders.show'
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
     * Show the form for the client to edit their own pending order.
     *
     * @param  Order  $order
     * @return InertiaResponse|RedirectResponse
     */
    public function editOrderForm(Order $order): InertiaResponse|RedirectResponse
    {
        $this->authorize('update', $order);

        if (Auth::id() !== $order->client_id) {
            Log::warning("User " . Auth::id() . " attempted to access edit form for order {$order->id} owned by client {$order->client_id}.");
            return redirect()->route('client.orders.show', $order->id)
                             ->with('error', 'You are not authorized to edit this order.');
        }

        if ($order->status !== 'pending_payment') {
            return redirect()->route('client.orders.show', $order->id)
                             ->with('error', 'This order cannot be edited at its current stage.');
        }

        // Load the order with its items, each item's product, and the current productPricing with its billingCycle
        $order->load([
            'items.product', // Load the product for each item
            'items.productPricing.billingCycle' // Load the currently selected pricing and its cycle for each item
        ]);

        // Prepare a new collection/array of items to pass to the view
        // This new collection will include the available pricing options directly
        $items_for_view = $order->items->map(function ($item) {
            $item_view_data = $item->toArray(); // Get base item data
            $available_pricings_for_select = [];
            if ($item->product) { // product relation should be loaded
                $product_pricings = ProductPricing::where('product_id', $item->product->id)
                                        ->with('billingCycle')
                                        ->where('is_active', true)
                                        ->get();
                foreach ($product_pricings as $pricing) {
                    if ($pricing->billingCycle) {
                        $available_pricings_for_select[] = [
                            'id' => $pricing->id,
                            'name' => $pricing->billingCycle->name,
                            'price' => $pricing->price,
                            'currency_code' => $pricing->currency_code,
                            'setup_fee' => $pricing->setup_fee
                        ];
                    }
                }
            }
            $item_view_data['available_pricings_for_select_explicit'] = $available_pricings_for_select; // Use a distinct name
            return $item_view_data;
        })->toArray(); // Convert collection of arrays to a simple array


        // Create a new representation of the order for the view
        $order_for_view = $order->toArray();
        $order_for_view['items'] = $items_for_view;

        // // dd($order_for_view); // Optional: User can uncomment this to check the final structure

        return Inertia::render('Client/Orders/EditOrderForm', [
            'order' => $order_for_view, // Pass the modified order structure
        ]);
    }

    /**
     * Update the client's own pending order.
     *
     * @param  Request  $request
     * @param  Order  $order
     * @return RedirectResponse
     */
    public function updateOrder(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        if (Auth::id() !== $order->client_id) {
            Log::warning("User " . Auth::id() . " attempted to update order {$order->id} owned by client {$order->client_id}.");
            return redirect()->route('client.orders.show', $order->id)
                             ->with('error', 'You are not authorized to update this order.');
        }

        if ($order->status !== 'pending_payment') {
            return redirect()->route('client.orders.show', $order->id)
                             ->with('error', 'This order cannot be updated at its current stage.');
        }

        // Basic validation for items structure
        $validatedData = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'exists:order_items,id,order_id,' . $order->id],
            'items.*.product_pricing_id' => ['required', 'exists:product_pricings,id'], // Renamed from billing_cycle_id to product_pricing_id for clarity
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        DB::beginTransaction();
        try {
            $originalOrderDetails = $order->toArray(); // For logging changes
            $originalOrderItemsDetails = $order->items->map(fn($item) => $item->toArray())->all();


            $newOrderTotalAmount = 0;
            $updatedOrderItemsData = [];

            foreach ($validatedData['items'] as $itemData) {
                $orderItem = OrderItem::find($itemData['id']);
                if (!$orderItem) {
                    // Should be caught by 'exists' validation, but good to double check
                    throw new Exception("Order item with ID {$itemData['id']} not found."); // Use statement applied
                }

                $newProductPricing = ProductPricing::with('billingCycle')->find($itemData['product_pricing_id']);
                if (!$newProductPricing || $newProductPricing->product_id !== $orderItem->product_id) {
                    // Ensure the new pricing belongs to the same product of the order item
                    throw new ValidationException( // Use statement applied
                        Validator::make([], []), // Use statement applied for Validator
                        response()->json(['message' => "Invalid billing cycle selected for item {$orderItem->description}."], 422)
                    );
                }

                $orderItem->product_pricing_id = $newProductPricing->id;
                $orderItem->quantity = $itemData['quantity'];
                $orderItem->unit_price = $newProductPricing->price;
                $orderItem->total_price = $newProductPricing->price * $itemData['quantity'];
                $orderItem->description = $orderItem->product->name . ($newProductPricing->billingCycle ? ' (' . $newProductPricing->billingCycle->name . ')' : '');
                $orderItem->save();

                $newOrderTotalAmount += $orderItem->total_price;
                $updatedOrderItemsData[] = $orderItem->toArray();
            }

            $order->total_amount = $newOrderTotalAmount;
            // Potentially update currency_code if it can change, though current logic assumes it's fixed from first product pricing.
            $order->save();

            // Update Associated Invoice
            $invoice = $order->invoice; // Assumes invoice relationship is loaded or can be lazy-loaded
            if ($invoice) {
                $invoice->subtotal = $newOrderTotalAmount; // Assuming no taxes for now
                $invoice->total_amount = $newOrderTotalAmount;

                // Remove old invoice items
                $invoice->items()->delete(); // Physical delete or soft delete depending on InvoiceItem model

                // Create new invoice items from updated order items
                foreach ($order->items()->get() as $updatedOrderItem) { // Fetch fresh items
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'order_item_id' => $updatedOrderItem->id,
                        'description' => $updatedOrderItem->description,
                        'quantity' => $updatedOrderItem->quantity,
                        'unit_price' => $updatedOrderItem->unit_price,
                        'total_price' => $updatedOrderItem->total_price,
                        'taxable' => $updatedOrderItem->product->taxable ?? true,
                    ]);
                }
                $invoice->save();
            } else {
                // This case should ideally not happen if an invoice is always created with an order.
                // If it can, an error should be logged or a new invoice created.
                Log::error("Order {$order->id} is pending payment but has no associated invoice during update.");
                // Potentially, create a new invoice here if that's the desired business logic.
                // For now, we assume invoice exists.
            }

            OrderActivity::create([
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'type' => 'order_edited_by_client',
                'details' => [
                    'reason' => 'Client updated items/quantity for pending payment order.',
                    'previous_total' => $originalOrderDetails['total_amount'],
                    'new_total' => $order->total_amount,
                    'original_items' => $originalOrderItemsDetails, // Could be large, consider summarizing
                    'updated_items' => $updatedOrderItemsData, // Could be large
                ]
            ]);

            DB::commit();

            return redirect()->route('client.orders.show', $order->id)
                             ->with('success', 'Order updated successfully.');

        } catch (ValidationException $e) { // Use statement applied
            DB::rollBack();
            // Laravel handles redirecting back with validation errors automatically.
            // Log::debug("Validation exception during order update by client: " . $e->getMessage(), $e->errors());
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) { // Use statement applied
            DB::rollBack();
            Log::error("Error updating order ID {$order->id} by client: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('client.orders.show', $order->id)
                             ->with('error', 'An unexpected error occurred while updating your order. ' . $e->getMessage());
        }
    }

    /**
     * Cancel an order that is still pending payment.
     *
     * @param  Order  $order
     * @return RedirectResponse
     */
    public function cancelPrePaymentOrder(Order $order): RedirectResponse
    {
        $this->authorize('update', $order); // Or a more specific policy like 'cancelPrePayment'

        // Defensive check, though policy should handle ownership
        if (Auth::id() !== $order->client_id) {
            // This case should ideally be caught by the OrderPolicy's update method
            // or a more specific policy method.
            // If it reaches here, it means the policy might be too permissive or not correctly applied.
            Log::warning("User " . Auth::id() . " attempted to cancel order {$order->id} owned by client {$order->client_id} without proper authorization (pre-payment).");
            return redirect()->route('client.orders.show', $order->id)
                             ->with('error', 'You are not authorized to perform this action.');
        }

        if ($order->status === 'pending_payment') {
            DB::beginTransaction();
            try {
                $previousStatus = $order->status;
                $order->status = 'cancelled';
                $order->save();

                if ($order->invoice_id) {
                    $invoice = $order->invoice; // Assumes invoice relationship is loaded or loads automatically
                    if ($invoice) {
                        $invoice->status = 'cancelled';
                        $invoice->save();
                    }
                }

                OrderActivity::create([
                    'order_id' => $order->id,
                    'user_id' => Auth::id(),
                    'type' => 'order_cancelled_by_client_prepayment',
                    'details' => ['previous_status' => $previousStatus, 'new_status' => 'cancelled']
                ]);

                DB::commit();
                return redirect()->route('client.orders.show', $order->id)
                                 ->with('success', 'Order and associated invoice have been cancelled.');
            } catch (Exception $e) { // Use statement applied
                DB::rollBack();
                Log::error("Error cancelling pre-payment order ID {$order->id}: " . $e->getMessage(), ['exception' => $e]);
                return redirect()->route('client.orders.show', $order->id)
                                 ->with('error', 'An error occurred while cancelling the order. Please try again.');
            }
        } else {
            return redirect()->route('client.orders.show', $order->id)
                             ->with('error', "This order cannot be cancelled directly as it's no longer pending payment.");
        }
    }

    /**
     * Request cancellation for an order that has already been paid or is in process.
     *
     * @param  Order  $order
     * @return RedirectResponse
     */
    public function requestPostPaymentCancellation(Order $order): RedirectResponse
    {
        $this->authorize('requestPostPaymentCancellation', $order);

        // Defensive check for ownership
        if (Auth::id() !== $order->client_id) {
            Log::warning("User " . Auth::id() . " attempted to request cancellation for order {$order->id} owned by client {$order->client_id} without proper authorization (post-payment).");
            return redirect()->route('client.orders.show', $order->id)
                             ->with('error', 'You are not authorized to perform this action.');
        }

        $allowedStatusesForRequest = ['paid_pending_execution', 'active', 'pending_provisioning'];
        if (in_array($order->status, $allowedStatusesForRequest)) {
            DB::beginTransaction();
            try {
                $previousStatus = $order->status;
                $order->status = 'cancellation_requested_by_client';
                $order->save();

                OrderActivity::create([
                    'order_id' => $order->id,
                    'user_id' => Auth::id(),
                    'type' => 'cancellation_requested_by_client',
                    'details' => ['previous_status' => $previousStatus, 'new_status' => 'cancellation_requested_by_client']
                ]);

                DB::commit();
                return redirect()->route('client.orders.show', $order->id)
                                 ->with('success', 'Your request to cancel this order has been submitted for review.');
            } catch (Exception $e) { // Use statement applied
                DB::rollBack();
                Log::error("Error requesting post-payment cancellation for order ID {$order->id}: " . $e->getMessage(), ['exception' => $e]);
                return redirect()->route('client.orders.show', $order->id)
                                 ->with('error', 'An error occurred while submitting your cancellation request. Please try again.');
            }
        } else {
            // Provide a more specific message if already requested or in a final state
            if ($order->status === 'cancellation_requested_by_client') {
                return redirect()->route('client.orders.show', $order->id)
                                 ->with('info', 'A cancellation request for this order has already been submitted.');
            }
            if (in_array($order->status, ['cancelled', 'completed', 'fraud'])) {
                 return redirect()->route('client.orders.show', $order->id)
                                 ->with('error', "This order is already in a final state ({$order->status}) and cannot be cancelled.");
            }
            return redirect()->route('client.orders.show', $order->id)
                             ->with('error', 'This order cannot be cancelled at its current stage.');
        }
    }

    // New methods to be added below as per the task

    /**
     * Show the form for creating a new order for a specific product.
     *
     * @param  Product  $product
     * @return InertiaResponse
     */
    public function showOrderForm(Product $product): InertiaResponse
    {
        $this->authorize('view', $product); // Assuming ProductPolicy@view exists

        // Load necessary product data.
        // configurableOptionGroups.options will load the groups and their respective options.
        $product->load(['pricings.billingCycle', 'configurableOptionGroups.options', 'productType']); // Corrected relationship name to match model

        return Inertia::render('Client/Orders/OrderForm', [
            'product' => $product,
        ]);
    }

    /**
     * Place a new order for a specific product.
     *
     * @param  Request  $request
     * @param  Product  $product
     * @return RedirectResponse
     */
    public function placeOrder(PlaceOrderRequest $request, Product $product, PlaceOrderAction $placeOrderAction): RedirectResponse
    {
        $this->authorize('create', Order::class);

        $validatedData = $request->validated();
        $client = Auth::user(); // Get authenticated client

        try {
            // The PlaceOrderAction handles its own DB transaction and ModelNotFoundException for ProductPricing.
            $order = $placeOrderAction->execute($product, $validatedData, $client);

            return redirect()->route('client.orders.show', $order->id)
                             ->with('success', 'Order placed and invoice generated successfully. Please proceed with payment.');

        } catch (ValidationException $e) {
            // This catch block might be redundant if PlaceOrderRequest handles all validation.
            // However, if the Action class could throw a ValidationException for some internal logic, it's fine to keep.
            // The Action class itself does not throw ValidationException, but it re-throws exceptions it catches.
            // FormRequest handles its own validation errors before this method is called.
            // If PlaceOrderAction itself were to perform further validation and throw ValidationException, this would catch it.
            // For now, as PlaceOrderAction doesn't do that, this specific catch might not be hit from the Action.
            // DB::rollBack(); // Action handles its own rollback.
            throw $e; // Re-throw to let Laravel handle it (usually redirects back with errors).
        } catch (ModelNotFoundException $e) {
            // This is specifically for ProductPricing::findOrFail inside the Action.
            // The Action re-throws this, so we can catch it here for specific user feedback.
            // DB::rollBack(); // Action handles its own rollback.
            Log::error("Product pricing not found during order placement (via Action): " . $e->getMessage(), [
                'product_id' => $product->id,
                'client_id' => $client->id,
                'validated_data' => $validatedData, // Be cautious about logging sensitive data
            ]);
            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Selected pricing option is not valid. Please try again.');
        } catch (Exception $e) {
            // General exception catch from the Action or other issues.
            // DB::rollBack(); // Action handles its own rollback.
            Log::error("Error placing order for product ID {$product->id} (via Action): " . $e->getMessage(), [
                'product_id' => $product->id,
                'client_id' => $client->id,
                'validated_data' => $validatedData, // Be cautious
                'exception' => $e
            ]);
            return redirect()->back()
                             ->withInput()
                             ->with('error', 'An unexpected error occurred while placing your order. Please try again.');
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
                 return redirect()->route('client.orders.show', $order->id) // NOTE: This might need to be 'client.orders.show'
                                 ->with('error', 'Cannot delete order: It has an active or unpaid invoice. Please cancel or refund the invoice first.');
            }

            $order->delete(); // This will soft delete if the trait is used

            return redirect()->route('client.orders.index') // NOTE: This might need to be 'client.orders.index'
                             ->with('success', 'Order successfully deleted (soft delete).');
        } catch (Exception $e) { // Use statement applied
            Log::error("Failed to delete order: " . $e->getMessage(), ['order_id' => $order->id, 'error' => $e]);
            return redirect()->route('client.orders.show', $order->id) // NOTE: This might need to be 'client.orders.show'
                             ->with('error', 'An unexpected error occurred while deleting the order.');
        }
    }

    /**
     * Mark the order as being processed by client.
     *
     * @param  Order  $order
     * @return RedirectResponse
     */
    public function startExecution(Order $order): RedirectResponse
    {
        $this->authorize('update', $order); // Or a more specific policy method like 'manageExecution'

        if ($order->status !== 'paid_pending_execution') {
            return redirect()->route('client.orders.show', $order->id) // NOTE: This might need to be 'client.orders.show'
                             ->with('error', 'Order cannot be started at its current stage. Expected status: Paid, Pending Execution.');
        }

        try {
            $order->status = 'pending_provisioning'; // Or 'provisioning_in_progress', 'client_processing'
                                                 // This 'pending_provisioning' was an original ENUM value.
            $order->save();

            OrderActivity::create([
                'order_id' => $order->id,
                'user_id' => Auth::id(), // client performing the action
                'type' => 'client_started_provisioning', // From ENUM list
                'details' => ['previous_status' => 'paid_pending_execution']
            ]);

            return redirect()->route('client.orders.show', $order->id) // NOTE: This might need to be 'client.orders.show'
                             ->with('success', 'Order status updated to: Processing by client.');
        } catch (Exception $e) { // Use statement applied
            Log::error("Error starting order execution for order ID: {$order->id}", ['error' => $e->getMessage()]);
            return redirect()->route('client.orders.show', $order->id) // NOTE: This might need to be 'client.orders.show'
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
             // Allow 'paid_pending_execution' to skip 'startExecution' if client wants to mark as directly active/completed.
            return redirect()->route('client.orders.show', $order->id) // NOTE: This might need to be 'client.orders.show'
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
                'type' => 'service_activated', // Or 'client_completed_provisioning' from ENUM
                'details' => ['previous_status' => $previousStatus, 'new_status' => 'active']
            ]);

            // Potentially trigger other actions: e.g., create ClientService record, send notification
            // (These are out of scope for this specific sub-task)

            return redirect()->route('client.orders.show', $order->id) // NOTE: This might need to be 'client.orders.show'
                             ->with('success', 'Order execution completed. Service is now active.');
        } catch (Exception $e) { // Use statement applied
            Log::error("Error completing order execution for order ID: {$order->id}", ['error' => $e->getMessage()]);
            return redirect()->route('client.orders.show', $order->id) // NOTE: This might need to be 'client.orders.show'
                             ->with('error', 'Failed to complete order execution.');
        }
    }

    // Method approveCancellationRequest has been removed from ClientOrderController
    // as its functionality (approving cancellation and issuing credit)
    // is correctly and solely handled by AdminOrderController.
}

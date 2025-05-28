<?php

namespace App\Http\Controllers\Client;

use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductPricing;
use App\Models\ConfigurableOption;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Necesario si usamos transacciones o helpers de DB
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\OrderActivity; // Added OrderActivity
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse; // Added RedirectResponse


use App\Http\Controllers\Controller;
// use Illuminate\Http\Request; // Request is part of StoreClientOrderRequest
use App\Models\Product; // Keep if product context is needed
use App\Http\Requests\Client\StoreClientOrderRequest;
use Inertia\Inertia;
// Ensure these are present for cancelPrePaymentOrder
// use Illuminate\Support\Facades\DB; // DB is already imported
// use Illuminate\Support\Facades\Log; // Log is already imported
// use Illuminate\Http\RedirectResponse; // RedirectResponse is already imported
// use Illuminate\Support\Facades\Auth; // Auth is already imported

class OrderController extends Controller
{
    /**
     * Muestra el formulario para crear una orden para un producto específico.
     *
     * @param  \App\Models\Product  $product
     * @return \Inertia\Response
     */
    public function showOrderForm(Product $product)
    {
        $product->load([
            'productPricings.billingCycle',
            'configurableOptionGroups.configurableOptions.optionPricings.billingCycle'
        ]);

        return Inertia::render('Client/Orders/Create', [
            'product' => $product,
        ]);
    }

    /**
     * Procesa la solicitud para crear una nueva orden.
     *
     * @param  StoreClientOrderRequest  $request
     * @return RedirectResponse
     */
    public function placeOrder(StoreClientOrderRequest $request): RedirectResponse
    {
        $this->authorize('create', Order::class);
        $user = Auth::user();
        $validatedData = $request->validated();

        DB::beginTransaction();

        try {
            // 1. Calculate Order Totals and Prepare Item Data (Adapted from original logic)
            $productPricing = ProductPricing::with('product', 'billingCycle') // Eager load product
                ->findOrFail($validatedData['product_pricing_id']);
            
            $product = $productPricing->product; // Get the product from the pricing

            $basePrice = $productPricing->price;
            $totalAmount = $basePrice;
            $currencyCode = $productPricing->currency_code ?? $user->currency_code ?? 'USD';

            $orderItemsData = [];
            // Main product item
            $orderItemsData[] = [
                'product_id' => $product->id,
                'product_pricing_id' => $productPricing->id,
                'item_type' => 'product',
                'description' => $product->name . ' - ' . ($productPricing->billingCycle->name ?? 'One Time'),
                'quantity' => 1,
                'unit_price' => $basePrice,
                'total_price' => $basePrice,
                'billing_cycle_id' => $productPricing->billing_cycle_id,
                'configurable_option_id' => null, // Not a configurable option
                'option_pricing_id' => null, // Not a configurable option
            ];

            // Process configurable options
            $configurableOptionInput = $validatedData['configurable_options'] ?? []; // Renamed from 'configurable_options_ids' for clarity
            if (!empty($configurableOptionInput)) {
                 // Assuming configurable_options is an array of selected option_pricing_ids or structured data
                 // For simplicity, let's assume it's an array of configurable_option_id => option_pricing_id
                 // This part may need adjustment based on the actual structure of 'configurable_options' from the request

                // If 'configurable_options' provides just IDs of ConfigurableOption:
                $selectedOptionIds = array_keys($configurableOptionInput); // If it's an assoc array of option_id => selected_value_id
                                                                        // Or just $configurableOptionInput if it's an array of IDs

                $configurableOptions = ConfigurableOption::whereIn('id', $selectedOptionIds)
                    ->with(['optionPricings' => function ($query) use ($productPricing) {
                        $query->where('billing_cycle_id', $productPricing->billing_cycle_id);
                    }])
                    ->get();
                
                foreach ($configurableOptions as $option) {
                    $optionPricing = $option->optionPricings->first(); // Assuming one pricing per option per cycle
                    if ($optionPricing) {
                        $totalAmount += $optionPricing->price;
                        $orderItemsData[] = [
                            'product_id' => null, // Or link to main product if structure allows
                            'product_pricing_id' => null,
                            'item_type' => 'configurable_option',
                            'description' => $option->name, // Or more detailed like "Option: RAM - 16GB"
                            'quantity' => 1,
                            'unit_price' => $optionPricing->price,
                            'total_price' => $optionPricing->price,
                            'billing_cycle_id' => $productPricing->billing_cycle_id,
                            'configurable_option_id' => $option->id,
                            'option_pricing_id' => $optionPricing->id,
                        ];
                    }
                }
            }
            
            // 2. Create Order
            $order = Order::create([
                'client_id' => $user->id,
                'reseller_id' => $user->reseller_id ?? null, // Assuming user might have a reseller_id
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'invoice_id' => null,
                'order_date' => Carbon::now(),
                'status' => 'pending_payment',
                'total_amount' => $totalAmount,
                'currency_code' => $currencyCode,
                'payment_gateway_slug' => null,
                'ip_address' => $request->ip(),
                'notes' => $validatedData['notes'] ?? null,
                'product_pricing_id' => $productPricing->id, // Storing chosen product pricing
                'billing_cycle_id' => $productPricing->billing_cycle_id, // Storing chosen billing cycle
            ]);

            // 3. Create OrderItems
            foreach ($orderItemsData as $itemData) {
                OrderItem::create(array_merge(['order_id' => $order->id], $itemData));
            }
            $order->load('items');

            // 4. Create Invoice
            $invoice = Invoice::create([
                'client_id' => $user->id,
                'reseller_id' => $user->reseller_id ?? null,
                'invoice_number' => 'INV-' . strtoupper(Str::random(10)),
                'issue_date' => Carbon::now(),
                'due_date' => Carbon::now(), // Or Carbon::now()->addDays(X) based on settings
                'paid_date' => null,
                'status' => 'unpaid',
                'subtotal' => $order->total_amount,
                'total_amount' => $order->total_amount,
                'currency_code' => $order->currency_code,
            ]);

            // 5. Link Order and Invoice
            $order->invoice_id = $invoice->id;
            $order->save();

            // 6. Create InvoiceItems
            foreach ($order->items as $orderItem) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'order_item_id' => $orderItem->id,
                    'description' => $orderItem->description,
                    'quantity' => $orderItem->quantity,
                    'unit_price' => $orderItem->unit_price,
                    'total_price' => $orderItem->total_price,
                    'taxable' => true, // Default, or from product/settings
                ]);
            }

            // 7. Create OrderActivity Log
            OrderActivity::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'type' => 'order_requested_by_client',
                'details' => [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount' => $order->total_amount,
                    'currency_code' => $order->currency_code,
                    'ip_address' => $request->ip(),
                ]
            ]);

            DB::commit();

            return redirect()->route('client.invoices.show', $invoice->id)
                             ->with('success', 'Order placed successfully! Please complete payment.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order placement failed: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString() // Log more details for debugging
            ]);
            return redirect()->back()->withInput()->with('error', 'There was an issue placing your order. Please try again or contact support.');
        }
    }

    /**
     * Muestra un listado de las órdenes del cliente autenticado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Order::class);

        $user = Auth::user();

        $orders = $user->orders()->with(['items', 'productPricing.billingCycle'])
                       ->latest() // Opcional: ordenar por fecha de orden descendente
                       ->paginate(10); // Paginación básica de 10 elementos por página

        return Inertia::render('Client/Orders/Index', [
            'orders' => $orders,
        ]);
    }

    public function cancelPrePaymentOrder(Order $order): RedirectResponse
    {
        // Authorize using the updated OrderPolicy@delete method
        $this->authorize('delete', $order); 

        // Double check status just in case, though policy should handle it
        if ($order->status !== 'pending_payment') {
            return redirect()->route('client.orders.index') // Or client.dashboard or client.orders.show
                             ->with('error', 'This order cannot be cancelled at its current stage.');
        }

        DB::beginTransaction();
        try {
            // 1. Soft Delete the Order
            $order->delete(); // Soft delete

            // 2. Mark associated Invoice as 'cancelled'
            // Ensure invoice relationship is loaded
            $order->loadMissing('invoice');
            if ($order->invoice) {
                $invoice = $order->invoice;
                $invoice->status = 'cancelled';
                // Consider if paid_date or other fields should be nulled if they could have been set
                $invoice->save();
            }

            // 3. No OrderActivity log for this specific client action as per user feedback.

            DB::commit();

            return redirect()->route('client.orders.index') // Or client.dashboard
                             ->with('success', 'Order #' . $order->order_number . ' has been successfully cancelled.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to cancel pre-payment order by client: " . $e->getMessage(), ['order_id' => $order->id, 'user_id' => Auth::id(), 'exception' => $e]);
            return redirect()->route('client.orders.index') // Redirect to index on error too for consistency
                             ->with('error', 'An error occurred while trying to cancel your order. Please try again.');
        }
    }

    // Ensure imports: Order, OrderActivity, RedirectResponse, Auth, DB, Log
    // use App\Models\OrderActivity; (new if not already used)

    public function requestPostPaymentCancellation(Order $order): RedirectResponse
    {
        // Authorize using the new OrderPolicy@requestPostPaymentCancellation method
        $this->authorize('requestPostPaymentCancellation', $order);

        // Double check status (policy should cover this, but good for defense)
        if ($order->status !== 'paid_pending_execution') {
            return redirect()->route('client.orders.show', $order->id) // Or client.orders.index if show doesn't exist
                             ->with('error', 'This order cannot have cancellation requested at its current stage.');
        }

        DB::beginTransaction();
        try {
            // 1. Update Order Status
            $order->status = 'cancellation_requested_by_client'; // New status
            $order->save();

            // 2. Create OrderActivity Log
            OrderActivity::create([
                'order_id' => $order->id,
                'user_id' => Auth::id(), // Client performing the action
                'type' => 'cancellation_requested_by_client_paid', // From ENUM
                'details' => [
                    'previous_status' => 'paid_pending_execution',
                    // Optionally add client's reason if a form field for it is added later
                ]
            ]);

            DB::commit();

            return redirect()->route('client.orders.show', $order->id) // Or client.orders.index
                             ->with('success', 'Your request to cancel order #' . $order->order_number . ' has been submitted. You will be notified once an administrator reviews your request.');

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            // Check for ENUM constraint violation if 'cancellation_requested_by_client' status isn't in DB yet
            if (str_contains($e->getMessage(), "Data truncated for column 'status'")) {
                 Log::error("Failed to update order status for cancellation request: Possible ENUM mismatch for 'cancellation_requested_by_client'", ['order_id' => $order->id, 'error' => $e]);
                 return redirect()->back()
                                 ->with('error', 'Failed to request cancellation: The order status value is not valid. Please contact support.');
            }
            Log::error("Failed to request cancellation for order by client: " . $e->getMessage(), ['order_id' => $order->id, 'user_id' => Auth::id(), 'exception' => $e]);
            return redirect()->route('client.orders.show', $order->id) // Or client.orders.index
                             ->with('error', 'An error occurred while trying to request cancellation. Please try again.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to request cancellation for order by client: " . $e->getMessage(), ['order_id' => $order->id, 'user_id' => Auth::id(), 'exception' => $e]);
            return redirect()->route('client.orders.show', $order->id) // Or client.orders.index
                             ->with('error', 'An error occurred while trying to request cancellation. Please try again.');
        }
    }
}

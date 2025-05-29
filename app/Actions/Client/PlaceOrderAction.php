<?php

namespace App\Actions\Client;

use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\OrderActivity;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // For logging errors within action if necessary
use Illuminate\Support\Str;
use Illuminate\Support\Carbon; // For dates
use Exception; // Import base Exception for re-throwing

class PlaceOrderAction
{
    /**
     * Execute the action to place an order.
     *
     * @param Product $product The product being ordered.
     * @param array $validatedData Validated data from the request.
     * @param User $client The authenticated client placing the order.
     * @return Order The created Order object.
     * @throws Exception If any error occurs during the process.
     */
    public function execute(Product $product, array $validatedData, User $client): Order
    {
        DB::beginTransaction();

        try {
            $productPricing = ProductPricing::with('billingCycle')->findOrFail($validatedData['billing_cycle_id']);

            // Basic total amount calculation.
            $basePrice = $productPricing->price;
            $totalAmount = $basePrice * $validatedData['quantity'];
            // TODO: Add costs from configurable options to $totalAmount if applicable.

            $order = Order::create([
                'client_id' => $client->id,
                'reseller_id' => $client->reseller_id, // Assuming client has a reseller_id
                'order_number' => 'ORD-' . time() . '-' . Str::upper(Str::random(4)),
                'order_date' => Carbon::now(),
                'total_amount' => $totalAmount,
                'currency_code' => $productPricing->currency_code ?? config('app.currency_code', 'USD'),
                'status' => 'pending_payment',
                'notes_to_client' => $validatedData['notes_to_client'] ?? null,
                // invoice_id will be updated after invoice creation
            ]);

            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_pricing_id' => $productPricing->id,
                'quantity' => $validatedData['quantity'],
                'unit_price' => $basePrice,
                'total_price' => $totalAmount,
                'description' => $product->name . ($productPricing->billingCycle ? ' (' . $productPricing->billingCycle->name . ')' : ''),
            ]);

            $invoice = Invoice::create([
                'client_id' => $order->client_id,
                'reseller_id' => $order->reseller_id,
                'invoice_number' => 'INV-' . date('Ymd') . '-' . Str::upper(Str::random(4)),
                'issue_date' => Carbon::now()->format('Y-m-d'),
                'due_date' => Carbon::now()->addDays(config('invoicing.due_days', 7))->format('Y-m-d'),
                'status' => 'unpaid',
                'subtotal' => $order->total_amount, // Assuming no taxes for now
                'total_amount' => $order->total_amount,
                'currency_code' => $order->currency_code,
                'notes_to_client' => "Invoice for Order #{$order->order_number}",
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'order_item_id' => $orderItem->id,
                'description' => $orderItem->description,
                'quantity' => $orderItem->quantity,
                'unit_price' => $orderItem->unit_price,
                'total_price' => $orderItem->total_price,
                'taxable' => $product->taxable ?? true,
            ]);
            
            $order->invoice_id = $invoice->id;
            $order->save();

            OrderActivity::create([
                'order_id' => $order->id,
                'user_id' => $client->id,
                'type' => 'order_placed',
                'details' => [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'billing_cycle' => $productPricing->billingCycle->name ?? 'N/A',
                    'quantity' => $validatedData['quantity'],
                    'total_amount' => $totalAmount,
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                ]
            ]);

            DB::commit();
            return $order;

        } catch (Exception $e) {
            DB::rollBack();
            // Log the error or handle it as needed before re-throwing
            Log::error("Error placing order in PlaceOrderAction: " . $e->getMessage(), [
                'product_id' => $product->id,
                'client_id' => $client->id,
                'validated_data' => $validatedData, // Be cautious about logging sensitive data
                'exception' => $e
            ]);
            throw $e; // Re-throw to be handled by the controller
        }
    }
}

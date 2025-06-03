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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Exception;

class PlaceOrderAction
{
    /**
     * Execute the action to place an order.
     *
     * @param Product $product The product being ordered.
     * @param array $data Validated data from the request.
     * @param User $client The authenticated client placing the order.
     * @return Order|null The first created Order object, or null if none created.
     * @throws Exception If any error occurs during the process.
     */
    public function execute(Product $product, array $data, User $client): ?Order
    {
        $product->loadMissing('productType'); // Ensure productType is loaded

        $billingCycleId = $data['billing_cycle_id'];
        $quantity = (int)$data['quantity'];
        $domainNames = $data['domainNames'] ?? []; // Array of domain names
        $notesToClient = $data['notes_to_client'] ?? null;

        $ordersCreated = [];

        DB::beginTransaction();

        try {
            // Hosting product (requires domain): create one order per domain/quantity
            if ($product->productType && $product->productType->requires_domain && $quantity > 0 && !empty($domainNames) && count($domainNames) === $quantity) {
                for ($i = 0; $i < $quantity; $i++) {
                    $domainName = $domainNames[$i] ?? null; // Should exist due to validation
                    $order = $this->createOrderAndAssociatedRecords(
                        $client,
                        $product,
                        $billingCycleId,
                        1, // Quantity for this specific order item will be 1
                        $domainName,
                        $notesToClient // Notes can be the same for all split orders or customized if needed
                    );
                    $ordersCreated[] = $order;
                }
            } else {
                // Non-hosting product or hosting product with quantity 1 (or domains not provided correctly, though validation should catch this)
                // For non-hosting, domainName might be irrelevant or taken from first entry if provided for some reason
                $domainName = ($product->productType && $product->productType->requires_domain && !empty($domainNames)) ? ($domainNames[0] ?? null) : null;
                $order = $this->createOrderAndAssociatedRecords(
                    $client,
                    $product,
                    $billingCycleId,
                    $quantity, // Original quantity for non-split items
                    $domainName,
                    $notesToClient
                );
                $ordersCreated[] = $order;
            }

            DB::commit();

            return $ordersCreated[0] ?? null; // Return the first order created, or null

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error placing order in PlaceOrderAction: " . $e->getMessage(), [
                'product_id' => $product->id,
                'client_id' => $client->id,
                'data' => $data,
                'exception' => $e
            ]);
            throw $e;
        }
    }

    /**
     * Create a single order and its associated records (OrderItem, Invoice, InvoiceItem, OrderActivity).
     *
     * @return Order The created Order object.
     */
    private function createOrderAndAssociatedRecords(
        User $client,
        Product $product,
        int $productPricingId,
        int $itemQuantity, // This will be 1 for split hosting orders, original quantity otherwise
        ?string $domainName,
        ?string $notesToClient
    ): Order {
        $productPricing = ProductPricing::with('billingCycle')->findOrFail($productPricingId);

        // Calculate total amount for this specific order/service instance
        $itemBasePrice = $productPricing->price;
        $itemSetupFee = $productPricing->setup_fee ?? 0;

        // Total amount for this specific order (itemQuantity is 1 for split hosting)
        $currentOrderTotalAmount = ($itemBasePrice * $itemQuantity) + ($itemSetupFee * $itemQuantity);

        // Placeholder for Order Number generation (ideally move to Order model)
        $orderNumber = 'ORD-' . time() . '-' . Str::upper(Str::random(4)) . ($domainName ? '-' . Str::slug(substr($domainName,0,10)) : '');


        $order = Order::create([
            'client_id' => $client->id,
            'reseller_id' => $client->reseller_id,
            'order_number' => $orderNumber, // TODO: Use Order::generateOrderNumber()
            'order_date' => Carbon::now(),
            'total_amount' => $currentOrderTotalAmount,
            'currency_code' => $productPricing->currency_code ?? config('app.currency_code', 'USD'),
            'status' => 'pending_payment',
            'notes' => $notesToClient, // Changed from notes_to_client to notes to match Order model
            // invoice_id will be updated after invoice creation
        ]);

        $orderItemDescription = $product->name . ($productPricing->billingCycle ? ' (' . $productPricing->billingCycle->name . ')' : '');
        if ($domainName) {
            $orderItemDescription .= ' - ' . $domainName;
        }

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_pricing_id' => $productPricing->id,
            'quantity' => $itemQuantity,
            'unit_price' => $itemBasePrice,
            'setup_fee' => $itemSetupFee,
            'total_price' => $currentOrderTotalAmount, // total for this item (price + setup) * itemQuantity
            'description' => $orderItemDescription,
            'domain_name' => $domainName, // Save domain name here
            'item_type' => Str::limit($product->productType->slug ?? 'general', 50), // Use slug from ProductType, default to 'general' if null, truncate to 50 chars
        ]);

        // Placeholder for Invoice Number generation (ideally move to Invoice model)
        $invoiceNumber = 'INV-' . date('Ymd') . '-' . Str::upper(Str::random(4)) . ($domainName ? '-' . Str::slug(substr($domainName,0,10)) : '');

        $invoice = Invoice::create([
            'client_id' => $order->client_id,
            'reseller_id' => $order->reseller_id,
            'order_id' => $order->id, // Link invoice to this specific order
            'invoice_number' => $invoiceNumber, // TODO: Use Invoice::generateInvoiceNumber()
            'issue_date' => Carbon::now()->format('Y-m-d'),
            'due_date' => Carbon::now()->addDays(config('invoicing.due_days', 7))->format('Y-m-d'),
            'status' => 'unpaid',
            'subtotal' => $currentOrderTotalAmount, // Assuming no taxes for now
            'total_amount' => $currentOrderTotalAmount,
            'currency_code' => $order->currency_code,
            'notes_to_client' => "Factura para Orden #{$order->order_number}",
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'order_item_id' => $orderItem->id,
            'description' => $orderItem->description,
            'quantity' => $orderItem->quantity,
            'unit_price' => $orderItem->unit_price, // This should be (unit_price + setup_fee) if setup_fee is per item on invoice
            'total_price' => $orderItem->total_price, // This is (unit_price + setup_fee) * quantity
            'taxable' => $product->taxable ?? true, // Default to true if not specified
        ]);

        $order->invoice_id = $invoice->id;
        $order->save();

        OrderActivity::create([
            'order_id' => $order->id,
            'user_id' => $client->id, // Client initiated
            'type' => 'order_created_pending_payment', // More accurate type
            'details' => json_encode([ // encode to json string
                'product_id' => $product->id,
                'product_name' => $product->name,
                'domain' => $domainName,
                'billing_cycle' => $productPricing->billingCycle->name ?? 'N/A',
                'quantity' => $itemQuantity,
                'total_amount' => $currentOrderTotalAmount,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
            ])
        ]);

        return $order;
    }
}

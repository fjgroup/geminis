<?php

namespace App\Actions\Client;

use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Exception;

class PlaceOrderAction
{
    /**
     * Execute the action to place an order, creating an Invoice directly.
     *
     * @param Product $product The product being ordered.
     * @param array $data Validated data from the request.
     * @param User $client The authenticated client placing the order.
     * @return Invoice|null The created Invoice object, or null if an error occurred.
     * @throws Exception If any error occurs during the process.
     */
    public function execute(Product $product, array $data, User $client): ?Invoice
    {
        $product->loadMissing('productType', 'productType.productCategory'); // Ensure necessary relations are loaded

        $billingCycleId = $data['billing_cycle_id'];
        $quantity = (int)$data['quantity']; // Overall quantity for the product
        $domainNames = $data['domainNames'] ?? []; // Array of domain names, if applicable
        $notesToClient = $data['notes_to_client'] ?? null;
        $ipAddress = $data['ip_address'] ?? request()->ip();
        $paymentGatewaySlug = $data['payment_gateway_slug'] ?? null;

        $invoiceItemsData = [];
        $firstProductPricing = null;

        DB::beginTransaction();

        try {
            // Case 1: Product requires a domain, and multiple domains are provided (quantity matches domain count)
            if ($product->productType && $product->productType->requires_domain && $quantity > 0 && !empty($domainNames) && count($domainNames) === $quantity) {
                $productPricing = ProductPricing::with('billingCycle')->findOrFail($billingCycleId); // Get pricing once if same for all
                if (!$firstProductPricing) $firstProductPricing = $productPricing;

                for ($i = 0; $i < $quantity; $i++) {
                    $domainName = $domainNames[$i];
                    $invoiceItemsData[] = [
                        'product_id' => $product->id,
                        'product_pricing_id' => $productPricing->id,
                        'quantity' => 1, // Each domain is a separate item with quantity 1
                        'domain_name' => $domainName,
                        'item_type' => $product->productType->slug ?? 'domain_service',
                        'product_object' => $product, // Pass for description, taxability
                        'product_pricing_object' => $productPricing, // Pass for prices, cycle name
                        'registration_period_years' => $productPricing->billingCycle->period_in_years ?? null,
                    ];
                }
            } else {
                // Case 2: Non-domain product, or domain product with quantity 1, or domain product where domains are not itemized
                $productPricing = ProductPricing::with('billingCycle')->findOrFail($billingCycleId);
                if (!$firstProductPricing) $firstProductPricing = $productPricing;

                $domainName = null;
                if ($product->productType && $product->productType->requires_domain && !empty($domainNames)) {
                    $domainName = $domainNames[0] ?? null; // Use first domain if provided, even if quantity > 1 but not itemized
                }

                $invoiceItemsData[] = [
                    'product_id' => $product->id,
                    'product_pricing_id' => $productPricing->id,
                    'quantity' => $quantity, // Use the overall quantity
                    'domain_name' => $domainName,
                    'item_type' => $product->productType->slug ?? ($product->productType?->productCategory?->slug ?? 'general_service'),
                    'product_object' => $product,
                    'product_pricing_object' => $productPricing,
                    'registration_period_years' => $productPricing->billingCycle->period_in_years ?? null,
                ];
            }

            if (empty($invoiceItemsData)) {
                throw new Exception("No items to invoice were prepared.");
            }

            $currencyCode = $firstProductPricing->currency_code ?? config('app.currency_code', 'USD');

            $invoice = $this->createInvoiceWithItems(
                $client,
                $invoiceItemsData,
                $currencyCode,
                $notesToClient,
                $ipAddress,
                $paymentGatewaySlug
            );

            DB::commit();
            return $invoice;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error placing order (creating invoice) in PlaceOrderAction: " . $e->getMessage(), [
                'product_id' => $product->id,
                'client_id' => $client->id,
                'data' => $data,
                'exception_trace' => $e->getTraceAsString()
            ]);
            throw $e; // Re-throw to be handled by controller or higher-level handler
        }
    }

    /**
     * Create a single Invoice and its associated InvoiceItems.
     *
     * @param User $client The client for whom the invoice is created.
     * @param array $itemsDataArray Array of data for each invoice item.
     * @param string $currencyCode Currency code for the invoice.
     * @param string|null $notesToClient Notes to display to the client.
     * @param string|null $ipAddress IP address of the client.
     * @param string|null $paymentGatewaySlug Selected payment gateway.
     * @return Invoice The created Invoice object.
     */
    private function createInvoiceWithItems(
        User $client,
        array $itemsDataArray,
        string $currencyCode,
        ?string $notesToClient,
        ?string $ipAddress,
        ?string $paymentGatewaySlug
    ): Invoice {
        // Generate Invoice Number
        $invoiceNumber = 'INV-' . date('Ymd') . '-' . Str::upper(Str::random(6)); // Consider a more robust unique generator

        $invoice = new Invoice([
            'client_id' => $client->id,
            'reseller_id' => $client->reseller_id, // Assuming client model has reseller_id
            'invoice_number' => $invoiceNumber, // TODO: Implement Invoice::generateInvoiceNumber() if needed
            'requested_date' => Carbon::now(),
            'issue_date' => Carbon::now()->toDateString(),
            'due_date' => Carbon::now()->addDays(config('invoicing.due_days', 7))->toDateString(),
            'status' => 'unpaid', // Or 'draft' if a review step is needed
            'currency_code' => $currencyCode,
            'ip_address' => $ipAddress,
            'payment_gateway_slug' => $paymentGatewaySlug,
            'notes_to_client' => $notesToClient,
            'subtotal' => 0, // Will be calculated
            'total_amount' => 0, // Will be calculated
            // tax fields can be added later if needed
        ]);
        // Save once to get ID for items, or pass around instance and save at end. Let's save at end.
        // $invoice->save(); // Not saving here, will save after items and totals calculation

        $currentSubtotal = 0;

        foreach ($itemsDataArray as $itemData) {
            $product = $itemData['product_object'];
            $productPricing = $itemData['product_pricing_object'];
            $itemQuantity = $itemData['quantity'];

            $unitPrice = $productPricing->price;
            $setupFee = $productPricing->setup_fee ?? 0;

            // Total price for this line item: (unit_price * quantity) + setup_fee
            // Assuming setup_fee is a one-time charge for the line item, not per unit within the quantity.
            $itemTotalPrice = ($unitPrice * $itemQuantity) + $setupFee;

            $description = $product->name . ($productPricing->billingCycle ? ' (' . $productPricing->billingCycle->name . ')' : '');
            if (!empty($itemData['domain_name'])) {
                $description .= ' - ' . $itemData['domain_name'];
            }

            // Ensure item_type is a string and has a reasonable default
            $itemType = 'general'; // Default value
            if (isset($product->productType) && $product->productType !== null && is_string($product->productType->slug) && !empty($product->productType->slug)) {
                $itemType = $product->productType->slug;
            } elseif (isset($product->productType, $product->productType->productCategory) && $product->productType->productCategory !== null && is_string($product->productType->productCategory->slug) && !empty($product->productType->productCategory->slug)) {
                 $itemType = $product->productType->productCategory->slug;
            }


            $invoiceItem = new InvoiceItem([
                // invoice_id will be set when saving via relationship or after invoice is saved
                'product_id' => $itemData['product_id'],
                'product_pricing_id' => $itemData['product_pricing_id'],
                'client_service_id' => null, // Not linking to existing client service at order time
                'description' => $description,
                'quantity' => $itemQuantity,
                'unit_price' => $unitPrice,
                'setup_fee' => $setupFee,
                'total_price' => $itemTotalPrice,
                'taxable' => $product->taxable ?? true, // Default to true
                'domain_name' => $itemData['domain_name'] ?? null,
                'registration_period_years' => $itemData['registration_period_years'] ?? null,
                'item_type' => Str::limit($itemType, 50),
            ]);
            // Collect items to save with the invoice later
            $invoiceItemsForSaving[] = $invoiceItem;
            $currentSubtotal += $itemTotalPrice;
        }

        $invoice->subtotal = $currentSubtotal;
        // Assuming total_amount is same as subtotal if no taxes are calculated yet.
        // Add tax calculation logic here if needed and adjust total_amount.
        $invoice->total_amount = $currentSubtotal;

        // Save the invoice first to get an ID
        $invoice->save();

        // Now save the items, associating them with the invoice
        if (!empty($invoiceItemsForSaving)) {
            $invoice->items()->saveMany($invoiceItemsForSaving);
        }

        // No OrderActivity to create in this refactored version.

        return $invoice;
    }
}

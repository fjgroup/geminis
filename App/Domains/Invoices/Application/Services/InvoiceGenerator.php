<?php

namespace App\Domains\Invoices\Application\Services;

use App\Domains\Invoices\Infrastructure\Persistence\Models\Invoice;
use App\Domains\Invoices\Infrastructure\Persistence\Models\InvoiceItem;
use App\Domains\Users\Infrastructure\Persistence\Models\User;
use App\Domains\ClientServices\Infrastructure\Persistence\Models\ClientService;
use App\Domains\Products\Infrastructure\Persistence\Models\Product;
use App\Domains\Products\Infrastructure\Persistence\Models\ProductPricing;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Servicio especializado para generación de facturas
 * 
 * Aplica Single Responsibility Principle - solo se encarga de generar facturas
 * Ubicado en Application layer según arquitectura hexagonal
 */
class InvoiceGenerator
{
    /**
     * Generar factura desde carrito de compras
     */
    public function generateFromCart(User $client, array $cart, array $additionalData = []): array
    {
        try {
            DB::beginTransaction();

            $notesToClient = $additionalData['notes_to_client'] ?? null;
            $ipAddress = $additionalData['ip_address'] ?? request()->ip();
            $paymentGatewaySlug = $additionalData['payment_gateway_slug'] ?? null;

            $invoiceCurrencyCode = $this->determineCurrencyCode($cart);

            // Crear factura base
            $invoice = Invoice::create([
                'client_id' => $client->id,
                'reseller_id' => $client->reseller_id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'issue_date' => Carbon::now()->toDateString(),
                'due_date' => Carbon::now()->addDays(config('invoicing.due_days', 7))->toDateString(),
                'status' => 'unpaid',
                'currency_code' => $invoiceCurrencyCode,
                'subtotal' => 0,
                'tax1_rate' => $client->tax_rate_1 ?? 0,
                'tax1_description' => $client->tax_description_1 ?? 'Tax 1',
                'tax1_amount' => 0,
                'tax2_rate' => $client->tax_rate_2 ?? 0,
                'tax2_description' => $client->tax_description_2 ?? 'Tax 2',
                'tax2_amount' => 0,
                'total_amount' => 0,
                'notes_to_client' => $notesToClient,
                'ip_address' => $ipAddress,
                'payment_gateway_slug' => $paymentGatewaySlug,
            ]);

            // Crear items de factura desde el carrito
            $subtotal = 0;
            foreach ($cart['accounts'] as $account) {
                if (!empty($account['primary_service'])) {
                    $subtotal += $this->createInvoiceItemFromCartItem($invoice, $account['primary_service']);
                }

                foreach ($account['additional_services'] as $additionalService) {
                    $subtotal += $this->createInvoiceItemFromCartItem($invoice, $additionalService);
                }
            }

            // Calcular impuestos y total
            $this->calculateInvoiceTotals($invoice, $subtotal);

            DB::commit();

            Log::info('Factura generada desde carrito exitosamente', [
                'invoice_id' => $invoice->id,
                'client_id' => $client->id,
                'total_amount' => $invoice->total_amount
            ]);

            return [
                'success' => true,
                'message' => 'Factura generada exitosamente',
                'invoice' => $invoice->load(['items', 'client'])
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error generando factura desde carrito', [
                'client_id' => $client->id,
                'error' => $e->getMessage(),
                'cart' => $cart
            ]);

            return [
                'success' => false,
                'message' => 'Error al generar la factura: ' . $e->getMessage(),
                'invoice' => null
            ];
        }
    }

    /**
     * Generar factura de renovación para un servicio
     */
    public function generateRenewalInvoice(ClientService $service): array
    {
        try {
            DB::beginTransaction();

            $client = $service->client;
            $product = $service->product;
            $productPricing = $service->productPricing;

            // Crear factura de renovación
            $invoice = Invoice::create([
                'client_id' => $service->client_id,
                'reseller_id' => $service->reseller_id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'issue_date' => Carbon::now()->toDateString(),
                'due_date' => $service->next_due_date->toDateString(),
                'status' => 'unpaid',
                'currency_code' => $productPricing->currency_code ?? 'USD',
                'subtotal' => 0,
                'tax1_rate' => $client->tax_rate_1 ?? 0,
                'tax1_description' => $client->tax_description_1 ?? 'Tax 1',
                'tax1_amount' => 0,
                'tax2_rate' => $client->tax_rate_2 ?? 0,
                'tax2_description' => $client->tax_description_2 ?? 'Tax 2',
                'tax2_amount' => 0,
                'total_amount' => 0,
                'notes_to_client' => "Renovación del servicio: {$service->domain_name}",
            ]);

            // Crear item principal de renovación
            $cycleStart = $service->next_due_date;
            $cycleEnd = $this->calculateCycleEnd($cycleStart, $service->billingCycle);

            $description = "Renovación de {$product->name}";
            if ($service->domain_name) {
                $description .= " ({$service->domain_name})";
            }
            $description .= " ({$cycleStart->format('d/m/Y')} - {$cycleEnd->format('d/m/Y')})";

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'client_service_id' => $service->id,
                'product_id' => $service->product_id,
                'product_pricing_id' => $service->product_pricing_id,
                'description' => $description,
                'quantity' => 1,
                'unit_price' => $service->billing_amount,
                'total_price' => $service->billing_amount,
            ]);

            // Calcular totales
            $this->calculateInvoiceTotals($invoice, $service->billing_amount);

            DB::commit();

            Log::info('Factura de renovación generada exitosamente', [
                'invoice_id' => $invoice->id,
                'service_id' => $service->id,
                'client_id' => $service->client_id
            ]);

            return [
                'success' => true,
                'message' => 'Factura de renovación generada exitosamente',
                'invoice' => $invoice->load(['items', 'client'])
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error generando factura de renovación', [
                'service_id' => $service->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error al generar la factura de renovación: ' . $e->getMessage(),
                'invoice' => null
            ];
        }
    }

    /**
     * Generar factura manual
     */
    public function generateManualInvoice(array $invoiceData): array
    {
        try {
            DB::beginTransaction();

            // Crear factura manual
            $invoice = Invoice::create([
                'client_id' => $invoiceData['client_id'],
                'reseller_id' => $invoiceData['reseller_id'] ?? null,
                'invoice_number' => $this->generateInvoiceNumber(),
                'issue_date' => $invoiceData['issue_date'] ?? Carbon::now()->toDateString(),
                'due_date' => $invoiceData['due_date'] ?? Carbon::now()->addDays(7)->toDateString(),
                'status' => 'unpaid',
                'currency_code' => $invoiceData['currency_code'] ?? 'USD',
                'subtotal' => 0,
                'tax1_rate' => $invoiceData['tax1_rate'] ?? 0,
                'tax1_description' => $invoiceData['tax1_description'] ?? 'Tax 1',
                'tax1_amount' => 0,
                'tax2_rate' => $invoiceData['tax2_rate'] ?? 0,
                'tax2_description' => $invoiceData['tax2_description'] ?? 'Tax 2',
                'tax2_amount' => 0,
                'total_amount' => 0,
                'notes_to_client' => $invoiceData['notes_to_client'] ?? null,
            ]);

            // Crear items de factura
            $subtotal = 0;
            foreach ($invoiceData['items'] as $itemData) {
                $totalPrice = $itemData['quantity'] * $itemData['unit_price'];
                
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $totalPrice,
                    'product_id' => $itemData['product_id'] ?? null,
                    'client_service_id' => $itemData['client_service_id'] ?? null,
                ]);

                $subtotal += $totalPrice;
            }

            // Calcular totales
            $this->calculateInvoiceTotals($invoice, $subtotal);

            DB::commit();

            Log::info('Factura manual generada exitosamente', [
                'invoice_id' => $invoice->id,
                'client_id' => $invoiceData['client_id'],
                'created_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => 'Factura manual generada exitosamente',
                'invoice' => $invoice->load(['items', 'client'])
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error generando factura manual', [
                'invoice_data' => $invoiceData,
                'error' => $e->getMessage(),
                'created_by' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Error al generar la factura manual: ' . $e->getMessage(),
                'invoice' => null
            ];
        }
    }

    /**
     * Crear item de factura desde item del carrito
     */
    private function createInvoiceItemFromCartItem(Invoice $invoice, array $cartItem): float
    {
        $product = Product::find($cartItem['product_id']);
        $quantity = $cartItem['quantity'] ?? 1;

        // Obtener precio del producto
        $unitPrice = $this->getProductPrice($product, $cartItem);
        $totalPrice = $quantity * $unitPrice;

        // Crear descripción
        $description = $product->name;
        if (isset($cartItem['configurable_options']) && !empty($cartItem['configurable_options'])) {
            $description .= " con opciones configurables";
        }

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'description' => $description,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
        ]);

        return $totalPrice;
    }

    /**
     * Obtener precio del producto
     */
    private function getProductPrice(Product $product, array $cartItem): float
    {
        // Por ahora retornamos un precio base
        // En el futuro esto debería usar ProductPricingService
        $pricing = $product->pricings()->first();
        return $pricing ? $pricing->price : 0;
    }

    /**
     * Calcular totales de la factura
     */
    private function calculateInvoiceTotals(Invoice $invoice, float $subtotal): void
    {
        $tax1Amount = ($subtotal * $invoice->tax1_rate) / 100;
        $tax2Amount = ($subtotal * $invoice->tax2_rate) / 100;
        $totalAmount = $subtotal + $tax1Amount + $tax2Amount;

        $invoice->update([
            'subtotal' => $subtotal,
            'tax1_amount' => $tax1Amount,
            'tax2_amount' => $tax2Amount,
            'total_amount' => $totalAmount,
        ]);
    }

    /**
     * Generar número de factura único
     */
    private function generateInvoiceNumber(): string
    {
        $prefix = config('invoicing.number_prefix', 'INV');
        $year = Carbon::now()->year;
        
        // Obtener el último número de factura del año
        $lastInvoice = Invoice::where('invoice_number', 'like', "{$prefix}-{$year}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -6);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%s-%d-%06d', $prefix, $year, $nextNumber);
    }

    /**
     * Determinar código de moneda desde el carrito
     */
    private function determineCurrencyCode(array $cart): string
    {
        // Por ahora retornamos USD por defecto
        // En el futuro esto debería analizar los productos del carrito
        return 'USD';
    }

    /**
     * Calcular fecha de fin de ciclo
     */
    private function calculateCycleEnd(Carbon $cycleStart, $billingCycle): Carbon
    {
        if (!$billingCycle) {
            return $cycleStart->copy()->addMonth();
        }

        switch ($billingCycle->name) {
            case 'Monthly':
                return $cycleStart->copy()->addMonth();
            case 'Quarterly':
                return $cycleStart->copy()->addMonths(3);
            case 'Semi-Annually':
                return $cycleStart->copy()->addMonths(6);
            case 'Annually':
                return $cycleStart->copy()->addYear();
            case 'Biennially':
                return $cycleStart->copy()->addYears(2);
            default:
                return $cycleStart->copy()->addMonth();
        }
    }
}

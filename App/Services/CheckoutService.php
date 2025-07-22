<?php
namespace App\Services;

use App\Models\ClientService;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\User;
use App\Services\PricingCalculatorService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Class CheckoutService
 *
 * Servicio para manejar el proceso de checkout público
 * Centraliza la lógica de negocio del checkout
 */
class CheckoutService
{
    public function __construct(
        private PricingCalculatorService $pricingCalculator,
        private UserService $userService,
        private InvoiceService $invoiceService
    ) {}

    /**
     * Procesar verificación de dominio con configuración de producto
     */
    public function processDomainVerification(array $validatedData, array $purchaseContext): array
    {
        try {
            $product = Product::where('slug', $purchaseContext['product_slug'])->first();

            if (! $product) {
                return [
                    'success' => false,
                    'message' => 'Producto no encontrado',
                ];
            }

            // Calcular precio usando el servicio especializado
            $priceCalculation = $this->pricingCalculator->calculateProductPrice(
                $product->id,
                $validatedData['billing_cycle_id'],
                $validatedData['configurable_options'] ?? []
            );

            Log::info('CheckoutService - Precio calculado', [
                'product_id'       => $product->id,
                'billing_cycle_id' => $validatedData['billing_cycle_id'],
                'calculation'      => $priceCalculation,
            ]);

            // Preparar contexto actualizado
            $updatedContext = array_merge($purchaseContext, [
                'domain'               => $validatedData['domain'],
                'domain_action'        => $validatedData['action'],
                'domain_price'         => $validatedData['action'] === 'register' ? 15.00 : 0,
                'billing_cycle_id'     => $validatedData['billing_cycle_id'],
                'configurable_options' => $validatedData['configurable_options'] ?? [],
                'price_calculation'    => $priceCalculation,
            ]);

            return [
                'success'         => true,
                'updated_context' => $updatedContext,
            ];

        } catch (\Exception $e) {
            Log::error('CheckoutService - Error en processDomainVerification', [
                'error' => $e->getMessage(),
                'data'  => $validatedData,
            ]);

            return [
                'success' => false,
                'message' => 'Error al procesar verificación de dominio',
            ];
        }
    }

    /**
     * Crear usuario para el checkout público
     */
    public function createCheckoutUser(array $validatedData): array
    {
        try {
            $userData = [
                'name'              => $validatedData['name'],
                'email'             => $validatedData['email'],
                'password'          => Hash::make($validatedData['password']),
                'role'              => 'client',
                'company_name'      => $validatedData['company_name'],
                'phone'             => $validatedData['phone'],
                'country'           => $validatedData['country'],
                'reseller_id'       => null,
                'status'            => 'active',
                'language_code'     => 'es',
                'currency_code'     => 'USD',
                'email_verified_at' => null, // Requiere verificación
            ];

            $user = $this->userService->createUser($userData);

            if (! $user) {
                return [
                    'success' => false,
                    'message' => 'Error al crear usuario',
                ];
            }

            Log::info('CheckoutService - Usuario creado', [
                'user_id' => $user->id,
                'email'   => $user->email,
            ]);

            return [
                'success' => true,
                'user'    => $user,
            ];

        } catch (\Exception $e) {
            Log::error('CheckoutService - Error creando usuario', [
                'error' => $e->getMessage(),
                'email' => $validatedData['email'] ?? 'N/A',
            ]);

            return [
                'success' => false,
                'message' => 'Error al crear la cuenta',
            ];
        }
    }

    /**
     * Procesar pago y crear factura con servicios
     */
    public function processPayment(User $user, array $purchaseContext, string $paymentMethod): array
    {
        try {
            // Verificar contexto de compra
            if (! isset($purchaseContext['price_calculation'])) {
                return [
                    'success' => false,
                    'message' => 'Información de precio perdida',
                ];
            }

            $product = Product::where('slug', $purchaseContext['product_slug'])->first();
            if (! $product) {
                return [
                    'success' => false,
                    'message' => 'Producto no encontrado',
                ];
            }

            // Crear factura
            $invoiceResult = $this->createInvoiceWithItems($user, $product, $purchaseContext);
            if (! $invoiceResult['success']) {
                return $invoiceResult;
            }

            $invoice = $invoiceResult['invoice'];

            // Crear servicios asociados
            $this->createAssociatedServices($user, $product, $purchaseContext, $invoice);

            Log::info('CheckoutService - Pago procesado exitosamente', [
                'user_id'        => $user->id,
                'invoice_id'     => $invoice->id,
                'payment_method' => $paymentMethod,
            ]);

            return [
                'success'        => true,
                'invoice'        => $invoice,
                'payment_method' => $paymentMethod,
            ];

        } catch (\Exception $e) {
            Log::error('CheckoutService - Error procesando pago', [
                'error'   => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return [
                'success' => false,
                'message' => 'Error procesando el pago',
            ];
        }
    }

    /**
     * Crear factura con items desde contexto de compra
     */
    public function createInvoiceFromPurchaseContext(User $user, array $purchaseContext): Invoice
    {
        $product = Product::where('slug', $purchaseContext['product_slug'])->first();

        if (! $product) {
            throw new \Exception('Producto no encontrado: ' . $purchaseContext['product_slug']);
        }

        if (! isset($purchaseContext['price_calculation'])) {
            throw new \Exception('Información de precio no encontrada');
        }

        $priceCalculation = $purchaseContext['price_calculation'];
        $subtotal         = $priceCalculation['total'];
        $domainPrice      = $purchaseContext['domain_price'] ?? 0;
        $total            = $subtotal + $domainPrice;

        // Crear factura usando el servicio especializado
        $invoiceData = [
            'client_id'     => $user->id,
            'subtotal'      => $subtotal,
            'tax_amount'    => 0,
            'total_amount'  => $total,
            'status'        => 'unpaid',
            'currency_code' => 'USD',
            'notes'         => $this->generateInvoiceNotes($purchaseContext),
        ];

        $invoice = $this->invoiceService->createInvoice($invoiceData);

        // Crear items de la factura
        $this->createInvoiceItems($invoice, $product, $purchaseContext, $subtotal, $domainPrice);

        // Crear servicios asociados
        $this->createAssociatedServices($user, $product, $purchaseContext, $invoice);

        Log::info('CheckoutService - Factura creada desde purchase_context', [
            'user_id'    => $user->id,
            'invoice_id' => $invoice->id,
            'total'      => $total,
        ]);

        return $invoice;
    }

    /**
     * Crear factura con items
     */
    private function createInvoiceWithItems(User $user, Product $product, array $purchaseContext): array
    {
        try {
            $priceCalculation = $purchaseContext['price_calculation'];
            $subtotal         = $priceCalculation['total'];
            $domainPrice      = $purchaseContext['domain_price'] ?? 0;
            $total            = $subtotal + $domainPrice;

            $invoiceData = [
                'client_id'     => $user->id,
                'subtotal'      => $subtotal,
                'tax_amount'    => 0,
                'total_amount'  => $total,
                'status'        => 'unpaid',
                'currency_code' => 'USD',
                'notes'         => $this->generateInvoiceNotes($purchaseContext),
            ];

            $invoice = $this->invoiceService->createInvoice($invoiceData);

            // Crear items
            $this->createInvoiceItems($invoice, $product, $purchaseContext, $subtotal, $domainPrice);

            return [
                'success' => true,
                'invoice' => $invoice,
            ];

        } catch (\Exception $e) {
            Log::error('CheckoutService - Error creando factura', [
                'error'   => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return [
                'success' => false,
                'message' => 'Error creando factura',
            ];
        }
    }

    /**
     * Crear items de factura
     */
    private function createInvoiceItems(Invoice $invoice, Product $product, array $purchaseContext, float $subtotal, float $domainPrice): void
    {
        // Item principal del producto
        InvoiceItem::create([
            'invoice_id'  => $invoice->id,
            'description' => $product->name . ' - Plan ' . ucfirst($purchaseContext['plan'] ?? 'professional'),
            'quantity'    => 1,
            'unit_price'  => $subtotal,
            'total_price' => $subtotal,
        ]);

        // Item de dominio si aplica
        if ($domainPrice > 0) {
            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'description' => 'Registro de dominio: ' . $purchaseContext['domain'],
                'quantity'    => 1,
                'unit_price'  => $domainPrice,
                'total_price' => $domainPrice,
            ]);
        }
    }

    /**
     * Crear servicios asociados a la compra
     */
    private function createAssociatedServices(User $user, Product $product, array $purchaseContext, Invoice $invoice): void
    {
        try {
            // Crear servicio de hosting
            $this->createHostingService($user, $product, $purchaseContext, $invoice);

            // Crear servicio de dominio si aplica
            $domainPrice = $purchaseContext['domain_price'] ?? 0;
            if ($domainPrice > 0 && isset($purchaseContext['domain'])) {
                $this->createDomainService($user, $purchaseContext, $invoice);
            }

            Log::info('CheckoutService - Servicios creados exitosamente', [
                'user_id'    => $user->id,
                'invoice_id' => $invoice->id,
            ]);

        } catch (\Exception $e) {
            Log::error('CheckoutService - Error creando servicios', [
                'error'      => $e->getMessage(),
                'user_id'    => $user->id,
                'invoice_id' => $invoice->id,
            ]);
            // No fallar el checkout por esto
        }
    }

    /**
     * Generar notas para la factura
     */
    private function generateInvoiceNotes(array $purchaseContext): string
    {
        return 'Compra desde landing page - Plan: ' . ($purchaseContext['plan'] ?? 'N/A') .
            ' - Dominio: ' . ($purchaseContext['domain'] ?? 'N/A') .
            ' - Caso de uso: ' . ($purchaseContext['use_case'] ?? 'N/A');
    }

    /**
     * Crear servicio de hosting
     */
    private function createHostingService(User $user, Product $product, array $purchaseContext, Invoice $invoice): void
    {
        $billingCycleId = $purchaseContext['billing_cycle_id'];
        $pricing        = $product->pricings()->where('billing_cycle_id', $billingCycleId)->first();

        if (! $pricing) {
            Log::error('CheckoutService - No se encontró pricing para el producto', [
                'product_id'       => $product->id,
                'billing_cycle_id' => $billingCycleId,
            ]);
            return;
        }

        $billingCycle = $pricing->billingCycle;
        $nextDueDate  = $this->calculateNextDueDate($billingCycle);

        $serviceNotes = $this->generateServiceNotes($purchaseContext);

        $service = ClientService::create([
            'client_id'          => $user->id,
            'product_id'         => $product->id,
            'product_pricing_id' => $pricing->id,
            'billing_cycle_id'   => $billingCycleId,
            'domain_name'        => $purchaseContext['domain'] ?? null,
            'status'             => 'pending',
            'registration_date'  => now(),
            'next_due_date'      => $nextDueDate,
            'billing_amount'     => $purchaseContext['price_calculation']['total'],
            'notes'              => implode("\n", $serviceNotes),
        ]);

        Log::info('CheckoutService - Servicio de hosting creado', [
            'user_id'    => $user->id,
            'service_id' => $service->id,
            'product'    => $product->name,
        ]);
    }

    /**
     * Crear servicio de dominio
     */
    private function createDomainService(User $user, array $purchaseContext, Invoice $invoice): void
    {
        $domainProduct = Product::where('name', 'LIKE', '%dominio%')
            ->orWhere('slug', 'LIKE', '%domain%')
            ->first();

        if (! $domainProduct) {
            Log::warning('CheckoutService - No se encontró producto de dominio');
            return;
        }

        $annualPricing = $domainProduct->pricings()
            ->whereHas('billingCycle', function ($q) {
                $q->where('slug', 'annually');
            })
            ->first();

        if (! $annualPricing) {
            Log::warning('CheckoutService - No se encontró pricing anual para dominio');
            return;
        }

        $nextDueDate = now()->addYear();

        $domainService = ClientService::create([
            'client_id'          => $user->id,
            'product_id'         => $domainProduct->id,
            'product_pricing_id' => $annualPricing->id,
            'billing_cycle_id'   => $annualPricing->billing_cycle_id,
            'domain_name'        => $purchaseContext['domain'],
            'status'             => 'pending',
            'registration_date'  => now(),
            'next_due_date'      => $nextDueDate,
            'billing_amount'     => $purchaseContext['domain_price'] ?? 15.00,
            'notes'              => "Registro de dominio: " . $purchaseContext['domain'] . "\nAcción: " . ($purchaseContext['domain_action'] ?? 'register'),
        ]);

        Log::info('CheckoutService - Servicio de dominio creado', [
            'user_id'    => $user->id,
            'service_id' => $domainService->id,
            'domain'     => $purchaseContext['domain'],
        ]);
    }

    /**
     * Generar notas del servicio
     */
    private function generateServiceNotes(array $purchaseContext): array
    {
        $serviceNotes   = [];
        $serviceNotes[] = "Plan: " . ucfirst($purchaseContext['plan'] ?? 'professional');
        $serviceNotes[] = "Caso de uso: " . ($purchaseContext['use_case'] ?? 'N/A');

        if (isset($purchaseContext['configurable_options']) && ! empty($purchaseContext['configurable_options'])) {
            $serviceNotes[] = "Opciones configurables:";
            foreach ($purchaseContext['configurable_options'] as $optionId => $quantity) {
                if ($quantity > 0) {
                    $serviceNotes[] = "- Opción {$optionId}: {$quantity} unidades";
                }
            }
        }

        return $serviceNotes;
    }

    /**
     * Calcular próxima fecha de vencimiento
     */
    private function calculateNextDueDate(\App\Models\BillingCycle $billingCycle): \Carbon\Carbon
    {
        $now = now();

        return match ($billingCycle->slug) {
            'monthly' => $now->addMonth(),
            'quarterly' => $now->addMonths(3),
            'semi_annually' => $now->addMonths(6),
            'annually' => $now->addYear(),
            'biennially' => $now->addYears(2),
            'triennially' => $now->addYears(3),
            default => $now->addMonth(),
        };
    }
}

<?php

/**
 * ⚠️ DEPRECATED - MARCADO PARA ELIMINACIÓN
 *
 * Este controlador monolítico (978 líneas) ha sido refactorizado y reemplazado por:
 * - PublicCheckoutControllerRefactored (manejo HTTP limpio)
 * - CheckoutService (lógica de checkout)
 * - UserService (gestión de usuarios)
 * - InvoiceService (gestión de facturas)
 * - PricingCalculatorService (cálculos de precios)
 *
 * TODO: Eliminar este archivo después de migrar completamente las rutas
 * Fecha de refactorización: 2025-01-22
 * Reemplazado por: PublicCheckoutControllerRefactored + Servicios
 */

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use App\Services\NameSiloService;
use App\Services\PricingCalculatorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PublicCheckoutController extends Controller
{
    protected NameSiloService $nameSiloService;
    protected PricingCalculatorService $pricingCalculator;

    public function __construct(NameSiloService $nameSiloService, PricingCalculatorService $pricingCalculator)
    {
        $this->nameSiloService   = $nameSiloService;
        $this->pricingCalculator = $pricingCalculator;
    }

    /**
     * Step 1: Domain verification for public users
     */
    public function showDomainVerification(Request $request): InertiaResponse | \Illuminate\Http\RedirectResponse
    {
        $purchaseContext = session('purchase_context');

        // 🔍 DEBUG: Log al entrar a showDomainVerification
        Log::info('🔍 ENTRADA A showDomainVerification', [
            'has_purchase_context' => ! ! $purchaseContext,
            'purchase_context'     => $purchaseContext,
            'session_id'           => session()->getId(),
            'url'                  => $request->fullUrl(),
        ]);

        if (! $purchaseContext) {
            Log::warning('⚠️ PURCHASE_CONTEXT PERDIDO en showDomainVerification', [
                'session_id'       => session()->getId(),
                'all_session_data' => session()->all(),
            ]);
            return redirect()->route('sales.home')
                ->with('error', 'Sesión expirada. Por favor, selecciona tu plan nuevamente.');
        }

        // Get the product with pricing and configurable options
        $product = Product::with([
            'pricings.billingCycle',
            'configurableOptionGroups.options.pricings.billingCycle',
        ])->where('slug', $purchaseContext['product_slug'])->first();

        if (! $product) {
            return redirect()->route('sales.home')
                ->with('error', 'Producto no encontrado.');
        }

        // Get available billing cycles for this product with calculated prices
        $availableBillingCycles = $product->pricings->map(function ($pricing) use ($product) {
            try {
                // Calculate total price including base resources for each cycle
                $priceCalculation = $this->pricingCalculator->calculateProductPrice(
                    $product->id,
                    $pricing->billingCycle->id,
                    []// No additional configurable options for base price
                );

                $totalPrice = $priceCalculation['total'];

                Log::info('PublicCheckout - Precio calculado para ciclo', [
                    'product_id'       => $product->id,
                    'billing_cycle_id' => $pricing->billingCycle->id,
                    'base_price'       => $pricing->price,
                    'total_price'      => $totalPrice,
                    'calculation'      => $priceCalculation,
                ]);

            } catch (\Exception $e) {
                Log::error('PublicCheckout - Error calculando precio para ciclo: ' . $e->getMessage(), [
                    'product_id'       => $product->id,
                    'billing_cycle_id' => $pricing->billingCycle->id,
                ]);
                $totalPrice = $pricing->price; // Fallback to base price
            }

            return [
                'id'         => $pricing->billingCycle->id,
                'name'       => $pricing->billingCycle->name,
                'slug'       => $pricing->billingCycle->slug,
                'days'       => $pricing->billingCycle->days,
                'base_price' => $pricing->price, // Precio base de cPanel
                'price'      => $totalPrice,     // Precio total con recursos incluidos
                'setup_fee'  => $pricing->setup_fee,
            ];
        });

        // Get discount percentages for this product
        $discountPercentages = \App\Models\DiscountPercentage::where('product_id', $product->id)
            ->where('is_active', true)
            ->with('billingCycle')
            ->get()
            ->keyBy('billing_cycle_id')
            ->map(function ($discount) {
                return [
                    'percentage'  => $discount->percentage,
                    'name'        => $discount->name,
                    'description' => $discount->description,
                ];
            });

        // Get configurable option groups for this product
        $configurableOptions = $product->configurableOptionGroups->map(function ($group) {
            return [
                'id'            => $group->id,
                'name'          => $group->name,
                'description'   => $group->description,
                'display_order' => $group->pivot->display_order,
                'base_quantity' => $group->pivot->base_quantity,
                'is_required'   => $group->pivot->is_required,
                'options'       => $group->options->map(function ($option) {
                    return [
                        'id'          => $option->id,
                        'name'        => $option->name,
                        'description' => $option->description,
                        'pricings'    => $option->pricings->map(function ($pricing) {
                            return [
                                'billing_cycle_id'   => $pricing->billing_cycle_id,
                                'price'              => $pricing->price,
                                'billing_cycle_name' => $pricing->billingCycle->name,
                            ];
                        }),
                    ];
                }),
            ];
        });

        return Inertia::render('PublicCheckout/DomainVerification', [
            'purchaseContext'        => $purchaseContext,
            'product'                => $product,
            'availableBillingCycles' => $availableBillingCycles,
            'configurableOptions'    => $configurableOptions,
            'discountPercentages'    => $discountPercentages,
            'useCaseMessages'        => $this->getUseCaseMessages($purchaseContext['use_case'] ?? null),
        ]);
    }

    /**
     * Process domain verification with product configuration
     */
    public function processDomainVerification(Request $request): \Illuminate\Http\RedirectResponse
    {
        $purchaseContext = session('purchase_context');

        if (! $purchaseContext) {
            return redirect()->route('sales.home')
                ->with('error', 'Sesión expirada. Por favor, selecciona tu plan nuevamente.');
        }

        $validated = $request->validate([
            'domain'                 => 'required|string|max:255',
            'action'                 => 'required|string|in:register,existing',
            'billing_cycle_id'       => 'required|integer|exists:billing_cycles,id',
            'configurable_options'   => 'nullable|array',
            'configurable_options.*' => 'integer|min:0',
        ]);

        // Get the product to calculate pricing
        $product = Product::where('slug', $purchaseContext['product_slug'])->first();

        if (! $product) {
            return redirect()->route('sales.home')
                ->with('error', 'Producto no encontrado.');
        }

        // Calculate pricing using PricingCalculatorService
        try {
            $priceCalculation = $this->pricingCalculator->calculateProductPrice(
                $product->id,
                $validated['billing_cycle_id'],
                $validated['configurable_options'] ?? []
            );

            Log::info('PublicCheckout - Precio calculado', [
                'product_id'           => $product->id,
                'billing_cycle_id'     => $validated['billing_cycle_id'],
                'configurable_options' => $validated['configurable_options'] ?? [],
                'calculation'          => $priceCalculation,
            ]);

        } catch (\Exception $e) {
            Log::error('PublicCheckout - Error calculando precio: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al calcular el precio. Por favor, inténtalo de nuevo.')
                ->withInput();
        }

        // Store domain info and pricing in session
        session([
            'purchase_context' => array_merge($purchaseContext, [
                'domain'               => $validated['domain'],
                'domain_action'        => $validated['action'],
                'domain_price'         => $validated['action'] === 'register' ? 15.00 : 0,
                'billing_cycle_id'     => $validated['billing_cycle_id'],
                'configurable_options' => $validated['configurable_options'] ?? [],
                'price_calculation'    => $priceCalculation,
            ]),
        ]);

        return redirect()->route('public.checkout.register');
    }

    /**
     * Step 3: User registration
     */
    public function showRegistration(Request $request): InertiaResponse | \Illuminate\Http\RedirectResponse
    {
        $purchaseContext = session('purchase_context');

        if (! $purchaseContext || ! isset($purchaseContext['domain'])) {
            return redirect()->route('public.checkout.domain')
                ->with('error', 'Por favor, verifica tu dominio primero.');
        }

        return Inertia::render('PublicCheckout/Registration', [
            'purchaseContext' => $purchaseContext,
            'useCaseMessages' => $this->getUseCaseMessages(),
        ]);
    }

    /**
     * Step 4: Process registration and redirect to payment
     */
    public function processRegistration(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|string|email|max:255|unique:users',
            'password'     => 'required|string|min:8|confirmed',
            'company_name' => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'country'      => 'required|string|max:2',
        ]);

        $purchaseContext = session('purchase_context');

        // 🔍 DEBUG: Log al inicio del proceso de registro
        Log::info('🔍 INICIO processRegistration', [
            'has_purchase_context' => ! ! $purchaseContext,
            'purchase_context'     => $purchaseContext,
            'session_id'           => session()->getId(),
            'user_email'           => $validated['email'],
        ]);

        if (! $purchaseContext) {
            Log::warning('⚠️ PURCHASE_CONTEXT PERDIDO en processRegistration', [
                'session_id'       => session()->getId(),
                'user_email'       => $validated['email'],
                'all_session_data' => session()->all(),
            ]);
            return redirect()->route('sales.home')
                ->with('error', 'Sesión expirada.');
        }

        try {
            // Create the user without email verification
            $user = User::create([
                'name'              => $validated['name'],
                'email'             => $validated['email'],
                'password'          => Hash::make($validated['password']),
                'role'              => 'client',
                'company_name'      => $validated['company_name'],
                'phone'             => $validated['phone'],
                'country'           => $validated['country'],
                'reseller_id'       => null,
                'status'            => 'active',
                'language_code'     => 'es',
                'currency_code'     => 'USD',
                'email_verified_at' => null, // No auto-verificar, enviar email
            ]);

            // Send custom email verification for purchase flow
            $user->notify(new \App\Notifications\PurchaseEmailVerification());

            // Store user info in session for later login after verification
            $pendingUserData = [
                'id'               => $user->id,
                'email'            => $user->email,
                'purchase_context' => $purchaseContext,
            ];

            session(['pending_user' => $pendingUserData]);

            // 🔍 DEBUG: Log después de crear usuario y guardar pending_user
            Log::info('✅ USUARIO CREADO - pending_user guardado', [
                'user_id'                    => $user->id,
                'user_email'                 => $user->email,
                'pending_user_data'          => $pendingUserData,
                'session_id'                 => session()->getId(),
                'purchase_context_preserved' => ! ! $purchaseContext,
            ]);

            // Redirect to verification notice page
            return redirect()->route('verification.notice')
                ->with('success', '¡Cuenta creada exitosamente! Hemos enviado un enlace de verificación a tu email. Por favor, revisa tu bandeja de entrada y haz clic en el enlace para continuar con tu compra.');

        } catch (\Exception $e) {
            Log::error('Error creating user during public checkout: ' . $e->getMessage());

            return redirect()->back()
                ->withErrors(['email' => 'Error al crear la cuenta. Por favor, inténtalo de nuevo.'])
                ->withInput();
        }
    }

    /**
     * Step 5: Payment page
     */
    public function showPayment(Request $request): InertiaResponse | \Illuminate\Http\RedirectResponse
    {
        $purchaseContext = session('purchase_context');

        if (! $purchaseContext || ! Auth::check()) {
            return redirect()->route('public.checkout.domain')
                ->with('error', 'Por favor, completa los pasos anteriores.');
        }

        // Verify we have price calculation from previous step
        if (! isset($purchaseContext['price_calculation'])) {
            return redirect()->route('public.checkout.domain')
                ->with('error', 'Información de precio perdida. Por favor, configura tu producto nuevamente.');
        }

        // Get product for display
        $product = Product::where('slug', $purchaseContext['product_slug'])->first();

        if (! $product) {
            return redirect()->route('sales.home')
                ->with('error', 'Producto no encontrado.');
        }

        // Use calculated pricing from session
        $priceCalculation = $purchaseContext['price_calculation'];
        $subtotal         = $priceCalculation['total'];
        $domainPrice      = $purchaseContext['domain_price'] ?? 0;
        $total            = $subtotal + $domainPrice;

        return Inertia::render('PublicCheckout/Payment', [
            'purchaseContext'  => $purchaseContext,
            'product'          => $product,
            'priceCalculation' => $priceCalculation,
            'subtotal'         => $subtotal,
            'domainPrice'      => $domainPrice,
            'total'            => $total,
            'useCaseMessages'  => $this->getUseCaseMessages(),
        ]);
    }

    /**
     * Process payment and create invoice
     */
    public function processPayment(Request $request): \Illuminate\Http\RedirectResponse
    {
        Log::info('ProcessPayment called', ['user_id' => Auth::id()]);

        $purchaseContext = session('purchase_context');

        if (! $purchaseContext || ! Auth::check()) {
            Log::warning('ProcessPayment failed - missing context or auth', [
                'has_context'      => ! ! $purchaseContext,
                'is_authenticated' => Auth::check(),
            ]);
            return redirect()->route('public.checkout.domain')
                ->with('error', 'Por favor, completa los pasos anteriores.');
        }

        $validated = $request->validate([
            'payment_method' => 'required|string|in:paypal,other_methods',
        ]);

        $user = Auth::user();

        // Verify we have price calculation
        if (! isset($purchaseContext['price_calculation'])) {
            return redirect()->route('public.checkout.domain')
                ->with('error', 'Información de precio perdida. Por favor, configura tu producto nuevamente.');
        }

        // Get product for invoice creation
        $product = Product::where('slug', $purchaseContext['product_slug'])->first();

        if (! $product) {
            return redirect()->route('sales.home')
                ->with('error', 'Producto no encontrado.');
        }

        // Use calculated pricing from session
        $priceCalculation = $purchaseContext['price_calculation'];
        $subtotal         = $priceCalculation['total'];
        $domainPrice      = $purchaseContext['domain_price'] ?? 0;
        $total            = $subtotal + $domainPrice;

        // Create invoice
        Log::info('Creating invoice', [
            'user_id'          => $user->id,
            'subtotal'         => $subtotal,
            'total'            => $total,
            'purchase_context' => $purchaseContext,
        ]);

        $invoice = \App\Models\Invoice::create([
            'client_id'      => $user->id,
            'invoice_number' => 'INV-' . date('Y') . '-' . str_pad(\App\Models\Invoice::count() + 1, 6, '0', STR_PAD_LEFT),
            'issue_date'     => now(),
            'due_date'       => now()->addDays(30),
            'subtotal'       => $subtotal,
            'tax_amount'     => 0,
            'total_amount'   => $total,
            'status'         => 'unpaid',
            'currency_code'  => 'USD',
            'notes'          => 'Compra desde landing page - Plan: ' . ($purchaseContext['plan'] ?? 'N/A') .
            ' - Dominio: ' . ($purchaseContext['domain'] ?? 'N/A') .
            ' - Caso de uso: ' . ($purchaseContext['use_case'] ?? 'N/A'),
        ]);

        Log::info('Invoice created successfully', ['invoice_id' => $invoice->id]);

        // Create invoice items
        \App\Domains\Invoices\Models\InvoiceItem::create([
            'invoice_id'  => $invoice->id,
            'description' => $product->name . ' - Plan ' . ucfirst($purchaseContext['plan'] ?? 'professional'),
            'quantity'    => 1,
            'unit_price'  => $subtotal,
            'total_price' => $subtotal,
        ]);

        // Add domain item if applicable
        if ($domainPrice > 0) {
            \App\Domains\Invoices\Models\InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'description' => 'Registro de dominio: ' . $purchaseContext['domain'],
                'quantity'    => 1,
                'unit_price'  => $domainPrice,
                'total_price' => $domainPrice,
            ]);
        }

        // 🚀 CREAR SERVICIOS DESPUÉS DE LA FACTURA
        try {
            $this->createHostingService($user, $product, $purchaseContext, $invoice);

            // 🌐 CREAR SERVICIO DE DOMINIO (si aplica)
            if ($domainPrice > 0 && isset($purchaseContext['domain'])) {
                $this->createDomainService($user, $purchaseContext, $invoice);
            }

            Log::info('✅ SERVICIOS CREADOS exitosamente en checkout público', [
                'user_id'    => $user->id,
                'invoice_id' => $invoice->id,
                'product'    => $product->name,
                'domain'     => $purchaseContext['domain'] ?? 'N/A',
            ]);
        } catch (\Exception $e) {
            Log::error('❌ ERROR creando servicios en checkout público: ' . $e->getMessage(), [
                'user_id'    => $user->id,
                'invoice_id' => $invoice->id,
                'trace'      => $e->getTraceAsString(),
            ]);
            // No fallar el checkout por esto, solo loggear el error
        }

        // Clear purchase context
        session()->forget(['purchase_context', 'pending_user']);

        // Handle payment method selection
        $paymentMethod = $request->input('payment_method');

        if ($paymentMethod === 'paypal') {
            // Redirect directly to PayPal payment for this invoice
            return redirect()->route('client.paypal.payment.create', ['invoice' => $invoice->id])
                ->with('success', '¡Factura creada exitosamente! Procede con el pago PayPal.');
        } else {
            // For other methods, redirect to invoice page
            return redirect()->route('client.invoices.show', $invoice->id)
                ->with('success', '¡Factura creada exitosamente! Procede con el pago.');
        }
    }

    /**
     * Show registration page with sales context
     */
    public function showRegistrationWithContext(Request $request): InertiaResponse | \Illuminate\Http\RedirectResponse
    {
        $salesContext = session('sales_context');

        if (! $salesContext) {
            return redirect()->route('sales.home')
                ->with('error', 'Sesión expirada. Por favor, selecciona tu plan nuevamente.');
        }

        return Inertia::render('PublicCheckout/RegistrationWithContext', [
            'salesContext' => $salesContext,
        ]);
    }

    /**
     * Process registration with sales context and redirect to domain selection
     */
    public function processRegistrationWithContext(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|string|email|max:255|unique:users',
            'password'     => 'required|string|min:8|confirmed',
            'company_name' => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'country'      => 'required|string|max:2',
        ]);

        $salesContext = session('sales_context');
        if (! $salesContext) {
            return redirect()->route('sales.home')
                ->with('error', 'Sesión expirada.');
        }

        try {
            // Create the user
            $user = User::create([
                'name'              => $validated['name'],
                'email'             => $validated['email'],
                'password'          => Hash::make($validated['password']),
                'role'              => 'client',
                'company_name'      => $validated['company_name'],
                'phone'             => $validated['phone'],
                'country'           => $validated['country'],
                'reseller_id'       => null,
                'status'            => 'active',
                'language_code'     => 'es',
                'currency_code'     => 'USD',
                'email_verified_at' => now(), // Auto-verificar email para flujo de compra
            ]);

            // Log the user in
            Auth::login($user);

            // Convert sales context to purchase context for the checkout flow
            session([
                'purchase_context' => [
                    'use_case'     => $salesContext['use_case'],
                    'plan'         => $salesContext['plan'] ?? 'professional',
                    'product_slug' => $salesContext['product_slug'],
                    'source'       => 'sales_landing',
                    'user_id'      => $user->id,
                ],
            ]);

            // Redirect to existing domain selection page
            return redirect()->route('client.checkout.selectDomain')
                ->with('success', '¡Cuenta creada exitosamente! Ahora selecciona tu dominio.');

        } catch (\Exception $e) {
            Log::error('Error creating user during sales checkout: ' . $e->getMessage());

            return redirect()->back()
                ->withErrors(['email' => 'Error al crear la cuenta. Por favor, inténtalo de nuevo.'])
                ->withInput();
        }
    }

    /**
     * Get use case specific messages
     */
    private function getUseCaseMessages(?string $useCase = null): array
    {
        $messages = [
            'educators'      => [
                'domain_suggestion'     => 'Ej: miacademia.com, cursosdeingles.com',
                'registration_title'    => 'Crea tu cuenta de educador',
                'registration_subtitle' => 'Comienza a enseñar online hoy mismo',
            ],
            'entrepreneurs'  => [
                'domain_suggestion'     => 'Ej: mitienda.com, ventasonline.com',
                'registration_title'    => 'Crea tu cuenta de emprendedor',
                'registration_subtitle' => 'Lanza tu negocio online',
            ],
            'professionals'  => [
                'domain_suggestion'     => 'Ej: miprofesion.com, consultoria.com',
                'registration_title'    => 'Crea tu cuenta profesional',
                'registration_subtitle' => 'Construye tu presencia online',
            ],
            'small-business' => [
                'domain_suggestion'     => 'Ej: minegocio.com, empresa.com',
                'registration_title'    => 'Crea tu cuenta empresarial',
                'registration_subtitle' => 'Digitaliza tu negocio',
            ],
        ];

        return $useCase ? ($messages[$useCase] ?? []) : $messages;
    }

    /**
     * Verify email for purchase flow (without requiring authentication)
     */
    public function verifyPurchaseEmail(Request $request, $id, $hash): RedirectResponse
    {
        // 🔍 DEBUG: Log al entrar a verifyPurchaseEmail
        Log::info('🔍 ENTRADA A verifyPurchaseEmail', [
            'user_id'    => $id,
            'hash'       => $hash,
            'url'        => $request->fullUrl(),
            'session_id' => session()->getId(),
        ]);

        // Find the user
        $user = User::findOrFail($id);

        // Verify the hash
        $expectedHash = sha1($user->getEmailForVerification());
        $hashMatches  = hash_equals((string) $hash, $expectedHash);

        Log::info('🔍 VERIFICACIÓN DE HASH', [
            'user_id'                => $user->id,
            'user_email'             => $user->email,
            'provided_hash'          => $hash,
            'expected_hash'          => $expectedHash,
            'hash_matches'           => $hashMatches,
            'email_already_verified' => $user->hasVerifiedEmail(),
        ]);

        if (! $hashMatches) {
            Log::warning('⚠️ HASH INVÁLIDO en verifyPurchaseEmail', [
                'user_id'       => $user->id,
                'provided_hash' => $hash,
                'expected_hash' => $expectedHash,
            ]);
            return redirect()->route('sales.home')
                ->with('error', 'El enlace de verificación no es válido.');
        }

        // Check if email is already verified
        if ($user->hasVerifiedEmail()) {
            // Log the user in
            Auth::login($user);

            // Check if user has pending purchase context
            $pendingUser = session('pending_user');

            Log::info('🔍 EMAIL YA VERIFICADO - verificando pending_user', [
                'user_id'           => $user->id,
                'has_pending_user'  => ! ! $pendingUser,
                'pending_user_data' => $pendingUser,
                'session_id'        => session()->getId(),
            ]);

            if ($pendingUser && $pendingUser['id'] == $user->id) {
                // Crear factura automáticamente con el purchase_context
                $invoice = $this->createInvoiceFromPurchaseContext($user, $pendingUser['purchase_context']);
                session()->forget('pending_user');

                Log::info('✅ FACTURA CREADA después de verificación (email ya verificado)', [
                    'user_id'    => $user->id,
                    'invoice_id' => $invoice->id,
                    'session_id' => session()->getId(),
                ]);

                return redirect()->route('public.checkout.payment.success', ['invoice' => $invoice->id])
                    ->with('success', '¡Email verificado! Tu factura está lista para pagar.');
            }

            Log::info('🔍 EMAIL VERIFICADO pero sin pending_user - redirigiendo a dashboard', [
                'user_id'    => $user->id,
                'session_id' => session()->getId(),
            ]);

            return redirect()->route('client.dashboard')
                ->with('success', '¡Email ya verificado!');
        }

        // Mark email as verified
        if ($user->markEmailAsVerified()) {
            event(new \Illuminate\Auth\Events\Verified($user));
        }

        // Log the user in
        Auth::login($user);

        // Check if user has pending purchase context
        $pendingUser = session('pending_user');
        if ($pendingUser && $pendingUser['id'] == $user->id) {
            // Crear factura automáticamente con el purchase_context
            $invoice = $this->createInvoiceFromPurchaseContext($user, $pendingUser['purchase_context']);
            session()->forget('pending_user');

            Log::info('✅ FACTURA CREADA después de verificación (email recién verificado)', [
                'user_id'    => $user->id,
                'invoice_id' => $invoice->id,
                'session_id' => session()->getId(),
            ]);

            return redirect()->route('public.checkout.payment.success', ['invoice' => $invoice->id])
                ->with('success', '¡Email verificado! Tu factura está lista para pagar.');
        }

        // If no purchase context, redirect to login page
        return redirect()->route('login')
            ->with('success', '¡Email verificado exitosamente! Ahora puedes iniciar sesión.');
    }

    /**
     * Show payment success page after email verification
     */
    public function showPaymentSuccess(Request $request, \App\Models\Invoice $invoice): InertiaResponse | \Illuminate\Http\RedirectResponse
    {
        // Verify the invoice belongs to the authenticated user
        if (! Auth::check() || $invoice->client_id !== Auth::id()) {
            return redirect()->route('login')
                ->with('error', 'No tienes acceso a esta factura.');
        }

        // Get purchase context from invoice notes or reconstruct it
        $purchaseContext = $this->reconstructPurchaseContextFromInvoice($invoice);

        return Inertia::render('PublicCheckout/PaymentSuccess', [
            'invoice'         => $invoice,
            'purchaseContext' => $purchaseContext,
        ]);
    }

    /**
     * Reconstruct purchase context from invoice data
     */
    private function reconstructPurchaseContextFromInvoice(\App\Models\Invoice $invoice): array
    {
        // Try to extract info from invoice notes
        $notes = $invoice->notes ?? '';

        $context = [
            'product_name'       => 'Hosting Plan',
            'plan'               => 'Professional',
            'domain'             => 'N/A',
            'domain_price'       => 0,
            'billing_cycle_slug' => 'monthly',
        ];

        // Extract domain from notes
        if (preg_match('/Dominio: ([^\s-]+)/', $notes, $matches)) {
            $context['domain'] = $matches[1];
        }

        // Extract plan from notes
        if (preg_match('/Plan: ([^\s-]+)/', $notes, $matches)) {
            $context['plan'] = $matches[1];
        }

        // Check if there's a domain item in invoice items
        $invoiceItems = $invoice->items ?? [];
        foreach ($invoiceItems as $item) {
            if (strpos($item->description, 'Registro de dominio') !== false) {
                $context['domain_price'] = $item->total_price;
                if (preg_match('/Registro de dominio: ([^\s]+)/', $item->description, $matches)) {
                    $context['domain'] = $matches[1];
                }
            }
        }

        return $context;
    }

    /**
     * Create invoice and services from purchase context
     */
    private function createInvoiceFromPurchaseContext(User $user, array $purchaseContext): \App\Models\Invoice
    {
        // Get product
        $product = Product::where('slug', $purchaseContext['product_slug'])->first();

        if (! $product) {
            throw new \Exception('Producto no encontrado: ' . $purchaseContext['product_slug']);
        }

        // Use calculated pricing from purchase context
        if (! isset($purchaseContext['price_calculation'])) {
            throw new \Exception('Información de precio no encontrada en el contexto de compra');
        }

        $priceCalculation = $purchaseContext['price_calculation'];
        $subtotal         = $priceCalculation['total'];
        $domainPrice      = $purchaseContext['domain_price'] ?? 0;
        $total            = $subtotal + $domainPrice;

        // Create invoice
        $invoice = \App\Domains\Invoices\Models\Invoice::create([
            'client_id'      => $user->id,
            'invoice_number' => 'INV-' . date('Y') . '-' . str_pad(\App\Domains\Invoices\Models\Invoice::count() + 1, 6, '0', STR_PAD_LEFT),
            'issue_date'     => now(),
            'due_date'       => now()->addDays(30),
            'subtotal'       => $subtotal,
            'tax_amount'     => 0,
            'total_amount'   => $total,
            'status'         => 'unpaid',
            'currency_code'  => 'USD',
            'notes'          => 'Compra desde landing page - Plan: ' . ($purchaseContext['plan'] ?? 'N/A') .
            ' - Dominio: ' . ($purchaseContext['domain'] ?? 'N/A') .
            ' - Caso de uso: ' . ($purchaseContext['use_case'] ?? 'N/A'),
        ]);

        // Create invoice items
        \App\Domains\Invoices\Models\InvoiceItem::create([
            'invoice_id'  => $invoice->id,
            'description' => $product->name . ' - Plan ' . ucfirst($purchaseContext['plan'] ?? 'professional'),
            'quantity'    => 1,
            'unit_price'  => $subtotal,
            'total_price' => $subtotal,
        ]);

        // Add domain item if applicable
        if ($domainPrice > 0) {
            \App\Domains\Invoices\Models\InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'description' => 'Registro de dominio: ' . $purchaseContext['domain'],
                'quantity'    => 1,
                'unit_price'  => $domainPrice,
                'total_price' => $domainPrice,
            ]);
        }

        // 🚀 CREAR SERVICIO DE HOSTING
        $this->createHostingService($user, $product, $purchaseContext, $invoice);

        // 🌐 CREAR SERVICIO DE DOMINIO (si aplica)
        if ($domainPrice > 0 && isset($purchaseContext['domain'])) {
            $this->createDomainService($user, $purchaseContext, $invoice);
        }

        Log::info('📄 FACTURA Y SERVICIOS CREADOS desde purchase_context', [
            'user_id'    => $user->id,
            'invoice_id' => $invoice->id,
            'product'    => $product->name,
            'plan'       => $purchaseContext['plan'],
            'domain'     => $purchaseContext['domain'] ?? 'N/A',
            'total'      => $total,
        ]);

        return $invoice;
    }

    /**
     * Crear servicio de hosting para el usuario
     */
    private function createHostingService(User $user, Product $product, array $purchaseContext, \App\Models\Invoice $invoice): void
    {
        // Obtener el pricing del producto
        $billingCycleId = $purchaseContext['billing_cycle_id'];
        $pricing        = $product->pricings()->where('billing_cycle_id', $billingCycleId)->first();

        if (! $pricing) {
            Log::error('No se encontró pricing para el producto', [
                'product_id'       => $product->id,
                'billing_cycle_id' => $billingCycleId,
            ]);
            return;
        }

        // Calcular próxima fecha de vencimiento
        $billingCycle = $pricing->billingCycle;
        $nextDueDate  = $this->calculateNextDueDate($billingCycle);

        // Crear notas del servicio con opciones configurables
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

        // Crear el servicio
        $service = \App\Models\ClientService::create([
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

        Log::info('🚀 SERVICIO DE HOSTING CREADO', [
            'user_id'    => $user->id,
            'service_id' => $service->id,
            'product'    => $product->name,
            'domain'     => $purchaseContext['domain'] ?? 'N/A',
            'amount'     => $purchaseContext['price_calculation']['total'],
        ]);
    }

    /**
     * Crear servicio de dominio para el usuario
     */
    private function createDomainService(User $user, array $purchaseContext, \App\Models\Invoice $invoice): void
    {
        // Buscar producto de dominio (asumiendo que existe un producto para dominios)
        $domainProduct = Product::where('name', 'LIKE', '%dominio%')
            ->orWhere('slug', 'LIKE', '%domain%')
            ->first();

        if (! $domainProduct) {
            Log::warning('No se encontró producto de dominio para crear el servicio');
            return;
        }

        // Obtener pricing anual para dominios (típicamente se facturan anualmente)
        $annualPricing = $domainProduct->pricings()
            ->whereHas('billingCycle', function ($q) {
                $q->where('slug', 'annually');
            })
            ->first();

        if (! $annualPricing) {
            Log::warning('No se encontró pricing anual para producto de dominio');
            return;
        }

        // Calcular próxima fecha de vencimiento (1 año)
        $nextDueDate = now()->addYear();

        // Crear el servicio de dominio
        $domainService = \App\Models\ClientService::create([
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

        Log::info('🌐 SERVICIO DE DOMINIO CREADO', [
            'user_id'    => $user->id,
            'service_id' => $domainService->id,
            'domain'     => $purchaseContext['domain'],
            'amount'     => $purchaseContext['domain_price'] ?? 15.00,
        ]);
    }

    /**
     * Calcular próxima fecha de vencimiento basada en el ciclo de facturación
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

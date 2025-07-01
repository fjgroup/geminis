<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use App\Services\NameSiloService;
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

    public function __construct(NameSiloService $nameSiloService)
    {
        $this->nameSiloService = $nameSiloService;
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

        // Get the product based on context
        $product = Product::where('slug', $purchaseContext['product_slug'])->first();

        if (! $product) {
            return redirect()->route('sales.home')
                ->with('error', 'Producto no encontrado.');
        }

        return Inertia::render('PublicCheckout/DomainVerification', [
            'purchaseContext' => $purchaseContext,
            'product'         => $product,
            'useCaseMessages' => $this->getUseCaseMessages($purchaseContext['use_case'] ?? null),
        ]);
    }

    /**
     * Process domain verification
     */
    public function processDomainVerification(Request $request): \Illuminate\Http\RedirectResponse
    {
        $purchaseContext = session('purchase_context');

        if (! $purchaseContext) {
            return redirect()->route('sales.home')
                ->with('error', 'Sesión expirada. Por favor, selecciona tu plan nuevamente.');
        }

        $validated = $request->validate([
            'domain' => 'required|string|max:255',
            'action' => 'required|string|in:register,existing',
        ]);

        // Store domain info in session
        session([
            'purchase_context' => array_merge($purchaseContext, [
                'domain'        => $validated['domain'],
                'domain_action' => $validated['action'],
                'domain_price'  => $validated['action'] === 'register' ? 15.00 : 0, // Precio ejemplo
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
            Log::info('📧 ENVIANDO NOTIFICACIÓN DE COMPRA', [
                'user_id'            => $user->id,
                'user_email'         => $user->email,
                'notification_class' => \App\Notifications\PurchaseEmailVerification::class,
            ]);

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

        // Get product and calculate total
        $product = Product::with(['pricings.billingCycle'])
            ->where('slug', $purchaseContext['product_slug'])
            ->first();

        if (! $product) {
            return redirect()->route('sales.home')
                ->with('error', 'Producto no encontrado.');
        }

        // Get monthly pricing for now (can be enhanced later)
        $pricing = $product->pricings->where('billingCycle.slug', 'monthly')->first();

        if (! $pricing) {
            return redirect()->route('sales.home')
                ->with('error', 'Precio no encontrado para este producto.');
        }

        $subtotal    = $pricing->price;
        $domainPrice = $purchaseContext['domain_price'] ?? 0;
        $total       = $subtotal + $domainPrice;

        return Inertia::render('PublicCheckout/Payment', [
            'purchaseContext' => $purchaseContext,
            'product'         => $product,
            'pricing'         => $pricing,
            'subtotal'        => $subtotal,
            'domainPrice'     => $domainPrice,
            'total'           => $total,
            'useCaseMessages' => $this->getUseCaseMessages(),
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
            'payment_method' => 'required|string|in:paypal,stripe,bank_transfer',
        ]);

        $user = Auth::user();

        // Get product and pricing
        $product = Product::with(['pricings.billingCycle'])
            ->where('slug', $purchaseContext['product_slug'])
            ->first();

        if (! $product) {
            return redirect()->route('sales.home')
                ->with('error', 'Producto no encontrado.');
        }

        $pricing = $product->pricings->where('billingCycle.slug', 'monthly')->first();

        if (! $pricing) {
            return redirect()->route('sales.home')
                ->with('error', 'Precio no encontrado para este producto.');
        }

        // Calculate totals
        $subtotal    = $pricing->price;
        $domainPrice = $purchaseContext['domain_price'] ?? 0;
        $total       = $subtotal + $domainPrice;

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
        \App\Models\InvoiceItem::create([
            'invoice_id'  => $invoice->id,
            'description' => $product->name . ' - Plan ' . ucfirst($purchaseContext['plan'] ?? 'professional'),
            'quantity'    => 1,
            'unit_price'  => $subtotal,
            'total_price' => $subtotal,
        ]);

        // Add domain item if applicable
        if ($domainPrice > 0) {
            \App\Models\InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'description' => 'Registro de dominio: ' . $purchaseContext['domain'],
                'quantity'    => 1,
                'unit_price'  => $domainPrice,
                'total_price' => $domainPrice,
            ]);
        }

        // Clear purchase context
        session()->forget(['purchase_context', 'pending_user']);

        // Redirect to invoice payment page
        return redirect()->route('client.invoices.show', $invoice->id)
            ->with('success', '¡Factura creada exitosamente! Procede con el pago.');
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

                return redirect()->route('client.dashboard')
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

            return redirect()->route('client.dashboard')
                ->with('success', '¡Email verificado! Tu factura está lista para pagar.');
        }

        // If no purchase context, redirect to login page
        return redirect()->route('login')
            ->with('success', '¡Email verificado exitosamente! Ahora puedes iniciar sesión.');
    }

    /**
     * Create invoice from purchase context
     */
    private function createInvoiceFromPurchaseContext(User $user, array $purchaseContext): \App\Models\Invoice
    {
        // Get product and pricing
        $product = Product::with(['pricings.billingCycle'])
            ->where('slug', $purchaseContext['product_slug'])
            ->first();

        if (! $product) {
            throw new \Exception('Producto no encontrado: ' . $purchaseContext['product_slug']);
        }

        // Get monthly pricing for now (can be enhanced later)
        $pricing = $product->pricings->where('billingCycle.slug', 'monthly')->first();

        if (! $pricing) {
            throw new \Exception('Precio no encontrado para el producto: ' . $product->name);
        }

        // Calculate totals
        $subtotal    = $pricing->price;
        $domainPrice = $purchaseContext['domain_price'] ?? 0;
        $total       = $subtotal + $domainPrice;

        // Create invoice
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

        // Create invoice items
        \App\Models\InvoiceItem::create([
            'invoice_id'  => $invoice->id,
            'description' => $product->name . ' - Plan ' . ucfirst($purchaseContext['plan'] ?? 'professional'),
            'quantity'    => 1,
            'unit_price'  => $subtotal,
            'total_price' => $subtotal,
        ]);

        // Add domain item if applicable
        if ($domainPrice > 0) {
            \App\Models\InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'description' => 'Registro de dominio: ' . $purchaseContext['domain'],
                'quantity'    => 1,
                'unit_price'  => $domainPrice,
                'total_price' => $domainPrice,
            ]);
        }

        Log::info('📄 FACTURA CREADA desde purchase_context', [
            'user_id'    => $user->id,
            'invoice_id' => $invoice->id,
            'product'    => $product->name,
            'plan'       => $purchaseContext['plan'],
            'domain'     => $purchaseContext['domain'] ?? 'N/A',
            'total'      => $total,
        ]);

        return $invoice;
    }
}

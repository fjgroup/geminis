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

        if (! $purchaseContext) {
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
        if (! $purchaseContext) {
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

            // Send email verification
            $user->sendEmailVerificationNotification();

            // Store user info in session for later login after verification
            session([
                'pending_user' => [
                    'id'               => $user->id,
                    'email'            => $user->email,
                    'purchase_context' => $purchaseContext,
                ],
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
        $pricing = $product->pricings->where('billingCycle.slug', 'mensual')->first();

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
}

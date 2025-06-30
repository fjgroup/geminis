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
    public function showDomainVerification(Request $request): InertiaResponse
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
            'useCaseMessages' => $this->getUseCaseMessages(),
        ]);
    }

    /**
     * Step 2: Process domain verification
     */
    public function processDomainVerification(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'domain' => 'required|string|max:255',
            'action' => 'required|string|in:register,transfer,existing',
        ]);

        $purchaseContext = session('purchase_context');
        if (! $purchaseContext) {
            return redirect()->route('sales.home')
                ->with('error', 'Sesión expirada.');
        }

        // Verify domain availability if registering
        if ($validated['action'] === 'register') {
            $domainCheck = $this->nameSiloService->checkDomainAvailability($validated['domain']);

            if ($domainCheck['status'] !== 'available') {
                return redirect()->back()
                    ->withErrors(['domain' => 'Este dominio no está disponible para registro.'])
                    ->withInput();
            }
        }

        // Store domain info in session
        session([
            'purchase_context' => array_merge($purchaseContext, [
                'domain'        => $validated['domain'],
                'domain_action' => $validated['action'],
                'domain_price'  => $validated['action'] === 'register' ? ($domainCheck['price'] ?? 0) : 0,
            ]),
        ]);

        return redirect()->route('public.checkout.register');
    }

    /**
     * Step 3: User registration
     */
    public function showRegistration(Request $request): InertiaResponse
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
            // Create the user
            $user = User::create([
                'name'          => $validated['name'],
                'email'         => $validated['email'],
                'password'      => Hash::make($validated['password']),
                'role'          => 'client',
                'company_name'  => $validated['company_name'],
                'phone'         => $validated['phone'],
                'country'       => $validated['country'],
                'reseller_id'   => null,
                'status'        => 'active',
                'language_code' => 'es',
                'currency_code' => 'USD',
            ]);

            // Log the user in
            Auth::login($user);

            // Store user ID in purchase context
            session([
                'purchase_context' => array_merge($purchaseContext, [
                    'user_id' => $user->id,
                ]),
            ]);

            // Redirect to payment
            return redirect()->route('public.checkout.payment');

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
    public function showPayment(Request $request): InertiaResponse
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
    public function showRegistrationWithContext(Request $request): InertiaResponse
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
                'name'          => $validated['name'],
                'email'         => $validated['email'],
                'password'      => Hash::make($validated['password']),
                'role'          => 'client',
                'company_name'  => $validated['company_name'],
                'phone'         => $validated['phone'],
                'country'       => $validated['country'],
                'reseller_id'   => null,
                'status'        => 'active',
                'language_code' => 'es',
                'currency_code' => 'USD',
            ]);

            // Log the user in
            Auth::login($user);

            // Keep the sales context for the checkout process
            session(['sales_context' => $salesContext]);

            // Redirect to existing domain selection page
            return redirect()->route('client.checkout.select-domain')
                ->with('success', '¡Cuenta creada exitosamente! Ahora selecciona tu dominio.');

        } catch (\Exception $e) {
            Log::error('Error creating user during sales checkout: ' . $e->getMessage());

            return redirect()->back()
                ->withErrors(['email' => 'Error al crear la cuenta. Por favor, inténtalo de nuevo.'])
                ->withInput();
        }
    }
}

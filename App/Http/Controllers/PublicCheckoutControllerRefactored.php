<?php

namespace App\Http\Controllers;

use App\Http\Requests\DomainVerificationRequest;
use App\Http\Requests\PublicRegistrationRequest;
use App\Http\Requests\PublicPaymentRequest;
use App\Models\Product;
use App\Models\User;
use App\Services\CheckoutService;
use App\Services\ProductService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Class PublicCheckoutControllerRefactored
 * 
 * Controlador refactorizado aplicando el Principio de Responsabilidad Única
 * Solo maneja HTTP requests/responses, delega lógica de negocio a servicios
 */
class PublicCheckoutControllerRefactored extends Controller
{
    public function __construct(
        private CheckoutService $checkoutService,
        private ProductService $productService,
        private UserService $userService
    ) {}

    /**
     * Mostrar verificación de dominio
     */
    public function showDomainVerification(Request $request): InertiaResponse|RedirectResponse
    {
        $purchaseContext = session('purchase_context');

        if (!$purchaseContext) {
            Log::warning('Purchase context perdido en showDomainVerification', [
                'session_id' => session()->getId()
            ]);
            
            return redirect()->route('sales.home')
                ->with('error', 'Sesión expirada. Por favor, selecciona tu plan nuevamente.');
        }

        // Obtener producto con información necesaria
        $product = $this->productService->getProductForCart(
            Product::where('slug', $purchaseContext['product_slug'])->first()?->id
        );

        if (!$product) {
            return redirect()->route('sales.home')
                ->with('error', 'Producto no encontrado.');
        }

        // Preparar datos para la vista
        $viewData = $this->prepareProductViewData($product, $purchaseContext);

        return Inertia::render('PublicCheckout/DomainVerification', $viewData);
    }

    /**
     * Procesar verificación de dominio
     */
    public function processDomainVerification(DomainVerificationRequest $request): RedirectResponse
    {
        $purchaseContext = session('purchase_context');

        if (!$purchaseContext) {
            return redirect()->route('sales.home')
                ->with('error', 'Sesión expirada.');
        }

        $validated = $request->validated();

        // Procesar verificación usando el servicio
        $result = $this->checkoutService->processDomainVerification($validated, $purchaseContext);

        if (!$result['success']) {
            return redirect()->back()
                ->with('error', $result['message'])
                ->withInput();
        }

        // Actualizar contexto en sesión
        session(['purchase_context' => $result['updated_context']]);

        return redirect()->route('public.checkout.register');
    }

    /**
     * Mostrar formulario de registro
     */
    public function showRegistration(Request $request): InertiaResponse|RedirectResponse
    {
        $purchaseContext = session('purchase_context');

        if (!$purchaseContext || !isset($purchaseContext['domain'])) {
            return redirect()->route('public.checkout.domain')
                ->with('error', 'Por favor, verifica tu dominio primero.');
        }

        return Inertia::render('PublicCheckout/Registration', [
            'purchaseContext' => $purchaseContext,
            'useCaseMessages' => $this->getUseCaseMessages($purchaseContext['use_case'] ?? null)
        ]);
    }

    /**
     * Procesar registro de usuario
     */
    public function processRegistration(PublicRegistrationRequest $request): RedirectResponse
    {
        $purchaseContext = session('purchase_context');

        if (!$purchaseContext) {
            return redirect()->route('sales.home')
                ->with('error', 'Sesión expirada.');
        }

        $validated = $request->validated();

        // Crear usuario usando el servicio
        $userResult = $this->checkoutService->createCheckoutUser($validated);

        if (!$userResult['success']) {
            return redirect()->back()
                ->withErrors(['email' => $userResult['message']])
                ->withInput();
        }

        $user = $userResult['user'];

        // Enviar notificación de verificación
        $user->notify(new \App\Notifications\PurchaseEmailVerification());

        // Guardar datos para después de la verificación
        session([
            'pending_user' => [
                'id' => $user->id,
                'email' => $user->email,
                'purchase_context' => $purchaseContext
            ]
        ]);

        Log::info('Usuario creado en checkout público', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);

        return redirect()->route('verification.notice')
            ->with('success', '¡Cuenta creada exitosamente! Revisa tu email para verificar tu cuenta.');
    }

    /**
     * Mostrar página de pago
     */
    public function showPayment(Request $request): InertiaResponse|RedirectResponse
    {
        $purchaseContext = session('purchase_context');

        if (!$purchaseContext || !Auth::check()) {
            return redirect()->route('public.checkout.domain')
                ->with('error', 'Por favor, completa los pasos anteriores.');
        }

        if (!isset($purchaseContext['price_calculation'])) {
            return redirect()->route('public.checkout.domain')
                ->with('error', 'Información de precio perdida.');
        }

        $product = Product::where('slug', $purchaseContext['product_slug'])->first();

        if (!$product) {
            return redirect()->route('sales.home')
                ->with('error', 'Producto no encontrado.');
        }

        // Preparar datos de pago
        $paymentData = $this->preparePaymentData($product, $purchaseContext);

        return Inertia::render('PublicCheckout/Payment', $paymentData);
    }

    /**
     * Procesar pago
     */
    public function processPayment(PublicPaymentRequest $request): RedirectResponse
    {
        $purchaseContext = session('purchase_context');

        if (!$purchaseContext || !Auth::check()) {
            return redirect()->route('public.checkout.domain')
                ->with('error', 'Por favor, completa los pasos anteriores.');
        }

        $validated = $request->validated();
        $user = Auth::user();

        // Procesar pago usando el servicio
        $paymentResult = $this->checkoutService->processPayment(
            $user, 
            $purchaseContext, 
            $validated['payment_method']
        );

        if (!$paymentResult['success']) {
            return redirect()->back()
                ->with('error', $paymentResult['message']);
        }

        $invoice = $paymentResult['invoice'];
        $paymentMethod = $paymentResult['payment_method'];

        // Limpiar contexto de compra
        session()->forget(['purchase_context', 'pending_user']);

        // Redirigir según método de pago
        if ($paymentMethod === 'paypal') {
            return redirect()->route('client.paypal.payment.create', ['invoice' => $invoice->id])
                ->with('success', '¡Factura creada exitosamente! Procede con el pago PayPal.');
        }

        return redirect()->route('client.invoices.show', $invoice->id)
            ->with('success', '¡Factura creada exitosamente! Procede con el pago.');
    }

    /**
     * Verificar email para flujo de compra
     */
    public function verifyPurchaseEmail(Request $request, $id, $hash): RedirectResponse
    {
        $user = User::findOrFail($id);

        // Verificar hash
        $expectedHash = sha1($user->getEmailForVerification());
        if (!hash_equals((string) $hash, $expectedHash)) {
            return redirect()->route('sales.home')
                ->with('error', 'El enlace de verificación no es válido.');
        }

        // Marcar email como verificado si no lo está
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new \Illuminate\Auth\Events\Verified($user));
        }

        // Loguear usuario
        Auth::login($user);

        // Verificar si hay contexto de compra pendiente
        $pendingUser = session('pending_user');
        if ($pendingUser && $pendingUser['id'] == $user->id) {
            // Crear factura automáticamente
            $invoice = $this->checkoutService->createInvoiceFromPurchaseContext(
                $user, 
                $pendingUser['purchase_context']
            );
            
            session()->forget('pending_user');

            return redirect()->route('public.checkout.payment.success', ['invoice' => $invoice->id])
                ->with('success', '¡Email verificado! Tu factura está lista para pagar.');
        }

        return redirect()->route('client.dashboard')
            ->with('success', '¡Email verificado exitosamente!');
    }

    /**
     * Mostrar página de éxito de pago
     */
    public function showPaymentSuccess(Request $request, \App\Models\Invoice $invoice): InertiaResponse|RedirectResponse
    {
        if (!Auth::check() || $invoice->client_id !== Auth::id()) {
            return redirect()->route('login')
                ->with('error', 'No tienes acceso a esta factura.');
        }

        $purchaseContext = $this->reconstructPurchaseContextFromInvoice($invoice);

        return Inertia::render('PublicCheckout/PaymentSuccess', [
            'invoice' => $invoice,
            'purchaseContext' => $purchaseContext
        ]);
    }

    /**
     * Preparar datos del producto para la vista
     */
    private function prepareProductViewData(Product $product, array $purchaseContext): array
    {
        // Esta lógica se puede mover a ProductService en el futuro
        $availableBillingCycles = $product->pricings->map(function ($pricing) use ($product) {
            $totalPrice = $this->productService->calculateTotalPrice(
                $product, 
                [], 
                $pricing->billingCycle->id
            );

            return [
                'id' => $pricing->billingCycle->id,
                'name' => $pricing->billingCycle->name,
                'slug' => $pricing->billingCycle->slug,
                'days' => $pricing->billingCycle->days,
                'base_price' => $pricing->price,
                'price' => $totalPrice,
                'setup_fee' => $pricing->setup_fee,
            ];
        });

        return [
            'purchaseContext' => $purchaseContext,
            'product' => $product,
            'availableBillingCycles' => $availableBillingCycles,
            'useCaseMessages' => $this->getUseCaseMessages($purchaseContext['use_case'] ?? null)
        ];
    }

    /**
     * Preparar datos de pago
     */
    private function preparePaymentData(Product $product, array $purchaseContext): array
    {
        $priceCalculation = $purchaseContext['price_calculation'];
        $subtotal = $priceCalculation['total'];
        $domainPrice = $purchaseContext['domain_price'] ?? 0;
        $total = $subtotal + $domainPrice;

        return [
            'purchaseContext' => $purchaseContext,
            'product' => $product,
            'priceCalculation' => $priceCalculation,
            'subtotal' => $subtotal,
            'domainPrice' => $domainPrice,
            'total' => $total,
            'useCaseMessages' => $this->getUseCaseMessages($purchaseContext['use_case'] ?? null)
        ];
    }

    /**
     * Obtener mensajes específicos por caso de uso
     */
    private function getUseCaseMessages(?string $useCase = null): array
    {
        $messages = [
            'educators' => [
                'domain_suggestion' => 'Ej: miacademia.com, cursosdeingles.com',
                'registration_title' => 'Crea tu cuenta de educador',
                'registration_subtitle' => 'Comienza a enseñar online hoy mismo',
            ],
            'entrepreneurs' => [
                'domain_suggestion' => 'Ej: mitienda.com, ventasonline.com',
                'registration_title' => 'Crea tu cuenta de emprendedor',
                'registration_subtitle' => 'Lanza tu negocio online',
            ],
            'professionals' => [
                'domain_suggestion' => 'Ej: miprofesion.com, consultoria.com',
                'registration_title' => 'Crea tu cuenta profesional',
                'registration_subtitle' => 'Construye tu presencia online',
            ],
            'small-business' => [
                'domain_suggestion' => 'Ej: minegocio.com, empresa.com',
                'registration_title' => 'Crea tu cuenta empresarial',
                'registration_subtitle' => 'Digitaliza tu negocio',
            ],
        ];

        return $useCase ? ($messages[$useCase] ?? []) : $messages;
    }

    /**
     * Reconstruir contexto de compra desde factura
     */
    private function reconstructPurchaseContextFromInvoice(\App\Models\Invoice $invoice): array
    {
        // Lógica simplificada - se puede mejorar
        $notes = $invoice->notes ?? '';
        
        $context = [
            'product_name' => 'Hosting Plan',
            'plan' => 'Professional',
            'domain' => 'N/A',
            'domain_price' => 0,
        ];

        // Extraer información de las notas
        if (preg_match('/Dominio: ([^\s-]+)/', $notes, $matches)) {
            $context['domain'] = $matches[1];
        }

        if (preg_match('/Plan: ([^\s-]+)/', $notes, $matches)) {
            $context['plan'] = $matches[1];
        }

        return $context;
    }
}

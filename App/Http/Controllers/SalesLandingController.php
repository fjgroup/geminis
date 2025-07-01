<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class SalesLandingController extends Controller
{
    /**
     * Load sales landing data from JSON file
     */
    private function loadSalesData(): array
    {
        $jsonPath = public_path('data/sales-landing.json');

        if (! file_exists($jsonPath)) {
            // Fallback data if file doesn't exist
            return [
                'appName'     => 'Fj Group CA',
                'heroSection' => [
                    'title'         => 'Convierte tu Pasión en tu Sitio Web Profesional',
                    'subtitle'      => 'Sin conocimientos técnicos. Sin complicaciones. Solo resultados.',
                    'ctaButtonText' => 'Crear Mi Sitio Web Ahora',
                ],
                'useCases'    => [],
                'pricing'     => ['plans' => []],
                'contactInfo' => [
                    'phone' => '+58 412 8172337',
                    'email' => 'cesarfigueroa@fjgroupca.com',
                ],
            ];
        }

        $jsonContent = file_get_contents($jsonPath);
        return json_decode($jsonContent, true) ?? [];
    }

    /**
     * Display the main sales landing page
     */
    public function showHome(Request $request): InertiaResponse
    {
        $salesData = $this->loadSalesData();

        return Inertia::render('SalesLanding', [
            'salesData'   => $salesData,
            'canLogin'    => Route::has('login'),
            'canRegister' => Route::has('register'),
            'auth'        => [
                'user' => $request->user(),
            ],
        ]);
    }

    /**
     * Display a specific use case landing page
     */
    public function showUseCase(Request $request, string $useCaseSlug): InertiaResponse
    {
        $salesData = $this->loadSalesData();

        // Find the specific use case
        $useCase = collect($salesData['useCases'] ?? [])->firstWhere('id', $useCaseSlug);

        if (! $useCase) {
            abort(404, 'Caso de uso no encontrado');
        }

        return Inertia::render('SalesLanding', [
            'salesData'      => $salesData,
            'focusedUseCase' => $useCase,
            'canLogin'       => Route::has('login'),
            'canRegister'    => Route::has('register'),
            'auth'           => [
                'user' => $request->user(),
            ],
        ]);
    }

    /**
     * Handle the purchase flow initiation
     */
    public function startPurchase(Request $request): \Illuminate\Http\RedirectResponse
    {
        // 🔍 DEBUG: Log al inicio del método startPurchase
        Log::info('🚀 INICIO startPurchase', [
            'request_data' => $request->all(),
            'method'       => $request->method(),
            'url'          => $request->fullUrl(),
            'user_agent'   => $request->userAgent(),
            'session_id'   => session()->getId(),
        ]);

        try {
            $validated = $request->validate([
                'use_case' => 'required|string|in:educators,small-business,entrepreneurs,professionals,technical-resellers,web-designers',
                'plan'     => 'required|string|in:starter,professional,business',
            ]);

            // 🔍 DEBUG: Log después de validación
            Log::info('✅ VALIDACIÓN EXITOSA en startPurchase', [
                'validated_data' => $validated,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // 🔍 DEBUG: Log errores de validación
            Log::error('❌ ERROR DE VALIDACIÓN en startPurchase', [
                'request_data'      => $request->all(),
                'validation_errors' => $e->errors(),
                'session_id'        => session()->getId(),
            ]);

            // Redirigir de vuelta con errores
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            // 🔍 DEBUG: Log cualquier otro error
            Log::error('❌ ERROR GENERAL en startPurchase', [
                'request_data'  => $request->all(),
                'error_message' => $e->getMessage(),
                'error_trace'   => $e->getTraceAsString(),
                'session_id'    => session()->getId(),
            ]);

            // Redirigir de vuelta con error general
            return redirect()->back()->with('error', 'Ocurrió un error al procesar tu solicitud. Por favor, inténtalo de nuevo.');
        }

        // Map plans to products (starter=eco, professional=pro, business=ultra)
        $productMapping = [
            'starter'      => 'hosting-web-eco',   // $10 - Plan básico
            'professional' => 'hosting-web-pro',   // $16 - Plan intermedio
            'business'     => 'hosting-web-ultra', // $22 - Plan avanzado
        ];

        $productSlug = $productMapping[$validated['plan']] ?? 'hosting-web-eco';

        // Store the context in session for the checkout process
        $purchaseContext = [
            'use_case'     => $validated['use_case'],
            'plan'         => $validated['plan'],
            'product_slug' => $productSlug,
            'source'       => 'sales_landing',
            'messages'     => $this->getUseCaseMessages($validated['use_case']),
        ];

        session(['purchase_context' => $purchaseContext]);

        // 🔍 DEBUG: Log cuando se crea el purchase_context
        Log::info('🛒 PURCHASE_CONTEXT CREADO en SalesLandingController', [
            'user_authenticated' => Auth::check(),
            'user_id'            => Auth::id(),
            'purchase_context'   => $purchaseContext,
            'session_id'         => session()->getId(),
            'ip'                 => request()->ip(),
        ]);

        // Check if user is authenticated
        if (Auth::check()) {
            // User is logged in, go directly to existing domain selection
            Log::info('🔍 Usuario autenticado - redirigiendo a selectDomain', [
                'user_id'          => Auth::id(),
                'purchase_context' => $purchaseContext,
            ]);
            return redirect()->route('client.checkout.selectDomain');
        } else {
            // User needs to verify domain first, then register
            Log::info('🔍 Usuario NO autenticado - redirigiendo a domain verification', [
                'purchase_context' => $purchaseContext,
            ]);
            return redirect()->route('public.checkout.domain');
        }
    }

    /**
     * Get contextual messages for a specific use case
     */
    private function getUseCaseMessages(string $useCase): array
    {
        $messages = [
            'educators'      => [
                'welcome_title'    => '¡Bienvenido a tu nueva academia online!',
                'welcome_subtitle' => 'Estás a solo unos pasos de comenzar a enseñar en línea',
                'domain_help'      => 'Elige un nombre fácil de recordar para tus estudiantes',
                'next_steps'       => 'Después de registrar tu dominio, instalaremos Moodle automáticamente',
            ],
            'small-business' => [
                'welcome_title'    => '¡Tu negocio estará online muy pronto!',
                'welcome_subtitle' => 'Vamos a crear tu presencia profesional en internet',
                'domain_help'      => 'Elige el nombre que representará tu negocio online',
                'next_steps'       => 'Instalaremos WordPress y configuraremos tu sitio empresarial',
            ],
            'entrepreneurs'  => [
                'welcome_title'    => '¡Tu tienda online está casi lista!',
                'welcome_subtitle' => 'Prepárate para comenzar a vender en internet',
                'domain_help'      => 'Elige el nombre que tus clientes recordarán',
                'next_steps'       => 'Configuraremos WooCommerce y tu sistema de pagos',
            ],
            'professionals'  => [
                'welcome_title'    => '¡Tu portafolio profesional te espera!',
                'welcome_subtitle' => 'Vamos a crear tu marca personal en internet',
                'domain_help'      => 'Elige un nombre que refleje tu profesionalismo',
                'next_steps'       => 'Crearemos tu portafolio con WordPress optimizado',
            ],
        ];

        return $messages[$useCase] ?? $messages['small-business'];
    }

    /**
     * Show specific page for educators
     */
    public function showEducators(Request $request): InertiaResponse
    {
        return Inertia::render('UseCases/Educators', [
            'canLogin'    => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }

    /**
     * Show specific page for entrepreneurs
     */
    public function showEntrepreneurs(Request $request): InertiaResponse
    {
        return Inertia::render('UseCases/Entrepreneurs', [
            'canLogin'    => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }

    /**
     * Show specific page for professionals
     */
    public function showProfessionals(Request $request): InertiaResponse
    {
        return Inertia::render('UseCases/Professionals', [
            'canLogin'    => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }

    /**
     * Show specific page for small business
     */
    public function showSmallBusiness(Request $request): InertiaResponse
    {
        return Inertia::render('UseCases/SmallBusiness', [
            'canLogin'    => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }

    /**
     * Show specific page for web designers
     */
    public function showWebDesigners(Request $request): InertiaResponse
    {
        return Inertia::render('UseCases/WebDesigners', [
            'canLogin'    => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }

    /**
     * Show specific page for technical resellers
     */
    public function showTechnicalResellers(Request $request): InertiaResponse
    {
        return Inertia::render('UseCases/TechnicalResellers', [
            'canLogin'    => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }
}

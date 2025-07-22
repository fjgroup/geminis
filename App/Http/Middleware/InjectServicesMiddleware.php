<?php

namespace App\Http\Middleware;

use App\Services\CartService;
use App\Services\ImpersonationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class InjectServicesMiddleware
 * 
 * Middleware para inyectar información de servicios en todas las vistas
 * Proporciona datos globales como carrito, impersonación, etc.
 */
class InjectServicesMiddleware
{
    public function __construct(
        private CartService $cartService,
        private ImpersonationService $impersonationService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Solo inyectar en respuestas Inertia
        if (!$request->expectsJson() && !$request->isMethod('GET')) {
            return $next($request);
        }

        // Inyectar datos globales en Inertia
        Inertia::share([
            // Información del usuario autenticado
            'auth' => function () {
                return [
                    'user' => Auth::user() ? [
                        'id' => Auth::user()->id,
                        'name' => Auth::user()->name,
                        'email' => Auth::user()->email,
                        'role' => Auth::user()->role,
                        'status' => Auth::user()->status,
                        'company_name' => Auth::user()->company_name,
                        'avatar_url' => Auth::user()->avatar_url,
                        'email_verified_at' => Auth::user()->email_verified_at,
                        'last_login_at' => Auth::user()->last_login_at,
                    ] : null,
                ];
            },

            // Información del carrito
            'cart' => function () {
                if (!Auth::check()) {
                    return $this->getAnonymousCartInfo();
                }

                return $this->getAuthenticatedCartInfo();
            },

            // Información de impersonación
            'impersonation' => function () {
                return [
                    'is_impersonating' => $this->impersonationService->isImpersonating(),
                    'info' => $this->impersonationService->getImpersonationInfo(),
                ];
            },

            // Configuración global de la aplicación
            'app_config' => function () {
                return [
                    'name' => config('app.name'),
                    'url' => config('app.url'),
                    'timezone' => config('app.timezone'),
                    'locale' => app()->getLocale(),
                    'currency' => [
                        'code' => 'USD',
                        'symbol' => '$',
                        'position' => 'before'
                    ],
                    'features' => [
                        'registration' => true,
                        'email_verification' => true,
                        'password_reset' => true,
                        'two_factor' => false,
                        'api_tokens' => false,
                    ]
                ];
            },

            // Flash messages
            'flash' => function () use ($request) {
                return [
                    'success' => $request->session()->get('success'),
                    'error' => $request->session()->get('error'),
                    'warning' => $request->session()->get('warning'),
                    'info' => $request->session()->get('info'),
                ];
            },

            // Errores de validación
            'errors' => function () use ($request) {
                return $request->session()->get('errors') 
                    ? $request->session()->get('errors')->getBag('default')->getMessages()
                    : (object) [];
            },

            // Información de navegación
            'navigation' => function () use ($request) {
                return [
                    'current_route' => $request->route()?->getName(),
                    'previous_url' => url()->previous(),
                    'intended_url' => session('url.intended'),
                ];
            },

            // Configuración de UI
            'ui_config' => function () {
                return [
                    'theme' => 'light', // Podría venir de configuración del usuario
                    'sidebar_collapsed' => false,
                    'notifications_enabled' => true,
                    'sound_enabled' => false,
                ];
            }
        ]);

        return $next($request);
    }

    /**
     * Obtener información del carrito para usuarios anónimos
     */
    private function getAnonymousCartInfo(): array
    {
        try {
            $cartDetails = $this->cartService->getCartDetails();

            if ($cartDetails['success']) {
                return [
                    'count' => $cartDetails['data']['count'] ?? 0,
                    'total' => $cartDetails['data']['total'] ?? 0,
                    'has_items' => ($cartDetails['data']['count'] ?? 0) > 0,
                    'currency' => 'USD',
                    'is_valid' => $cartDetails['data']['is_valid'] ?? true,
                    'errors' => $cartDetails['data']['integrity_errors'] ?? []
                ];
            }

            return $this->getEmptyCartInfo();

        } catch (\Exception $e) {
            return $this->getEmptyCartInfo();
        }
    }

    /**
     * Obtener información del carrito para usuarios autenticados
     */
    private function getAuthenticatedCartInfo(): array
    {
        try {
            $cartDetails = $this->cartService->getCartDetails();

            if ($cartDetails['success']) {
                return [
                    'count' => $cartDetails['data']['count'] ?? 0,
                    'total' => $cartDetails['data']['total'] ?? 0,
                    'has_items' => ($cartDetails['data']['count'] ?? 0) > 0,
                    'currency' => 'USD',
                    'is_valid' => $cartDetails['data']['is_valid'] ?? true,
                    'errors' => $cartDetails['data']['integrity_errors'] ?? [],
                    'applied_discount' => $cartDetails['data']['applied_discount'] ?? null,
                    'last_updated' => now()->toISOString()
                ];
            }

            return $this->getEmptyCartInfo();

        } catch (\Exception $e) {
            return $this->getEmptyCartInfo();
        }
    }

    /**
     * Obtener información de carrito vacío
     */
    private function getEmptyCartInfo(): array
    {
        return [
            'count' => 0,
            'total' => 0,
            'has_items' => false,
            'currency' => 'USD',
            'is_valid' => true,
            'errors' => []
        ];
    }
}

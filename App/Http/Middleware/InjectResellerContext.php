<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class InjectResellerContext
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Inyectar contexto del usuario en todas las vistas
            Inertia::share([
                'userContext' => [
                    'role' => $user->role,
                    'isReseller' => $user->role === 'reseller',
                    'isAdmin' => $user->role === 'admin',
                    'isClient' => $user->role === 'client',
                    'resellerId' => $user->role === 'reseller' ? $user->id : $user->reseller_id,
                    'companyName' => $user->company_name,
                    'userName' => $user->name,
                ],
                'panelConfig' => [
                    'title' => $this->getPanelTitle($user),
                    'logo' => $this->getPanelLogo($user),
                    'primaryColor' => $this->getPrimaryColor($user),
                    'showResellerFeatures' => $user->role === 'reseller',
                    'showAdminFeatures' => $user->role === 'admin',
                ]
            ]);
        }

        return $next($request);
    }

    /**
     * Get panel title based on user role
     */
    private function getPanelTitle($user): string
    {
        return match($user->role) {
            'admin' => 'Panel de Administración',
            'reseller' => $user->company_name ? "Panel {$user->company_name}" : 'Panel Reseller',
            'client' => 'Mi Panel',
            default => 'Panel'
        };
    }

    /**
     * Get panel logo based on user role
     */
    private function getPanelLogo($user): ?string
    {
        if ($user->role === 'reseller' && $user->logo_url) {
            return $user->logo_url;
        }
        
        return null; // Usar logo por defecto
    }

    /**
     * Get primary color based on user role
     */
    private function getPrimaryColor($user): string
    {
        return match($user->role) {
            'admin' => '#1f2937', // Gray-800
            'reseller' => '#7c3aed', // Purple-600
            'client' => '#2563eb', // Blue-600
            default => '#1f2937'
        };
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdminOrReseller
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar autenticación básica
        if (!Auth::check()) {
            $this->logSecurityEvent('unauthenticated_admin_reseller_access', $request);
            abort(401, 'Authentication required');
        }

        $user = Auth::user();

        // Verificar si el usuario es admin o reseller
        if (!in_array($user->role, ['admin', 'reseller'])) {
            $this->logSecurityEvent('unauthorized_admin_reseller_access', $request, $user);
            abort(403, 'Admin or Reseller access required');
        }

        // Verificar estado del usuario
        if ($user->status !== 'active') {
            $this->logSecurityEvent('inactive_user_admin_reseller_access', $request, $user);
            Auth::logout();
            abort(403, 'Account is not active');
        }

        // Para revendedores, agregar contexto adicional a la sesión
        if ($user->role === 'reseller') {
            // Marcar que es un reseller para personalizar la interfaz
            session(['user_context' => 'reseller']);
            
            // Log del acceso de reseller al panel admin
            Log::info('Reseller accessed admin panel', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);
        } else {
            // Es admin
            session(['user_context' => 'admin']);
        }

        return $next($request);
    }

    /**
     * Log security events
     */
    private function logSecurityEvent(string $event, Request $request, $user = null): void
    {
        $logData = [
            'event' => $event,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'timestamp' => now(),
        ];

        if ($user) {
            $logData['user_id'] = $user->id;
            $logData['user_email'] = $user->email;
            $logData['user_role'] = $user->role;
        }

        Log::warning('Admin/Reseller access security event', $logData);
    }
}

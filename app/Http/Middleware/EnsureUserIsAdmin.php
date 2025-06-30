<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar autenticación básica
        if (! Auth::check()) {
            $this->logSecurityEvent('unauthenticated_admin_access', $request);
            abort(401, 'Authentication required');
        }

        $user = Auth::user();

        // Verificar si el usuario actual es admin
        if ($user->role === 'admin') {
            // Verificar estado del usuario
            if ($user->status !== 'active') {
                $this->logSecurityEvent('inactive_admin_access', $request, $user);
                Auth::logout();
                abort(403, 'Account is not active');
            }

            return $next($request);
        }

        // EXCEPCIÓN DE SEGURIDAD: Permitir stop-impersonation si hay una sesión de impersonation activa
        // Esto es seguro porque:
        // 1. Solo funciona si hay una sesión de impersonation válida
        // 2. Solo permite la acción específica de volver al admin
        // 3. Valida que el admin original existe y tiene permisos
        if ($request->routeIs('admin.stop-impersonation') &&
            session()->has('impersonating_admin_id')) {

            // Verificar que el admin original existe y tiene permisos
            $adminId       = session('impersonating_admin_id');
            $originalAdmin = \App\Models\User::find($adminId);

            if ($originalAdmin && $originalAdmin->role === 'admin' && $originalAdmin->status === 'active') {
                return $next($request);
            }

            // Si el admin original no existe o no tiene permisos, limpiar la sesión
            session()->forget('impersonating_admin_id');
            $this->logSecurityEvent('invalid_impersonation_session', $request, $user);
        }

        // Registrar intento de acceso no autorizado
        $this->logSecurityEvent('unauthorized_admin_access', $request, $user);

        // Si no es admin y no es una excepción válida, denegar acceso
        abort(403, 'Unauthorized action. Admin access required.');
    }

    /**
     * Registrar eventos de seguridad
     */
    private function logSecurityEvent(string $event, Request $request, $user = null): void
    {
        $logData = [
            'event'      => $event,
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url'        => $request->fullUrl(),
            'method'     => $request->method(),
            'timestamp'  => now()->toISOString(),
        ];

        if ($user) {
            $logData['user_id']     = $user->id;
            $logData['user_email']  = $user->email;
            $logData['user_role']   = $user->role;
            $logData['user_status'] = $user->status;
        }

        Log::warning("Admin security event: {$event}", $logData);

        // Rate limiting para intentos sospechosos
        if (in_array($event, ['unauthorized_admin_access', 'unauthenticated_admin_access'])) {
            $key = 'admin_security_violations:' . $request->ip();
            RateLimiter::hit($key, 3600); // 1 hora

            // Si hay muchos intentos, bloquear temporalmente
            if (RateLimiter::attempts($key) > 5) {
                Log::critical('Multiple admin security violations detected', $logData);
            }
        }
    }
}

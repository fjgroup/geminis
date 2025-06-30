<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class AdminSecurityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Verificar autenticación
        if (!Auth::check()) {
            Log::warning('Unauthorized admin access attempt', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
            ]);
            abort(401, 'Authentication required');
        }

        $user = Auth::user();

        // 2. Verificar rol de administrador
        if ($user->role !== 'admin') {
            Log::warning('Non-admin user attempted admin access', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_role' => $user->role,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);
            abort(403, 'Admin access required');
        }

        // 3. Rate limiting específico para admin
        $key = 'admin_actions:' . $user->id . ':' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 100)) { // 100 requests per minute
            Log::warning('Admin rate limit exceeded', [
                'user_id' => $user->id,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);
            abort(429, 'Too many requests');
        }
        RateLimiter::hit($key, 60); // 1 minute decay

        // 4. Validar integridad de la sesión
        if (!$this->validateSessionIntegrity($request, $user)) {
            Log::error('Session integrity validation failed', [
                'user_id' => $user->id,
                'ip' => $request->ip(),
            ]);
            Auth::logout();
            abort(401, 'Session integrity compromised');
        }

        // 5. Detectar patrones sospechosos
        $this->detectSuspiciousActivity($request, $user);

        // 6. Sanitizar entrada para operaciones críticas
        if ($this->isCriticalOperation($request)) {
            $this->sanitizeInput($request);
        }

        // 7. Logging de acciones administrativas
        $this->logAdminAction($request, $user);

        return $next($request);
    }

    /**
     * Validar integridad de la sesión
     */
    private function validateSessionIntegrity(Request $request, $user): bool
    {
        // Verificar que el user agent no haya cambiado drásticamente
        $sessionUserAgent = session('admin_user_agent');
        $currentUserAgent = $request->userAgent();

        if ($sessionUserAgent && $sessionUserAgent !== $currentUserAgent) {
            // Permitir cambios menores pero detectar cambios significativos
            $similarity = similar_text($sessionUserAgent, $currentUserAgent, $percent);
            if ($percent < 80) {
                return false;
            }
        } else {
            session(['admin_user_agent' => $currentUserAgent]);
        }

        // Verificar timestamp de última actividad
        $lastActivity = session('admin_last_activity');
        $now = time();
        
        if ($lastActivity && ($now - $lastActivity) > 7200) { // 2 horas
            return false;
        }
        
        session(['admin_last_activity' => $now]);

        return true;
    }

    /**
     * Detectar actividad sospechosa
     */
    private function detectSuspiciousActivity(Request $request, $user): void
    {
        $suspiciousPatterns = [
            // Intentos de inyección SQL
            '/(\bUNION\b|\bSELECT\b|\bINSERT\b|\bDELETE\b|\bDROP\b)/i',
            // Intentos de XSS
            '/<script[^>]*>.*?<\/script>/i',
            // Intentos de path traversal
            '/\.\.\/|\.\.\\\\/',
            // Intentos de command injection
            '/(\b(exec|system|shell_exec|passthru|eval)\b)/i',
        ];

        $input = json_encode($request->all());
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                Log::critical('Suspicious admin activity detected', [
                    'user_id' => $user->id,
                    'pattern' => $pattern,
                    'input' => $input,
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                
                // Opcional: bloquear temporalmente al usuario
                RateLimiter::hit('suspicious_admin:' . $user->id, 3600); // 1 hora
                break;
            }
        }
    }

    /**
     * Determinar si es una operación crítica
     */
    private function isCriticalOperation(Request $request): bool
    {
        $criticalRoutes = [
            'admin.users.store',
            'admin.users.update',
            'admin.users.destroy',
            'admin.products.store',
            'admin.products.update',
            'admin.products.destroy',
        ];

        return in_array($request->route()?->getName(), $criticalRoutes) ||
               in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE']);
    }

    /**
     * Sanitizar entrada para operaciones críticas
     */
    private function sanitizeInput(Request $request): void
    {
        $input = $request->all();
        
        array_walk_recursive($input, function (&$value) {
            if (is_string($value)) {
                // Remover caracteres potencialmente peligrosos
                $value = preg_replace('/[<>"\']/', '', $value);
                // Escapar caracteres especiales
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        });

        $request->merge($input);
    }

    /**
     * Registrar acciones administrativas
     */
    private function logAdminAction(Request $request, $user): void
    {
        // Solo loggear operaciones importantes
        if ($this->isCriticalOperation($request)) {
            Log::info('Admin action performed', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'action' => $request->route()?->getName(),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toISOString(),
            ]);
        }
    }
}

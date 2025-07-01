<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ResellerSecurityMiddleware
{
    /**
     * Handle an incoming request.
     * Middleware de seguridad específico para resellers
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'reseller') {
            $this->logSecurityEvent('unauthorized_reseller_access', $request);
            abort(403, 'Acceso denegado. Se requiere rol de reseller.');
        }

        // Log de todas las acciones de resellers para auditoría
        $this->logResellerActivity($request, $user);

        // Verificar intentos sospechosos
        $this->detectSuspiciousActivity($request, $user);

        // Verificar acceso a recursos permitidos
        $this->validateResourceAccess($request, $user);

        return $next($request);
    }

    /**
     * Log de actividad de resellers
     */
    private function logResellerActivity(Request $request, $user): void
    {
        Log::info('Reseller Activity', [
            'reseller_id' => $user->id,
            'reseller_email' => $user->email,
            'reseller_company' => $user->company_name,
            'action' => $request->method() . ' ' . $request->path(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now(),
            'request_data' => $this->sanitizeRequestData($request),
        ]);
    }

    /**
     * Detectar actividad sospechosa
     */
    private function detectSuspiciousActivity(Request $request, $user): void
    {
        $suspiciousPatterns = [
            // Intentos de acceso a rutas de admin
            '/admin/' => 'admin_route_access_attempt',
            // Intentos de manipular IDs de otros resellers
            'reseller_id' => 'reseller_id_manipulation',
            // Intentos de acceso a datos de otros usuarios
            'user_id' => 'user_id_manipulation',
            // Intentos de SQL injection
            'union select' => 'sql_injection_attempt',
            'drop table' => 'sql_injection_attempt',
            // Intentos de XSS
            '<script' => 'xss_attempt',
            'javascript:' => 'xss_attempt',
        ];

        $requestData = json_encode($request->all());
        $url = $request->fullUrl();

        foreach ($suspiciousPatterns as $pattern => $eventType) {
            if (stripos($url, $pattern) !== false || stripos($requestData, $pattern) !== false) {
                $this->logSecurityEvent($eventType, $request, $user, [
                    'pattern_detected' => $pattern,
                    'severity' => 'HIGH',
                ]);
                
                // Para patrones críticos, bloquear inmediatamente
                if (in_array($eventType, ['sql_injection_attempt', 'admin_route_access_attempt'])) {
                    abort(403, 'Actividad sospechosa detectada. Acceso bloqueado.');
                }
            }
        }
    }

    /**
     * Validar acceso a recursos
     */
    private function validateResourceAccess(Request $request, $user): void
    {
        // Verificar que el reseller solo acceda a sus propios recursos
        $routeName = $request->route()->getName();
        
        // Para rutas que involucran usuarios
        if (str_contains($routeName, 'users.') && $request->route('user')) {
            $targetUser = $request->route('user');
            
            if ($targetUser->reseller_id !== $user->id && $targetUser->id !== $user->id) {
                $this->logSecurityEvent('unauthorized_user_access', $request, $user, [
                    'target_user_id' => $targetUser->id,
                    'target_reseller_id' => $targetUser->reseller_id,
                    'severity' => 'HIGH',
                ]);
                
                abort(403, 'No tienes permisos para acceder a este usuario.');
            }
        }

        // Para rutas que involucran productos
        if (str_contains($routeName, 'products.') && $request->route('product')) {
            $product = $request->route('product');
            
            // Resellers solo pueden acceder a productos públicos o sus propios productos
            if ($product->owner_id && $product->owner_id !== $user->id) {
                $this->logSecurityEvent('unauthorized_product_access', $request, $user, [
                    'product_id' => $product->id,
                    'product_owner_id' => $product->owner_id,
                    'severity' => 'MEDIUM',
                ]);
                
                abort(403, 'No tienes permisos para acceder a este producto.');
            }
        }
    }

    /**
     * Log de eventos de seguridad
     */
    private function logSecurityEvent(string $eventType, Request $request, $user = null, array $extra = []): void
    {
        Log::warning('Reseller Security Event', array_merge([
            'event_type' => $eventType,
            'reseller_id' => $user?->id,
            'reseller_email' => $user?->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'timestamp' => now(),
        ], $extra));
    }

    /**
     * Sanitizar datos de request para logging
     */
    private function sanitizeRequestData(Request $request): array
    {
        $data = $request->all();
        
        // Remover campos sensibles
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'api_key'];
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }
        
        return $data;
    }
}

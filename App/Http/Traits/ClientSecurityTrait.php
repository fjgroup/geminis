<?php

namespace App\Http\Traits;

use App\Models\ClientService;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

trait ClientSecurityTrait
{
    /**
     * Verificar que el usuario autenticado es propietario del servicio
     */
    protected function ensureUserOwnsService(ClientService $service): void
    {
        $user = Auth::user();
        
        if (!$user || $user->id !== $service->client_id) {
            Log::warning('Intento de acceso no autorizado a servicio', [
                'user_id' => $user?->id,
                'service_id' => $service->id,
                'service_client_id' => $service->client_id,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
            
            abort(403, 'No tienes permiso para acceder a este servicio.');
        }
    }

    /**
     * Verificar que el usuario autenticado es propietario de la factura
     */
    protected function ensureUserOwnsInvoice(Invoice $invoice): void
    {
        $user = Auth::user();
        
        if (!$user || $user->id !== $invoice->client_id) {
            Log::warning('Intento de acceso no autorizado a factura', [
                'user_id' => $user?->id,
                'invoice_id' => $invoice->id,
                'invoice_client_id' => $invoice->client_id,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
            
            abort(403, 'No tienes permiso para acceder a esta factura.');
        }
    }

    /**
     * Aplicar rate limiting para operaciones sensibles
     */
    protected function applyRateLimit(string $operation, int $maxAttempts = 10, int $decayMinutes = 1): void
    {
        $key = $this->getRateLimitKey($operation);
        
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            
            Log::warning('Rate limit excedido', [
                'operation' => $operation,
                'user_id' => Auth::id(),
                'ip' => request()->ip(),
                'seconds_until_available' => $seconds
            ]);
            
            abort(429, "Demasiados intentos. Intenta de nuevo en {$seconds} segundos.");
        }

        RateLimiter::hit($key, $decayMinutes * 60);
    }

    /**
     * Generar clave única para rate limiting
     */
    private function getRateLimitKey(string $operation): string
    {
        $user = Auth::user();
        return sprintf(
            'client_operation:%s:%s:%s',
            $operation,
            $user ? $user->id : 'guest',
            request()->ip()
        );
    }

    /**
     * Validar y sanitizar datos de entrada
     */
    protected function sanitizeInput(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Remover caracteres peligrosos
                $value = strip_tags($value);
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                $value = trim($value);
            }
            
            $sanitized[$key] = $value;
        }
        
        return $sanitized;
    }

    /**
     * Verificar integridad de precios en el carrito
     */
    protected function validateCartPriceIntegrity(array $cart): bool
    {
        foreach ($cart['accounts'] ?? [] as $account) {
            // Verificar dominio
            if (isset($account['domain_info']['override_price'])) {
                $price = $account['domain_info']['override_price'];
                if (!is_numeric($price) || $price < 0 || $price > 9999.99) {
                    Log::warning('Precio de dominio inválido detectado en carrito', [
                        'user_id' => Auth::id(),
                        'price' => $price,
                        'domain' => $account['domain_info']['domain_name'] ?? 'unknown'
                    ]);
                    return false;
                }
            }

            // Verificar servicio principal
            if (isset($account['primary_service']['price'])) {
                $price = $account['primary_service']['price'];
                if (!is_numeric($price) || $price < 0 || $price > 99999.99) {
                    Log::warning('Precio de servicio inválido detectado en carrito', [
                        'user_id' => Auth::id(),
                        'price' => $price,
                        'service' => $account['primary_service']['product_name'] ?? 'unknown'
                    ]);
                    return false;
                }
            }
        }
        
        return true;
    }

    /**
     * Registrar actividad del usuario para auditoría
     */
    protected function logUserActivity(string $action, array $context = []): void
    {
        Log::info('Actividad del cliente', [
            'action' => $action,
            'user_id' => Auth::id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now(),
            'context' => $context
        ]);
    }

    /**
     * Verificar que el usuario puede realizar la acción basada en el estado de su cuenta
     */
    protected function ensureUserCanPerformAction(): void
    {
        $user = Auth::user();
        
        if (!$user) {
            abort(401, 'Usuario no autenticado.');
        }

        if ($user->status !== 'active') {
            Log::warning('Usuario inactivo intentó realizar acción', [
                'user_id' => $user->id,
                'status' => $user->status,
                'action' => request()->route()?->getActionName()
            ]);
            
            abort(403, 'Tu cuenta está inactiva. Contacta al soporte.');
        }
    }
}

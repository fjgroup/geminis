<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CartRateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $maxAttempts = '30', string $decayMinutes = '1'): Response
    {
        $key = $this->resolveRequestSignature($request);
        
        if (RateLimiter::tooManyAttempts($key, (int) $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            
            Log::warning('Rate limit excedido para operaciones de carrito', [
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
                'route' => $request->route()?->getName(),
                'seconds_until_available' => $seconds,
                'max_attempts' => $maxAttempts,
                'decay_minutes' => $decayMinutes
            ]);
            
            return response()->json([
                'message' => "Demasiadas operaciones de carrito. Intenta de nuevo en {$seconds} segundos.",
                'retry_after' => $seconds
            ], 429);
        }

        RateLimiter::hit($key, (int) $decayMinutes * 60);

        $response = $next($request);

        // Agregar headers de rate limiting
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', max(0, (int) $maxAttempts - RateLimiter::attempts($key)));

        return $response;
    }

    /**
     * Resolve the request signature for rate limiting.
     */
    protected function resolveRequestSignature(Request $request): string
    {
        $user = Auth::user();
        
        return sprintf(
            'cart_operations:%s:%s:%s',
            $user ? $user->id : 'guest',
            $request->ip(),
            $request->route()?->getName() ?? 'unknown'
        );
    }
}

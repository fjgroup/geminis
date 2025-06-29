<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsClient
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar que el usuario esté autenticado
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Por favor, inicia sesión para continuar.');
        }

        $user = Auth::user();
        
        // Verificar que el usuario tenga rol de cliente o reseller
        // Los resellers pueden actuar como clientes de la plataforma
        if (!in_array($user->role, ['client', 'reseller'])) {
            abort(403, 'Acceso no autorizado. Se requiere rol de cliente.');
        }

        // Verificar que la cuenta esté activa
        if ($user->status !== 'active') {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Tu cuenta está inactiva. Contacta al soporte.');
        }

        return $next($request);
    }
}

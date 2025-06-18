<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsReseller
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // auth()->user() aquí ya debería estar disponible si 'auth' middleware se ejecutó antes.
        if (!Auth::check() || Auth::user()->role !== 'reseller') {
            // O redirigir a donde consideres apropiado, ej: su dashboard o un error de no autorizado
            return redirect('/dashboard')->with('error', 'No tienes permisos de revendedor.');
        }
        return $next($request);
    }
}

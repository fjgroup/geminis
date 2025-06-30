<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        // Verificar si el usuario actual es admin
        if (Auth::check() && Auth::user()->role === 'admin') {
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

            if ($originalAdmin && $originalAdmin->role === 'admin') {
                return $next($request);
            }

            // Si el admin original no existe o no tiene permisos, limpiar la sesión
            session()->forget('impersonating_admin_id');
        }

        // Si no es admin y no es una excepción válida, denegar acceso
        abort(403, 'Unauthorized action. Admin access required.');
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;



class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()  && Auth::user()->role === 'admin') {
            return $next($request);
        }

        // Si no es admin, redirigir o abortar.
        // Opción 1: Redirigir al dashboard general (si existe) o a la home.
        // return redirect('/dashboard')->with('error', 'You do not have admin access.');
        // Opción 2: Abortar con un error 403 Forbidden.
        abort(403, 'Unauthorized action. Admin access required.');
    }
}

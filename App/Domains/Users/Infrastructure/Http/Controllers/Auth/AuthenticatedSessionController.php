<?php

namespace App\Domains\Users\Infrastructure\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Domains\Users\Infrastructure\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador para sesiones autenticadas en arquitectura hexagonal
 * 
 * Ubicado en Infrastructure layer como adaptador de entrada HTTP
 * Maneja autenticación de usuarios siguiendo principios SOLID
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status'           => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // La lógica de autenticación debería estar en un Use Case
        // Por ahora mantenemos la lógica existente para compatibilidad
        $credentials = $request->getCredentials();
        
        if (!Auth::attempt($credentials, $request->shouldRemember())) {
            return back()->withErrors([
                'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->role === 'admin') {
            return redirect(route('admin.dashboard', absolute: false));
        } elseif ($user->role === 'client') {
            // Verificar si el email está verificado
            if (! is_null($user->email_verified_at)) {
                // Check if user has pending purchase context
                $pendingUser = session('pending_user');
                if ($pendingUser && $pendingUser['id'] == $user->id) {
                    // Restore purchase context and redirect to payment
                    session(['purchase_context' => $pendingUser['purchase_context']]);
                    session()->forget('pending_user');
                    return redirect()->route('public.checkout.payment')
                        ->with('success', '¡Bienvenido de vuelta! Continuando con tu compra.');
                }
                
                return redirect(route('client.dashboard', absolute: false));
            } else {
                // Redirect to email verification
                return redirect(route('verification.notice', absolute: false));
            }
        } elseif ($user->role === 'reseller') {
            return redirect(route('reseller.dashboard', absolute: false));
        }

        // Default redirect
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}

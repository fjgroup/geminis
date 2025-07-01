<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

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
        $request->authenticate();

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
                // Redirigir a verificación de email
                return redirect(route('verification.notice'));
            }
        } elseif ($user->role === 'reseller') {
            return redirect(route('reseller.dashboard', absolute: false));
        } else {
            return redirect()->intended(route('sales.home', absolute: false));
        }
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

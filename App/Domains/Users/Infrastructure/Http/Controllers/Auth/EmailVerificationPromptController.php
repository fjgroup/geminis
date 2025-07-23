<?php
namespace App\Domains\Users\Infrastructure\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse | Response
    {
        if ($request->user()->hasVerifiedEmail()) {
            // Redirigir según el rol del usuario
            $user = $request->user();
            if ($user->role === 'admin') {
                return redirect()->intended(route('admin.dashboard', absolute: false));
            } elseif ($user->role === 'client') {
                return redirect()->intended(route('client.dashboard', absolute: false));
            } elseif ($user->role === 'reseller') {
                return redirect()->intended(route('reseller.dashboard', absolute: false));
            } else {
                return redirect()->intended(route('sales.home', absolute: false));
            }
        }

        return Inertia::render('Auth/VerifyEmailCustom', ['status' => session('status')]);
    }
}

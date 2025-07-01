<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            // Check if user has pending purchase context
            $pendingUser = session('pending_user');
            if ($pendingUser && $pendingUser['id'] == $request->user()->id) {
                // Restore purchase context and redirect to payment
                session(['purchase_context' => $pendingUser['purchase_context']]);
                session()->forget('pending_user');
                return redirect()->route('public.checkout.payment')
                    ->with('success', '¡Email verificado exitosamente! Ahora puedes continuar con tu compra.');
            }

            return redirect()->intended(route('dashboard', absolute: false) . '?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        // Check if user has pending purchase context
        $pendingUser = session('pending_user');

        // 🔍 DEBUG: Log en verificación de email (usuario ya verificado)
        Log::info('🔍 EMAIL YA VERIFICADO - verificando pending_user', [
            'user_id'           => $request->user()->id,
            'has_pending_user'  => ! ! $pendingUser,
            'pending_user_data' => $pendingUser,
            'session_id'        => session()->getId(),
        ]);

        if ($pendingUser && $pendingUser['id'] == $request->user()->id) {
            // Restore purchase context and redirect to payment
            session(['purchase_context' => $pendingUser['purchase_context']]);
            session()->forget('pending_user');

            // 🔍 DEBUG: Log cuando se restaura purchase_context
            Log::info('✅ PURCHASE_CONTEXT RESTAURADO después de verificación', [
                'user_id'                   => $request->user()->id,
                'restored_purchase_context' => $pendingUser['purchase_context'],
                'session_id'                => session()->getId(),
            ]);

            return redirect()->route('public.checkout.payment')
                ->with('success', '¡Email verificado exitosamente! Ahora puedes continuar con tu compra.');
        }

        return redirect()->intended(route('dashboard', absolute: false) . '?verified=1');
    }
}

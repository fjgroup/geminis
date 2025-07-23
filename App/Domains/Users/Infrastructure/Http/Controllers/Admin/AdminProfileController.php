<?php
namespace App\Domains\Users\Infrastructure\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AdminProfileController extends Controller
{
    /**
     * Display the admin's or reseller's profile form.
     */
    public function edit(Request $request): Response
    {
        // Ensure user is admin or reseller
        if (! in_array($request->user()->role, ['admin', 'reseller'])) {
            abort(403, 'Access denied. Admin or Reseller role required.');
        }

        Log::info('Admin accessing profile page', [
            'user_id'    => Auth::id(),
            'user_email' => Auth::user()->email,
        ]);

        return Inertia::render('Admin/Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status'          => session('status'),
            'auth'            => [
                'user' => $request->user(),
            ],
        ]);
    }

    /**
     * Update the admin's or reseller's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Ensure user is admin or reseller
        if (! in_array($user->role, ['admin', 'reseller'])) {
            abort(403, 'Access denied. Admin or Reseller role required.');
        }

        $validated = $request->validated();

        // Handle company logo upload
        if ($request->hasFile('company_logo')) {
            // Delete old logo if exists
            if ($user->company_logo) {
                Storage::disk('public')->delete($user->company_logo);
            }

            // Store new logo
            $logoPath                  = $request->file('company_logo')->store('company-logos', 'public');
            $validated['company_logo'] = $logoPath;
        }

        // Handle password change
        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);

            Log::info('Admin password updated', [
                'user_id'    => $user->id,
                'user_email' => $user->email,
            ]);
        } else {
            unset($validated['password']);
        }

        // Remove current_password from validated data
        unset($validated['current_password']);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;

            Log::info('Admin email changed - verification required', [
                'user_id'   => $user->id,
                'old_email' => $user->getOriginal('email'),
                'new_email' => $user->email,
            ]);
        }

        $user->save();

        Log::info('Admin profile updated', [
            'user_id'        => $user->id,
            'user_email'     => $user->email,
            'updated_fields' => array_keys($validated),
        ]);

        // Redirect based on user role
        $route   = $user->role === 'admin' ? 'admin.profile.edit' : 'reseller.profile.edit';
        $message = $user->role === 'admin' ? 'Perfil de administrador actualizado exitosamente.' : 'Perfil de reseller actualizado exitosamente.';

        return Redirect::route($route)->with('success', $message);
    }

    /**
     * Delete the admin's account.
     * Note: This is typically not allowed for admin accounts for security reasons.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // For security, we might want to disable this for admin accounts
        // or require additional verification

        $user = $request->user();

        if (! in_array($user->role, ['admin', 'reseller'])) {
            abort(403, 'Access denied. Admin or Reseller role required.');
        }

        // Additional security check - prevent deletion if this is the only admin
        $adminCount = \App\Models\User::where('role', 'admin')->where('status', 'active')->count();

        if ($adminCount <= 1) {
            return Redirect::back()->withErrors([
                'password' => 'No se puede eliminar la cuenta. Debe haber al menos un administrador activo en el sistema.',
            ]);
        }

        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        Log::warning('Admin account deleted', [
            'user_id'    => $user->id,
            'user_email' => $user->email,
            'deleted_by' => $user->id,
        ]);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/admin/login')->with('success', 'Cuenta de administrador eliminada exitosamente.');
    }
}

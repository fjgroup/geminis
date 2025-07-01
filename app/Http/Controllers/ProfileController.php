<?php
namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     * Redirect admin/reseller to their specific profile pages.
     */
    public function edit(Request $request): Response | RedirectResponse
    {
        $user = $request->user();

        // Redirect admin and reseller to their specific profile pages
        if ($user->role === 'admin') {
            return Redirect::route('admin.profile.edit');
        }

        if ($user->role === 'reseller') {
            return Redirect::route('reseller.profile.edit');
        }

        // For regular clients, show the normal profile page
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status'          => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     * Redirect admin/reseller to their specific profile pages.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Redirect admin and reseller to their specific profile pages
        if ($user->role === 'admin') {
            return Redirect::route('admin.profile.edit');
        }

        if ($user->role === 'reseller') {
            return Redirect::route('reseller.profile.edit');
        }
        $user      = $request->user();
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
        } else {
            unset($validated['password']);
        }

        // Remove current_password from validated data
        unset($validated['current_password']);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('success', 'Perfil actualizado exitosamente.');
    }

    /**
     * Delete the user's account.
     * Redirect admin/reseller to their specific profile pages.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Redirect admin and reseller to their specific profile pages
        if ($user->role === 'admin') {
            return Redirect::route('admin.profile.edit');
        }

        if ($user->role === 'reseller') {
            return Redirect::route('reseller.profile.edit');
        }
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}

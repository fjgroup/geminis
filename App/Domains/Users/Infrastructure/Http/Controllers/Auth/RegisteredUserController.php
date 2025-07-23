<?php

namespace App\Domains\Users\Infrastructure\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Domains\Users\Infrastructure\Persistence\Models\User;
use App\Domains\Users\Infrastructure\Http\Requests\PublicRegistrationRequest;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador para registro de usuarios en arquitectura hexagonal
 * 
 * Ubicado en Infrastructure layer como adaptador de entrada HTTP
 * Maneja registro de usuarios siguiendo principios SOLID
 */
class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(PublicRegistrationRequest $request): RedirectResponse
    {
        // La lógica de registro debería estar en un Use Case
        // Por ahora mantenemos la lógica existente para compatibilidad
        $registrationData = $request->getRegistrationData();
        
        $user = User::create([
            'name' => $registrationData['name'],
            'email' => $registrationData['email'],
            'password' => Hash::make($registrationData['password']),
            'role' => 'client', // Por defecto los registros públicos son clientes
            'reseller_id' => null, // Clientes registrados directamente en la plataforma
            'status' => 'active', // Estado por defecto
            'language_code' => config('app.locale', 'es'), // Idioma por defecto de la app
            'currency_code' => 'USD', // Moneda por defecto
            'company_name' => $registrationData['company_name'] ?? null,
            'phone' => $registrationData['phone'] ?? null,
            'country' => $registrationData['country'] ?? null,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('client.dashboard', absolute: false));
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

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
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'client', // Por defecto los registros públicos son clientes
            // 'reseller_id' => null, // Clientes registrados directamente en la plataforma no se asignan a un reseller específico.
                               // Serán gestionados por el Admin. Los clientes de resellers se crearán desde el panel del reseller.
            'reseller_id' => null,
            'status' => 'active', // Estado por defecto
            'language_code' => config('app.locale', 'es'), // Idioma por defecto de la app
            'currency_code' => 'USD', // Moneda por defecto
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('client.dashboard', absolute: false));
    }
}

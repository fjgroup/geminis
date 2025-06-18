<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reseller\StoreClientByResellerRequest; // Asegúrate que el namespace sea correcto
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia; // Si vas a usar Inertia para las vistas del revendedor

class ResellerClientController extends Controller
{
    /**
     * Display a listing of the clients for the reseller.
     */
    public function index()
    {
        // Solo mostrar clientes que pertenecen al revendedor autenticado
        $clients = User::where('role', 'client')
                        ->where('reseller_id', Auth::id())
                        ->latest()
                        ->paginate(10);

        // Ejemplo si usas Inertia para el panel de revendedor
        // return Inertia::render('Reseller/Clients/Index', compact('clients'));

        // O una vista Blade tradicional
        // return view('reseller.clients.index', compact('clients'));

        // Por ahora, solo para asegurar que la ruta funciona si está protegida:
        return response()->json($clients);
    }

    /**
     * Show the form for creating a new client by the reseller.
     */
    public function create()
    {
        // Ejemplo si usas Inertia
        // return Inertia::render('Reseller/Clients/Create');

        // O una vista Blade tradicional
        // return view('reseller.clients.create');

        return response('Formulario para crear cliente por revendedor');
    }

    /**
     * Store a newly created client in storage by the reseller.
     */
    public function store(StoreClientByResellerRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();

        $client = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => 'client', // Los revendedores solo crean clientes
            'reseller_id' => Auth::id(), // ID del revendedor autenticado
            'company_name' => $validatedData['company_name'] ?? null,
            'phone_number' => $validatedData['phone_number'] ?? null,
            'address_line1' => $validatedData['address_line1'] ?? null,
            'address_line2' => $validatedData['address_line2'] ?? null,
            'city' => $validatedData['city'] ?? null,
            'state_province' => $validatedData['state_province'] ?? null,
            'postal_code' => $validatedData['postal_code'] ?? null,
            'country' => $validatedData['country'] ?? null,
            'status' => $validatedData['status'] ?? 'active', // Valor por defecto si no se envía
            'language_code' => $validatedData['language_code'] ?? Auth::user()->language_code ?? 'es', // Heredar del revendedor o default
            'currency_code' => $validatedData['currency_code'] ?? Auth::user()->currency_code ?? 'USD', // Heredar del revendedor o default
        ]);

        // Lógica adicional si es necesaria, como enviar una notificación.

        // Ajusta la ruta según cómo tengas nombradas las rutas del panel de revendedor
        // return redirect()->route('reseller.clients.index')->with('success', 'Cliente creado exitosamente.');
        return back()->with('success', 'Cliente creado exitosamente.'); // O redirigir atrás
    }

    // Aquí podrías añadir métodos para edit, update, show, destroy para los clientes del revendedor
}

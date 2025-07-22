<?php

namespace App\Domains\Users\Infrastructure\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reseller\StoreClientByResellerRequest;
use App\Domains\Users\Models\User;
use App\Domains\Users\Services\UserCreator;
use App\Domains\Users\DataTransferObjects\CreateUserDTO;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ResellerClientController extends Controller
{
    public function __construct(
        private UserCreator $userCreator
    ) {}
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
        try {
            $validatedData = $request->validated();

            // Crear DTO para el nuevo cliente
            $dto = CreateUserDTO::fromResellerClientRequest($validatedData, Auth::id());

            // Crear cliente usando el servicio especializado
            $result = $this->userCreator->createResellerClient($dto, Auth::id());

            if (!$result['success']) {
                Log::warning('Error al crear cliente por reseller', [
                    'reseller_id' => Auth::id(),
                    'errors' => $result['errors'],
                    'message' => $result['message']
                ]);

                return redirect()->back()
                                ->withErrors($result['errors'])
                                ->withInput()
                                ->with('error', $result['message']);
            }

            Log::info('Cliente creado exitosamente por reseller', [
                'reseller_id' => Auth::id(),
                'client_id' => $result['data']->id,
                'client_email' => $result['data']->email
            ]);

            return back()->with('success', 'Cliente creado exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error inesperado al crear cliente por reseller', [
                'reseller_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error interno al crear el cliente. Inténtelo de nuevo.');
        }
    }

    // Aquí podrías añadir métodos para edit, update, show, destroy para los clientes del revendedor
}

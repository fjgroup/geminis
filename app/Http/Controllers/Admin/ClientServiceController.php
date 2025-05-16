<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller; // Asegúrate que Controller está importado
use App\Http\Requests\Admin\StoreClientServiceRequest; // Importar el FormRequest
use App\Models\Product;
use App\Models\User;
use App\Models\ClientService;
use Illuminate\Http\Request;
use Inertia\Inertia; // Importar Inertia
use Inertia\Response; // Importar Response

class ClientServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // TODO: Implementar autorización, ej:
        //$this->authorize('viewAny', ClientService::class);

        $clientServices = ClientService::with(['client:id,name', 'product:id,name', 'reseller:id,name'])
            ->latest('id') // O el orden que prefieras, ej: 'next_due_date'
            ->paginate(10)
            ->through(fn ($service) => [
                'id' => $service->id,
                'client_name' => $service->client->name,
                'product_name' => $service->product->name,
                'domain_name' => $service->domain_name,
                'status' => $service->status,
                'next_due_date_formatted' => $service->next_due_date->format('d/m/Y'),
                'billing_amount' => $service->billing_amount,
                'reseller_name' => $service->reseller ? $service->reseller->name : 'N/A (Plataforma)',
            ]);

        return Inertia::render('Admin/ClientServices/Index', [
            'clientServices' => $clientServices,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        // TODO: Implementar autorización, ej: $this->authorize('create', ClientService::class);

        $clients = User::where('role', 'client')->orderBy('name')->get(['id', 'name']);
        $products = Product::where('status', 'active')->orderBy('name')->get(['id', 'name']); // Solo productos activos
        // Nota: Los ProductPricings se cargarán dinámicamente en el formulario o se pasarán todos
        // y se filtrarán en el frontend, o se pasarán asociados al producto seleccionado.
        // Por simplicidad inicial, podríamos pasar todos los activos.
        // $productPricings = \App\Models\ProductPricing::where('is_active', true)->get(['id', 'billing_cycle', 'price', 'product_id']);

        $resellers = User::where('role', 'reseller')->orderBy('name')->get(['id', 'name']);
        // $servers = \App\Models\Server::orderBy('name')->get(['id', 'name']); // Cuando exista el modelo Server

        return Inertia::render('Admin/ClientServices/Create', [
            'clients' => $clients->map(fn($user) => ['value' => $user->id, 'label' => $user->name]),
            'products' => $products->map(fn($product) => ['value' => $product->id, 'label' => $product->name]),
            // 'productPricings' => $productPricings, // Considerar cómo manejar esto
            'resellers' => $resellers->map(fn($user) => ['value' => $user->id, 'label' => $user->name]),
            // 'servers' => $servers->map(fn($server) => ['value' => $server->id, 'label' => $server->name]),
            'statusOptions' => ClientService::getPossibleEnumValues('status'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientServiceRequest $request)
    {
        
        // La autorización ya se maneja en StoreClientServiceRequest
        // TODO: Si ClientServicePolicy está implementada, puedes añadir: $this->authorize('create', ClientService::class);

        ClientService::create($request->validated());

        return redirect()->route('admin.client-services.index')
            ->with('success', 'Servicio de cliente creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ClientService $clientService)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClientService $clientService)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ClientService $clientService)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClientService $clientService)
    {
        //
    }
}

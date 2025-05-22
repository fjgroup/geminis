<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller; // Asegúrate que Controller está importado
use App\Http\Requests\Admin\StoreClientServiceRequest; // Importar el FormRequest
use App\Http\Requests\Admin\UpdateClientServiceRequest; // Importar el FormRequest de actualización

use App\Models\Product;
use App\Models\User;
use App\Models\ClientService;
use App\Models\BillingCycle; // Importar BillingCycle
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia; // Importar Inertia
use Inertia\Response; // Importar Response


class ClientServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response // Inyectar Request
    {
        // TODO: Implementar autorización, ej: $this->authorize('viewAny', ClientService::class);

        $query = ClientService::with(['client:id,name', 'product:id,name', 'reseller:id,name', 'billingCycle:id,name']); // Cargar billingCycle

        // Aplicar búsqueda si el parámetro 'search' está presente
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('domain_name', 'LIKE', "%{$searchTerm}%")
                  ->orWhereHas('client', fn($qr) => $qr->where('name', 'LIKE', "%{$searchTerm}%"))
                  ->orWhereHas('product', fn($qr) => $qr->where('name', 'LIKE', "%{$searchTerm}%"));
            });
        }

        $clientServices = $query->latest('id') // O el orden que prefieras

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
                'billing_cycle_name' => $service->billingCycle ? $service->billingCycle->name : 'N/A', // Añadir billing_cycle_name
            ]);
// Pasar los filtros actuales a la vista para que el input de búsqueda pueda mantener su valor

        return Inertia::render('Admin/ClientServices/Index', [
            'clientServices' => $clientServices,
            'filters' => $request->only(['search']), // Pasa los filtros a la vista

        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        // TODO: Implementar autorización, ej: $this->authorize('create', ClientService::class);

        $clients = User::where('role', 'client')->orderBy('name')->get(['id', 'name']);
        $products = Product::with(['pricings.billingCycle'])->where('status', 'active')->orderBy('name')->get(['id', 'name']); // Solo productos activos, con sus precios, cargando pricings y su billingCycle
        // Nota: Los ProductPricings se cargarán dinámicamente en el formulario o se pasarán todos
        // y se filtrarán en el frontend, o se pasarán asociados al producto seleccionado.
        // Por simplicidad inicial, podríamos pasar todos los activos.
        // $productPricings = \App\Models\ProductPricing::where('is_active', true)->get(['id', 'billing_cycle', 'price', 'product_id']);

        $resellers = User::where('role', 'reseller')->orderBy('name')->get(['id', 'name']);
        // $servers = \App\Models\Server::orderBy('name')->get(['id', 'name']); // Cuando exista el modelo Server

        $billingCycles = BillingCycle::all(); // Obtener todos los BillingCycle

        return Inertia::render('Admin/ClientServices/Create',
        [
            'clients' => $clients->map(fn($user) => ['value' => $user->id, 'label' => $user->name]),

            // Formatear productos para que usen 'value' y 'label', pero manteniendo 'pricings'
            'products' => $products->map(fn($product) => ['value' => $product->id, 'label' => $product->name, 'pricings' => $product->pricings]),


            // 'productPricings' => $productPricings, // Considerar cómo manejar esto
            'resellers' => $resellers->map(fn($user) => ['value' => $user->id, 'label' => $user->name]),

            // 'servers' => $servers->map(fn($server) => ['value' => $server->id, 'label' => $server->name]),

            'statusOptions' => ClientService::getPossibleEnumValues('status'),

            'billingCycles' => $billingCycles, // Pasar billingCycles a la vista
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientServiceRequest $request)
    {

        // La autorización ya se maneja en StoreClientServiceRequest
        // TODO: Si ClientServicePolicy está implementada, puedes añadir: $this->authorize('create', ClientService::class);

        // Asumiendo que StoreClientServiceRequest ya valida billing_cycle_id
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
    public function edit(ClientService $clientService): Response
    {
        // TODO: Implementar autorización, ej: $this->authorize('update', $clientService);

        // Cargar las relaciones necesarias si no están ya cargadas o si quieres asegurarte
        // $clientService->loadMissing(['client', 'product', 'reseller', 'server']);

        $clients = User::where('role', 'client')->orderBy('name')->get(['id', 'name']);

        // Asegurarse de cargar pricings y billingCycle para la lista de productos, similar a create()
        $products = Product::with(['pricings.billingCycle']) // Cargar pricings y sus billingCycle
                            ->where('status', 'active')
                            ->orWhere('id', $clientService->product_id) // Incluir el producto actual aunque no esté activo
                            ->orderBy('name')->get(); // Obtener todas las columnas para que las relaciones funcionen



        $resellers = User::where('role', 'reseller')->orderBy('name')->get(['id', 'name']);
        // $servers = \App\Models\Server::orderBy('name')->get(['id', 'name']); // Cuando exista

        // Formatear fechas para los inputs de tipo 'date'
        // Los casts 'date' en el modelo ya convierten estos a objetos Carbon.
        $clientService->registration_date_formatted = $clientService->registration_date ? $clientService->registration_date->format('Y-m-d') : null;
        $clientService->next_due_date_formatted = $clientService->next_due_date ? $clientService->next_due_date->format('Y-m-d') : null;
        $clientService->termination_date_formatted = $clientService->termination_date ? $clientService->termination_date->format('Y-m-d') : null;


        $clientService->load([
            'client', // ¡Asegúrate de que esta relación esté aquí!
            'productPricing',
            'product.pricings', // Esto carga el producto y luego sus pricings
            'billingCycle'
        ]); // Cargar billingCycle

        $billingCycles = BillingCycle::all(); // Obtener todos los BillingCycle

        return Inertia::render('Admin/ClientServices/Edit', [
            'clientService' => $clientService,
            // Pasamos los datos para los selectores, similar al método create
            'clients' => $clients->map(fn($user) => ['value' => $user->id, 'label' => $user->name]),

            // Formatear productos para que usen 'value' y 'label', pero manteniendo 'pricings'
            'products' => $products->map(fn($product) => ['value' => $product->id, 'label' => $product->name, 'pricings' => $product->pricings]),


            'resellers' => $resellers->map(fn($user) => ['value' => $user->id, 'label' => $user->name]),

            // 'servers' => $servers->map(fn($server) => ['value' => $server->id, 'label' => $server->name]),
            'statusOptions' => ClientService::getPossibleEnumValues('status'),
            'billingCycles' => $billingCycles, // Pasar billingCycles a la vista
            // 'productPricings' se cargarán dinámicamente en el formulario _Form.vue
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClientServiceRequest $request, ClientService $clientService)
    {
        // La autorización ya se maneja en UpdateClientServiceRequest
        // TODO: Si ClientServicePolicy está implementada, puedes añadir: $this->authorize('update', $clientService);

        $validatedData = $request->validated();

        // Si password_encrypted no está en los datos validados (porque era null o no se envió),
        // no se intentará actualizar. El cast 'encrypted' en el modelo maneja la encriptación
        // si el campo está presente y no es null.
        if (array_key_exists('password_encrypted', $validatedData) && $validatedData['password_encrypted'] === null) {
            // Si se envió explícitamente null (ej. para borrarla, si la lógica lo permite)
            // o si el campo no se envió y queremos asegurar que se ponga a null en la BD.
            $clientService->password_encrypted = null; // Esto activará el cast a 'encrypted' con null
            unset($validatedData['password_encrypted']); // Quitarlo para que no se pase en el update masivo si ya lo manejamos
        }

        // Asumiendo que UpdateClientServiceRequest ya valida billing_cycle_id
        $clientService->update($validatedData);

        return redirect()->route('admin.client-services.index')
            ->with('success', 'Servicio de cliente actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClientService $clientService): RedirectResponse
    {
        // TODO: Implementar autorización, ej: $this->authorize('delete', $clientService);

        $clientService->delete(); // Realiza un borrado suave (SoftDelete) porque el modelo ClientService usa el trait SoftDeletes.
                                 // Para un borrado permanente, se usaría: $clientService->forceDelete();

        return redirect()->route('admin.client-services.index')
            ->with('success', 'Servicio de cliente eliminado exitosamente.');
    }
    /**
     * Get pricings for a given product.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductPricings(Product $product): JsonResponse
    {
        return response()->json($product->pricings);
    }
}

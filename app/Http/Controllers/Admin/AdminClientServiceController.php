<?php

/**
 * ⚠️ DEPRECATED - MARCADO PARA ELIMINACIÓN
 *
 * Este controlador ha sido refactorizado y reemplazado por:
 * - AdminClientServiceControllerRefactored (manejo HTTP)
 * - ClientServiceManagementService (lógica de negocio)
 * - ImpersonationService (impersonación)
 *
 * TODO: Eliminar este archivo después de migrar completamente las rutas
 * Fecha de refactorización: 2025-01-22
 * Reemplazado por: AdminClientServiceControllerRefactored
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;                    // Asegúrate que Controller está importado
use App\Http\Requests\Admin\StoreClientServiceRequest;  // Importar el FormRequest
use App\Http\Requests\Admin\UpdateClientServiceRequest; // Importar el FormRequest de actualización

use App\Jobs\ProvisionClientServiceJob;
use App\Models\BillingCycle;
use App\Models\ClientService;
use App\Models\PaymentMethod; // Importar BillingCycle
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse; // Importar Inertia
use Illuminate\Http\Request;          // Importar Response
use Illuminate\Support\Facades\Auth;  // Added
use Illuminate\Support\Facades\Gate;  // Added
use Illuminate\Support\Facades\Hash;  // Added, though likely already available via Controller
use Illuminate\Support\Facades\Log;   // Added for auth()->user()
use Inertia\Inertia;                  // Import PaymentMethod
use Inertia\Response;

// Import Hash facade for password hashing

class AdminClientServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response// Inyectar Request
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
            ->through(fn($service) => [
                'id'                      => $service->id,
                'client_name'             => $service->client->name,
                'product_name'            => $service->product->name,
                'domain_name'             => $service->domain_name,
                'status'                  => $service->status,
                'next_due_date_formatted' => $service->next_due_date->format('d/m/Y'),
                'billing_amount'          => $service->billing_amount,
                'reseller_name'           => $service->reseller ? $service->reseller->name : 'N/A (Plataforma)',
                'billing_cycle_name'      => $service->billingCycle ? $service->billingCycle->name : 'N/A', // Añadir billing_cycle_name
            ]);
// Pasar los filtros actuales a la vista para que el input de búsqueda pueda mantener su valor

        return Inertia::render('Admin/ClientServices/Index', [
            'clientServices' => $clientServices,
            'filters'        => $request->only(['search']), // Pasa los filtros a la vista

        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        // TODO: Implementar autorización, ej: $this->authorize('create', ClientService::class);

        $clients  = User::where('role', 'client')->orderBy('name')->get(['id', 'name']);
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
                'clients'       => $clients->map(fn($user) => ['value' => $user->id, 'label' => $user->name]),

                // Formatear productos para que usen 'value' y 'label', pero manteniendo 'pricings'
                'products'      => $products->map(fn($product) => ['value' => $product->id, 'label' => $product->name, 'pricings' => $product->pricings]),

                // 'productPricings' => $productPricings, // Considerar cómo manejar esto
                'resellers'     => $resellers->map(fn($user) => ['value' => $user->id, 'label' => $user->name]),

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
    public function show(ClientService $clientService): Response
    {
        $clientService->load([
            'client',
            'product.productType',
            'productPricing.billingCycle',
            'reseller',
            // 'server', // Comentado hasta que se implemente el modelo Server
        ]);

        return Inertia::render('Admin/ClientServices/Show', [
            'clientService' => [
                'id'              => $clientService->id,
                'domain_name'     => $clientService->domain_name,
                'status'          => $clientService->status,
                'next_due_date'   => $clientService->next_due_date,
                'created_at'      => $clientService->created_at,
                'updated_at'      => $clientService->updated_at,
                'client'          => $clientService->client,
                'product'         => $clientService->product,
                'product_pricing' => $clientService->productPricing,
                'reseller'        => $clientService->reseller,
                // 'server'          => $clientService->server, // Comentado hasta que se implemente el modelo Server
            ],
        ]);
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
            ->orderBy('name')->get();                   // Obtener todas las columnas para que las relaciones funcionen

        $resellers = User::where('role', 'reseller')->orderBy('name')->get(['id', 'name']);
        // $servers = \App\Models\Server::orderBy('name')->get(['id', 'name']); // Cuando exista

        // Formatear fechas para los inputs de tipo 'date'
        // Los casts 'date' en el modelo ya convierten estos a objetos Carbon.
        $clientService->registration_date_formatted = $clientService->registration_date ? $clientService->registration_date->format('Y-m-d') : null;
        $clientService->next_due_date_formatted     = $clientService->next_due_date ? $clientService->next_due_date->format('Y-m-d') : null;
        $clientService->termination_date_formatted  = $clientService->termination_date ? $clientService->termination_date->format('Y-m-d') : null;

        $clientService->load([
            'client', // ¡Asegúrate de que esta relación esté aquí!
            'productPricing',
            'product.pricings', // Esto carga el producto y luego sus pricings
            'billingCycle',
        ]); // Cargar billingCycle

        $billingCycles = BillingCycle::all(); // Obtener todos los BillingCycle

        // Cargar métodos de pago activos para el formulario de confirmación manual
        $paymentMethods = \App\Models\PaymentMethod::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/ClientServices/Edit', [
            'clientService'  => [
                'id'                 => $clientService->id,
                'client_id'          => $clientService->client_id,
                'product_id'         => $clientService->product_id,
                'product_pricing_id' => $clientService->product_pricing_id,
                'billing_cycle_id'   => $clientService->billing_cycle_id,
                'reseller_id'        => $clientService->reseller_id,
                'domain_name'        => $clientService->domain_name,
                'status'             => $clientService->status,
                'registration_date'  => $clientService->registration_date_formatted,
                'next_due_date'      => $clientService->next_due_date_formatted,
                'termination_date'   => $clientService->termination_date_formatted,
                'notes'              => $clientService->notes,
                'client'             => $clientService->client,
                'product'            => $clientService->product,
                'product_pricing'    => $clientService->productPricing,
                'billing_cycle'      => $clientService->billingCycle,
            ],
            // Pasamos los datos para los selectores, similar al método create
            'clients'        => $clients->map(fn($user) => ['value' => $user->id, 'label' => $user->name]),

            // Formatear productos para que usen 'value' y 'label', pero manteniendo 'pricings'
            'products'       => $products->map(fn($product) => ['value' => $product->id, 'label' => $product->name, 'pricings' => $product->pricings]),

            'resellers'      => $resellers->map(fn($user) => ['value' => $user->id, 'label' => $user->name]),

            // 'servers' => $servers->map(fn($server) => ['value' => $server->id, 'label' => $server->name]),
            'statusOptions'  => ClientService::getPossibleEnumValues('status'),
            'billingCycles'  => $billingCycles,                                                                          // Pasar billingCycles a la vista
            'paymentMethods' => $paymentMethods->map(fn($method) => ['value' => $method->id, 'label' => $method->name]), // Pasar métodos de pago formateados
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

        // Manejo de la contraseña
        if (isset($validatedData['password_encrypted']) && $validatedData['password_encrypted'] !== null) {
            // Si se proporcionó una nueva contraseña y no es null (después de prepareForValidation),
            // hashearla y asignarla directamente al modelo.
            $clientService->password_encrypted = Hash::make($validatedData['password_encrypted']);
            // Quitarla de $validatedData para que no se intente guardar sin hashear por el update() masivo.
            unset($validatedData['password_encrypted']);
        } elseif (isset($validatedData['password_encrypted']) && $validatedData['password_encrypted'] === null) {
            // Si explícitamente se envió null (campo vacío en el formulario que prepareForValidation convirtió a null),
            // y la intención es borrar la contraseña o establecerla a null.
            // Considerar si realmente se quiere permitir establecer la contraseña a null.
            // Por ahora, replicamos la lógica anterior de setear a null si es explícitamente null.
            $clientService->password_encrypted = null;
            unset($validatedData['password_encrypted']);
        }
        // Si 'password_encrypted' no está en $validatedData (porque no se envió en el form o se envió vacío
        // y prepareForValidation no lo puso a null sino que lo eliminó, o si la validación falló para ese campo),
        // entonces no se toca $clientService->password_encrypted aquí, y la contraseña existente no se modifica.

        // Actualizar el resto de los datos validados.
        $clientService->update($validatedData);

        // Si se modificó password_encrypted explícitamente (hasheado o a null),
        // es necesario guardar el modelo $clientService si update() no lo hace con los cambios directos.
        // Sin embargo, update() actualiza y guarda. Si $clientService->password_encrypted se asignó antes,
        // y luego $clientService->update($validatedData) se llama (sin password_encrypted en $validatedData),
        // el cambio a password_encrypted podría no persistir si update() no considera atributos ya "sucios".
        // Una forma más segura es guardar explícitamente después de modificar el atributo password_encrypted
        // y antes de la redirección si es necesario, o asegurar que el update masivo no lo sobreescriba.

        // Refinamiento:
        // La lógica anterior con unset y luego update() es correcta.
        // Si $clientService->password_encrypted fue modificado,
        // $clientService->update($validatedData) actualizará los otros campos
        // y los cambios en $clientService (como password_encrypted) también se persistirán
        // porque el modelo ya está "sucio" con ese cambio.

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

    /**
     * Retry provisioning for a client service.
     *
     * @param ClientService $clientService
     * @return RedirectResponse
     */
    public function retryProvisioning(ClientService $clientService): RedirectResponse
    {
        Gate::authorize('update', $clientService); // Or a more specific policy action like 'retryProvisioning'

        if ($clientService->status !== 'provisioning_failed') {
            return redirect()->route('admin.client-services.edit', $clientService->id)
                ->with('error', 'El servicio no está en estado de fallo de aprovisionamiento.');
        }

        // Load the orderItem and its necessary nested relations for the job
        // Ensure these relations are defined in the respective models.
        $clientService->loadMissing([
            'orderItem.order.client',
            'orderItem.product.productType',
            'orderItem.productPricing.billingCycle',
        ]);

        if (! $clientService->orderItem) {
            Log::error("AdminClientServiceController: No se encontró OrderItem para ClientService ID: {$clientService->id} durante el reintento de aprovisionamiento.");
            return redirect()->route('admin.client-services.edit', $clientService->id)
                ->with('error', 'No se pudo encontrar el ítem de orden asociado para reintentar el aprovisionamiento.');
        }

        // Check if all required nested relations for the job are loaded on orderItem
        if (! $clientService->orderItem->order ||
            ! $clientService->orderItem->order->client ||
            ! $clientService->orderItem->product ||
            ! $clientService->orderItem->product->productType ||
            ! $clientService->orderItem->productPricing ||
            ! $clientService->orderItem->productPricing->billingCycle
        ) {
            Log::error("AdminClientServiceController: Faltan relaciones necesarias en OrderItem ID: {$clientService->orderItem->id} para el reintento de aprovisionamiento de ClientService ID: {$clientService->id}.",
                [
                    'order_loaded'          => ! ! $clientService->orderItem->order,
                    'client_loaded'         => ! ! ($clientService->orderItem->order && $clientService->orderItem->order->client),
                    'product_loaded'        => ! ! $clientService->orderItem->product,
                    'productType_loaded'    => ! ! ($clientService->orderItem->product && $clientService->orderItem->product->productType),
                    'productPricing_loaded' => ! ! $clientService->orderItem->productPricing,
                    'billingCycle_loaded'   => ! ! ($clientService->orderItem->productPricing && $clientService->orderItem->productPricing->billingCycle),
                ]
            );
            return redirect()->route('admin.client-services.edit', $clientService->id)
                ->with('error', 'Faltan datos relacionados con el ítem de orden. No se puede reintentar el aprovisionamiento.');
        }

                                                          // Optionally, update status to indicate a retry is in progress
        $clientService->status = 'pending_configuration'; // Reset to a state where provisioning can be attempted
        $clientService->notes  = ($clientService->notes ? $clientService->notes . "\n" : '') . "Reintento de aprovisionamiento iniciado por admin (" . Auth::user()->name . ") el " . now()->toDateTimeString() . ".";
        $clientService->save();

        ProvisionClientServiceJob::dispatch($clientService->orderItem);

        Log::info("AdminClientServiceController: Reintento de aprovisionamiento despachado para ClientService ID: {$clientService->id} vía OrderItem ID: {$clientService->orderItem->id}.");

        return redirect()->route('admin.client-services.edit', $clientService->id)
            ->with('success', 'Se ha encolado el reintento de aprovisionamiento para el servicio.');
    }

    /**
     * Permitir al admin ingresar al panel del cliente (impersonation)
     */
    public function impersonateClient(ClientService $clientService): RedirectResponse
    {
        // Verificar que el usuario actual es admin
        if (! Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'No tienes permisos para realizar esta acción.');
        }

        // Obtener el cliente del servicio
        $client = $clientService->client;

        if (! $client) {
            return redirect()->back()->with('error', 'No se pudo encontrar el cliente asociado a este servicio.');
        }

        // TODO: En el futuro, validar que si el admin es un reseller,
        // solo pueda acceder a clientes de su propiedad
        // if (Auth::user()->role === 'reseller') {
        //     if ($client->reseller_id !== Auth::id()) {
        //         abort(403, 'No tienes permisos para acceder al panel de este cliente.');
        //     }
        // }

        // Guardar el ID del admin original en la sesión para poder volver
        session(['impersonating_admin_id' => Auth::id()]);

        // Hacer login como el cliente
        Auth::login($client);

        // Log de la acción para auditoría
        Log::info('Admin impersonation started', [
            'admin_id'     => session('impersonating_admin_id'),
            'admin_email'  => User::find(session('impersonating_admin_id'))->email,
            'client_id'    => $client->id,
            'client_email' => $client->email,
            'service_id'   => $clientService->id,
            'timestamp'    => now(),
        ]);

        return redirect()->route('client.dashboard')
            ->with('success', 'Has ingresado al panel del cliente. Puedes volver al panel de admin cuando termines.');
    }

    /**
     * Volver al panel de admin desde impersonation
     */
    public function stopImpersonation(Request $request): RedirectResponse
    {
        // Verificar que hay una sesión de impersonation activa
        if (! session()->has('impersonating_admin_id')) {
            return redirect()->route('client.dashboard')
                ->with('error', 'No hay una sesión de impersonation activa.');
        }

        $adminId       = session('impersonating_admin_id');
        $currentClient = Auth::user();

        // Buscar el admin original
        $admin = User::find($adminId);

        if (! $admin || $admin->role !== 'admin') {
            // Limpiar la sesión y redirigir al login
            session()->forget('impersonating_admin_id');
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'No se pudo encontrar el administrador original. Por favor, inicia sesión nuevamente.');
        }

        // Log de la acción para auditoría
        Log::info('Admin impersonation ended', [
            'admin_id'     => $adminId,
            'admin_email'  => $admin->email,
            'client_id'    => $currentClient->id,
            'client_email' => $currentClient->email,
            'timestamp'    => now(),
        ]);

        // Limpiar la sesión de impersonation
        session()->forget('impersonating_admin_id');

        // Hacer login como el admin original
        Auth::login($admin);

        return redirect()->route('admin.client-services.index')
            ->with('success', 'Has vuelto al panel de administración.');
    }
}

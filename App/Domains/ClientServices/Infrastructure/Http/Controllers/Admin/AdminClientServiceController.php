<?php

namespace App\Domains\ClientServices\Infrastructure\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\ClientServices\Infrastructure\Http\Requests\Admin\StoreClientServiceRequest;
use App\Domains\ClientServices\Infrastructure\Http\Requests\Admin\UpdateClientServiceRequest;
use App\Domains\ClientServices\Infrastructure\Persistence\Models\ClientService;
use App\Domains\Products\Infrastructure\Persistence\Models\Product;
use App\Domains\ClientServices\Services\ClientServiceManagementService;
use App\Domains\Users\Services\ImpersonationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador de servicios de clientes para administradores en arquitectura hexagonal
 *
 * Aplicando el Principio de Responsabilidad Única:
 * - Solo maneja HTTP requests/responses
 * - Delega lógica de negocio a servicios
 * - Ubicado en Infrastructure layer como Input Adapter
 */
class AdminClientServiceController extends Controller
{
    public function __construct(
        private ClientServiceManagementService $clientServiceService,
        private ImpersonationService $impersonationService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        // TODO: Implementar autorización
        // $this->authorize('viewAny', ClientService::class);

        $filters = $request->only(['search', 'status', 'client_id', 'product_id']);
        $result = $this->clientServiceService->getClientServices($filters, 10);

        if (!$result['success']) {
            Log::error('Error obteniendo servicios de clientes', [
                'filters' => $filters,
                'error' => $result['message']
            ]);
            
            // En caso de error, mostrar página vacía
            $result['data'] = collect()->paginate(10);
        }

        return Inertia::render('Admin/ClientServices/Index', [
            'clientServices' => $result['data'],
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        // TODO: Implementar autorización
        // $this->authorize('create', ClientService::class);

        $formData = $this->clientServiceService->getFormData();

        return Inertia::render('Admin/ClientServices/Create', $formData);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientServiceRequest $request): RedirectResponse
    {
        $result = $this->clientServiceService->createClientService($request->validated());

        if ($result['success']) {
            return redirect()->route('admin.client-services.index')
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->withErrors(['error' => $result['message']])
            ->withInput();
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
        ]);

        return Inertia::render('Admin/ClientServices/Show', [
            'clientService' => [
                'id' => $clientService->id,
                'domain_name' => $clientService->domain_name,
                'status' => $clientService->status,
                'next_due_date' => $clientService->next_due_date,
                'created_at' => $clientService->created_at,
                'updated_at' => $clientService->updated_at,
                'client' => $clientService->client,
                'product' => $clientService->product,
                'product_pricing' => $clientService->productPricing,
                'reseller' => $clientService->reseller,
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClientService $clientService): Response
    {
        // TODO: Implementar autorización
        // $this->authorize('update', $clientService);

        $clientService->load([
            'client',
            'productPricing',
            'product.pricings',
            'billingCycle',
        ]);

        // Formatear fechas para los inputs
        $clientService->registration_date_formatted = $clientService->registration_date?->format('Y-m-d');
        $clientService->next_due_date_formatted = $clientService->next_due_date?->format('Y-m-d');
        $clientService->termination_date_formatted = $clientService->termination_date?->format('Y-m-d');

        $formData = $this->clientServiceService->getFormData();

        return Inertia::render('Admin/ClientServices/Edit', [
            'clientService' => [
                'id' => $clientService->id,
                'client_id' => $clientService->client_id,
                'product_id' => $clientService->product_id,
                'product_pricing_id' => $clientService->product_pricing_id,
                'billing_cycle_id' => $clientService->billing_cycle_id,
                'reseller_id' => $clientService->reseller_id,
                'domain_name' => $clientService->domain_name,
                'status' => $clientService->status,
                'registration_date' => $clientService->registration_date_formatted,
                'next_due_date' => $clientService->next_due_date_formatted,
                'termination_date' => $clientService->termination_date_formatted,
                'notes' => $clientService->notes,
                'client' => $clientService->client,
                'product' => $clientService->product,
                'product_pricing' => $clientService->productPricing,
                'billing_cycle' => $clientService->billingCycle,
            ],
            ...$formData
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClientServiceRequest $request, ClientService $clientService): RedirectResponse
    {
        $result = $this->clientServiceService->updateClientService($clientService, $request->validated());

        if ($result['success']) {
            return redirect()->route('admin.client-services.index')
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->withErrors(['error' => $result['message']])
            ->withInput();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClientService $clientService): RedirectResponse
    {
        // TODO: Implementar autorización
        // $this->authorize('delete', $clientService);

        $result = $this->clientServiceService->deleteClientService($clientService);

        if ($result['success']) {
            return redirect()->route('admin.client-services.index')
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->withErrors(['error' => $result['message']]);
    }

    /**
     * Get pricings for a given product.
     */
    public function getProductPricings(Product $product): JsonResponse
    {
        $pricings = $this->clientServiceService->getProductPricings($product);

        return response()->json($pricings);
    }

    /**
     * Retry provisioning for a client service.
     */
    public function retryProvisioning(ClientService $clientService): RedirectResponse
    {
        $result = $this->clientServiceService->retryProvisioning($clientService);

        if ($result['success']) {
            return redirect()->route('admin.client-services.edit', $clientService->id)
                ->with('success', $result['message']);
        }

        return redirect()->route('admin.client-services.edit', $clientService->id)
            ->with('error', $result['message']);
    }

    /**
     * Permitir al admin ingresar al panel del cliente (impersonation)
     */
    public function impersonateClient(ClientService $clientService): RedirectResponse
    {
        $client = $clientService->client;

        if (!$client) {
            return redirect()->back()
                ->with('error', 'No se pudo encontrar el cliente asociado a este servicio.');
        }

        $result = $this->impersonationService->impersonateClient($client);

        if ($result['success']) {
            return redirect()->route('client.dashboard')
                ->with('success', 'Has ingresado al panel del cliente. Puedes volver al panel de admin cuando termines.');
        }

        return redirect()->back()
            ->with('error', $result['message']);
    }

    /**
     * Volver al panel de admin desde impersonation
     */
    public function stopImpersonation(Request $request): RedirectResponse
    {
        $result = $this->impersonationService->stopImpersonation();

        if ($result['success']) {
            return redirect()->route('admin.client-services.index')
                ->with('success', $result['message']);
        }

        if (isset($result['force_logout']) && $result['force_logout']) {
            return redirect()->route('login')
                ->with('error', $result['message']);
        }

        return redirect()->route('client.dashboard')
            ->with('error', $result['message']);
    }

    /**
     * Obtener información de impersonación activa (para el frontend)
     */
    public function getImpersonationInfo(): JsonResponse
    {
        $info = $this->impersonationService->getImpersonationInfo();

        return response()->json([
            'is_impersonating' => $this->impersonationService->isImpersonating(),
            'impersonation_info' => $info
        ]);
    }

    /**
     * Buscar clientes para autocompletado
     */
    public function searchClients(Request $request): JsonResponse
    {
        $search = $request->input('search', '');
        
        if (strlen($search) < 2) {
            return response()->json([]);
        }

        try {
            $clients = \App\Models\User::where('role', 'client')
                ->where(function ($query) use ($search) {
                    $query->where('name', 'LIKE', "%{$search}%")
                          ->orWhere('email', 'LIKE', "%{$search}%")
                          ->orWhere('company_name', 'LIKE', "%{$search}%");
                })
                ->limit(10)
                ->get(['id', 'name', 'email', 'company_name'])
                ->map(function ($client) {
                    return [
                        'value' => $client->id,
                        'label' => $client->name . ($client->company_name ? " ({$client->company_name})" : " ({$client->email})")
                    ];
                });

            return response()->json($clients);

        } catch (\Exception $e) {
            Log::error('Error buscando clientes', [
                'error' => $e->getMessage(),
                'search' => $search
            ]);

            return response()->json([]);
        }
    }

    /**
     * Obtener estadísticas de servicios
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                'total_services' => ClientService::count(),
                'active_services' => ClientService::where('status', 'active')->count(),
                'pending_services' => ClientService::where('status', 'pending')->count(),
                'failed_services' => ClientService::where('status', 'provisioning_failed')->count(),
                'suspended_services' => ClientService::where('status', 'suspended')->count(),
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas de servicios', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'total_services' => 0,
                'active_services' => 0,
                'pending_services' => 0,
                'failed_services' => 0,
                'suspended_services' => 0,
            ]);
        }
    }
}

<?php

namespace App\Domains\ClientServices\Infrastructure\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\ClientServices\Application\UseCases\CreateClientServiceUseCase;
use App\Domains\ClientServices\Application\Commands\CreateClientServiceCommand;
use App\Domains\ClientServices\Infrastructure\Http\Requests\StoreClientServiceRequest;
use App\Domains\ClientServices\Infrastructure\Http\Requests\UpdateClientServiceRequest;
use App\Domains\ClientServices\Infrastructure\Persistence\Models\ClientService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador hexagonal para gestión de servicios de cliente
 */
class ClientServiceController extends Controller
{
    public function __construct(
        private CreateClientServiceUseCase $createClientServiceUseCase
    ) {}

    /**
     * Display a listing of client services
     */
    public function index(Request $request): Response
    {
        $services = ClientService::with(['client', 'product', 'reseller'])
            ->when($request->search, function ($query, $search) {
                $query->whereHas('client', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Admin/ClientServices/Index', [
            'services' => $services,
            'filters' => $request->only(['search', 'status'])
        ]);
    }

    /**
     * Show the form for creating a new client service
     */
    public function create(): Response
    {
        return Inertia::render('Admin/ClientServices/Create');
    }

    /**
     * Store a newly created client service
     */
    public function store(StoreClientServiceRequest $request): JsonResponse
    {
        try {
            $command = new CreateClientServiceCommand(
                clientId: $request->client_id,
                productId: $request->product_id,
                price: $request->price,
                currency: $request->currency ?? 'USD',
                nextDueDate: $request->next_due_date,
                resellerId: $request->reseller_id
            );

            $clientService = $this->createClientServiceUseCase->execute($command);

            return response()->json([
                'success' => true,
                'message' => 'Client service created successfully',
                'data' => $clientService
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating client service: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified client service
     */
    public function show(ClientService $clientService): Response
    {
        $clientService->load(['client', 'product', 'reseller']);

        return Inertia::render('Admin/ClientServices/Show', [
            'service' => $clientService
        ]);
    }

    /**
     * Show the form for editing the specified client service
     */
    public function edit(ClientService $clientService): Response
    {
        return Inertia::render('Admin/ClientServices/Edit', [
            'service' => $clientService
        ]);
    }

    /**
     * Update the specified client service
     */
    public function update(UpdateClientServiceRequest $request, ClientService $clientService): JsonResponse
    {
        try {
            $clientService->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Client service updated successfully',
                'data' => $clientService
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating client service: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified client service
     */
    public function destroy(ClientService $clientService): JsonResponse
    {
        try {
            $clientService->delete();

            return response()->json([
                'success' => true,
                'message' => 'Client service deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting client service: ' . $e->getMessage()
            ], 500);
        }
    }
}

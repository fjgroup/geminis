<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Domains\Users\Models\User;
use App\Services\UserService;
use App\Domains\Users\Services\UserManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Class AdminUserControllerRefactored
 *
 * Controlador refactorizado aplicando el Principio de Responsabilidad Única
 * Solo maneja HTTP requests/responses, delega lógica de negocio a UserManagementService
 */
class AdminUserControllerRefactored extends Controller
{
    public function __construct(
        private UserManagementService $userManagementService,
        private UserService $userService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        // TODO: Implementar autorización
        // $this->authorize('viewAny', User::class);

        $filters = $request->only(['search', 'role', 'status', 'reseller_id']);
        $result = $this->userManagementService->getUsers($filters, 10);

        if (!$result['success']) {
            Log::error('Error obteniendo usuarios', [
                'filters' => $filters,
                'error' => $result['message']
            ]);

            // En caso de error, mostrar página vacía
            $result['data'] = collect()->paginate(10);
        }

        return Inertia::render('Admin/Users/Index', [
            'users' => $result['data'],
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        // TODO: Implementar autorización
        // $this->authorize('create', User::class);

        $formData = $this->userManagementService->getFormData();

        return Inertia::render('Admin/Users/Create', $formData);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $result = $this->userManagementService->createUser($request->validated());

        if ($result['success']) {
            return redirect()->route('admin.users.index')
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->withErrors(['error' => $result['message']])
            ->withInput();
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): Response
    {
        $user->load(['reseller', 'resellerProfile', 'clientServices.product']);

        $stats = $this->userService->getUserStats($user);

        return Inertia::render('Admin/Users/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
                'company_name' => $user->company_name,
                'phone_number' => $user->phone_number,
                'reseller' => $user->reseller,
                'reseller_profile' => $user->resellerProfile,
                'recent_services' => $user->clientServices()->with('product')->latest()->limit(5)->get(),
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): Response
    {
        // TODO: Implementar autorización
        // $this->authorize('update', $user);

        $user->load(['reseller', 'resellerProfile']);
        $formData = $this->userManagementService->getFormData();

        return Inertia::render('Admin/Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
                'company_name' => $user->company_name,
                'phone_number' => $user->phone_number,
                'reseller_id' => $user->reseller_id,
                'reseller_profile' => $user->resellerProfile ? [
                    'commission_rate' => $user->resellerProfile->commission_rate,
                    'max_clients' => $user->resellerProfile->max_clients,
                    'allowed_products' => $user->resellerProfile->allowed_products,
                ] : null,
            ],
            ...$formData
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $result = $this->userManagementService->updateUser($user, $request->validated());

        if ($result['success']) {
            return redirect()->route('admin.users.index')
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->withErrors(['error' => $result['message']])
            ->withInput();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        // TODO: Implementar autorización
        // $this->authorize('delete', $user);

        $result = $this->userManagementService->deleteUser($user);

        if ($result['success']) {
            return redirect()->route('admin.users.index')
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->withErrors(['error' => $result['message']]);
    }

    /**
     * Toggle user status (AJAX)
     */
    public function toggleStatus(User $user): JsonResponse
    {
        try {
            $success = $this->userService->toggleUserStatus($user);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Estado del usuario actualizado exitosamente',
                    'new_status' => $user->fresh()->status
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error actualizando estado del usuario'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Error cambiando estado de usuario', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Search users for autocomplete (AJAX)
     */
    public function search(Request $request): JsonResponse
    {
        $criteria = $request->only(['search', 'role', 'status', 'reseller_id']);
        $limit = $request->input('limit', 10);

        try {
            $users = $this->userService->searchUsers($criteria, $limit);

            return response()->json($users);

        } catch (\Exception $e) {
            Log::error('Error buscando usuarios', [
                'error' => $e->getMessage(),
                'criteria' => $criteria
            ]);

            return response()->json([]);
        }
    }

    /**
     * Get user statistics (AJAX)
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'active_users' => User::where('status', 'active')->count(),
                'inactive_users' => User::where('status', 'inactive')->count(),
                'admin_users' => User::where('role', 'admin')->count(),
                'reseller_users' => User::where('role', 'reseller')->count(),
                'client_users' => User::where('role', 'client')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas de usuarios', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo estadísticas'
            ], 500);
        }
    }

    /**
     * Reset user password (AJAX)
     */
    public function resetPassword(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'new_password' => 'required|string|min:8|confirmed'
        ]);

        try {
            $result = $this->userService->changePassword($user, $request->new_password);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Contraseña actualizada exitosamente'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);

        } catch (\Exception $e) {
            Log::error('Error reseteando contraseña', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Get user's client services (AJAX)
     */
    public function getClientServices(User $user): JsonResponse
    {
        try {
            if ($user->role !== 'client') {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario no es un cliente'
                ], 400);
            }

            $services = $user->clientServices()
                ->with(['product', 'productPricing.billingCycle'])
                ->latest()
                ->get()
                ->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'product_name' => $service->product->name,
                        'domain_name' => $service->domain_name,
                        'status' => $service->status,
                        'next_due_date' => $service->next_due_date,
                        'created_at' => $service->created_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $services
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo servicios del cliente', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo servicios'
            ], 500);
        }
    }
}

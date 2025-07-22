<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientService;
use App\Services\ClientServiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Controlador refactorizado para la gestión de servicios del cliente
 * 
 * Aplicando el Principio de Responsabilidad Única:
 * - Solo maneja HTTP requests/responses
 * - Delega toda la lógica de negocio a ClientServiceService
 */
class ClientServiceControllerRefactored extends Controller
{
    public function __construct(
        private ClientServiceService $clientServiceService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): InertiaResponse
    {
        $user = $request->user();
        
        $result = $this->clientServiceService->getClientServicesWithDetails($user);

        if (!$result['success']) {
            Log::error('Error obteniendo servicios del cliente en ClientServiceController', [
                'client_id' => $user->id,
                'error' => $result['message']
            ]);
            
            // Datos por defecto en caso de error
            $result['data'] = [
                'clientServices' => collect(),
                'actionableInvoicesCount' => 0,
                'unpaidInvoicesCount' => 0,
                'accountBalance' => $user->balance ?? 0,
                'formattedAccountBalance' => '$0.00',
            ];
        }

        return Inertia::render('Client/Services/Index', $result['data']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, ClientService $service): InertiaResponse
    {
        $this->authorize('view', $service);

        $result = $this->clientServiceService->getServiceDetails($service);

        if (!$result['success']) {
            Log::error('Error obteniendo detalles del servicio', [
                'service_id' => $service->id,
                'error' => $result['message']
            ]);
            
            // Datos mínimos en caso de error
            $result['data'] = [
                'service' => $service,
                'configurable_options' => [],
                'configurable_options_total' => 0,
                'has_configurable_options' => false,
            ];
        }

        return Inertia::render('Client/Services/Show', $result['data']);
    }

    /**
     * Show upgrade/downgrade options for a service.
     */
    public function showUpgradeDowngradeOptions(Request $request, ClientService $service): InertiaResponse
    {
        $this->authorize('viewUpgradeDowngradeOptions', $service);

        $result = $this->clientServiceService->getUpgradeDowngradeOptions($service);

        if (!$result['success']) {
            return redirect()->route('client.services.index')
                ->with('error', $result['message']);
        }

        return Inertia::render('Client/Services/UpgradeDowngrade', $result['data']);
    }

    /**
     * Change service password.
     */
    public function changePassword(Request $request, ClientService $service): RedirectResponse
    {
        $this->authorize('update', $service);

        $validated = $request->validate([
            'current_password' => 'nullable|string',
            'new_password' => ['required', 'string', Password::min(8)->letters()->mixedCase()->numbers()],
            'new_password_confirmation' => 'required|string|same:new_password',
        ]);

        $result = $this->clientServiceService->changeServicePassword($service, $validated);

        if ($result['success']) {
            return redirect()->route('client.services.show', $service)
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->withErrors(['error' => $result['message']])
            ->withInput();
    }

    /**
     * Get service details for AJAX requests.
     */
    public function getServiceDetails(ClientService $service): JsonResponse
    {
        $this->authorize('view', $service);

        $result = $this->clientServiceService->getServiceDetails($service);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'data' => $result['data']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 500);
    }

    /**
     * Get upgrade/downgrade options for AJAX requests.
     */
    public function getUpgradeDowngradeOptionsAjax(ClientService $service): JsonResponse
    {
        $this->authorize('viewUpgradeDowngradeOptions', $service);

        $result = $this->clientServiceService->getUpgradeDowngradeOptions($service);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'data' => $result['data']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 500);
    }

    /**
     * Suspend a service (if authorized).
     */
    public function suspend(ClientService $service): RedirectResponse
    {
        $this->authorize('suspend', $service);

        try {
            $service->update(['status' => 'suspended']);

            Log::info('Servicio suspendido por el cliente', [
                'service_id' => $service->id,
                'client_id' => $service->client_id
            ]);

            return redirect()->route('client.services.show', $service)
                ->with('success', 'Servicio suspendido exitosamente');

        } catch (\Exception $e) {
            Log::error('Error suspendiendo servicio', [
                'error' => $e->getMessage(),
                'service_id' => $service->id
            ]);

            return redirect()->back()
                ->with('error', 'Error al suspender el servicio');
        }
    }

    /**
     * Request service cancellation.
     */
    public function requestCancellation(Request $request, ClientService $service): RedirectResponse
    {
        $this->authorize('requestCancellation', $service);

        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:500',
            'cancellation_type' => 'required|in:immediate,end_of_period',
        ]);

        try {
            $service->update([
                'status' => 'pending_cancellation',
                'cancellation_reason' => $validated['cancellation_reason'],
                'notes' => ($service->notes ?? '') . "\n\nSolicitud de cancelación: " . $validated['cancellation_reason']
            ]);

            Log::info('Solicitud de cancelación de servicio', [
                'service_id' => $service->id,
                'client_id' => $service->client_id,
                'reason' => $validated['cancellation_reason'],
                'type' => $validated['cancellation_type']
            ]);

            return redirect()->route('client.services.show', $service)
                ->with('success', 'Solicitud de cancelación enviada exitosamente');

        } catch (\Exception $e) {
            Log::error('Error solicitando cancelación de servicio', [
                'error' => $e->getMessage(),
                'service_id' => $service->id
            ]);

            return redirect()->back()
                ->with('error', 'Error al solicitar la cancelación del servicio');
        }
    }

    /**
     * Download service configuration or files (if applicable).
     */
    public function downloadConfig(ClientService $service): JsonResponse
    {
        $this->authorize('view', $service);

        try {
            // Esta funcionalidad dependería del tipo de servicio
            // Por ahora retornamos la información básica del servicio
            $config = [
                'service_id' => $service->id,
                'domain_name' => $service->domain_name,
                'username' => $service->username,
                'product_name' => $service->product->name,
                'status' => $service->status,
                'next_due_date' => $service->next_due_date->format('Y-m-d'),
            ];

            return response()->json([
                'success' => true,
                'data' => $config,
                'message' => 'Configuración del servicio obtenida exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo configuración del servicio', [
                'error' => $e->getMessage(),
                'service_id' => $service->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la configuración del servicio'
            ], 500);
        }
    }

    /**
     * Renew service (create renewal invoice).
     */
    public function renew(ClientService $service): RedirectResponse
    {
        $this->authorize('renew', $service);

        try {
            // Esta funcionalidad requeriría integración con el sistema de facturación
            // Por ahora solo registramos la intención
            Log::info('Solicitud de renovación de servicio', [
                'service_id' => $service->id,
                'client_id' => $service->client_id
            ]);

            return redirect()->route('client.services.show', $service)
                ->with('info', 'Solicitud de renovación registrada. Se generará una factura próximamente.');

        } catch (\Exception $e) {
            Log::error('Error solicitando renovación de servicio', [
                'error' => $e->getMessage(),
                'service_id' => $service->id
            ]);

            return redirect()->back()
                ->with('error', 'Error al solicitar la renovación del servicio');
        }
    }
}

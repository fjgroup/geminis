<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Services\PaymentMethodService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Controlador refactorizado para la gestión de métodos de pago
 * 
 * Aplicando el Principio de Responsabilidad Única:
 * - Solo maneja HTTP requests/responses
 * - Delega toda la lógica de negocio a PaymentMethodService
 */
class AdminPaymentMethodControllerRefactored extends Controller
{
    public function __construct(
        private PaymentMethodService $paymentMethodService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): InertiaResponse
    {
        $this->authorize('viewAny', PaymentMethod::class);

        $filters = $request->only(['type', 'is_active', 'search']);
        
        $result = $this->paymentMethodService->getPaymentMethods($filters);

        if (!$result['success']) {
            Log::error('Error obteniendo métodos de pago en AdminPaymentMethodController', [
                'filters' => $filters,
                'error' => $result['message']
            ]);
            
            // En caso de error, mostrar colección vacía
            $result['data'] = collect();
        }

        $formData = $this->paymentMethodService->getFormData();

        return Inertia::render('Admin/PaymentMethods/Index', [
            'paymentMethods' => $result['data'],
            'paymentMethodTypes' => $formData['paymentMethodTypes'],
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): InertiaResponse
    {
        $this->authorize('create', PaymentMethod::class);

        $formData = $this->paymentMethodService->getFormData();

        return Inertia::render('Admin/PaymentMethods/Create', $formData);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', PaymentMethod::class);

        // Validación dinámica basada en el tipo
        $type = $request->input('type');
        
        if (!$this->paymentMethodService->isValidType($type)) {
            return redirect()->back()
                ->withErrors(['type' => 'Tipo de método de pago inválido'])
                ->withInput();
        }

        $commonRules = $this->paymentMethodService->getCommonValidationRules();
        $typeSpecificRules = $this->paymentMethodService->getTypeSpecificValidationRules($type);
        
        $allRules = array_merge($commonRules, $typeSpecificRules);
        
        $validated = $request->validate($allRules);

        $result = $this->paymentMethodService->createPaymentMethod($validated);

        if ($result['success']) {
            return redirect()->route('admin.payment-methods.index')
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->withErrors(['error' => $result['message']])
            ->withInput();
    }

    /**
     * Display the specified resource.
     */
    public function show(PaymentMethod $paymentMethod): InertiaResponse
    {
        $this->authorize('view', $paymentMethod);

        return Inertia::render('Admin/PaymentMethods/Show', [
            'paymentMethod' => $paymentMethod,
            'paymentMethodTypes' => $this->paymentMethodService->getAvailableTypes(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaymentMethod $paymentMethod): InertiaResponse
    {
        $this->authorize('update', $paymentMethod);

        $formData = $this->paymentMethodService->getFormData();

        return Inertia::render('Admin/PaymentMethods/Edit', [
            'paymentMethod' => $paymentMethod,
            ...$formData
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $this->authorize('update', $paymentMethod);

        // Validación dinámica basada en el tipo
        $type = $request->input('type');
        
        if (!$this->paymentMethodService->isValidType($type)) {
            return redirect()->back()
                ->withErrors(['type' => 'Tipo de método de pago inválido'])
                ->withInput();
        }

        $commonRules = $this->paymentMethodService->getCommonValidationRules();
        $typeSpecificRules = $this->paymentMethodService->getTypeSpecificValidationRules($type);
        
        $allRules = array_merge($commonRules, $typeSpecificRules);
        
        $validated = $request->validate($allRules);

        $result = $this->paymentMethodService->updatePaymentMethod($paymentMethod, $validated);

        if ($result['success']) {
            return redirect()->route('admin.payment-methods.index')
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->withErrors(['error' => $result['message']])
            ->withInput();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        $this->authorize('delete', $paymentMethod);

        $result = $this->paymentMethodService->deletePaymentMethod($paymentMethod);

        if ($result['success']) {
            return redirect()->route('admin.payment-methods.index')
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->withErrors(['error' => $result['message']]);
    }

    /**
     * Get validation rules for a specific payment method type (AJAX)
     */
    public function getValidationRules(Request $request): \Illuminate\Http\JsonResponse
    {
        $type = $request->input('type');

        if (!$this->paymentMethodService->isValidType($type)) {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de método de pago inválido'
            ], 400);
        }

        $commonRules = $this->paymentMethodService->getCommonValidationRules();
        $typeSpecificRules = $this->paymentMethodService->getTypeSpecificValidationRules($type);

        return response()->json([
            'success' => true,
            'data' => [
                'common_rules' => $commonRules,
                'type_specific_rules' => $typeSpecificRules,
                'all_rules' => array_merge($commonRules, $typeSpecificRules)
            ]
        ]);
    }

    /**
     * Get active payment methods for forms (AJAX)
     */
    public function getActivePaymentMethods(): \Illuminate\Http\JsonResponse
    {
        try {
            $paymentMethods = $this->paymentMethodService->getActivePaymentMethods();

            return response()->json([
                'success' => true,
                'data' => $paymentMethods
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo métodos de pago activos', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener métodos de pago activos'
            ], 500);
        }
    }

    /**
     * Toggle payment method status (activate/deactivate)
     */
    public function toggleStatus(PaymentMethod $paymentMethod): RedirectResponse
    {
        $this->authorize('update', $paymentMethod);

        try {
            $newStatus = !$paymentMethod->is_active;
            
            $result = $this->paymentMethodService->updatePaymentMethod($paymentMethod, [
                'name' => $paymentMethod->name,
                'type' => $paymentMethod->type,
                'is_active' => $newStatus,
                'instructions' => $paymentMethod->instructions,
                'logo_url' => $paymentMethod->logo_url,
                'account_holder_name' => $paymentMethod->account_holder_name,
                'bank_name' => $paymentMethod->bank_name,
                'account_number' => $paymentMethod->account_number,
                'identification_number' => $paymentMethod->identification_number,
                'swift_code' => $paymentMethod->swift_code,
                'iban' => $paymentMethod->iban,
                'branch_name' => $paymentMethod->branch_name,
                'platform_name' => $paymentMethod->platform_name,
                'email_address' => $paymentMethod->email_address,
                'payment_link' => $paymentMethod->payment_link,
            ]);

            if ($result['success']) {
                $statusText = $newStatus ? 'activado' : 'desactivado';
                return redirect()->route('admin.payment-methods.index')
                    ->with('success', "Método de pago {$statusText} exitosamente");
            }

            return redirect()->back()
                ->withErrors(['error' => $result['message']]);

        } catch (\Exception $e) {
            Log::error('Error cambiando estado del método de pago', [
                'error' => $e->getMessage(),
                'payment_method_id' => $paymentMethod->id
            ]);

            return redirect()->back()
                ->with('error', 'Error al cambiar el estado del método de pago');
        }
    }
}

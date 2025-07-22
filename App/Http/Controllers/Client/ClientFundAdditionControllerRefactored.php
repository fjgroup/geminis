<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreFundAdditionRequest;
use App\Services\FundAdditionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Controlador refactorizado para la adición de fondos del cliente
 * 
 * Aplicando el Principio de Responsabilidad Única:
 * - Solo maneja HTTP requests/responses
 * - Delega toda la lógica de negocio a FundAdditionService
 */
class ClientFundAdditionControllerRefactored extends Controller
{
    public function __construct(
        private FundAdditionService $fundAdditionService
    ) {}

    /**
     * Show the form for adding funds.
     */
    public function showAddFundsForm(): InertiaResponse
    {
        $client = Auth::user();
        
        $result = $this->fundAdditionService->getFormData($client);

        if (!$result['success']) {
            Log::error('Error cargando formulario de adición de fondos', [
                'client_id' => $client->id,
                'error' => $result['message']
            ]);
            
            // Datos por defecto en caso de error
            $result['data'] = [
                'paymentMethods' => collect(),
                'currencyCode' => 'USD',
                'currentBalance' => $client->balance ?? 0,
                'formattedBalance' => '$0.00',
            ];
        }

        return Inertia::render('Client/Funds/AddForm', $result['data']);
    }

    /**
     * Process the fund addition request from the client.
     */
    public function processFundAddition(StoreFundAdditionRequest $request): RedirectResponse
    {
        $client = Auth::user();
        
        $result = $this->fundAdditionService->processManualFundAddition($client, $request->validated());

        if ($result['success']) {
            return redirect()->route('client.transactions.index')
                ->with('success', $result['message']);
        }

        return redirect()->back()
            ->withErrors(['error' => $result['message']])
            ->withInput();
    }

    /**
     * Initiate PayPal payment for fund addition.
     */
    public function initiatePayPalPayment(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return redirect()->route('client.funds.create')
                ->withErrors($validator)
                ->withInput();
        }

        $client = Auth::user();
        $amount = (float) $request->input('amount');

        $result = $this->fundAdditionService->initiatePayPalPayment($client, $amount);

        if ($result['success']) {
            // Guardar datos en sesión
            foreach ($result['data']['session_data'] as $key => $value) {
                $request->session()->put($key, $value);
            }

            return Inertia::location($result['data']['approval_link']);
        }

        return redirect()->route('client.funds.create')
            ->with('error', $result['message'])
            ->withInput();
    }

    /**
     * Handle PayPal payment success.
     */
    public function handlePayPalSuccess(Request $request): RedirectResponse
    {
        $client = Auth::user();
        
        $sessionData = [
            'paypal_fund_order_id' => $request->session()->get('paypal_fund_order_id'),
            'paypal_fund_amount' => $request->session()->get('paypal_fund_amount'),
            'paypal_fund_currency' => $request->session()->get('paypal_fund_currency'),
            'paypal_fund_identifier' => $request->session()->get('paypal_fund_identifier'),
        ];

        $result = $this->fundAdditionService->handlePayPalSuccess($client, $sessionData);

        // Limpiar datos de sesión
        $request->session()->forget([
            'paypal_fund_order_id', 
            'paypal_fund_amount', 
            'paypal_fund_currency', 
            'paypal_fund_identifier'
        ]);

        if ($result['success']) {
            return redirect()->route('client.dashboard')
                ->with('success', $result['message']);
        }

        return redirect()->route('client.funds.create')
            ->with('error', $result['message']);
    }

    /**
     * Handle PayPal payment cancellation.
     */
    public function handlePayPalCancel(Request $request): RedirectResponse
    {
        $client = Auth::user();
        
        // Limpiar datos de sesión
        $request->session()->forget([
            'paypal_fund_order_id', 
            'paypal_fund_amount', 
            'paypal_fund_currency', 
            'paypal_fund_identifier'
        ]);

        $result = $this->fundAdditionService->handlePayPalCancel($client);

        return redirect()->route('client.funds.create')
            ->with('info', $result['message']);
    }

    /**
     * Show fund addition history.
     */
    public function showHistory(Request $request): InertiaResponse
    {
        $client = Auth::user();
        $perPage = $request->input('per_page', 10);
        
        $history = $this->fundAdditionService->getFundAdditionHistory($client, $perPage);
        $stats = $this->fundAdditionService->getFundAdditionStats($client);

        return Inertia::render('Client/Funds/History', [
            'history' => $history,
            'stats' => $stats['success'] ? $stats['data'] : [],
            'currentBalance' => $client->balance,
            'formattedBalance' => $client->formatted_balance,
        ]);
    }

    /**
     * Get fund addition statistics for AJAX requests.
     */
    public function getStats(): \Illuminate\Http\JsonResponse
    {
        try {
            $client = Auth::user();
            $result = $this->fundAdditionService->getFundAdditionStats($client);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result['data']
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);

        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas de fondos', [
                'error' => $e->getMessage(),
                'client_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ], 500);
        }
    }

    /**
     * Validate fund addition data for AJAX requests.
     */
    public function validateFundAddition(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $request->only(['amount', 'payment_method_id', 'reference_number', 'payment_date']);
            
            $validation = $this->fundAdditionService->validateManualFundAddition($data);

            return response()->json([
                'valid' => $validation['valid'],
                'errors' => $validation['errors']
            ]);

        } catch (\Exception $e) {
            Log::error('Error validando datos de adición de fondos', [
                'error' => $e->getMessage(),
                'client_id' => Auth::id()
            ]);

            return response()->json([
                'valid' => false,
                'errors' => ['general' => 'Error en la validación']
            ], 500);
        }
    }

    /**
     * Get available payment methods for fund addition.
     */
    public function getPaymentMethods(): \Illuminate\Http\JsonResponse
    {
        try {
            $client = Auth::user();
            $result = $this->fundAdditionService->getFormData($client);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result['data']['paymentMethods']
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener métodos de pago'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Error obteniendo métodos de pago para fondos', [
                'error' => $e->getMessage(),
                'client_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener métodos de pago'
            ], 500);
        }
    }

    /**
     * Check PayPal payment status.
     */
    public function checkPayPalStatus(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $orderId = $request->session()->get('paypal_fund_order_id');
            
            if (!$orderId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay orden de PayPal en progreso'
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $orderId,
                    'amount' => $request->session()->get('paypal_fund_amount'),
                    'currency' => $request->session()->get('paypal_fund_currency'),
                    'status' => 'pending'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error verificando estado de PayPal', [
                'error' => $e->getMessage(),
                'client_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al verificar estado del pago'
            ], 500);
        }
    }

    /**
     * Cancel pending fund addition request.
     */
    public function cancelPendingRequest(Request $request): RedirectResponse
    {
        try {
            $transactionId = $request->input('transaction_id');
            
            // Esta funcionalidad requeriría un método en el servicio para cancelar
            // Por ahora solo registramos la intención
            Log::info('Solicitud de cancelación de adición de fondos', [
                'transaction_id' => $transactionId,
                'client_id' => Auth::id()
            ]);

            return redirect()->back()
                ->with('info', 'Solicitud de cancelación registrada. Un administrador la revisará.');

        } catch (\Exception $e) {
            Log::error('Error cancelando solicitud de fondos', [
                'error' => $e->getMessage(),
                'client_id' => Auth::id()
            ]);

            return redirect()->back()
                ->with('error', 'Error al procesar la cancelación');
        }
    }

    /**
     * Get minimum amounts for different payment methods.
     */
    public function getMinimumAmounts(): \Illuminate\Http\JsonResponse
    {
        try {
            $minimums = [
                'paypal' => 30.00,
                'bank_transfer' => 10.00,
                'crypto' => 50.00,
                'default' => 5.00,
            ];

            return response()->json([
                'success' => true,
                'data' => $minimums
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo montos mínimos', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener montos mínimos'
            ], 500);
        }
    }
}

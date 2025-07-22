<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\ClientInvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Controlador refactorizado para la gestión de facturas del cliente
 * 
 * Aplicando el Principio de Responsabilidad Única:
 * - Solo maneja HTTP requests/responses
 * - Delega toda la lógica de negocio a ClientInvoiceService
 */
class ClientInvoiceControllerRefactored extends Controller
{
    public function __construct(
        private ClientInvoiceService $clientInvoiceService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): InertiaResponse
    {
        $this->authorize('viewAny', Invoice::class);

        $client = $request->user();
        $perPage = $request->input('per_page', 10);
        
        $result = $this->clientInvoiceService->getClientInvoices($client, $perPage);

        if (!$result['success']) {
            Log::error('Error obteniendo facturas del cliente en ClientInvoiceController', [
                'client_id' => $client->id,
                'error' => $result['message']
            ]);
            
            // En caso de error, mostrar colección vacía
            $result['data'] = collect()->paginate($perPage);
        }

        // Obtener estadísticas adicionales
        $stats = $this->clientInvoiceService->getClientInvoiceStats($client);

        return Inertia::render('Client/Invoices/Index', [
            'invoices' => $result['data'],
            'stats' => $stats['success'] ? $stats['data'] : [],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice): InertiaResponse
    {
        $this->authorize('view', $invoice);

        $result = $this->clientInvoiceService->getInvoiceDetails($invoice);

        if (!$result['success']) {
            Log::error('Error obteniendo detalles de factura', [
                'invoice_id' => $invoice->id,
                'error' => $result['message']
            ]);
            
            // Datos mínimos en caso de error
            $result['data'] = $invoice;
        }

        $client = Auth::user();
        $canPayWithBalance = $this->clientInvoiceService->canPayWithBalance($client, $invoice);

        return Inertia::render('Client/Invoices/Show', [
            'invoice' => $result['data'],
            'canPayWithBalance' => $canPayWithBalance,
            'clientBalance' => $client->balance,
            'formattedBalance' => $client->formatted_balance,
        ]);
    }

    /**
     * Process payment of the specified invoice using user's balance.
     */
    public function payWithBalance(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('payWithBalance', $invoice);

        $client = $request->user();
        
        $result = $this->clientInvoiceService->payWithBalance($client, $invoice);

        if ($result['success']) {
            return redirect()->route('client.invoices.show', $invoice->id)
                ->with('success', $result['message']);
        }

        return redirect()->route('client.invoices.show', $invoice->id)
            ->with('error', $result['message']);
    }

    /**
     * Cancel payment report for an invoice.
     */
    public function cancelPaymentReport(Invoice $invoice): RedirectResponse
    {
        $this->authorize('cancelPaymentReport', $invoice);

        $result = $this->clientInvoiceService->cancelPaymentReport($invoice);

        if ($result['success']) {
            return redirect()->route('client.invoices.show', $invoice->id)
                ->with('success', $result['message']);
        }

        return redirect()->route('client.invoices.show', $invoice->id)
            ->with('error', $result['message']);
    }

    /**
     * Request cancellation of an invoice and its associated pending services.
     */
    public function requestInvoiceCancellation(Invoice $invoice): RedirectResponse
    {
        $this->authorize('requestCancellationForNewServiceInvoice', $invoice);

        $result = $this->clientInvoiceService->requestInvoiceCancellation($invoice);

        if ($result['success']) {
            return redirect()->route('client.invoices.show', $invoice)
                ->with('success', $result['message']);
        }

        return redirect()->route('client.invoices.show', $invoice)
            ->with('error', $result['message']);
    }

    /**
     * Get invoices by status for AJAX requests.
     */
    public function getInvoicesByStatus(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $status = $request->input('status');
            $perPage = $request->input('per_page', 10);
            
            if (!in_array($status, ['unpaid', 'paid', 'pending_confirmation', 'cancelled'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estado de factura inválido'
                ], 400);
            }

            $client = $request->user();
            $invoices = $this->clientInvoiceService->getInvoicesByStatus($client, $status, $perPage);

            return response()->json([
                'success' => true,
                'data' => $invoices
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo facturas por estado', [
                'error' => $e->getMessage(),
                'client_id' => Auth::id(),
                'status' => $request->input('status')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener facturas'
            ], 500);
        }
    }

    /**
     * Get invoice statistics for AJAX requests.
     */
    public function getStats(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $client = $request->user();
            $result = $this->clientInvoiceService->getClientInvoiceStats($client);

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
            Log::error('Error obteniendo estadísticas de facturas', [
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
     * Check if client can pay with balance for AJAX requests.
     */
    public function checkBalancePayment(Invoice $invoice): \Illuminate\Http\JsonResponse
    {
        try {
            $this->authorize('view', $invoice);
            
            $client = Auth::user();
            $canPay = $this->clientInvoiceService->canPayWithBalance($client, $invoice);

            return response()->json([
                'success' => true,
                'data' => [
                    'can_pay' => $canPay,
                    'client_balance' => $client->balance,
                    'invoice_amount' => $invoice->total_amount,
                    'formatted_balance' => $client->formatted_balance,
                    'formatted_amount' => $invoice->formatted_total_amount,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error verificando pago con balance', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id,
                'client_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al verificar disponibilidad de pago'
            ], 500);
        }
    }

    /**
     * Download invoice PDF.
     */
    public function downloadPdf(Invoice $invoice): \Symfony\Component\HttpFoundation\Response
    {
        $this->authorize('view', $invoice);

        try {
            // Esta funcionalidad requeriría un servicio de generación de PDF
            // Por ahora solo registramos la intención
            Log::info('Solicitud de descarga de PDF de factura', [
                'invoice_id' => $invoice->id,
                'client_id' => Auth::id()
            ]);

            return redirect()->route('client.invoices.show', $invoice)
                ->with('info', 'La descarga de PDF estará disponible próximamente.');

        } catch (\Exception $e) {
            Log::error('Error descargando PDF de factura', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id
            ]);

            return redirect()->back()
                ->with('error', 'Error al descargar el PDF de la factura');
        }
    }

    /**
     * Get unpaid invoices count for dashboard.
     */
    public function getUnpaidCount(): \Illuminate\Http\JsonResponse
    {
        try {
            $client = Auth::user();
            $unpaidCount = $client->invoices()->where('status', 'unpaid')->count();

            return response()->json([
                'success' => true,
                'data' => ['unpaid_count' => $unpaidCount]
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo conteo de facturas impagadas', [
                'error' => $e->getMessage(),
                'client_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener conteo'
            ], 500);
        }
    }

    /**
     * Search invoices for autocomplete.
     */
    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $query = $request->input('q', '');
            
            if (strlen($query) < 2) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            $client = $request->user();
            $invoices = $client->invoices()
                ->where(function ($q) use ($query) {
                    $q->where('invoice_number', 'like', '%' . $query . '%')
                      ->orWhere('description', 'like', '%' . $query . '%');
                })
                ->limit(10)
                ->get(['id', 'invoice_number', 'total_amount', 'status', 'issue_date']);

            return response()->json([
                'success' => true,
                'data' => $invoices->map(function ($invoice) {
                    return [
                        'id' => $invoice->id,
                        'label' => "#{$invoice->invoice_number} - {$invoice->formatted_total_amount} ({$invoice->status})",
                        'value' => $invoice->id,
                        'invoice' => $invoice
                    ];
                })
            ]);

        } catch (\Exception $e) {
            Log::error('Error buscando facturas', [
                'error' => $e->getMessage(),
                'client_id' => Auth::id(),
                'query' => $request->input('q')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error en la búsqueda'
            ], 500);
        }
    }
}

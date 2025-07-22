<?php

namespace App\Domains\BillingAndPayments\Infrastructure\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ConfirmManualPaymentRequest;
use App\Domains\BillingAndPayments\Models\Transaction;
use App\Domains\BillingAndPayments\Services\TransactionManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Controlador de transacciones para administradores en arquitectura hexagonal
 *
 * Aplicando el Principio de Responsabilidad Única:
 * - Solo maneja HTTP requests/responses
 * - Delega toda la lógica de negocio a TransactionManagementService
 * - Ubicado en Infrastructure layer como Input Adapter
 */
class AdminTransactionController extends Controller
{
    public function __construct(
        private TransactionManagementService $transactionManagementService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): InertiaResponse
    {
        $this->authorize('viewAny', Transaction::class);

        $filters = $request->only(['status', 'type', 'client_id', 'date_from', 'date_to', 'search']);
        
        $transactions = $this->transactionManagementService->getTransactions($filters, 10);

        // Obtener transacciones pendientes de adición de fondos para el dashboard
        $pendingFundAdditions = $this->transactionManagementService->getPendingFundAdditions();

        return Inertia::render('Admin/Transactions/Index', [
            'transactions' => $transactions,
            'pendingFundAdditions' => $pendingFundAdditions,
            'filters' => $filters,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction): InertiaResponse
    {
        $this->authorize('view', $transaction);

        $transaction->load(['client', 'invoice', 'paymentMethod']);

        return Inertia::render('Admin/Transactions/Show', [
            'transaction' => $transaction
        ]);
    }

    /**
     * Confirmar pago manual para una factura
     */
    public function confirmManualPayment(ConfirmManualPaymentRequest $request): RedirectResponse
    {
        // La autorización se maneja en el FormRequest o aquí si es necesario
        // $this->authorize('confirmManualPayment', Transaction::class);

        $result = $this->transactionManagementService->confirmManualPayment($request->validated());

        if ($result['success']) {
            Log::info('Pago manual confirmado exitosamente', [
                'invoice_id' => $request->input('invoice_id'),
                'transaction_id' => $result['data']['transaction']->id ?? null
            ]);

            return redirect()->route('admin.invoices.show', $request->input('invoice_id'))
                ->with('success', $result['message']);
        }

        Log::error('Error confirmando pago manual', [
            'invoice_id' => $request->input('invoice_id'),
            'error' => $result['message']
        ]);

        return redirect()->back()
            ->with('error', $result['message']);
    }

    /**
     * Confirmar una transacción pendiente
     */
    public function confirm(Transaction $transaction): RedirectResponse
    {
        $this->authorize('confirm', $transaction);

        $result = $this->transactionManagementService->confirmTransaction($transaction);

        if ($result['success']) {
            Log::info('Transacción confirmada exitosamente', [
                'transaction_id' => $transaction->id,
                'type' => $transaction->type
            ]);

            return redirect()->route('admin.transactions.index')
                ->with('success', $result['message']);
        }

        Log::error('Error confirmando transacción', [
            'transaction_id' => $transaction->id,
            'error' => $result['message']
        ]);

        return redirect()->route('admin.transactions.index')
            ->with('error', $result['message']);
    }

    /**
     * Rechazar una transacción pendiente
     */
    public function reject(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('reject', $transaction);

        $reason = $request->input('reason', 'Rechazado por administrador');
        
        $result = $this->transactionManagementService->rejectTransaction($transaction, $reason);

        if ($result['success']) {
            Log::info('Transacción rechazada exitosamente', [
                'transaction_id' => $transaction->id,
                'reason' => $reason
            ]);

            return redirect()->route('admin.transactions.index')
                ->with('success', $result['message']);
        }

        Log::error('Error rechazando transacción', [
            'transaction_id' => $transaction->id,
            'error' => $result['message']
        ]);

        return redirect()->route('admin.transactions.index')
            ->with('error', $result['message']);
    }

    /**
     * Crear reembolso para una transacción
     */
    public function createRefund(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('createRefund', $transaction);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $transaction->amount,
            'reason' => 'required|string|max:500',
        ]);

        $result = $this->transactionManagementService->createRefund(
            $transaction,
            $validated['amount'],
            $validated['reason']
        );

        if ($result['success']) {
            Log::info('Reembolso creado exitosamente', [
                'original_transaction_id' => $transaction->id,
                'refund_transaction_id' => $result['data']['refund']->id ?? null,
                'amount' => $validated['amount']
            ]);

            return redirect()->route('admin.transactions.show', $transaction)
                ->with('success', $result['message']);
        }

        Log::error('Error creando reembolso', [
            'transaction_id' => $transaction->id,
            'error' => $result['message']
        ]);

        return redirect()->back()
            ->with('error', $result['message']);
    }

    /**
     * Obtener estadísticas de transacciones para dashboard
     */
    public function getStats(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $filters = $request->only(['date_from', 'date_to']);
            
            // Usar el servicio para obtener estadísticas
            $stats = [
                'total_transactions' => $this->transactionManagementService->getTransactions($filters)->count(),
                'pending_transactions' => $this->transactionManagementService->getTransactions(
                    array_merge($filters, ['status' => 'pending'])
                )->count(),
                'completed_transactions' => $this->transactionManagementService->getTransactions(
                    array_merge($filters, ['status' => 'completed'])
                )->count(),
                'pending_fund_additions' => $this->transactionManagementService->getPendingFundAdditions()->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas de transacciones', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ], 500);
        }
    }

    /**
     * Exportar transacciones a CSV
     */
    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->authorize('export', Transaction::class);

        $filters = $request->only(['status', 'type', 'client_id', 'date_from', 'date_to', 'search']);
        
        $transactions = $this->transactionManagementService->getTransactions($filters, 1000); // Límite alto para exportación

        $filename = 'transacciones_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            
            // Encabezados CSV
            fputcsv($handle, [
                'ID',
                'Cliente',
                'Factura',
                'Método de Pago',
                'Monto',
                'Moneda',
                'Estado',
                'Tipo',
                'Fecha de Transacción',
                'Descripción'
            ]);

            // Datos
            foreach ($transactions as $transaction) {
                fputcsv($handle, [
                    $transaction->id,
                    $transaction->client->name ?? 'N/A',
                    $transaction->invoice->invoice_number ?? 'N/A',
                    $transaction->paymentMethod->name ?? 'N/A',
                    $transaction->amount,
                    $transaction->currency_code,
                    $transaction->status,
                    $transaction->type,
                    $transaction->transaction_date->format('Y-m-d H:i:s'),
                    $transaction->description
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Buscar transacciones para autocompletado
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

            $transactions = $this->transactionManagementService->getTransactions([
                'search' => $query
            ], 10);

            return response()->json([
                'success' => true,
                'data' => $transactions->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'label' => "#{$transaction->id} - {$transaction->client->name} - {$transaction->amount} {$transaction->currency_code}",
                        'value' => $transaction->id,
                        'transaction' => $transaction
                    ];
                })
            ]);

        } catch (\Exception $e) {
            Log::error('Error buscando transacciones', [
                'error' => $e->getMessage(),
                'query' => $request->input('q')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error en la búsqueda'
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ConfirmManualPaymentRequest;
use App\Models\Invoice;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Gate; // For authorization
use Carbon\Carbon; // For setting paid_date

class AdminTransactionController extends Controller
{
    /**
     * Muestra una lista de transacciones.
     * (Implementación básica por ahora, se puede expandir después)
     */
    public function index()
    {
        // TODO: Implementar autorización: $this->authorize('viewAny', Transaction::class);

        $transactions = Transaction::with(['client:id,name', 'invoice:id,invoice_number'])
                                ->latest()
                                ->paginate(10); // O ajustar según necesidad

        return \Inertia\Inertia::render('Admin/Transactions/Index', [
            'transactions' => $transactions
        ]);
    }

     /**
     * Muestra una transacción específica.
     */
    public function show(Transaction $transaction)
    {
        // TODO: Implementar autorización: $this->authorize('view', $transaction);

        $transaction->load(['client', 'invoice', 'paymentMethod']); // Cargar relaciones necesarias

        return \Inertia\Inertia::render('Admin/Transactions/Show', [
            'transaction' => $transaction
        ]);
    }

    /**
     * Procesa la confirmación de un pago manual por parte del administrador.
     */
    public function confirmManualPayment(ConfirmManualPaymentRequest $request)
    {
        // TODO: Implementar autorización granular si es necesario
        // Gate::authorize('confirmManualPayment', Transaction::class); // O una acción/Policy más específica

        $validatedData = $request->validated();

        DB::beginTransaction();

        try {
            $invoice = Invoice::findOrFail($validatedData['invoice_id']);

            // TODO: Implementar Policy para Invoice
            // $this->authorize('update', $invoice); // Asegurar que el admin puede actualizar esta factura

            if ($invoice->status === 'paid') {
                DB::rollBack();
                return Redirect::back()
                    ->with('error', 'Esta factura ya ha sido marcada como pagada.');
            }

            // Crear la transacción si no existe una pendiente asociada a esta factura y método/monto.
            // Opcionalmente, podríamos buscar una transacción existente con status 'pending'
            // y vincularla/actualizarla en lugar de crear una nueva.
            // Por ahora, asumimos que ConfirmManualPaymentRequest es para un nuevo registro de pago manual.
            $transaction = Transaction::create([
                'client_id' => $invoice->client_id,
                'invoice_id' => $invoice->id,
                'order_id' => $invoice->order_id, // Asociar al order_id de la factura si existe
                'payment_method_id' => $validatedData['payment_method_id'],
                'gateway_slug' => 'manual_payment', // O un slug más específico del método manual
                'gateway_transaction_id' => $validatedData['reference_number'] ?? null,
                'amount' => $validatedData['amount'],
                'currency_code' => $invoice->currency_code, // Usar la moneda de la factura
                'status' => 'completed', // Marcar como completada inmediatamente al confirmar
                'type' => 'payment',
                'transaction_date' => $validatedData['transaction_date'],
                'description' => $validatedData['notes'] ?? "Pago manual confirmado para Factura #{$invoice->invoice_number}",
                'fees_amount' => 0, // Asumimos 0 fees para pagos manuales a menos que se especifique
            ]);

            // Actualizar el estado de la factura a 'paid'
            $invoice->status = 'paid';
            $invoice->paid_date = Carbon::parse($validatedData['transaction_date']); // Usar la fecha de transacción para paid_date
            $invoice->save();

            DB::commit();

            Log::info("AdminTransactionController: Pago manual confirmado para Factura ID: {$invoice->id}. Transacción ID: {$transaction->id}.");

            // Los Observers de Invoice y Order deberían disparar la lógica subsiguiente (ej. aprovisionamiento)

            return Redirect::route('admin.invoices.show', $invoice->id) // Redirigir a la vista de la factura
                ->with('success', 'Pago manual confirmado y factura marcada como pagada.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("AdminTransactionController: Fallo al confirmar pago manual para Factura ID: {$request->input('invoice_id')}. Error: " . $e->getMessage(), [
                'exception' => $e
            ]);

            return Redirect::back()
                ->with('error', 'Error al confirmar el pago manual. Inténtalo de nuevo.');
        }
    }

    // TODO: Añadir otros métodos CRUD para Transacciones si son necesarios (create, store, edit, update, destroy)
}

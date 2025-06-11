<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ConfirmManualPaymentRequest;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\User; // Added User model import
use Illuminate\Http\RedirectResponse; // Added RedirectResponse import
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

    /**
     * Confirma una transacción pendiente.
     * Si es una adición de fondos (credit_added), actualiza el balance del cliente.
     */
    public function confirm(Transaction $transaction): RedirectResponse
    {
        // TODO: Implementar una Policy más robusta para la autorización.
        // Por ahora, una verificación simple o Gate::authorize si ya está configurado.
        // $this->authorize('confirm', $transaction);

        if ($transaction->status === 'completed') {
            return Redirect::route('admin.invoices.index')->with('info', 'Esta transacción ya ha sido confirmada anteriormente.');
        }

        if ($transaction->status !== 'pending') {
            return Redirect::route('admin.invoices.index')->with('error', 'Solo las transacciones pendientes pueden ser confirmadas. Estado actual: ' . $transaction->status);
        }

        DB::beginTransaction();
        try {
            // Primero, actualizamos el estado de la transacción
            $transaction->status = 'completed';
            $transaction->save(); // Guardamos el cambio de estado de la transacción

            // Si es una adición de fondos, actualizamos el balance del cliente
            if ($transaction->type === 'credit_added') {
                $client = User::find($transaction->client_id);
                if ($client) {
                    $client->balance += $transaction->amount;
                    $client->save(); // Guardamos el cambio en el balance del cliente
                    Log::info("Balance actualizado para cliente ID {$client->id}. Nuevo balance: {$client->balance}. Transacción ID: {$transaction->id}");
                } else {
                    Log::error("Cliente no encontrado (ID: {$transaction->client_id}) para la transacción de adición de fondos ID: {$transaction->id}. No se actualizó el balance. La transacción fue marcada como completada.");
                    // Considerar si se debe lanzar una excepción aquí para revertir el cambio de estado de la transacción si el cliente no se encuentra.
                    // throw new \Exception("Cliente no encontrado (ID: {$transaction->client_id}) para la transacción ID: {$transaction->id}. No se pudo actualizar el balance.");
                }
            }

            // Lógica adicional podría ir aquí para otros tipos de transacciones si es necesario.
            // Por ejemplo, si es un 'payment' para una factura, podríamos querer actualizar el estado de la factura aquí
            // si no se maneja ya por un observer o el flujo de `confirmManualPayment`.

            DB::commit();

            Log::info("Transacción ID {$transaction->id} procesada para confirmación.");
            return Redirect::route('admin.invoices.index')->with('success', 'Transacción confirmada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al confirmar la transacción ID {$transaction->id}: " . $e->getMessage(), ['exception' => $e]);
            // Asegurarse de que el estado de la transacción se revierte si falló después de cambiarlo y antes del commit
            // Esto es manejado por DB::rollBack() si la $transaction->save() estaba dentro del try-catch
            // y la excepción ocurrió después. Si $transaction->save() fue antes del try,
            // entonces su estado no se revertiría automáticamente por el rollback de esta transacción DB.
            // La forma en que está ahora (save() dentro del try) es correcta.
            return Redirect::route('admin.invoices.index')->with('error', 'Error al confirmar la transacción: ' . $e->getMessage());
        }
    }

    /**
     * Rechaza una transacción pendiente.
     * Simplemente cambia el estado de la transacción a 'rejected'.
     */
    public function reject(Transaction $transaction): RedirectResponse
    {
        // TODO: Implementar una Policy más robusta para la autorización.
        // $this->authorize('reject', $transaction);

        if ($transaction->status === 'rejected') {
            return Redirect::route('admin.invoices.index')->with('info', 'Esta transacción ya ha sido rechazada anteriormente.');
        }

        if ($transaction->status === 'completed') {
            return Redirect::route('admin.invoices.index')->with('info', 'Esta transacción ya ha sido completada y no puede ser rechazada.');
        }

        if ($transaction->status !== 'pending') {
            return Redirect::route('admin.invoices.index')->with('error', 'Solo las transacciones pendientes pueden ser rechazadas. Estado actual: ' . $transaction->status);
        }

        DB::beginTransaction();
        try {
            $transaction->status = 'failed'; // O 'failed' o 'cancelled' según la semántica deseada
            $transaction->save();

            DB::commit();

            Log::info("Transacción ID {$transaction->id} rechazada exitosamente.");
            return Redirect::route('admin.invoices.index')->with('success', 'Transacción rechazada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al rechazar la transacción ID {$transaction->id}: " . $e->getMessage(), ['exception' => $e]);
            return Redirect::route('admin.invoices.index')->with('error', 'Error al rechazar la transacción: ' . $e->getMessage());
        }
    }
}

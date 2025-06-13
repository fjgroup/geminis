<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Transaction; // Added for future use
use App\Services\PayPalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
// Remove Illuminate\Support\Facades\Redirect; if using redirect() helper
// Remove use Carbon\Carbon; if using now() helper

class PayPalPaymentController extends Controller
{
    protected $payPalService;

    public function __construct(PayPalService $payPalService)
    {
        $this->payPalService = $payPalService;
        // Ejemplo: $this->middleware('auth'); // Aplicar a todos los métodos o especificar
    }

    public function createPayment(Request $request, Invoice $invoice)
    {
        // Validación de propietario de la factura (descomentar y ajustar si es necesario)
        /*
        if ($invoice->client_id !== Auth::id()) {
            // Log::warning("Intento no autorizado de pagar factura ID {$invoice->id} por usuario ID " . Auth::id());
            // Asumiendo que tienes una ruta 'client.invoices.show' o similar
            return redirect()->route('client.invoices.show', $invoice->id)->with('error', 'No está autorizado para pagar esta factura.');
        }
        */

        if (strtolower($invoice->status) !== 'unpaid' && strtolower($invoice->status) !== 'overdue') {
            // Log::warning("Intento de pagar factura ID {$invoice->id} con estado '{$invoice->status}'.");
             // Asumiendo que tienes una ruta 'client.invoices.show' o similar
            return redirect()->route('client.invoices.show', $invoice->id)->with('error', 'Esta factura no se puede pagar (estado: ' . $invoice->status . ').');
        }

        try {
            // Estas rutas se definirán más adelante. Asegúrate de que los nombres coincidan.
            $successUrl = route('client.paypal.payment.success');
            $cancelUrl = route('client.paypal.payment.cancel');

            $paypalOrder = $this->payPalService->createOrder($invoice, $successUrl, $cancelUrl);

            if ($paypalOrder && isset($paypalOrder['approval_link'])) {
                $request->session()->put('paypal_payment_order_id', $paypalOrder['order_id']); // Usar paypal_payment_order_id para claridad
                $request->session()->put('paypal_invoice_id', $invoice->id); // Para referencia al volver

                return redirect()->away($paypalOrder['approval_link']);
            } else {
                // Log::error("No se pudo obtener el enlace de aprobación de PayPal para la factura ID {$invoice->id}.");
                 // Asumiendo que tienes una ruta 'client.invoices.show' o similar
                return redirect()->route('client.invoices.show', $invoice->id)->with('error', 'Problema al iniciar el pago con PayPal. Intente más tarde.');
            }
        } catch (\Exception $e) {
            // Log::error("Excepción al crear pago PayPal para factura ID {$invoice->id}: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
             // Asumiendo que tienes una ruta 'client.invoices.show' o similar
            return redirect()->route('client.invoices.show', $invoice->id)->with('error', 'Error inesperado con PayPal. Contacte a soporte.');
        }
    }

    public function handlePaymentSuccess(Request $request)
    {
        $orderId = $request->session()->get('paypal_payment_order_id');
        $invoiceId = $request->session()->get('paypal_invoice_id');

        if (!$orderId || !$invoiceId) {
            Log::error("PayPal success callback - Order ID o Invoice ID no encontrados en sesión.", [
                'order_id' => $orderId,
                'invoice_id' => $invoiceId
            ]);
            // Redirect to a generic client page or home with an error message
            return redirect()->route('client.dashboard')->with('error', 'Error procesando el pago de PayPal: Información de la sesión perdida.');
        }

        $invoice = Invoice::find($invoiceId);
        if (!$invoice) {
            Log::error("PayPal success callback - Factura no encontrada para ID: {$invoiceId}");
            // Clear session variables even if invoice not found, to prevent reuse
            $request->session()->forget('paypal_payment_order_id');
            $request->session()->forget('paypal_invoice_id');
            return redirect()->route('client.dashboard')->with('error', 'Error procesando el pago de PayPal: Factura no encontrada.');
        }

        // Idempotency Check: If invoice is already paid
        if (strtolower($invoice->status) === 'paid') {
            Log::info("Invoice ID: {$invoiceId} is already paid. Skipping further processing in handlePaymentSuccess for PayPal Order ID: {$orderId}.");
            $request->session()->forget('paypal_payment_order_id');
            $request->session()->forget('paypal_invoice_id');
            return redirect()->route('client.invoices.show', $invoiceId)->with('success', 'Your payment for this invoice has already been processed.');
        }

        $captureResponse = $this->payPalService->captureOrder($orderId);

        if ($captureResponse && isset($captureResponse['status']) && $captureResponse['status'] === 'COMPLETED') {
            try {
                $invoice->status = 'paid';
                $invoice->paid_date = now(); // Or Carbon::parse($captureResponse['full_response']['purchase_units'][0]['payments']['captures'][0]['create_time'])
                $invoice->save();

                Transaction::create([
                    'client_id' => $invoice->client_id,
                    'invoice_id' => $invoice->id,
                    'payment_method_id' => null, // Or a specific ID for PayPal
                    'gateway_slug' => 'paypal',
                    'gateway_transaction_id' => $captureResponse['paypal_capture_id'],
                    'amount' => $invoice->total_amount, // Consistent with invoice
                    'currency_code' => $invoice->currency_code, // Consistent with invoice
                    'status' => 'completed',
                    'type' => 'payment',
                    'transaction_date' => now(), // Or parse from PayPal capture create_time
                    'description' => "PayPal payment for Invoice #" . $invoice->invoice_number,
                    'fees_amount' => $captureResponse['paypal_fee'] ?? 0.00,
                ]);

                Log::info("Payment captured successfully via controller for PayPal Order ID: {$orderId}, Invoice ID: {$invoiceId}. Capture ID: " . ($captureResponse['paypal_capture_id'] ?? 'N/A'));

                $request->session()->forget('paypal_payment_order_id');
                $request->session()->forget('paypal_invoice_id');

                return redirect()->route('client.invoices.show', $invoiceId)->with('success', 'Pago completado exitosamente.');

            } catch (\Exception $e) {
                Log::error("Error processing successful PayPal payment for Invoice ID: {$invoiceId}, PayPal Order ID: {$orderId}. Exception: " . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                    'capture_response' => $captureResponse
                ]);
                // Session variables are cleared below to avoid retry issues with same data
                // Do not redirect yet, fall through to the generic error handling for capture status not completed
            }
        }

        // If capture was not 'COMPLETED' or if an exception occurred during DB operations
        Log::error("Failed to capture PayPal Order ID: {$orderId} for Invoice ID: {$invoiceId} during success handling. Status: " . ($captureResponse['status'] ?? 'Unknown'), $captureResponse ?? []);

        $request->session()->forget('paypal_payment_order_id');
        $request->session()->forget('paypal_invoice_id');

        return redirect()->route('client.invoices.show', $invoiceId)->with('error', 'Failed to finalize PayPal payment. Please contact support.');
    }

    public function handlePaymentCancel(Request $request)
    {
        $invoiceId = $request->session()->get('paypal_invoice_id');

        // Clear all related session variables regardless
        $request->session()->forget('paypal_payment_order_id');
        $request->session()->forget('paypal_invoice_id');

        if ($invoiceId) {
            Log::info("Pago de PayPal cancelado por el usuario para Invoice ID: {$invoiceId}");
            return redirect()->route('client.invoices.show', $invoiceId)->with('info', 'El pago con PayPal fue cancelado.');
        } else {
            Log::info("Pago de PayPal cancelado por el usuario, pero no se encontró Invoice ID en sesión.");
            // Redirect to a general client dashboard or services page
            return redirect()->route('client.dashboard')->with('info', 'El pago con PayPal fue cancelado.');
        }
    }
}
```

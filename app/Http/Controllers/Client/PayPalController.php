<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
// Carbon might not be needed here anymore if only webhook used it
// use Illuminate\Support\Carbon;
// PayPalClient is likely not needed if only webhook used it directly
// use Srmklive\PayPal\Services\PayPal as PayPalClient;
// use App\Services\PaymentGatewayService; // Will be replaced by interface
use App\Interfaces\PaymentGatewayInterface; // Added
use Throwable;
use Exception; // Added for catching specific exception from service

class PayPalController extends Controller
{
    /**
     * Initiate the PayPal checkout process for an invoice.
     */
    public function checkout(Request $request, Invoice $invoice, PaymentGatewayInterface $paymentGatewayService) // Changed type hint
    {
        $this->authorize('pay', $invoice);

        if ($invoice->status !== 'unpaid') {
            return Redirect::route('client.invoices.show', $invoice->id)
                ->with('error', 'Esta factura no está pendiente de pago o ya ha sido pagada.');
        }

        try {
            // Method name in interface is createPaymentOrder
            $approveUrl = $paymentGatewayService->createPaymentOrder($invoice);

            if ($approveUrl) {
                return Redirect::away($approveUrl);
            } else {
                // This case should ideally be handled by an exception from the service
                // if approveUrl is critical and not found.
                Log::error('PayPalController: Approve URL not returned from PaymentGatewayService for Invoice ID: ' . $invoice->id);
                return redirect()->route('client.invoices.show', $invoice->id)->with('error', 'No se pudo obtener el enlace de aprobación de PayPal. Intente más tarde.');
            }

        } catch (Exception $e) { // Catching specific \Exception thrown by the service
            Log::error('PayPalController: Exception during PayPal order creation for Invoice ID: ' . $invoice->id, [
                'message' => $e->getMessage(),
                // 'trace' => $e->getTraceAsString() // Optionally log trace if needed for debugging
            ]);
            return redirect()->route('client.invoices.show', $invoice->id)->with('error', 'Error al procesar el pago con PayPal: ' . $e->getMessage());
        } catch (Throwable $th) { // Catch any other general errors
            Log::error('PayPalController: General Throwable for Invoice ID: ' . $invoice->id, [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
            return redirect()->route('client.invoices.show', $invoice->id)->with('error', 'Ocurrió un error inesperado al intentar conectar con PayPal.');
        }
    }

    /**
     * Handle the successful return from PayPal after payment.
     */
    public function success(Request $request, Invoice $invoice)
    {
        Log::info("PayPal payment approved by user for Invoice ID: {$invoice->id}. Waiting for webhook.", [
            'invoice_id' => $invoice->id,
            'paypal_order_id_from_invoice' => $invoice->paypal_order_id,
            'paypal_token_from_url' => $request->query('token'),
            'payer_id_from_url' => $request->query('PayerID')
        ]);

        return redirect()->route('client.invoices.show', $invoice->id)
                         ->with('success', '¡Gracias! Tu pago con PayPal está siendo procesado. El estado de tu factura se actualizará en breve una vez que recibamos la confirmación final.');
    }

    /**
     * Handle the cancellation of payment by the user at PayPal.
     */
    public function cancel(Request $request, Invoice $invoice)
    {
        Log::info("PayPal payment cancelled by user for Invoice ID: {$invoice->id}", [
            'invoice_id' => $invoice->id,
            'paypal_order_id_from_invoice' => $invoice->paypal_order_id,
            'paypal_token_from_url' => $request->query('token')
        ]);

        return redirect()->route('client.invoices.show', $invoice->id)
                         ->with('info', 'El proceso de pago con PayPal fue cancelado. Puedes intentar pagar de nuevo si lo deseas.');
    }

    // Webhook method has been moved to Webhook\PayPalWebhookController
}

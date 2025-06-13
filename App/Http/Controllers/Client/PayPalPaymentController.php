<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\PayPalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
            $successUrl = route('paypal.payment.success');
            $cancelUrl = route('paypal.payment.cancel');

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
}
```

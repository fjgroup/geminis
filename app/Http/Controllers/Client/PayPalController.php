<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Order; // Added
use App\Models\OrderActivity; // Added
use App\Models\Transaction; // Added
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Carbon; // Added
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Throwable;

class PayPalController extends Controller
{
    /**
     * Initiate the PayPal checkout process for an invoice.
     */
    public function checkout(Request $request, Invoice $invoice)
    {
        $this->authorize('pay', $invoice);

        if ($invoice->status !== 'unpaid') {
            return Redirect::route('client.invoices.show', $invoice->id)
                ->with('error', 'Esta factura no está pendiente de pago o ya ha sido pagada.');
        }

        try {
            $payPalClient = new PayPalClient(config('paypal'));
            // Force the config to be loaded for the SDK, especially the mode.
            // This ensures it uses .env values.
            $payPalClient->setApiCredentials(config('paypal'));


            $orderData = [
                "intent" => "CAPTURE",
                "purchase_units" => [
                    [
                        "amount" => [
                            "currency_code" => $invoice->currency_code,
                            "value" => number_format($invoice->total_amount, 2, '.', '')
                        ],
                        "description" => "Pago de Factura #{$invoice->invoice_number}",
                        "invoice_id" => $invoice->invoice_number,
                        "custom_id" => (string)$invoice->id, // Ensure custom_id is a string
                    ]
                ],
                "application_context" => [
                    "brand_name" => config('app.name', 'Laravel App'),
                    "return_url" => route('paypal.success', $invoice->id),
                    "cancel_url" => route('paypal.cancel', $invoice->id),
                    "shipping_preference" => "NO_SHIPPING",
                    "user_action" => "PAY_NOW",
                ]
            ];

            Log::info("PayPal Create Order Request for Invoice ID: {$invoice->id}", ['data' => $orderData]);
            $response = $payPalClient->createOrder($orderData);
            Log::info("PayPal Create Order Response for Invoice ID: {$invoice->id}", ['response' => $response]);


            if ($response && isset($response['id']) && $response['status'] === 'CREATED') {
                $invoice->paypal_order_id = $response['id'];
                $invoice->save();

                $approveUrl = null;
                foreach ($response['links'] as $link) {
                    if ($link['rel'] === 'approve') {
                        $approveUrl = $link['href'];
                        break;
                    }
                }

                if ($approveUrl) {
                    return Redirect::away($approveUrl);
                } else {
                    Log::error('PayPal Approve URL not found in response for Invoice ID: ' . $invoice->id, ['response' => $response]);
                    return redirect()->route('client.invoices.show', $invoice->id)->with('error', 'No se pudo obtener el enlace de aprobación de PayPal. Intente más tarde.');
                }
            } else {
                $errorMessage = 'Error al crear la orden en PayPal.';
                if (isset($response['error']['message'])) {
                    $errorMessage .= ' Detalle: ' . $response['error']['message'];
                } elseif (isset($response['message'])) {
                    $errorMessage .= ' Detalle: ' . $response['message'];
                } else if (isset($response['details'][0]['description'])) {
                     $errorMessage .= ' Detalle: ' . $response['details'][0]['description'];
                } else if (is_array($response) && !empty($response['message'])) { // Catch other forms of error messages
                    $errorMessage .= ' Detalle: ' . json_encode($response);
                }
                Log::error('PayPal Create Order Failed for Invoice ID: ' . $invoice->id, ['response' => $response ?? ['error' => 'Unknown error from SDK']]);
                return redirect()->route('client.invoices.show', $invoice->id)->with('error', $errorMessage);
            }
        } catch (Throwable $th) {
            Log::error('PayPal Checkout Exception for Invoice ID: ' . $invoice->id, [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
            return redirect()->route('client.invoices.show', $invoice->id)->with('error', 'Ocurrió un error inesperado al intentar conectar con PayPal: ' . $th->getMessage());
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

    /**
     * Handle incoming PayPal webhooks.
     */
    public function webhook(Request $request)
    {
        Log::info('PayPal Webhook Received', ['event_type' => $request->input('event_type'), 'resource' => $request->input('resource')]);

        // TODO: CRITICAL - Implement PayPal Webhook Signature Verification here.
        // Example (conceptual, check SDK for actual method and parameters):
        // $payPalClient = new PayPalClient(config('paypal'));
        // $payPalClient->setApiCredentials(config('paypal'));
        // $webhookId = config('paypal.webhook_id'); // From your PayPal developer portal settings
        // if (!$payPalClient->verifyWebhook($request, $webhookId)) {
        //     Log::warning('PayPal Webhook: Verification failed.');
        //     return response()->json(['status' => 'failed', 'message' => 'Webhook verification failed.'], 403);
        // }
        // Log::info('PayPal Webhook: Signature verified successfully.');


        $eventType = $request->input('event_type');
        $resource = $request->input('resource');

        if ($eventType === 'PAYMENT.CAPTURE.COMPLETED') {
            try {
                $paypalCaptureId = $resource['id'] ?? null;
                // Assuming 'custom_id' was set to our internal invoice ID during order creation
                $internalInvoiceId = $resource['purchase_units'][0]['custom_id'] ?? null;

                if (!$internalInvoiceId) {
                    Log::error('PayPal Webhook (PAYMENT.CAPTURE.COMPLETED): Could not extract internal_invoice_id (custom_id).', ['resource' => $resource]);
                    return response()->json(['status' => 'error', 'message' => 'Internal invoice identifier missing.'], 200); // Ack to PayPal
                }

                $invoice = Invoice::find($internalInvoiceId);

                if (!$invoice) {
                    Log::error("PayPal Webhook (PAYMENT.CAPTURE.COMPLETED): Invoice not found with ID: {$internalInvoiceId}.", ['resource' => $resource]);
                    return response()->json(['status' => 'error', 'message' => 'Invoice not found.'], 200);
                }

                if ($invoice->status === 'paid') {
                    Log::info("PayPal Webhook (PAYMENT.CAPTURE.COMPLETED): Invoice ID {$invoice->id} is already marked as paid.", ['paypal_capture_id' => $paypalCaptureId]);
                    return response()->json(['status' => 'success', 'message' => 'Invoice already paid.'], 200);
                }

                $paypalAmount = $resource['amount']['value'] ?? null;
                $paypalCurrency = $resource['amount']['currency_code'] ?? null;

                if (number_format((float)$invoice->total_amount, 2, '.', '') != number_format((float)$paypalAmount, 2, '.', '') || $invoice->currency_code != $paypalCurrency) {
                    Log::warning("PayPal Webhook (PAYMENT.CAPTURE.COMPLETED): Amount or currency mismatch for Invoice ID {$invoice->id}.", [
                        'invoice_amount' => $invoice->total_amount, 'paypal_amount' => $paypalAmount,
                        'invoice_currency' => $invoice->currency_code, 'paypal_currency' => $paypalCurrency,
                        'paypal_capture_id' => $paypalCaptureId
                    ]);
                    // Decide if you want to proceed or flag for manual review. For now, proceed but log.
                }

                // Start DB transaction
                \Illuminate\Support\Facades\DB::beginTransaction();

                $invoice->status = 'paid';
                $invoice->paid_date = Carbon::parse($resource['create_time'] ?? now());
                // $invoice->paypal_order_id was already saved at checkout initiation, can re-verify if needed:
                // if ($invoice->paypal_order_id !== ($resource['purchase_units'][0]['payments']['captures'][0]['id'] ?? $resource['id'])) { /* log mismatch */ }
                $invoice->save();

                Transaction::create([
                    'client_id' => $invoice->client_id,
                    'invoice_id' => $invoice->id,
                    'order_id' => $invoice->order_id,
                    'payment_method_id' => null, // Or determine if a generic "PayPal" PaymentMethod record exists
                    'gateway_slug' => 'paypal',
                    'gateway_transaction_id' => $paypalCaptureId,
                    'amount' => $paypalAmount,
                    'currency_code' => $paypalCurrency,
                    'status' => 'completed',
                    'type' => 'payment', // Or 'order_payment' if that's more specific
                    'transaction_date' => Carbon::parse($resource['create_time'] ?? now()),
                    'description' => "Pago vía PayPal para Factura #{$invoice->invoice_number}",
                    'fees_amount' => $resource['seller_receivable_breakdown']['paypal_fee']['value'] ?? 0, // If available
                ]);

                $order = $invoice->order()->with('items.product', 'client')->first();
                if ($order && $order->status === 'pending_payment') {
                    $order->status = 'paid_pending_execution';
                    $order->save();

                    OrderActivity::create([
                        'order_id' => $order->id,
                        'user_id' => $order->client_id, // Action by client via PayPal
                        'type' => 'payment_confirmed_paypal',
                        'details' => json_encode([
                            'invoice_id' => $invoice->id,
                            'invoice_number' => $invoice->invoice_number,
                            'paypal_capture_id' => $paypalCaptureId,
                            'amount' => $paypalAmount . ' ' . $paypalCurrency,
                        ])
                    ]);
                    Log::info("PayPal Webhook: Order {$order->id} paid. Service activation logic should be triggered here if applicable.", [
                        'order_id' => $order->id,
                        'product_type' => $order->items->first()->product->type ?? 'unknown'
                    ]);
                }

                \Illuminate\Support\Facades\DB::commit();
                Log::info("PayPal Webhook (PAYMENT.CAPTURE.COMPLETED): Successfully processed for Invoice ID {$invoice->id}.");

            } catch (Throwable $th) {
                \Illuminate\Support\Facades\DB::rollBack();
                Log::error('PayPal Webhook (PAYMENT.CAPTURE.COMPLETED) Processing Exception:', [
                    'message' => $th->getMessage(),
                    'trace' => $th->getTraceAsString(),
                    'resource' => $resource
                ]);
                // Still return 200 to PayPal to prevent retries for our internal processing error
                return response()->json(['status' => 'error', 'message' => 'Internal server error during webhook processing.'], 200);
            }
        } else {
            Log::info("PayPal Webhook: Received unhandled event type: {$eventType}");
        }

        return response()->json(['status' => 'success'], 200);
    }
}

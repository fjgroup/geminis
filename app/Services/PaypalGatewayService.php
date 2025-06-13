<?php

namespace App\Services;

use App\Interfaces\PaymentGatewayInterface;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\PaymentMethod; // Import PaymentMethod model
// use App\Models\Order; // Removed
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Throwable;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PaypalGatewayService implements PaymentGatewayInterface
{
    protected PayPalClient $payPalClient;

    public function __construct()
    {
        $this->payPalClient = new PayPalClient(config('paypal'));
        $this->payPalClient->setApiCredentials(config('paypal'));
    }

    /**
     * Initiate a payment order for the given invoice.
     *
     * @param Invoice $invoice The invoice to be paid.
     * @return string The redirect URL for the user to approve payment.
     * @throws \Exception If payment order creation fails.
     */
    public function createPaymentOrder(Invoice $invoice): string
    {
        // This method was previously named createPaypalOrder
        $orderData = [
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => $invoice->currency_code,
                        "value" => number_format($invoice->total_amount, 2, '.', '')
                    ],
                    "description" => "Pago de Factura #" . $invoice->invoice_number,
                    "invoice_id" => $invoice->invoice_number,
                    "custom_id" => (string)$invoice->id,
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

        Log::info("PaypalGatewayService: PayPal Create Order Request for Invoice ID: {$invoice->id}", ['data' => $orderData]);
        $response = $this->payPalClient->createOrder($orderData);
        Log::info("PaypalGatewayService: PayPal Create Order Response for Invoice ID: {$invoice->id}", ['response' => $response]);

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
                return $approveUrl;
            } else {
                Log::error('PaypalGatewayService: PayPal Approve URL not found for Invoice ID: ' . $invoice->id, ['response' => $response]);
                throw new Exception('No se pudo obtener el enlace de aprobación de PayPal de la respuesta.');
            }
        } else {
            $errorMessage = 'Error al crear la orden en PayPal.';
            if (isset($response['error']['message'])) {
                $errorMessage .= ' Detalle: ' . $response['error']['message'];
            } elseif (isset($response['message']) && !isset($response['id'])) {
                $errorMessage .= ' Detalle: ' . $response['message'];
            } else if (isset($response['details'][0]['description'])) {
                 $errorMessage .= ' Detalle: ' . $response['details'][0]['description'];
            } else if (is_array($response) && !empty($response['name']) && !empty($response['message'])) {
                 $errorMessage .= ' Detalle: ' . $response['name'] . ' - ' . $response['message'];
            } else {
                 $errorMessage .= ' Respuesta inesperada de PayPal.';
            }
            Log::error('PaypalGatewayService: PayPal Create Order Failed for Invoice ID: ' . $invoice->id, ['response' => $response ?? ['error' => 'Unknown error from SDK']]);
            throw new Exception($errorMessage);
        }
    }

    /**
     * Verify the signature of an incoming webhook request.
     *
     * @param Request $request The incoming HTTP request.
     * @return bool True if the signature is valid, false otherwise.
     * @throws \Exception If configuration for verification is missing or on other critical errors.
     */
    public function verifyWebhookSignature(Request $request): bool
    {
        $paypalMode = config('paypal.mode');
        $webhookIdKey = 'paypal.' . $paypalMode . '.webhook_id';
        $webhookId = config($webhookIdKey);

        if (empty($webhookId)) {
            Log::error('PaypalGatewayService: PayPal Webhook ID is not configured.', [
                'key_checked' => $webhookIdKey,
                'paypal_mode' => $paypalMode
            ]);
            // Throwing exception as this is a critical configuration error.
            throw new Exception('Webhook ID not configured for PayPal.');
        }

        // The srmklive/paypal-v2-sdk verifyWebHookSignature method might throw an exception on certain errors.
        // We catch Throwable to be safe, but specific exceptions from the SDK could also be caught if known.
        try {
            $headers = array_change_key_case($request->headers->all(), CASE_UPPER);

            $verified = $this->payPalClient->verifyWebHookSignature(
                $request->getContent(),
                $headers,
                $webhookId
            );

            if (!$verified || (isset($verified['verification_status']) && $verified['verification_status'] !== 'SUCCESS')) {
                Log::warning('PaypalGatewayService: PayPal Webhook signature verification failed.', [
                    'verification_result' => $verified ?? 'Not verified',
                    'event_type' => $request->input('event_type'),
                    'webhook_id_used' => $webhookId
                ]);
                return false;
            }

            Log::info('PaypalGatewayService: PayPal Webhook signature verified successfully.', ['event_type' => $request->input('event_type')]);
            return true;

        } catch (Throwable $th) {
            Log::error('PaypalGatewayService: Exception during PayPal webhook signature verification.', [
                'message' => $th->getMessage(),
                'trace_snippet' => substr($th->getTraceAsString(), 0, 500),
                'event_type' => $request->input('event_type')
            ]);
            // Re-throw or throw a new specific exception if the caller needs to distinguish this.
            // For now, returning false indicates verification failure due to an exception.
            // Depending on strictness, could throw new Exception('Error during signature verification: ' . $th->getMessage());
            return false;
        }
    }

    /**
     * Handle an incoming webhook notification from the payment gateway.
     *
     * @param Request $request The incoming HTTP request.
     * @return array A status array, e.g., ['status' => 'success', 'message' => '...']
     */
    public function handleWebhook(Request $request): array
    {
        try {
            if (!$this->verifyWebhookSignature($request)) {
                return ['status' => 'error', 'message' => 'Webhook signature verification failed.'];
            }
        } catch (Exception $e) {
            // Catch exceptions from verifyWebhookSignature (e.g., config missing)
            return ['status' => 'error', 'message' => $e->getMessage()];
        }

        $eventType = $request->input('event_type');
        $webhookResource = $request->input('resource', []);

        if ($eventType === 'PAYMENT.CAPTURE.COMPLETED') {
            // This is the logic from the old handlePaypalPaymentCompleted method
            try {
                $paypalCaptureId = $webhookResource['id'] ?? null;
                $internalInvoiceId = $webhookResource['purchase_units'][0]['custom_id'] ?? null;

                if (!$internalInvoiceId) {
                    Log::error('PaypalGatewayService (PAYMENT.CAPTURE.COMPLETED): Could not extract internal_invoice_id.', ['resource' => $webhookResource]);
                    return ['status' => 'error', 'message' => 'Internal invoice identifier missing.'];
                }

                $invoice = Invoice::find($internalInvoiceId);

                if (!$invoice) {
                    Log::error("PaypalGatewayService (PAYMENT.CAPTURE.COMPLETED): Invoice not found with ID: {$internalInvoiceId}.", ['resource' => $webhookResource]);
                    return ['status' => 'error', 'message' => 'Invoice not found.'];
                }

                if ($invoice->status === 'paid') {
                    Log::info("PaypalGatewayService (PAYMENT.CAPTURE.COMPLETED): Invoice ID {$invoice->id} already paid.", ['paypal_capture_id' => $paypalCaptureId]);
                    return ['status' => 'success', 'message' => 'Invoice already paid.'];
                }

                $paypalAmount = $webhookResource['amount']['value'] ?? null;
                $paypalCurrency = $webhookResource['amount']['currency_code'] ?? null;

                if (number_format((float)$invoice->total_amount, 2, '.', '') != number_format((float)$paypalAmount, 2, '.', '') || $invoice->currency_code != $paypalCurrency) {
                    Log::warning("PaypalGatewayService (PAYMENT.CAPTURE.COMPLETED): Amount/currency mismatch for Invoice ID {$invoice->id}.", [
                        'invoice_amount' => $invoice->total_amount, 'paypal_amount' => $paypalAmount,
                        'invoice_currency' => $invoice->currency_code, 'paypal_currency' => $paypalCurrency,
                    ]);
                }

                DB::beginTransaction();
                $invoice->status = 'paid';
                $invoice->paid_date = Carbon::parse($webhookResource['create_time'] ?? now());
                $invoice->save();

                $paypalPaymentMethod = PaymentMethod::where('slug', 'paypal')->first();

                Transaction::create([
                    'client_id' => $invoice->client_id,
                    'invoice_id' => $invoice->id,
                    // 'order_id' => $invoice->order_id, // LÍNEA ELIMINADA
                    'payment_method_id' => $paypalPaymentMethod ? $paypalPaymentMethod->id : null,
                    'gateway_slug' => 'paypal',
                    'gateway_transaction_id' => $paypalCaptureId,
                    'amount' => $paypalAmount,
                    'currency_code' => $paypalCurrency,
                    'status' => 'completed',
                    'type' => 'payment',
                    'transaction_date' => Carbon::parse($webhookResource['create_time'] ?? now()),
                    'description' => "Pago vía PayPal para Factura #{$invoice->invoice_number}",
                    'fees_amount' => $webhookResource['seller_receivable_breakdown']['paypal_fee']['value'] ?? 0,
                ]);

                DB::commit();
                Log::info("PaypalGatewayService (PAYMENT.CAPTURE.COMPLETED): Processed for Invoice ID {$invoice->id}.");
                return ['status' => 'success', 'message' => 'Webhook processed successfully. Invoice marked as paid.'];

            } catch (Throwable $th) {
                DB::rollBack();
                Log::error('PaypalGatewayService (PAYMENT.CAPTURE.COMPLETED) Exception:', [
                    'message' => $th->getMessage(),
                    'trace_snippet' => substr($th->getTraceAsString(), 0, 500),
                    'resource' => $webhookResource
                ]);
                return ['status' => 'error', 'message' => 'Internal server error during webhook processing.', 'exception_message' => $th->getMessage()];
            }
        } else {
            Log::info("PaypalGatewayService: Unhandled event type: {$eventType}", ['resource_id' => $webhookResource['id'] ?? 'N/A']);
            // It's important to return a success status for unhandled events too, so PayPal doesn't retry.
            return ['status' => 'success', 'message' => 'Webhook received but event type not handled.'];
        }
    }
}

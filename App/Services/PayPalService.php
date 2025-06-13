<?php

namespace App\Services;

use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Support\Facades\Log;
use App\Models\Invoice; // Asegúrate de importar el modelo Invoice

class PayPalService
{
    protected $payPalClient;

    public function __construct()
    {
        $this->payPalClient = new PayPalClient;
        // Cargar credenciales. El paquete srmklive/paypal v3 usualmente las toma automáticamente
        // desde config/paypal.php si se publicó y configuró el .env.
        // Si es necesario forzar la carga o se quiere ser explícito:
        // $this->payPalClient->setApiCredentials(config('paypal'));
        // $this->payPalClient->getAccessToken(); // Para obtener un token de acceso si no lo hace automático
    }

    /**
     * Crea una orden de pago en PayPal.
     *
     * @param \App\Models\Invoice $invoice La factura para la cual crear el pago.
     * @param string $returnUrl La URL a la que PayPal redirigirá tras la aprobación.
     * @param string $cancelUrl La URL a la que PayPal redirigirá tras la cancelación.
     * @return array|null Un array con el ID de la orden de PayPal y el enlace de aprobación, o null si falla.
     */
    public function createOrder(Invoice $invoice, string $returnUrl, string $cancelUrl): ?array
    {
        try {
            // Asegurarse de que el token de acceso está fresco si es necesario (algunas versiones del paquete lo hacen auto)
             $this->payPalClient->getAccessToken();

            $orderData = [
                "intent" => "CAPTURE",
                "purchase_units" => [
                    [
                        "amount" => [
                            "currency_code" => strtoupper($invoice->currency_code ?: config('paypal.currency', 'USD')),
                            "value" => number_format($invoice->total_amount, 2, '.', '')
                        ],
                        "description" => "Pago de Factura #" . $invoice->invoice_number,
                        "invoice_id" => (string) $invoice->id, // ID interno de tu factura, convertido a string
                        // "custom_id" => (string) $invoice->id, // Puedes usar custom_id para pasar tu ID de factura
                    ]
                ],
                "application_context" => [
                    "cancel_url" => $cancelUrl,
                    "return_url" => $returnUrl,
                    "brand_name" => config('app.name', 'FJGroupCA'),
                    "shipping_preference" => "NO_SHIPPING",
                    "user_action" => "PAY_NOW",
                ]
            ];

            $order = $this->payPalClient->createOrder($orderData);

            if (isset($order['id']) && $order['id'] != null && isset($order['links'])) {
                $approvalLink = null;
                foreach ($order['links'] as $link) {
                    if ($link['rel'] === 'approve') {
                        $approvalLink = $link['href'];
                        break;
                    }
                }
                if ($approvalLink) {
                    return [
                        'order_id' => $order['id'],
                        'approval_link' => $approvalLink,
                    ];
                }
            }

            // Loguear error si la estructura de la respuesta no es la esperada
            $errorMessage = 'Error al crear orden de PayPal: Respuesta inesperada o falta enlace de aprobación.';
            if (isset($order['message'])) {
                $errorMessage .= ' Mensaje de PayPal: ' . $order['message'];
            }
            if (isset($order['error'])) {
                 $errorMessage .= ' Error de PayPal: ' . json_encode($order['error']);
            }
            Log::error($errorMessage, ['invoice_id' => $invoice->id, 'paypal_response' => $order]);
            return null;

        } catch (\Exception $e) {
            Log::error('Excepción al crear orden de PayPal para la factura ID ' . $invoice->id . ': ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    // Futuros métodos:
    // public function captureOrder(string $orderId): ?array
    // public function verifyWebhookSignature(array $data, string $signature): bool

    /**
     * Captura un pago para una orden de PayPal existente.
     *
     * @param string $paypalOrderId El ID de la orden de PayPal a capturar.
     * @return array Un array con el resultado de la captura.
     */
    public function captureOrder(string $paypalOrderId): array
    {
        try {
            // Asegurarse de que el token de acceso está fresco si es necesario
            $this->payPalClient->getAccessToken();

            $response = $this->payPalClient->capturePaymentOrder($paypalOrderId);

            Log::info("PayPal Capture Order Response for Order ID {$paypalOrderId}: ", $response);

            if (isset($response['status']) && $response['status'] === 'COMPLETED') {
                $captureId = null;
                $feeAmount = null;

                if (isset($response['purchase_units'][0]['payments']['captures'][0]['id'])) {
                    $captureId = $response['purchase_units'][0]['payments']['captures'][0]['id'];
                }
                if (isset($response['purchase_units'][0]['payments']['captures'][0]['seller_receivable_breakdown']['paypal_fee']['value'])) {
                    $feeAmount = $response['purchase_units'][0]['payments']['captures'][0]['seller_receivable_breakdown']['paypal_fee']['value'];
                }

                return [
                    'status' => 'COMPLETED',
                    'paypal_capture_id' => $captureId,
                    'paypal_fee' => $feeAmount,
                    'full_response' => $response
                ];
            } else {
                $errorMessage = $response['message'] ?? 'Unknown error during capture.';
                if (isset($response['details'][0]['description'])) { // PayPal often puts more details here
                    $errorMessage = $response['details'][0]['description'];
                }
                Log::error("Failed to capture PayPal payment for Order ID {$paypalOrderId}. Status: " . ($response['status'] ?? 'Unknown'), $response);
                return [
                    'status' => $response['status'] ?? 'ERROR',
                    'message' => $errorMessage,
                    'full_response' => $response
                ];
            }
        } catch (\Exception $e) {
            Log::error("Exception during PayPal captureOrder for Order ID {$paypalOrderId}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'status' => 'EXCEPTION',
                'message' => $e->getMessage(),
                'full_response' => null
            ];
        }
    }
}

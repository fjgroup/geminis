<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Interfaces\PaymentGatewayInterface; // Changed to interface
use Illuminate\Support\Facades\Log;
// PayPalClient is no longer directly used here as verification is in the service
// use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PayPalWebhookController extends Controller
{
    /**
     * Handle incoming PayPal webhooks.
     *
     * @param Request $request
     * @param PaymentGatewayInterface $paymentGatewayService // Changed to interface
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, PaymentGatewayInterface $paymentGatewayService)
    {
        Log::info('PayPal Webhook Received (Webhook Controller)', [
            'event_type' => $request->input('event_type'),
            'resource_id' => $request->input('resource.id') ?? 'N/A'
        ]);

        $result = $paymentGatewayService->handleWebhook($request);

        if ($result['status'] === 'success') {
            return response()->json(['status' => 'success', 'message' => $result['message']], 200);
        }
        // Specific handling for signature verification failure, which might warrant a 403
        elseif ($result['status'] === 'error' && $result['message'] === 'Webhook signature verification failed.') {
             Log::warning("PayPalWebhookController: Webhook signature verification failed.", [
                'event_type' => $request->input('event_type'),
                'service_message' => $result['message'],
            ]);
            return response()->json(['status' => 'error', 'message' => $result['message']], 403);
        }
        // For other errors, including configuration errors from verifyWebhookSignature
        // or processing errors from the main logic.
        else { // $result['status'] === 'error'
            Log::error("PayPalWebhookController: Error processing webhook.", [
                'event_type' => $request->input('event_type'),
                'service_message' => $result['message'],
                'exception_message' => $result['exception_message'] ?? null,
                'resource_id' => $request->input('resource.id') ?? 'N/A'
            ]);
            // For most processing errors (invoice not found, already paid), PayPal expects a 200.
            // For critical config errors (like webhook ID missing, caught in service's verify method),
            // the service might throw an exception that leads to a 500, or return a specific message.
            // If the service's verifyWebhookSignature throws an exception for missing config,
            // handleWebhook catches it and returns an error message.
            // The HTTP status code here depends on the nature of $result['message'].
            // If it's "Webhook ID not configured.", a 500 might be more appropriate, but the service currently returns that message in an array.
            // Let's keep it simple and return 200 for now for business logic errors,
            // and rely on earlier 500 for truly critical unhandled exceptions or direct config errors in controller.
            // However, the service's verifyWebhookSignature now throws an exception for missing webhook_id.
            // The service's handleWebhook catches this and returns an error array.
            // So, the controller just translates this.
            // Let's refine: if the message indicates a server-side config issue, a 500 from controller might be better.
            // For now, we'll stick to the defined return from service.
            // The service's verifyWebhookSignature could return a more specific error type or code.
             if (str_contains($result['message'], 'Webhook ID not configured')) {
                return response()->json(['status' => 'error', 'message' => $result['message']], 500);
            }
            return response()->json(['status' => 'error', 'message' => $result['message']], 200);
        }
    }
}

<?php

namespace App\Interfaces;

use App\Models\Invoice;
use Illuminate\Http\Request; // Request is used for webhook handling

interface PaymentGatewayInterface
{
    /**
     * Initiate a payment order for the given invoice.
     *
     * @param Invoice $invoice The invoice to be paid.
     * @return string The redirect URL for the user to approve payment, or payment initiation data.
     * @throws \Exception If payment order creation fails.
     */
    public function createPaymentOrder(Invoice $invoice): string;

    /**
     * Handle an incoming webhook notification from the payment gateway.
     * This method will typically include signature verification internally.
     *
     * @param Request $request The incoming HTTP request.
     * @return array A status array, e.g., ['status' => 'success', 'message' => '...']
     *               or ['status' => 'error', 'message' => '...'].
     */
    public function handleWebhook(Request $request): array;

    /**
     * Verify the signature of an incoming webhook request.
     * This method might be called by handleWebhook or could be exposed if direct verification
     * is needed before further processing by the main handleWebhook logic.
     *
     * @param Request $request The incoming HTTP request.
     * @return bool True if the signature is valid, false otherwise.
     * @throws \Exception If configuration for verification is missing or on other critical errors during verification.
     */
    public function verifyWebhookSignature(Request $request): bool;
}

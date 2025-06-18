<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Required for Rule::in

class StoreTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * For now, we'll return true and handle authorization in the controller via policy.
     * Or, we could inject InvoicePolicy and check here.
     * @return bool
     */
    public function authorize(): bool
    {
        // Authorization will be handled by TransactionPolicy in the controller
        // or by checking admin role directly if a dedicated policy is not yet created.
        return true; 
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'invoice_id' => ['required', 'integer', 'exists:invoices,id'], // Ensure invoice_id is passed and exists
            'transaction_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency_code' => ['required', 'string', 'size:3'], // e.g., USD
            'gateway_slug' => ['required', 'string', 'max:255'], // e.g., 'manual_payment', 'stripe_card'
            'gateway_transaction_id' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(['payment', 'refund', 'credit_added'])], // Only these types for manual entry initially
            'status' => ['required', 'string', Rule::in(['completed', 'pending', 'failed'])], // Manual entries usually 'completed'
            'description' => ['nullable', 'string', 'max:255'],
            'fees_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'invoice_id.required' => 'The invoice ID is required.',
            'invoice_id.exists' => 'The selected invoice does not exist.',
            'amount.min' => 'The payment amount must be at least 0.01.',
            // Add other custom messages as needed
        ];
    }
}

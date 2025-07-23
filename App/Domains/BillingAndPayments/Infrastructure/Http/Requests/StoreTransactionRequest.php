<?php

namespace App\Domains\BillingAndPayments\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request para crear transacciones en arquitectura hexagonal
 * 
 * Ubicado en Infrastructure layer como adaptador de entrada HTTP
 * Cumple con Single Responsibility Principle - solo valida datos de entrada
 */
class StoreTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization will be handled by TransactionPolicy in the controller
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
            'invoice_id' => ['required', 'integer', 'exists:invoices,id'],
            'client_id' => ['required', 'integer', 'exists:users,id'],
            'reseller_id' => ['nullable', 'integer', 'exists:users,id'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'transaction_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency_code' => ['required', 'string', 'size:3'],
            'gateway_slug' => ['required', 'string', 'max:255'],
            'gateway_transaction_id' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(['payment', 'refund', 'credit_added'])],
            'status' => ['required', 'string', Rule::in(['completed', 'pending', 'failed'])],
            'description' => ['nullable', 'string', 'max:255'],
            'fees_amount' => ['nullable', 'numeric', 'min:0'],
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'invoice_id.required' => 'El ID de la factura es obligatorio.',
            'invoice_id.exists' => 'La factura seleccionada no existe.',
            'client_id.required' => 'El ID del cliente es obligatorio.',
            'client_id.exists' => 'El cliente seleccionado no existe.',
            'payment_method_id.required' => 'El método de pago es obligatorio.',
            'payment_method_id.exists' => 'El método de pago seleccionado no existe.',
            'amount.required' => 'El monto es obligatorio.',
            'amount.min' => 'El monto debe ser al menos 0.01.',
            'currency_code.required' => 'El código de moneda es obligatorio.',
            'currency_code.size' => 'El código de moneda debe tener exactamente 3 caracteres.',
            'gateway_slug.required' => 'El gateway de pago es obligatorio.',
            'type.required' => 'El tipo de transacción es obligatorio.',
            'type.in' => 'El tipo de transacción debe ser payment, refund o credit_added.',
            'status.required' => 'El estado de la transacción es obligatorio.',
            'status.in' => 'El estado debe ser completed, pending o failed.',
            'transaction_date.required' => 'La fecha de transacción es obligatoria.',
            'transaction_date.date' => 'La fecha de transacción debe ser una fecha válida.',
        ];
    }

    /**
     * Get transaction data for creation
     */
    public function getTransactionData(): array
    {
        return $this->validated();
    }
}

<?php

namespace App\Domains\BillingAndPayments\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request para confirmar pagos manuales en arquitectura hexagonal
 * 
 * Ubicado en Infrastructure layer como adaptador de entrada HTTP
 * Cumple con Single Responsibility Principle - solo valida datos de entrada
 */
class ConfirmManualPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // La autorización se manejará en el controlador a través de una Policy
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'invoice_id' => ['required', 'integer', 'exists:invoices,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transaction_date' => ['required', 'date', 'before_or_equal:today'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'gateway_transaction_id' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'invoice_id.required' => 'El ID de la factura es obligatorio.',
            'invoice_id.integer' => 'El ID de la factura debe ser un número entero.',
            'invoice_id.exists' => 'La factura seleccionada no existe.',
            'amount.required' => 'El monto del pago es obligatorio.',
            'amount.numeric' => 'El monto del pago debe ser un número.',
            'amount.min' => 'El monto del pago debe ser al menos 0.01.',
            'transaction_date.required' => 'La fecha de la transacción es obligatoria.',
            'transaction_date.date' => 'La fecha de la transacción no es una fecha válida.',
            'transaction_date.before_or_equal' => 'La fecha de la transacción no puede ser futura.',
            'payment_method_id.required' => 'El método de pago es obligatorio.',
            'payment_method_id.integer' => 'El ID del método de pago debe ser un número entero.',
            'payment_method_id.exists' => 'El método de pago seleccionado no existe.',
            'reference_number.max' => 'El número de referencia no puede exceder los 255 caracteres.',
            'notes.max' => 'Las notas no pueden exceder los 1000 caracteres.',
            'gateway_transaction_id.max' => 'El ID de transacción del gateway no puede exceder los 255 caracteres.',
        ];
    }

    /**
     * Get payment confirmation data
     */
    public function getPaymentData(): array
    {
        return $this->validated();
    }
}

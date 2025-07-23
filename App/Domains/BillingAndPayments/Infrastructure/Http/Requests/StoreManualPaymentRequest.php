<?php

namespace App\Domains\BillingAndPayments\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request para pagos manuales de cliente en arquitectura hexagonal
 * 
 * Ubicado en Infrastructure layer como adaptador de entrada HTTP
 * Cumple con Single Responsibility Principle - solo valida datos de entrada
 */
class StoreManualPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by policies in the controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'payment_method_id' => [
                'required',
                'integer',
                Rule::exists('payment_methods', 'id')->where('is_active', true)
            ],
            'reference_number' => [
                'required', 
                'string', 
                'max:100', 
                Rule::unique('transactions', 'gateway_transaction_id')
            ],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'amount_paid' => ['nullable', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:500'],
            'payment_receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'payment_method_id.required' => 'Por favor, selecciona un método de pago.',
            'payment_method_id.exists' => 'El método de pago seleccionado no es válido o no está activo.',
            'reference_number.required' => 'Por favor, ingresa el número de referencia de tu pago.',
            'reference_number.max' => 'El número de referencia no debe exceder los 100 caracteres.',
            'reference_number.unique' => 'Este número de referencia ya ha sido registrado.',
            'payment_date.required' => 'Por favor, selecciona la fecha en que realizaste el pago.',
            'payment_date.date' => 'La fecha de pago no es válida.',
            'payment_date.before_or_equal' => 'La fecha de pago no puede ser futura.',
            'amount_paid.numeric' => 'El monto pagado debe ser un número.',
            'amount_paid.min' => 'El monto pagado debe ser al menos 0.01.',
            'notes.max' => 'Las notas no pueden exceder los 500 caracteres.',
            'payment_receipt.file' => 'El comprobante debe ser un archivo.',
            'payment_receipt.mimes' => 'El comprobante debe ser una imagen (jpg, jpeg, png) o PDF.',
            'payment_receipt.max' => 'El comprobante no puede ser mayor a 2MB.',
        ];
    }

    /**
     * Get payment data for processing
     */
    public function getPaymentData(): array
    {
        return $this->validated();
    }
}

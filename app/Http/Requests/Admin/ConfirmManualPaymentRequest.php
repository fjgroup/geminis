<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConfirmManualPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // La autorización se manejará en el controlador a través de una Policy
        // o verificando el rol de administrador directamente.
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
            'transaction_date' => ['required', 'date'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
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
            'invoice_id.required' => 'El ID de la factura es obligatorio.',
            'invoice_id.integer' => 'El ID de la factura debe ser un número entero.',
            'invoice_id.exists' => 'La factura seleccionada no existe.',
            'amount.required' => 'El monto del pago es obligatorio.',
            'amount.numeric' => 'El monto del pago debe ser un número.',
            'amount.min' => 'El monto del pago debe ser al menos :min.',
            'transaction_date.required' => 'La fecha de la transacción es obligatoria.',
            'transaction_date.date' => 'La fecha de la transacción no es una fecha válida.',
            'payment_method_id.required' => 'El método de pago es obligatorio.',
            'payment_method_id.integer' => 'El ID del método de pago debe ser un número entero.',
            'payment_method_id.exists' => 'El método de pago seleccionado no existe.',
            'reference_number.max' => 'El número de referencia no puede exceder los :max caracteres.',
            'notes.max' => 'Las notas no pueden exceder los :max caracteres.',
        ];
    }
}

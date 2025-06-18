<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreManualPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Actual authorization is handled in the controller using policies on the Invoice model.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by $this->authorize('pay', $invoice) in the controller.
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
            'reference_number' => ['required', 'string', 'max:100', Rule::unique('transactions', 'gateway_transaction_id')],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            // You might also want to add a field for 'amount_paid' if you allow partial manual payments
            // or if the client needs to specify the amount they sent.
            // For now, it assumes the full invoice amount.
            // 'payment_receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'], // Optional receipt upload
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
            'payment_method_id.required' => 'Por favor, selecciona un método de pago.',
            'payment_method_id.exists' => 'El método de pago seleccionado no es válido o no está activo.',
            'reference_number.required' => 'Por favor, ingresa el número de referencia de tu pago.',
            'reference_number.max' => 'El número de referencia no debe exceder los 100 caracteres.',
            'reference_number.unique' => 'Este número de referencia ya ha sido registrado.',
            'payment_date.required' => 'Por favor, selecciona la fecha en que realizaste el pago.',
            'payment_date.date' => 'La fecha de pago no es válida.',
            'payment_date.before_or_equal' => 'La fecha de pago no puede ser futura.',
        ];
    }
}

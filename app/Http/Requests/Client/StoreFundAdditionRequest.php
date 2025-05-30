<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class StoreFundAdditionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::check(); // Ensures the user is logged in. Specific role checks can be added if needed.
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
            'reference_number' => ['required', 'string', 'max:100'],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'amount' => ['required', 'numeric', 'min:1.00'], // Ensuring a positive value, adjust min as needed
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
            'payment_date.required' => 'Por favor, selecciona la fecha en que realizaste el pago.',
            'payment_date.date' => 'La fecha de pago no es válida.',
            'payment_date.before_or_equal' => 'La fecha de pago no puede ser futura.',
            'amount.required' => 'Por favor, ingresa el monto que deseas agregar.',
            'amount.numeric' => 'El monto debe ser un número.',
            'amount.min' => 'El monto debe ser de al menos :min.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'payment_method_id' => 'método de pago',
            'reference_number' => 'número de referencia',
            'payment_date' => 'fecha de pago',
            'amount' => 'monto',
        ];
    }
}

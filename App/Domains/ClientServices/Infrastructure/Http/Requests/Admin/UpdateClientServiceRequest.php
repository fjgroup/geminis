<?php

namespace App\Domains\ClientServices\Infrastructure\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request para actualizar un servicio de cliente existente
 * 
 * Ubicado en Infrastructure layer como Input Adapter
 * Aplica Single Responsibility Principle - solo valida datos de entrada
 */
class UpdateClientServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'client_id' => 'sometimes|exists:users,id',
            'product_id' => 'sometimes|exists:products,id',
            'billing_cycle_id' => 'sometimes|exists:billing_cycles,id',
            'billing_amount' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:active,suspended,terminated,pending',
            'next_due_date' => 'sometimes|date',
            'domain' => 'nullable|string|max:255',
            'username' => 'nullable|string|max:100',
            'password' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'configurable_options' => 'nullable|array',
            'configurable_options.*.option_id' => 'required_with:configurable_options|exists:configurable_options,id',
            'configurable_options.*.quantity' => 'required_with:configurable_options|integer|min:1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'client_id.exists' => 'El cliente seleccionado no existe.',
            'product_id.exists' => 'El producto seleccionado no existe.',
            'billing_cycle_id.exists' => 'El ciclo de facturación seleccionado no existe.',
            'billing_amount.numeric' => 'El monto de facturación debe ser un número.',
            'billing_amount.min' => 'El monto de facturación debe ser mayor o igual a 0.',
            'status.in' => 'El estado debe ser: active, suspended, terminated o pending.',
            'next_due_date.date' => 'La fecha de vencimiento debe ser una fecha válida.',
            'domain.max' => 'El dominio no puede exceder 255 caracteres.',
            'username.max' => 'El nombre de usuario no puede exceder 100 caracteres.',
            'password.max' => 'La contraseña no puede exceder 255 caracteres.',
            'notes.max' => 'Las notas no pueden exceder 1000 caracteres.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'client_id' => 'cliente',
            'product_id' => 'producto',
            'billing_cycle_id' => 'ciclo de facturación',
            'billing_amount' => 'monto de facturación',
            'status' => 'estado',
            'next_due_date' => 'fecha de vencimiento',
            'domain' => 'dominio',
            'username' => 'nombre de usuario',
            'password' => 'contraseña',
            'notes' => 'notas',
        ];
    }
}

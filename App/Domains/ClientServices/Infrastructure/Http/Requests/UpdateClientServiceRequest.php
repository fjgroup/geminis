<?php

namespace App\Domains\ClientServices\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request para actualizar servicios de cliente en arquitectura hexagonal
 *
 * Ubicado en Infrastructure layer como adaptador de entrada HTTP
 * Cumple con Single Responsibility Principle - solo valida datos de entrada
 */
class UpdateClientServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: Implementar ClientServicePolicy y usarla aquí
        // $clientService = $this->route('client_service');
        // return $this->user()->can('update', $clientService);
        return true; // Temporalmente true
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_id' => ['sometimes', 'integer', Rule::exists('users', 'id')],
            'product_id' => ['sometimes', 'integer', Rule::exists('products', 'id')],
            'product_pricing_id' => ['sometimes', 'integer', Rule::exists('product_pricings', 'id')],
            'billing_cycle_id' => ['sometimes', 'integer', Rule::exists('billing_cycles', 'id')],
            'registration_date' => ['sometimes', 'date'],
            'next_due_date' => ['sometimes', 'date', 'after_or_equal:registration_date'],
            'billing_amount' => ['sometimes', 'numeric', 'min:0'],
            'status' => ['sometimes', Rule::in(['pending', 'active', 'suspended', 'terminated', 'cancelled', 'fraud', 'pending_configuration', 'provisioning_failed'])],
            'domain_name' => ['nullable', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255'],
            'password_encrypted' => ['nullable', 'string', 'min:6'],
            'reseller_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', 'reseller')],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('password_encrypted') && $this->password_encrypted === '') {
            $this->merge(['password_encrypted' => null]);
        }
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'client_id.exists' => 'El cliente seleccionado no existe.',
            'product_id.exists' => 'El producto seleccionado no existe.',
            'product_pricing_id.exists' => 'El precio del producto seleccionado no existe.',
            'billing_cycle_id.exists' => 'El ciclo de facturación seleccionado no existe.',
            'registration_date.date' => 'La fecha de registro debe ser una fecha válida.',
            'next_due_date.date' => 'La fecha de vencimiento debe ser una fecha válida.',
            'next_due_date.after_or_equal' => 'La próxima fecha de vencimiento debe ser igual o posterior a la fecha de registro.',
            'billing_amount.numeric' => 'El monto de facturación debe ser un número.',
            'billing_amount.min' => 'El monto de facturación no puede ser negativo.',
            'status.in' => 'El estado debe ser uno de los valores permitidos.',
            'domain_name.max' => 'El nombre de dominio no puede exceder los 255 caracteres.',
            'username.max' => 'El nombre de usuario no puede exceder los 255 caracteres.',
            'password_encrypted.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'reseller_id.exists' => 'El reseller seleccionado no existe o no es válido.',
            'notes.max' => 'Las notas no pueden exceder los 1000 caracteres.',
        ];
    }

    /**
     * Get client service data for update
     */
    public function getClientServiceData(): array
    {
        return $this->validated();
    }
}

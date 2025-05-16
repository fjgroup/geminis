<?php

namespace App\Http\Requests\Admin;

use App\Models\ClientService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClientServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: Implementar ClientServicePolicy y usarla aquí
        // return $this->user()->can('create', ClientService::class);
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
            'client_id' => ['required', 'integer', Rule::exists('users', 'id')], // Asegurar que el cliente exista
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'product_pricing_id' => ['required', 'integer', Rule::exists('product_pricings', 'id')], // Asegurar que el pricing exista y pertenezca al producto sería ideal
            'registration_date' => ['required', 'date'],
            'next_due_date' => ['required', 'date', 'after_or_equal:registration_date'],
            'billing_amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['pending', 'active', 'suspended', 'terminated', 'cancelled', 'fraud'])],
            'domain_name' => ['nullable', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255'],
            'password_encrypted' => ['nullable', 'string', 'min:6'], // Si se envía, que tenga un mínimo
            'reseller_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', 'reseller')],
            'server_id' => ['nullable', 'integer', Rule::exists('servers', 'id')], // Cuando la tabla servers exista
            'notes' => ['nullable', 'string'],
            // 'order_id' no se suele crear manualmente aquí, se asociaría si viene de una orden.
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->password_encrypted === null || $this->password_encrypted === '') {
            $this->merge(['password_encrypted' => null]);
        }
    }

    public function messages(): array
    {
        return [
            'next_due_date.after_or_equal' => 'La próxima fecha de vencimiento debe ser igual o posterior a la fecha de registro.',
        ];
    }
}

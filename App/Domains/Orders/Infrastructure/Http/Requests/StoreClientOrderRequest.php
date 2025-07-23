<?php

namespace App\Domains\Orders\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Request para crear órdenes de cliente en arquitectura hexagonal
 * 
 * Ubicado en Infrastructure layer como adaptador de entrada HTTP
 * Cumple con Single Responsibility Principle - solo valida datos de entrada
 */
class StoreClientOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && in_array(Auth::user()->role, ['client', 'reseller']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_pricing_id' => ['required', 'integer', 'exists:product_pricings,id'],
            'billing_cycle_id' => ['required', 'integer', 'exists:billing_cycles,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'configurable_options' => ['nullable', 'array'],
            'configurable_options.*' => ['integer', 'exists:configurable_options,id'],
            'domain_names' => ['nullable', 'array'],
            'domain_names.*' => [
                'string',
                'max:255',
                'regex:/^(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,63}$/'
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
            'client_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'El producto es requerido.',
            'product_id.exists' => 'El producto seleccionado no existe.',
            'product_pricing_id.required' => 'El precio del producto es requerido.',
            'product_pricing_id.exists' => 'El precio del producto seleccionado no es válido.',
            'billing_cycle_id.required' => 'El ciclo de facturación es requerido.',
            'billing_cycle_id.exists' => 'El ciclo de facturación seleccionado no es válido.',
            'quantity.required' => 'La cantidad es requerida.',
            'quantity.min' => 'La cantidad debe ser al menos 1.',
            'quantity.max' => 'La cantidad no puede exceder 100.',
            'configurable_options.*.exists' => 'Una de las opciones configurables seleccionadas no es válida.',
            'domain_names.*.regex' => 'Uno de los nombres de dominio no tiene un formato válido.',
            'notes.max' => 'Las notas no pueden exceder los 1000 caracteres.',
            'client_notes.max' => 'Las notas del cliente no pueden exceder los 1000 caracteres.',
        ];
    }

    /**
     * Get order data for creation
     */
    public function getOrderData(): array
    {
        $data = $this->validated();
        $data['client_id'] = Auth::id();
        $data['status'] = 'pending_payment';
        $data['ip_address'] = $this->ip();
        
        return $data;
    }

    /**
     * Get configurable options
     */
    public function getConfigurableOptions(): array
    {
        return $this->input('configurable_options', []);
    }

    /**
     * Get domain names
     */
    public function getDomainNames(): array
    {
        return $this->input('domain_names', []);
    }
}

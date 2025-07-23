<?php

namespace App\Domains\Orders\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request para operaciones del carrito en arquitectura hexagonal
 * 
 * Ubicado en Infrastructure layer como adaptador de entrada HTTP
 * Cumple con Single Responsibility Principle - solo valida datos de entrada
 */
class CartOperationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['client', 'reseller']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'operation' => ['required', 'string', Rule::in(['add_domain', 'add_service', 'remove_domain', 'remove_service', 'update_quantity'])],
            'product_id' => ['required_unless:operation,remove_domain,remove_service', 'integer', 'exists:products,id'],
            'pricing_id' => ['required_unless:operation,remove_domain,remove_service', 'integer', 'exists:product_pricings,id'],
            'billing_cycle_id' => ['nullable', 'integer', 'exists:billing_cycles,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:100'],
            'domain_name' => ['required_if:operation,add_domain', 'nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9][a-zA-Z0-9-]{0,61}[a-zA-Z0-9]?\.[a-zA-Z]{2,}$/'],
            'tld_extension' => ['required_if:operation,add_domain', 'nullable', 'string', 'max:10', 'regex:/^[a-zA-Z]{2,}$/'],
            'override_price' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'configurable_options' => ['nullable', 'array', 'max:20'],
            'configurable_options.*' => ['nullable'],
            'account_id' => ['required_if:operation,remove_domain,remove_service', 'nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'operation.required' => 'La operación es requerida.',
            'operation.in' => 'La operación debe ser una de las permitidas.',
            'product_id.required_unless' => 'El producto es requerido para esta operación.',
            'product_id.exists' => 'El producto seleccionado no existe.',
            'pricing_id.required_unless' => 'El plan de precios es requerido para esta operación.',
            'pricing_id.exists' => 'El plan de precios seleccionado no es válido.',
            'billing_cycle_id.exists' => 'El ciclo de facturación seleccionado no es válido.',
            'quantity.min' => 'La cantidad mínima es 1.',
            'quantity.max' => 'La cantidad máxima permitida es 100.',
            'domain_name.required_if' => 'El nombre de dominio es requerido para agregar un dominio.',
            'domain_name.regex' => 'El formato del dominio no es válido. Debe ser como ejemplo.com',
            'tld_extension.required_if' => 'La extensión del dominio es requerida.',
            'tld_extension.regex' => 'La extensión del dominio debe contener solo letras.',
            'override_price.max' => 'El precio no puede exceder $9,999.99',
            'configurable_options.max' => 'No se pueden seleccionar más de 20 opciones configurables.',
            'account_id.required_if' => 'El ID de cuenta es requerido para esta operación.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitizar el nombre del dominio
        if ($this->has('domain_name')) {
            $this->merge([
                'domain_name' => strtolower(trim($this->input('domain_name'))),
            ]);
        }

        // Sanitizar la extensión TLD
        if ($this->has('tld_extension')) {
            $this->merge([
                'tld_extension' => strtolower(trim($this->input('tld_extension'))),
            ]);
        }
    }

    /**
     * Get cart operation data
     */
    public function getCartOperationData(): array
    {
        return $this->validated();
    }

    /**
     * Get the operation type
     */
    public function getOperation(): string
    {
        return $this->input('operation');
    }

    /**
     * Check if operation is adding a domain
     */
    public function isAddingDomain(): bool
    {
        return $this->getOperation() === 'add_domain';
    }

    /**
     * Check if operation is adding a service
     */
    public function isAddingService(): bool
    {
        return $this->getOperation() === 'add_service';
    }

    /**
     * Check if operation is removing something
     */
    public function isRemoving(): bool
    {
        return in_array($this->getOperation(), ['remove_domain', 'remove_service']);
    }
}

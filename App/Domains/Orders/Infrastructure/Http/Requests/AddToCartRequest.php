<?php

namespace App\Domains\Orders\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request para agregar productos al carrito en arquitectura hexagonal
 * 
 * Ubicado en Infrastructure layer como adaptador de entrada HTTP
 * Cumple con Single Responsibility Principle - solo valida datos de entrada
 * La lógica de validación compleja debe estar en servicios especializados
 */
class AddToCartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Permitir a todos los usuarios (anónimos y logueados)
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(function ($query) {
                    $query->where('status', 'active')
                        ->where('is_publicly_available', true);
                }),
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:100',
            ],
            'billing_cycle_id' => [
                'required',
                'integer',
                'exists:billing_cycles,id',
            ],
            'configurable_options' => [
                'sometimes',
                'array',
            ],
            'configurable_options.*' => [
                'integer',
                'exists:configurable_options,id',
            ],
            'domain_name' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}$/'
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'El producto es requerido.',
            'product_id.exists' => 'El producto seleccionado no existe o no está disponible.',
            'quantity.required' => 'La cantidad es requerida.',
            'quantity.min' => 'La cantidad mínima es 1.',
            'quantity.max' => 'La cantidad máxima permitida es 100.',
            'quantity.integer' => 'La cantidad debe ser un número entero.',
            'billing_cycle_id.required' => 'El ciclo de facturación es requerido.',
            'billing_cycle_id.exists' => 'El ciclo de facturación seleccionado no es válido.',
            'configurable_options.array' => 'Las opciones configurables deben ser un array.',
            'configurable_options.*.exists' => 'Una o más opciones configurables no son válidas.',
            'domain_name.regex' => 'El formato del nombre de dominio no es válido.',
            'domain_name.max' => 'El nombre de dominio no puede exceder los 255 caracteres.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'product_id' => 'producto',
            'quantity' => 'cantidad',
            'billing_cycle_id' => 'ciclo de facturación',
            'configurable_options' => 'opciones configurables',
            'domain_name' => 'nombre de dominio',
        ];
    }

    /**
     * Get cart item data
     */
    public function getCartItemData(): array
    {
        return $this->validated();
    }

    /**
     * Get the product ID
     */
    public function getProductId(): int
    {
        return $this->input('product_id');
    }

    /**
     * Get the quantity
     */
    public function getQuantity(): int
    {
        return $this->input('quantity');
    }

    /**
     * Get the billing cycle ID
     */
    public function getBillingCycleId(): int
    {
        return $this->input('billing_cycle_id');
    }

    /**
     * Get configurable options
     */
    public function getConfigurableOptions(): array
    {
        return $this->input('configurable_options', []);
    }

    /**
     * Get domain name if provided
     */
    public function getDomainName(): ?string
    {
        return $this->input('domain_name');
    }
}

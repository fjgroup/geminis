<?php

namespace App\Domains\Orders\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request para actualizar carrito en arquitectura hexagonal
 * 
 * Ubicado en Infrastructure layer como adaptador de entrada HTTP
 * Cumple con Single Responsibility Principle - solo valida datos de entrada
 * La lógica de validación compleja debe estar en servicios especializados
 */
class UpdateCartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Permitir a todos los usuarios
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'cart_item_id' => [
                'required',
                'string',
                'max:255'
            ],
            'quantity' => [
                'required',
                'integer',
                'min:0', // 0 para remover el item
                'max:100'
            ],
            'billing_cycle_id' => [
                'nullable',
                'integer',
                'exists:billing_cycles,id'
            ],
            'configurable_options' => [
                'nullable',
                'array'
            ],
            'configurable_options.*' => [
                'integer',
                'exists:configurable_options,id'
            ],
            'domain_name' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}$/'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'cart_item_id.required' => 'El ID del elemento del carrito es requerido.',
            'cart_item_id.max' => 'El ID del elemento del carrito no puede exceder los 255 caracteres.',
            'quantity.required' => 'La cantidad es requerida.',
            'quantity.min' => 'La cantidad no puede ser negativa.',
            'quantity.max' => 'La cantidad máxima permitida es 100.',
            'quantity.integer' => 'La cantidad debe ser un número entero.',
            'billing_cycle_id.exists' => 'El ciclo de facturación seleccionado no es válido.',
            'configurable_options.array' => 'Las opciones configurables deben ser un array.',
            'configurable_options.*.exists' => 'Una de las opciones configurables seleccionadas no es válida.',
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
            'cart_item_id' => 'elemento del carrito',
            'quantity' => 'cantidad',
            'billing_cycle_id' => 'ciclo de facturación',
            'configurable_options' => 'opciones configurables',
            'domain_name' => 'nombre de dominio',
        ];
    }

    /**
     * Get cart update data
     */
    public function getCartUpdateData(): array
    {
        return $this->validated();
    }

    /**
     * Get cart item ID
     */
    public function getCartItemId(): string
    {
        return $this->input('cart_item_id');
    }

    /**
     * Get quantity
     */
    public function getQuantity(): int
    {
        return $this->input('quantity');
    }

    /**
     * Check if item should be removed (quantity = 0)
     */
    public function shouldRemoveItem(): bool
    {
        return $this->getQuantity() === 0;
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

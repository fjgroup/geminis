<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateCartRequest
 * 
 * Form Request para validar datos al actualizar el carrito
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
            'product_id' => [
                'required',
                'integer',
                'exists:products,id'
            ],
            'quantity' => [
                'required',
                'integer',
                'min:0', // 0 para remover el item
                'max:100'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'El ID del producto es requerido.',
            'product_id.exists' => 'El producto no existe.',
            'quantity.required' => 'La cantidad es requerida.',
            'quantity.min' => 'La cantidad no puede ser negativa.',
            'quantity.max' => 'La cantidad máxima permitida es 100.',
            'quantity.integer' => 'La cantidad debe ser un número entero.'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validación adicional: verificar stock si se trackea
            if ($this->has('product_id') && $this->has('quantity') && $this->input('quantity') > 0) {
                $product = \App\Models\Product::find($this->input('product_id'));
                
                if ($product && $product->track_stock) {
                    if ($product->stock_quantity < $this->input('quantity')) {
                        $validator->errors()->add('quantity', 
                            "Stock insuficiente. Solo hay {$product->stock_quantity} unidades disponibles."
                        );
                    }
                }
            }
        });
    }
}

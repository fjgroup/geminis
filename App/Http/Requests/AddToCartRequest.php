<?php
namespace App\Http\Requests;

use Illuminate\Validation\Rule;

/**
 * Class AddToCartRequest
 *
 * Form Request para validar datos al agregar productos al carrito
 */
class AddToCartRequest extends BaseFormRequest
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
            'product_id'             => [
                'required',
                'integer',
                'exists:products,id',
                Rule::exists('products', 'id')->where(function ($query) {
                    $query->where('status', 'active')
                        ->where('is_publicly_available', true);
                }),
            ],
            'quantity'               => [
                'required',
                'integer',
                'min:1',
                'max:100', // Límite razonable para prevenir abuso
            ],
            'configurable_options'   => [
                'sometimes',
                'array',
            ],
            'configurable_options.*' => [
                'integer',
                'exists:configurable_options,id',
            ],
            'billing_cycle_id'       => [
                'sometimes',
                'integer',
                'exists:billing_cycles,id',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'product_id.required'           => 'El ID del producto es requerido.',
            'product_id.exists'             => 'El producto seleccionado no existe o no está disponible.',
            'quantity.required'             => 'La cantidad es requerida.',
            'quantity.min'                  => 'La cantidad mínima es 1.',
            'quantity.max'                  => 'La cantidad máxima permitida es 100.',
            'quantity.integer'              => 'La cantidad debe ser un número entero.',
            'configurable_options.array'    => 'Las opciones configurables deben ser un array.',
            'configurable_options.*.exists' => 'Una o más opciones configurables no son válidas.',
            'billing_cycle_id.exists'       => 'El ciclo de facturación seleccionado no es válido.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'product_id'           => 'producto',
            'quantity'             => 'cantidad',
            'configurable_options' => 'opciones configurables',
            'billing_cycle_id'     => 'ciclo de facturación',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validación adicional: verificar stock si se trackea
            if ($this->has('product_id') && $this->has('quantity')) {
                $product = \App\Models\Product::find($this->input('product_id'));

                if ($product && $product->track_stock) {
                    if ($product->stock_quantity < $this->input('quantity')) {
                        $validator->errors()->add('quantity',
                            "Stock insuficiente. Solo hay {$product->stock_quantity} unidades disponibles."
                        );
                    }
                }

                // Validar que el producto no requiera aprobación manual
                if ($product && $product->requires_approval) {
                    $validator->errors()->add('product_id',
                        'Este producto requiere aprobación manual y no puede ser agregado al carrito directamente.'
                    );
                }
            }
        });
    }

}

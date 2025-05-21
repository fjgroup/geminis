<?php

namespace App\Http\Requests\Admin;
use App\Models\ProductPricing;
use App\Models\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductPricingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Usar la ProductPricingPolicy o una lógica directa
        return $this->user()->can('create', ProductPricing::class);


    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'billing_cycle_id' => 'required|exists:billing_cycles,id', // Cambiado a validar el ID
            'price' => 'required|numeric|min:0.00',
            'setup_fee' => 'nullable|numeric|min:0.00',
            'currency_code' => 'required|string|max:3',
            'is_active' => 'required|boolean',
            // product_id se tomará de la ruta y se añadirá en el controlador, no es parte del form del precio.
        ];
    }
}

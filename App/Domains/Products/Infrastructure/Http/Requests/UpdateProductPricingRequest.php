<?php

namespace App\Domains\Products\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Domains\Products\Infrastructure\Persistence\Models\ProductPricing;

class UpdateProductPricingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Laravel intentará resolver el modelo ProductPricing basado en el parámetro de ruta 'pricing'.
        $pricing = $this->route('pricing');

        // Si $pricing no es una instancia de ProductPricing (por ejemplo, si la ruta no se resolvió correctamente),
        // es mejor denegar por defecto. Aunque con route model binding esto es menos probable.
        return $pricing instanceof ProductPricing && $this->user()->can('update', $pricing);


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
            'price' => 'required|numeric|min:0',
            'setup_fee' => 'nullable|numeric|min:0',
            'currency_code' => 'required|string|max:3',
            'is_active' => 'required|boolean',

        ];
    }
}

<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_pricing_id' => ['required', 'integer', 'exists:product_pricings,id'],
            'configurable_options' => ['nullable', 'array'], // Changed key from configurable_options_ids
            'configurable_options.*' => ['integer', 'exists:configurable_options,id'], // Ensure items in array are valid IDs
            'notes' => ['nullable', 'string', 'max:5000'], // Added for order notes
        ];
    }
}

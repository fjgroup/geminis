<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule; // Import Rule

class PlaceOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Allow all authenticated users to make this request.
        // Specific product/order policies will be handled in the controller or action.
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // $product is available via $this->route('product')
        $product = $this->route('product');

        return [
            'billing_cycle_id' => [
                'required',
                // Ensure the product_pricing_id (referred to as billing_cycle_id in the form)
                // exists and belongs to the product specified in the route.
                Rule::exists('product_pricings', 'id')->where(function ($query) use ($product) {
                    if ($product) { // Check if product is resolved from route
                        return $query->where('product_id', $product->id);
                    }
                    // If product is not resolved, this rule might fail or pass unexpectedly based on DB state.
                    // However, route model binding should ensure $product is available.
                    // If it's not, a different error (e.g., 404) would likely occur before validation.
                    return $query; // Fallback, though ideally $product should always be present.
                }),
            ],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes_to_client' => ['nullable', 'string', 'max:2000'], // Optional notes
            // TODO: Add validation for configurable options if they are part of this process in the future
            // 'configurable_options' => ['nullable', 'array'],
            // 'configurable_options.*.option_id' => ['required_with:configurable_options', 'exists:configurable_options,id'],
            // 'configurable_options.*.value_id' => ['required_with:configurable_options', 'exists:configurable_option_values,id'], // Example if options have pre-defined values
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'billing_cycle_id.exists' => 'The selected billing cycle is not valid for this product.',
        ];
    }
}

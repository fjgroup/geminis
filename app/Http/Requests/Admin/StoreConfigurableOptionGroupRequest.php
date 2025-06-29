<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConfigurableOptionGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: Implement authorization logic (e.g., check if user is admin)
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
            'name'          => ['required', 'string', 'max:255'],
            'slug'          => ['nullable', 'string', 'max:255', 'unique:configurable_option_groups,slug'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'product_ids'   => ['nullable', 'array'],
            'product_ids.*' => ['integer', Rule::exists('products', 'id')],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_active'     => ['boolean'],
            'is_required'   => ['boolean'],
        ];
    }
}

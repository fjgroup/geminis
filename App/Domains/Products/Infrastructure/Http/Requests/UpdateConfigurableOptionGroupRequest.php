<?php

namespace App\Domains\Products\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateConfigurableOptionGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by policies
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $groupId = $this->route('configurableOptionGroup')->id ?? $this->route('configurableOptionGroup');

        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'display_order' => 'integer|min:0',
            'is_required' => 'boolean',
            'option_type' => 'required|in:dropdown,radio,checkbox,text,textarea',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del grupo es requerido.',
            'name.max' => 'El nombre no puede exceder 255 caracteres.',
            'option_type.required' => 'El tipo de opción es requerido.',
            'option_type.in' => 'El tipo de opción debe ser: dropdown, radio, checkbox, text o textarea.',
        ];
    }
}

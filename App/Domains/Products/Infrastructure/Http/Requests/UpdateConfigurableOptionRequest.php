<?php

namespace App\Domains\Products\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConfigurableOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'option_type' => 'required|in:dropdown,radio,checkbox,text,textarea',
            'display_order' => 'integer|min:0',
            'is_required' => 'boolean',
            'configurable_option_group_id' => 'required|exists:configurable_option_groups,id',
        ];
    }
}

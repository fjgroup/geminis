<?php

namespace App\Domains\Products\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreConfigurableOptionGroupRequest extends FormRequest
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
            'display_order' => 'integer|min:0',
            'is_required' => 'boolean',
            'option_type' => 'required|in:dropdown,radio,checkbox,text,textarea',
        ];
    }
}

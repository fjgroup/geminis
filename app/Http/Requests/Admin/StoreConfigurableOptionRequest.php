<?php

namespace App\Http\Requests\Admin;
use App\Models\ConfigurableOption; // Asegúrate de importar el modelo

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConfigurableOptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $option = $this->route('option'); // 'option' es el nombre del parámetro en la ruta
        return $this->user()->can('create', ConfigurableOption::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Obtener el ID del grupo padre desde la ruta.
        // Asumimos que el parámetro en la ruta se llama 'configurable_option_group'
        // como en: Route::post('configurable-option-groups/{configurable_option_group}/options', ...)
        $groupId = $this->route('configurable_option_group')->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('configurable_options')->where(function ($query) use ($groupId) {
                    return $query->where('group_id', $groupId);
                })
            ],
            'value' => ['nullable', 'string', 'max:255'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            // group_id se tomará de la ruta, no del formulario directamente
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->display_order === null || $this->display_order === '') {
            $this->merge(['display_order' => 0]);
        }
    }
}

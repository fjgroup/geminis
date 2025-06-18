<?php

namespace App\Http\Requests\Admin;
use Illuminate\Validation\Rule; // Asegúrate de importar Rule

use Illuminate\Foundation\Http\FormRequest;

class UpdateConfigurableOptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: Implementar lógica de autorización, ej:
         $option = $this->route('option'); // 'option' es el nombre del parámetro en la ruta
        return $this->user()->can('update', $option);

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Obtener el ID de la opción desde la ruta.
        // Asumimos que el parámetro en la ruta se llama 'option'
        // como en: Route::put('options/{option}', ...)
        $optionId = $this->route('option')->id;
        // Obtener el group_id de la opción que se está actualizando.
        $groupId = $this->route('option')->group_id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('configurable_options')->where(function ($query) use ($groupId) {
                    return $query->where('group_id', $groupId);
                })->ignore($optionId)
            ],
            'value' => ['nullable', 'string', 'max:255'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->display_order === null || $this->display_order === '') {
            $this->merge(['display_order' => 0]);
        }
    }
}

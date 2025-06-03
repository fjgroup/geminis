<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Product;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Obtener el producto de la ruta.
        // Asumiendo que tu parámetro de ruta es 'product' (ej. Route::resource('products', ...))
        $product = $this->route('product');
        return $this->user()->can('update', $product);

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('products', 'name')->ignore($this->route('product')?->id)],
            // El slug se genera en el controlador si el nombre cambia.
            // Si se enviara un slug desde el form (actualmente no se hace), esta regla lo validaría.
            'slug' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($this->route('product')?->id)],
            'description' => ['sometimes', 'nullable', 'string'],
            'product_type_id' => ['sometimes', 'required', 'integer', 'exists:product_types,id'],
            'type' => ['sometimes', 'nullable', 'string', Rule::in(['shared_hosting', 'vps', 'dedicated_server', 'domain_registration', 'ssl_certificate', 'other'])], // Old field, make nullable or remove if fully deprecated
            'module_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'owner_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'inactive', 'hidden'])],
            'is_publicly_available' => ['sometimes', 'boolean'], // Booleans are false if not present
            'is_resellable_by_default' => ['sometimes', 'boolean'], // Booleans are false if not present

            // Validación para los grupos de opciones configurables
            'configurable_option_groups' => ['sometimes', 'nullable', 'array'],

            // Validar que cada clave en configurable_option_groups sea un ID de grupo existente
            'configurable_option_groups.*' => ['sometimes', 'array'], // Cada elemento del array es un objeto/array
            'configurable_option_groups.*.display_order' => ['required_with:configurable_option_groups.*', 'integer', 'min:0'],
            
            // 'welcome_email_template_id' => ['nullable', 'integer', 'exists:email_templates,id'], // Si tienes esta tabla
            // 'display_order' => ['nullable', 'integer'],
        ];

    }
}

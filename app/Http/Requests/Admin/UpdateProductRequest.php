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
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($this->route('product')?->id)],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string', Rule::in(['shared_hosting', 'vps', 'dedicated_server', 'domain_registration', 'ssl_certificate', 'other'])],
            'module_name' => ['nullable', 'string', 'max:255'],
            'owner_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive', 'hidden'])],
            'is_publicly_available' => ['required', 'boolean'],
            'is_resellable_by_default' => ['required', 'boolean'],
            // 'welcome_email_template_id' => ['nullable', 'integer', 'exists:email_templates,id'], // Si tienes esta tabla
            // 'display_order' => ['nullable', 'integer'],
        ];

    }
}

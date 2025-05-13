<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Product; // Necesario para la autorización de creaciónapp/Providers/AuthServiceProvider.php
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Verificar si el usuario autenticado puede crear productos
        return $this->user()->can('create', Product::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('products', 'name')],
            // El slug se puede generar automáticamente a partir del nombre en el controlador,
            // o si se envía, debe ser único.
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')],
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

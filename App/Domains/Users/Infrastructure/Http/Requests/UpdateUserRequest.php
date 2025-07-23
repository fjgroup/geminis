<?php

namespace App\Domains\Users\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

/**
 * Request para actualizar usuarios en arquitectura hexagonal
 * 
 * Ubicado en Infrastructure layer como adaptador de entrada HTTP
 * Cumple con Single Responsibility Principle - solo valida datos de entrada
 */
class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Obtener el usuario que se está intentando actualizar desde la ruta
        $userToUpdate = $this->route('user');
        return $this->user()->can('update', $userToUpdate);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')->id;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', Rule::in(['admin', 'client', 'reseller'])],
            'reseller_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    return $query->where('role', 'reseller');
                })
            ],
            'company_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'language_code' => ['nullable', 'string', 'max:10'],
            'currency_code' => ['nullable', 'string', 'max:3'],
        ];

        // Reglas para ResellerProfile (aplicables si role es 'reseller')
        if ($this->input('role') === 'reseller') {
            $userBeingUpdated = $this->route('user');
            $resellerProfileIdToIgnore = $userBeingUpdated->resellerProfile?->id;

            $rules['reseller_profile.brand_name'] = ['nullable', 'string', 'max:255'];
            $rules['reseller_profile.custom_domain'] = [
                'nullable',
                'string',
                'max:255',
                Rule::unique('reseller_profiles', 'custom_domain')->when($resellerProfileIdToIgnore, function ($rule) use ($resellerProfileIdToIgnore) {
                    return $rule->ignore($resellerProfileIdToIgnore);
                })
            ];
            $rules['reseller_profile.logo_url'] = ['nullable', 'url', 'max:255'];
            $rules['reseller_profile.support_email'] = ['nullable', 'email', 'max:255'];
            $rules['reseller_profile.terms_url'] = ['nullable', 'url', 'max:255'];
            $rules['reseller_profile.allow_custom_products'] = ['boolean'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'Este email ya está registrado por otro usuario.',
            'reseller_id.exists' => 'El reseller seleccionado no existe o no es válido.',
            'role.in' => 'El rol debe ser admin, client o reseller.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ];
    }
}

<?php

namespace App\Http\Requests\Admin;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

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
                    return $query->where('role', 'reseller'); // Asegurar que el ID sea de un revendedor
                })
            ],
            'company_name' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:255'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state_province' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'], // Asegúrate que el campo en la BD sea 'country'
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'language_code' => ['nullable', 'string', 'max:10'],
            'currency_code' => ['nullable', 'string', 'max:3'],
        ];

        // Reglas para ResellerProfile (aplicables si role es 'reseller')
        // (Asegúrate que esto ya esté en tu UpdateUserRequest.php como lo tenías)
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
}

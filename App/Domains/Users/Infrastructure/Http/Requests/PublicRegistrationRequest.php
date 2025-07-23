<?php

namespace App\Domains\Users\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Request para registro público en arquitectura hexagonal
 * 
 * Ubicado en Infrastructure layer como adaptador de entrada HTTP
 * Cumple con Single Responsibility Principle - solo valida datos de entrada
 * La lógica de validación compleja debe estar en servicios especializados
 */
class PublicRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
                'regex:/^[a-zA-ZÀ-ÿ\s]+$/' // Solo letras y espacios
            ],
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                'unique:users,email'
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
            'company_name' => [
                'nullable',
                'string',
                'max:255',
                'min:2'
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[\+]?[0-9\s\-\(\)]+$/'
            ],
            'country' => [
                'required',
                'string',
                'size:2',
                'regex:/^[A-Z]{2}$/'
            ],
            'terms_accepted' => [
                'required',
                'accepted'
            ],
            'privacy_accepted' => [
                'required',
                'accepted'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es requerido.',
            'name.min' => 'El nombre debe tener al menos 2 caracteres.',
            'name.regex' => 'El nombre solo puede contener letras y espacios.',
            'email.required' => 'El email es requerido.',
            'email.email' => 'El formato del email no es válido.',
            'email.unique' => 'Este email ya está registrado.',
            'password.required' => 'La contraseña es requerida.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'company_name.min' => 'El nombre de la empresa debe tener al menos 2 caracteres.',
            'phone.regex' => 'El formato del teléfono no es válido.',
            'country.required' => 'El país es requerido.',
            'country.size' => 'El código de país debe tener exactamente 2 caracteres.',
            'country.regex' => 'El código de país debe estar en mayúsculas.',
            'terms_accepted.required' => 'Debes aceptar los términos y condiciones.',
            'terms_accepted.accepted' => 'Debes aceptar los términos y condiciones.',
            'privacy_accepted.required' => 'Debes aceptar la política de privacidad.',
            'privacy_accepted.accepted' => 'Debes aceptar la política de privacidad.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'email' => 'correo electrónico',
            'password' => 'contraseña',
            'company_name' => 'nombre de la empresa',
            'phone' => 'teléfono',
            'country' => 'país',
            'terms_accepted' => 'términos y condiciones',
            'privacy_accepted' => 'política de privacidad'
        ];
    }

    /**
     * Get the registration data
     */
    public function getRegistrationData(): array
    {
        return $this->only([
            'name',
            'email',
            'password',
            'company_name',
            'phone',
            'country'
        ]);
    }
}

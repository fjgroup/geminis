<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Class PublicRegistrationRequest
 * 
 * Form Request para validar registro de usuario en checkout público
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
                'min:2'
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
                'max:255'
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
            'email.required' => 'El email es requerido.',
            'email.email' => 'El formato del email no es válido.',
            'email.unique' => 'Este email ya está registrado.',
            'password.required' => 'La contraseña es requerida.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
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
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validación adicional del nombre
            $name = $this->input('name');
            if ($name) {
                // Verificar que no contenga solo números
                if (is_numeric($name)) {
                    $validator->errors()->add('name', 'El nombre no puede contener solo números.');
                }

                // Verificar que no contenga caracteres especiales peligrosos
                if (preg_match('/[<>"\']/', $name)) {
                    $validator->errors()->add('name', 'El nombre contiene caracteres no permitidos.');
                }
            }

            // Validación adicional del email
            $email = $this->input('email');
            if ($email) {
                // Verificar dominios temporales conocidos
                $tempDomains = ['10minutemail.com', 'guerrillamail.com', 'mailinator.com'];
                $emailDomain = substr(strrchr($email, "@"), 1);
                
                if (in_array(strtolower($emailDomain), $tempDomains)) {
                    $validator->errors()->add('email', 'No se permiten emails temporales.');
                }
            }

            // Validación adicional de la empresa
            $companyName = $this->input('company_name');
            if ($companyName && strlen($companyName) < 2) {
                $validator->errors()->add('company_name', 'El nombre de la empresa debe tener al menos 2 caracteres.');
            }
        });
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        // Log de intentos de registro fallidos para detectar posibles ataques
        \Illuminate\Support\Facades\Log::warning('Validación fallida en registro público', [
            'errors' => $validator->errors()->toArray(),
            'email' => $this->input('email'),
            'ip' => $this->ip(),
            'user_agent' => $this->userAgent()
        ]);

        parent::failedValidation($validator);
    }
}

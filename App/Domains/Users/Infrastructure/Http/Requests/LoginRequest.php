<?php

namespace App\Domains\Users\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request para login en arquitectura hexagonal
 * 
 * Ubicado en Infrastructure layer como adaptador de entrada HTTP
 * Cumple con Single Responsibility Principle - solo valida datos de entrada
 * La lógica de autenticación debe estar en un Use Case o Service
 */
class LoginRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'remember' => ['boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe tener un formato válido.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ];
    }

    /**
     * Get the credentials for authentication
     */
    public function getCredentials(): array
    {
        return $this->only('email', 'password');
    }

    /**
     * Check if remember me is requested
     */
    public function shouldRemember(): bool
    {
        return $this->boolean('remember');
    }
}

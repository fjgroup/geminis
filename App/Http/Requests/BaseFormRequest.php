<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

/**
 * Class BaseFormRequest
 * 
 * Clase base para Form Requests con funcionalidades comunes
 * Proporciona validaciones reutilizables y manejo de errores estandarizado
 */
abstract class BaseFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Por defecto, permite el acceso. Las clases hijas pueden sobrescribir esto.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get common validation rules that can be reused
     */
    protected function getCommonRules(): array
    {
        return [
            'email' => [
                'email:rfc,dns',
                'max:255',
                function ($attribute, $value, $fail) {
                    if ($this->isTemporaryEmail($value)) {
                        $fail('No se permiten emails temporales.');
                    }
                }
            ],
            'password' => [
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
            ],
            'phone' => [
                'regex:/^[\+]?[0-9\s\-\(\)]+$/',
                'max:20'
            ],
            'name' => [
                'string',
                'max:255',
                'min:2',
                function ($attribute, $value, $fail) {
                    if (is_numeric($value)) {
                        $fail('El nombre no puede contener solo números.');
                    }
                    if (preg_match('/[<>"\']/', $value)) {
                        $fail('El nombre contiene caracteres no permitidos.');
                    }
                }
            ],
            'company_name' => [
                'nullable',
                'string',
                'max:255',
                'min:2'
            ],
            'country' => [
                'string',
                'size:2',
                'regex:/^[A-Z]{2}$/'
            ],
            'currency' => [
                'string',
                'size:3',
                'in:USD,EUR,GBP,CAD,AUD'
            ],
            'amount' => [
                'numeric',
                'min:0',
                'max:999999.99'
            ],
            'domain' => [
                'regex:/^[a-zA-Z0-9][a-zA-Z0-9-]{0,61}[a-zA-Z0-9]?\.[a-zA-Z]{2,}$/',
                'max:255',
                function ($attribute, $value, $fail) {
                    if ($this->isReservedDomain($value)) {
                        $fail('Este dominio está reservado y no puede ser usado.');
                    }
                }
            ]
        ];
    }

    /**
     * Get common validation messages
     */
    protected function getCommonMessages(): array
    {
        return [
            'email.email' => 'El formato del email no es válido.',
            'email.max' => 'El email no puede tener más de 255 caracteres.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.regex' => 'La contraseña debe contener al menos una mayúscula, una minúscula, un número y un símbolo.',
            'phone.regex' => 'El formato del teléfono no es válido.',
            'phone.max' => 'El teléfono no puede tener más de 20 caracteres.',
            'name.string' => 'El nombre debe ser texto.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'name.min' => 'El nombre debe tener al menos 2 caracteres.',
            'company_name.min' => 'El nombre de la empresa debe tener al menos 2 caracteres.',
            'company_name.max' => 'El nombre de la empresa no puede tener más de 255 caracteres.',
            'country.size' => 'El código de país debe tener exactamente 2 caracteres.',
            'country.regex' => 'El código de país debe estar en mayúsculas.',
            'currency.size' => 'El código de moneda debe tener exactamente 3 caracteres.',
            'currency.in' => 'La moneda seleccionada no es válida.',
            'amount.numeric' => 'El monto debe ser un número.',
            'amount.min' => 'El monto no puede ser negativo.',
            'amount.max' => 'El monto no puede exceder 999,999.99.',
            'domain.regex' => 'El formato del dominio no es válido.',
            'domain.max' => 'El dominio no puede tener más de 255 caracteres.',
        ];
    }

    /**
     * Get common attributes for error messages
     */
    protected function getCommonAttributes(): array
    {
        return [
            'email' => 'correo electrónico',
            'password' => 'contraseña',
            'phone' => 'teléfono',
            'name' => 'nombre',
            'company_name' => 'nombre de la empresa',
            'country' => 'país',
            'currency' => 'moneda',
            'amount' => 'monto',
            'domain' => 'dominio',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        // Log de validaciones fallidas para análisis
        Log::warning('Validación fallida en ' . static::class, [
            'errors' => $validator->errors()->toArray(),
            'input' => $this->getSafeInputForLogging(),
            'ip' => $this->ip(),
            'user_agent' => $this->userAgent(),
            'user_id' => auth()->id(),
            'route' => $this->route()?->getName(),
            'method' => $this->method(),
            'url' => $this->url()
        ]);

        parent::failedValidation($validator);
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization(): void
    {
        // Log de intentos de acceso no autorizados
        Log::warning('Autorización fallida en ' . static::class, [
            'user_id' => auth()->id(),
            'ip' => $this->ip(),
            'user_agent' => $this->userAgent(),
            'route' => $this->route()?->getName(),
            'method' => $this->method(),
            'url' => $this->url()
        ]);

        parent::failedAuthorization();
    }

    /**
     * Get safe input data for logging (excluding sensitive fields)
     */
    protected function getSafeInputForLogging(): array
    {
        $input = $this->all();
        
        // Campos sensibles que no deben loggearse
        $sensitiveFields = [
            'password',
            'password_confirmation',
            'current_password',
            'new_password',
            'card_number',
            'cvv',
            'card_token',
            'bank_account',
            'ssn',
            'tax_id'
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($input[$field])) {
                $input[$field] = '[REDACTED]';
            }
        }

        return $input;
    }

    /**
     * Check if email is from a temporary email provider
     */
    protected function isTemporaryEmail(string $email): bool
    {
        $tempDomains = [
            '10minutemail.com',
            'guerrillamail.com',
            'mailinator.com',
            'tempmail.org',
            'throwaway.email',
            'temp-mail.org',
            'yopmail.com',
            'maildrop.cc'
        ];

        $domain = substr(strrchr($email, "@"), 1);
        return in_array(strtolower($domain), $tempDomains);
    }

    /**
     * Check if domain is reserved
     */
    protected function isReservedDomain(string $domain): bool
    {
        $reservedDomains = [
            'localhost',
            'example.com',
            'example.org',
            'example.net',
            'test.com',
            'test.org',
            'test.net',
            'invalid',
            'local'
        ];

        return in_array(strtolower($domain), $reservedDomains);
    }

    /**
     * Sanitize string input
     */
    protected function sanitizeString(string $value): string
    {
        // Remover caracteres peligrosos
        $value = strip_tags($value);
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        
        // Remover espacios extra
        $value = trim($value);
        $value = preg_replace('/\s+/', ' ', $value);
        
        return $value;
    }

    /**
     * Validate that a value is a valid UUID
     */
    protected function isValidUuid(string $uuid): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid);
    }

    /**
     * Get rate limiting key for this request
     */
    protected function getRateLimitKey(): string
    {
        return static::class . '|' . $this->ip();
    }
}

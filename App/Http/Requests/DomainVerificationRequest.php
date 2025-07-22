<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class DomainVerificationRequest
 * 
 * Form Request para validar verificación de dominio en checkout público
 */
class DomainVerificationRequest extends FormRequest
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
            'domain' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9][a-zA-Z0-9-]{0,61}[a-zA-Z0-9]?\.[a-zA-Z]{2,}$/'
            ],
            'action' => [
                'required',
                'string',
                'in:register,existing'
            ],
            'billing_cycle_id' => [
                'required',
                'integer',
                'exists:billing_cycles,id'
            ],
            'configurable_options' => [
                'nullable',
                'array'
            ],
            'configurable_options.*' => [
                'integer',
                'min:0'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'domain.required' => 'El dominio es requerido.',
            'domain.regex' => 'El formato del dominio no es válido.',
            'action.required' => 'Debes seleccionar una acción para el dominio.',
            'action.in' => 'La acción del dominio debe ser "register" o "existing".',
            'billing_cycle_id.required' => 'Debes seleccionar un ciclo de facturación.',
            'billing_cycle_id.exists' => 'El ciclo de facturación seleccionado no es válido.',
            'configurable_options.array' => 'Las opciones configurables deben ser un array.',
            'configurable_options.*.integer' => 'Las opciones configurables deben ser números enteros.',
            'configurable_options.*.min' => 'Las opciones configurables no pueden ser negativas.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'domain' => 'dominio',
            'action' => 'acción del dominio',
            'billing_cycle_id' => 'ciclo de facturación',
            'configurable_options' => 'opciones configurables'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validación adicional del dominio
            $domain = $this->input('domain');
            if ($domain) {
                // Verificar que no sea un dominio reservado
                $reservedDomains = ['localhost', 'example.com', 'test.com'];
                if (in_array(strtolower($domain), $reservedDomains)) {
                    $validator->errors()->add('domain', 'Este dominio está reservado y no puede ser usado.');
                }

                // Verificar longitud mínima
                if (strlen($domain) < 4) {
                    $validator->errors()->add('domain', 'El dominio debe tener al menos 4 caracteres.');
                }
            }
        });
    }
}

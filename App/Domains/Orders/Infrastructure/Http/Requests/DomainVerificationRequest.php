<?php

namespace App\Domains\Orders\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request para verificación de dominio en arquitectura hexagonal
 * 
 * Ubicado en Infrastructure layer como adaptador de entrada HTTP
 * Cumple con Single Responsibility Principle - solo valida datos de entrada
 * La lógica de validación compleja debe estar en servicios especializados
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
     * Get domain verification data
     */
    public function getDomainVerificationData(): array
    {
        return $this->validated();
    }

    /**
     * Get the domain name
     */
    public function getDomain(): string
    {
        return $this->input('domain');
    }

    /**
     * Get the action type
     */
    public function getAction(): string
    {
        return $this->input('action');
    }

    /**
     * Check if domain should be registered
     */
    public function shouldRegisterDomain(): bool
    {
        return $this->getAction() === 'register';
    }

    /**
     * Check if domain is existing
     */
    public function isExistingDomain(): bool
    {
        return $this->getAction() === 'existing';
    }

    /**
     * Get billing cycle ID
     */
    public function getBillingCycleId(): int
    {
        return $this->input('billing_cycle_id');
    }

    /**
     * Get configurable options
     */
    public function getConfigurableOptions(): array
    {
        return $this->input('configurable_options', []);
    }
}

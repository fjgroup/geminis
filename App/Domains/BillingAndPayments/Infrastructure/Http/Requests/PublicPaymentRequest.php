<?php

namespace App\Domains\BillingAndPayments\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request para pagos públicos en arquitectura hexagonal
 * 
 * Ubicado en Infrastructure layer como adaptador de entrada HTTP
 * Cumple con Single Responsibility Principle - solo valida datos de entrada
 * La lógica de validación compleja debe estar en servicios especializados
 */
class PublicPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Solo usuarios autenticados pueden procesar pagos
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'payment_method_id' => [
                'required',
                'integer',
                'exists:payment_methods,id'
            ],
            'invoice_id' => [
                'required',
                'integer',
                'exists:invoices,id'
            ],
            'terms_accepted' => [
                'required',
                'accepted'
            ],
            'privacy_accepted' => [
                'required',
                'accepted'
            ],
            // Campos opcionales para métodos específicos
            'card_token' => [
                'nullable',
                'string',
                'max:255'
            ],
            'bank_reference' => [
                'nullable',
                'string',
                'max:100',
                'min:5'
            ],
            'gateway_data' => [
                'nullable',
                'array'
            ],
            'return_url' => [
                'nullable',
                'url'
            ],
            'cancel_url' => [
                'nullable',
                'url'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'payment_method_id.required' => 'Debes seleccionar un método de pago.',
            'payment_method_id.exists' => 'El método de pago seleccionado no es válido.',
            'invoice_id.required' => 'Se requiere una factura válida.',
            'invoice_id.exists' => 'La factura seleccionada no existe.',
            'terms_accepted.required' => 'Debes aceptar los términos y condiciones.',
            'terms_accepted.accepted' => 'Debes aceptar los términos y condiciones.',
            'privacy_accepted.required' => 'Debes aceptar la política de privacidad.',
            'privacy_accepted.accepted' => 'Debes aceptar la política de privacidad.',
            'bank_reference.min' => 'La referencia bancaria debe tener al menos 5 caracteres.',
            'bank_reference.max' => 'La referencia bancaria no puede exceder los 100 caracteres.',
            'return_url.url' => 'La URL de retorno debe ser válida.',
            'cancel_url.url' => 'La URL de cancelación debe ser válida.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'payment_method_id' => 'método de pago',
            'invoice_id' => 'factura',
            'terms_accepted' => 'términos y condiciones',
            'privacy_accepted' => 'política de privacidad',
            'card_token' => 'información de tarjeta',
            'bank_reference' => 'referencia bancaria'
        ];
    }

    /**
     * Get payment data for processing
     */
    public function getPaymentData(): array
    {
        return $this->validated();
    }

    /**
     * Get the selected payment method ID
     */
    public function getPaymentMethodId(): int
    {
        return $this->input('payment_method_id');
    }

    /**
     * Get the invoice ID
     */
    public function getInvoiceId(): int
    {
        return $this->input('invoice_id');
    }
}

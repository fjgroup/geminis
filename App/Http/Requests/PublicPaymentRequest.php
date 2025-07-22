<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class PublicPaymentRequest
 * 
 * Form Request para validar procesamiento de pago en checkout público
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
            'payment_method' => [
                'required',
                'string',
                'in:paypal,stripe,bank_transfer,manual'
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
                'required_if:payment_method,stripe',
                'string'
            ],
            'bank_reference' => [
                'required_if:payment_method,bank_transfer',
                'string',
                'max:100'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'payment_method.required' => 'Debes seleccionar un método de pago.',
            'payment_method.in' => 'El método de pago seleccionado no es válido.',
            'terms_accepted.required' => 'Debes aceptar los términos y condiciones.',
            'terms_accepted.accepted' => 'Debes aceptar los términos y condiciones.',
            'privacy_accepted.required' => 'Debes aceptar la política de privacidad.',
            'privacy_accepted.accepted' => 'Debes aceptar la política de privacidad.',
            'card_token.required_if' => 'Se requiere información de la tarjeta para pago con Stripe.',
            'bank_reference.required_if' => 'Se requiere una referencia bancaria para transferencias.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'payment_method' => 'método de pago',
            'terms_accepted' => 'términos y condiciones',
            'privacy_accepted' => 'política de privacidad',
            'card_token' => 'información de tarjeta',
            'bank_reference' => 'referencia bancaria'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Verificar que el usuario tenga contexto de compra válido
            $purchaseContext = session('purchase_context');
            if (!$purchaseContext) {
                $validator->errors()->add('payment_method', 'Sesión de compra expirada. Por favor, reinicia el proceso.');
                return;
            }

            // Verificar que tenga información de precio
            if (!isset($purchaseContext['price_calculation'])) {
                $validator->errors()->add('payment_method', 'Información de precio perdida. Por favor, configura tu producto nuevamente.');
                return;
            }

            // Validaciones específicas por método de pago
            $paymentMethod = $this->input('payment_method');
            
            if ($paymentMethod === 'stripe') {
                $this->validateStripePayment($validator);
            } elseif ($paymentMethod === 'bank_transfer') {
                $this->validateBankTransfer($validator);
            }
        });
    }

    /**
     * Validar pago con Stripe
     */
    private function validateStripePayment($validator): void
    {
        $cardToken = $this->input('card_token');
        
        if ($cardToken) {
            // Validar formato básico del token de Stripe
            if (!preg_match('/^tok_[a-zA-Z0-9]+$/', $cardToken)) {
                $validator->errors()->add('card_token', 'El token de tarjeta no tiene un formato válido.');
            }
        }
    }

    /**
     * Validar transferencia bancaria
     */
    private function validateBankTransfer($validator): void
    {
        $bankReference = $this->input('bank_reference');
        
        if ($bankReference) {
            // Validar que la referencia no contenga caracteres especiales
            if (preg_match('/[<>"\']/', $bankReference)) {
                $validator->errors()->add('bank_reference', 'La referencia bancaria contiene caracteres no permitidos.');
            }

            // Validar longitud mínima
            if (strlen($bankReference) < 5) {
                $validator->errors()->add('bank_reference', 'La referencia bancaria debe tener al menos 5 caracteres.');
            }
        }
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        // Log de intentos de pago fallidos para detectar posibles fraudes
        \Illuminate\Support\Facades\Log::warning('Validación fallida en procesamiento de pago', [
            'errors' => $validator->errors()->toArray(),
            'payment_method' => $this->input('payment_method'),
            'user_id' => auth()->id(),
            'ip' => $this->ip(),
            'user_agent' => $this->userAgent(),
            'has_purchase_context' => !!session('purchase_context')
        ]);

        parent::failedValidation($validator);
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization(): void
    {
        // Log de intentos de acceso no autorizados
        \Illuminate\Support\Facades\Log::warning('Intento de pago sin autenticación', [
            'ip' => $this->ip(),
            'user_agent' => $this->userAgent(),
            'url' => $this->fullUrl()
        ]);

        parent::failedAuthorization();
    }
}

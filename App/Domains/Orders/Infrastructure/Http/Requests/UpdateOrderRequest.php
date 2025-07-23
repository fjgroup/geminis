<?php

namespace App\Domains\Orders\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request para actualizar órdenes en arquitectura hexagonal
 * 
 * Ubicado en Infrastructure layer como adaptador de entrada HTTP
 * Cumple con Single Responsibility Principle - solo valida datos de entrada
 */
class UpdateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization will be handled by OrderPolicy in the controller
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $possibleStatuses = [
            'pending_payment', 
            'pending_provisioning', 
            'active', 
            'fraud', 
            'cancelled', 
            'completed',
            'refunded'
        ];

        return [
            'status' => ['required', 'string', Rule::in($possibleStatuses)],
            'notes' => ['nullable', 'string', 'max:2000'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
            'fraud_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'ip_address' => ['nullable', 'ip'],
            'payment_method_id' => ['nullable', 'integer', 'exists:payment_methods,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status.required' => 'El estado de la orden es obligatorio.',
            'status.in' => 'El estado seleccionado no es válido.',
            'notes.max' => 'Las notas no pueden exceder los 2000 caracteres.',
            'admin_notes.max' => 'Las notas administrativas no pueden exceder los 2000 caracteres.',
            'fraud_score.numeric' => 'El puntaje de fraude debe ser un número.',
            'fraud_score.min' => 'El puntaje de fraude no puede ser negativo.',
            'fraud_score.max' => 'El puntaje de fraude no puede exceder 100.',
            'ip_address.ip' => 'La dirección IP debe ser válida.',
            'payment_method_id.exists' => 'El método de pago seleccionado no existe.',
        ];
    }

    /**
     * Get order data for update
     */
    public function getOrderData(): array
    {
        return $this->validated();
    }

    /**
     * Check if order is being marked as fraud
     */
    public function isMarkingAsFraud(): bool
    {
        return $this->input('status') === 'fraud';
    }

    /**
     * Check if order is being cancelled
     */
    public function isBeingCancelled(): bool
    {
        return $this->input('status') === 'cancelled';
    }

    /**
     * Check if order is being completed
     */
    public function isBeingCompleted(): bool
    {
        return $this->input('status') === 'completed';
    }
}

<?php

namespace App\Domains\Invoices\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request para actualizar facturas en arquitectura hexagonal
 * 
 * Ubicado en Infrastructure layer como adaptador de entrada HTTP
 * Cumple con Single Responsibility Principle - solo valida datos de entrada
 */
class UpdateInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization will be handled by InvoicePolicy in the controller
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $possibleStatuses = ['unpaid', 'paid', 'overdue', 'cancelled', 'refunded', 'collections', 'draft'];
        $currencies = ['USD', 'EUR', 'GBP', 'CAD', 'AUD'];

        return [
            'client_id' => [
                'sometimes', 
                'required', 
                'integer', 
                Rule::exists('users', 'id')->where('role', 'client')
            ],
            'reseller_id' => [
                'sometimes', 
                'nullable', 
                'integer', 
                Rule::exists('users', 'id')->where('role', 'reseller')
            ],
            'issue_date' => ['sometimes', 'required', 'date'],
            'due_date' => ['sometimes', 'required', 'date', 'after_or_equal:issue_date'],
            'paid_date' => ['nullable', 'date', 'required_if:status,paid'],
            'status' => ['sometimes', 'required', 'string', Rule::in($possibleStatuses)],
            'currency_code' => ['sometimes', 'required', 'string', Rule::in($currencies)],
            'notes_to_client' => ['nullable', 'string', 'max:2000'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
            'payment_gateway_slug' => ['nullable', 'string', 'max:100'],
            
            // Tax configuration
            'tax1_name' => ['nullable', 'string', 'max:100'],
            'tax1_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tax1_amount' => ['nullable', 'numeric', 'min:0'],
            'tax2_name' => ['nullable', 'string', 'max:100'],
            'tax2_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tax2_amount' => ['nullable', 'numeric', 'min:0'],
            
            // Totals (usually calculated, but allow manual override)
            'subtotal' => ['sometimes', 'numeric', 'min:0'],
            'total_amount' => ['sometimes', 'numeric', 'min:0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'client_id.exists' => 'El cliente seleccionado no existe o no es válido.',
            'reseller_id.exists' => 'El reseller seleccionado no existe o no es válido.',
            'issue_date.required' => 'La fecha de emisión es obligatoria.',
            'issue_date.date' => 'La fecha de emisión debe ser una fecha válida.',
            'due_date.required' => 'La fecha de vencimiento es obligatoria.',
            'due_date.date' => 'La fecha de vencimiento debe ser una fecha válida.',
            'due_date.after_or_equal' => 'La fecha de vencimiento debe ser igual o posterior a la fecha de emisión.',
            'paid_date.date' => 'La fecha de pago debe ser una fecha válida.',
            'paid_date.required_if' => 'La fecha de pago es obligatoria cuando el estado es "pagado".',
            'status.required' => 'El estado es obligatorio.',
            'status.in' => 'El estado seleccionado no es válido.',
            'currency_code.required' => 'El código de moneda es obligatorio.',
            'currency_code.in' => 'La moneda seleccionada no es válida.',
            'notes_to_client.max' => 'Las notas al cliente no pueden exceder los 2000 caracteres.',
            'admin_notes.max' => 'Las notas administrativas no pueden exceder los 2000 caracteres.',
            'tax1_rate.numeric' => 'La tasa de impuesto 1 debe ser un número.',
            'tax1_rate.min' => 'La tasa de impuesto 1 no puede ser negativa.',
            'tax1_rate.max' => 'La tasa de impuesto 1 no puede exceder el 100%.',
            'tax2_rate.numeric' => 'La tasa de impuesto 2 debe ser un número.',
            'tax2_rate.min' => 'La tasa de impuesto 2 no puede ser negativa.',
            'tax2_rate.max' => 'La tasa de impuesto 2 no puede exceder el 100%.',
            'subtotal.numeric' => 'El subtotal debe ser un número.',
            'subtotal.min' => 'El subtotal no puede ser negativo.',
            'total_amount.numeric' => 'El total debe ser un número.',
            'total_amount.min' => 'El total no puede ser negativo.',
        ];
    }

    /**
     * Get invoice data for update
     */
    public function getInvoiceData(): array
    {
        return $this->validated();
    }

    /**
     * Check if status is being changed to paid
     */
    public function isMarkingAsPaid(): bool
    {
        return $this->has('status') && $this->input('status') === 'paid';
    }

    /**
     * Check if status is being changed to cancelled
     */
    public function isMarkingAsCancelled(): bool
    {
        return $this->has('status') && $this->input('status') === 'cancelled';
    }
}

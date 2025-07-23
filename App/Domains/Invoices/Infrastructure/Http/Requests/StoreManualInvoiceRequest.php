<?php

namespace App\Domains\Invoices\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request para crear facturas manuales en arquitectura hexagonal
 * 
 * Ubicado en Infrastructure layer como adaptador de entrada HTTP
 * Cumple con Single Responsibility Principle - solo valida datos de entrada
 */
class StoreManualInvoiceRequest extends FormRequest
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
        return [
            'client_id' => [
                'required', 
                'integer', 
                Rule::exists('users', 'id')->where('role', 'client')
            ],
            'reseller_id' => [
                'nullable', 
                'integer', 
                Rule::exists('users', 'id')->where('role', 'reseller')
            ],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'status' => ['required', 'string', Rule::in(['unpaid', 'paid', 'cancelled', 'draft'])],
            'currency_code' => ['required', 'string', 'size:3'],
            'notes_to_client' => ['nullable', 'string', 'max:2000'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
            'payment_gateway_slug' => ['nullable', 'string', 'max:100'],
            
            // Tax configuration
            'tax1_name' => ['nullable', 'string', 'max:100'],
            'tax1_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tax2_name' => ['nullable', 'string', 'max:100'],
            'tax2_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            
            // Invoice items
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.taxable' => ['nullable', 'boolean'],
            'items.*.item_type' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'client_id.required' => 'Debe seleccionar un cliente.',
            'client_id.exists' => 'El cliente seleccionado no existe o no es válido.',
            'reseller_id.exists' => 'El reseller seleccionado no existe o no es válido.',
            'issue_date.required' => 'La fecha de emisión es obligatoria.',
            'issue_date.date' => 'La fecha de emisión debe ser una fecha válida.',
            'due_date.required' => 'La fecha de vencimiento es obligatoria.',
            'due_date.date' => 'La fecha de vencimiento debe ser una fecha válida.',
            'due_date.after_or_equal' => 'La fecha de vencimiento debe ser igual o posterior a la fecha de emisión.',
            'status.required' => 'El estado es obligatorio.',
            'status.in' => 'El estado debe ser uno de los valores permitidos.',
            'currency_code.required' => 'El código de moneda es obligatorio.',
            'currency_code.size' => 'El código de moneda debe tener exactamente 3 caracteres.',
            'notes_to_client.max' => 'Las notas al cliente no pueden exceder los 2000 caracteres.',
            'admin_notes.max' => 'Las notas administrativas no pueden exceder los 2000 caracteres.',
            'tax1_rate.numeric' => 'La tasa de impuesto 1 debe ser un número.',
            'tax1_rate.min' => 'La tasa de impuesto 1 no puede ser negativa.',
            'tax1_rate.max' => 'La tasa de impuesto 1 no puede exceder el 100%.',
            'tax2_rate.numeric' => 'La tasa de impuesto 2 debe ser un número.',
            'tax2_rate.min' => 'La tasa de impuesto 2 no puede ser negativa.',
            'tax2_rate.max' => 'La tasa de impuesto 2 no puede exceder el 100%.',
            'items.required' => 'Se requiere al menos un elemento de línea.',
            'items.min' => 'Se requiere al menos un elemento de línea.',
            'items.*.description.required' => 'La descripción de cada elemento es obligatoria.',
            'items.*.description.max' => 'La descripción no puede exceder los 255 caracteres.',
            'items.*.quantity.required' => 'La cantidad de cada elemento es obligatoria.',
            'items.*.quantity.min' => 'La cantidad del elemento debe ser al menos 1.',
            'items.*.unit_price.required' => 'El precio unitario de cada elemento es obligatorio.',
            'items.*.unit_price.min' => 'El precio unitario debe ser al menos 0.',
        ];
    }

    /**
     * Get invoice data for creation
     */
    public function getInvoiceData(): array
    {
        $data = $this->validated();
        
        // Calculate totals from items
        $subtotal = 0;
        foreach ($data['items'] as $item) {
            $subtotal += $item['quantity'] * $item['unit_price'];
        }
        
        $data['subtotal'] = $subtotal;
        
        // Calculate taxes
        $tax1Amount = 0;
        $tax2Amount = 0;
        
        if (!empty($data['tax1_rate'])) {
            $tax1Amount = $subtotal * ($data['tax1_rate'] / 100);
        }
        
        if (!empty($data['tax2_rate'])) {
            $tax2Amount = $subtotal * ($data['tax2_rate'] / 100);
        }
        
        $data['tax1_amount'] = $tax1Amount;
        $data['tax2_amount'] = $tax2Amount;
        $data['total_amount'] = $subtotal + $tax1Amount + $tax2Amount;
        
        return $data;
    }
}

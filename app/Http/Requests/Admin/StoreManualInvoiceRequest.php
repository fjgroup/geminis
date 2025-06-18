<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User; // For checking client existence

class StoreManualInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Authorization will be handled by InvoicePolicy in the controller.
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
            'client_id' => ['required', 'integer', Rule::exists('users', 'id')->where(function ($query) {
                // Optionally, ensure the user is a client or active
                // $query->where('role', 'client')->where('status', 'active'); 
            })],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'status' => ['required', 'string', Rule::in(['unpaid', 'paid', 'cancelled'])], // Initial statuses for manual invoice
            'currency_code' => ['required', 'string', 'size:3'], // e.g., USD
            'notes_to_client' => ['nullable', 'string', 'max:5000'],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
            
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.taxable' => ['nullable', 'boolean'], // Assuming taxable is a boolean
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'A client must be selected.',
            'client_id.exists' => 'The selected client does not exist or is invalid.',
            'due_date.after_or_equal' => 'The due date must be on or after the issue date.',
            'items.required' => 'At least one line item is required.',
            'items.min' => 'At least one line item is required.',
            'items.*.description.required' => 'Each item description is required.',
            'items.*.quantity.required' => 'Each item quantity is required.',
            'items.*.quantity.min' => 'Item quantity must be at least 1.',
            'items.*.unit_price.required' => 'Each item unit price is required.',
            'items.*.unit_price.min' => 'Item unit price must be at least 0.',
        ];
    }
}

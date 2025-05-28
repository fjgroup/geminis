<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Invoice; // For ENUM status values
use App\Models\User;   // For client_id existence

class UpdateInvoiceRequest extends FormRequest
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
        // All current ENUM values for an invoice
        $possibleStatuses = ['unpaid', 'paid', 'overdue', 'cancelled', 'refunded', 'collections'];
        $currencies = ['USD', 'EUR', 'GBP']; // From controller

        return [
            // Fields an admin might edit on an existing invoice:
            'client_id' => ['sometimes', 'required', 'integer', Rule::exists('users', 'id')], // Typically not changed, but allow if admin needs to.
            'issue_date' => ['sometimes', 'required', 'date'],
            'due_date' => ['sometimes', 'required', 'date', 'after_or_equal:issue_date'],
            'status' => ['sometimes', 'required', 'string', Rule::in($possibleStatuses)],
            'currency_code' => ['sometimes', 'required', 'string', Rule::in($currencies)], // Highly unlikely to be changed if transactions exist.
            'notes_to_client' => ['nullable', 'string', 'max:5000'],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
            'paid_date' => ['nullable', 'date', 'required_if:status,paid'], // Required if status is 'paid'

            // Line items are NOT part of this update request for now to keep complexity down.
            // If line items were editable, their validation would go here:
            // 'items' => ['sometimes', 'array', 'min:1'],
            // 'items.*.id' => ['nullable', 'integer', 'exists:invoice_items,id'], // For existing items
            // 'items.*.description' => ['required', 'string', 'max:255'],
            // 'items.*.quantity' => ['required', 'integer', 'min:1'],
            // 'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            // 'items.*.taxable' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.exists' => 'The selected client does not exist or is invalid.',
            'due_date.after_or_equal' => 'The due date must be on or after the issue date.',
            'status.in' => 'The selected status is not valid.',
            'currency_code.in' => 'The selected currency is not valid.',
            'paid_date.required_if' => 'The paid date is required when status is "paid".',
        ];
    }
}

<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Order; // For ENUM status values

class UpdateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Authorization will be handled by OrderPolicy in the controller.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Assuming Order model has a method like getPossibleStatusValues()
        // or we define them explicitly here.
        // For now, let's use the known statuses from the migration.
        $possibleStatuses = ['pending_payment', 'pending_provisioning', 'active', 'fraud', 'cancelled', 'completed']; 
        // Added 'completed' as a common final status. Adjust if Order model has specific constants.

        return [
            'status' => ['required', 'string', Rule::in($possibleStatuses)],
            'notes' => ['nullable', 'string', 'max:5000'], // Admin notes for the order
            // Add other fields here if they become editable, e.g., ip_address
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Order status is required.',
            'status.in' => 'The selected status is not valid.',
        ];
    }
}

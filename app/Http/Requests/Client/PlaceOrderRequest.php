<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PlaceOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $product = $this->route('product');
        if ($product) {
            $product->loadMissing('productType');
        }

        $rules = [
            'billing_cycle_id' => [
                'required',
                Rule::exists('product_pricings', 'id')->where(function ($query) use ($product) {
                    if ($product) {
                        return $query->where('product_id', $product->id);
                    }
                    return $query;
                }),
            ],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes_to_client' => ['nullable', 'string', 'max:2000'],
            'domainNames' => ['sometimes', 'array'], // Use 'sometimes' as it might not be present if not hosting
            // Configurable options rules can be added here later
        ];

        if ($product && $product->productType && $product->productType->requires_domain) {
            $quantity = $this->input('quantity', 1);
            // If quantity is somehow invalid (e.g. not a number), default to 1 for size rule to prevent errors.
            // The main quantity rule will catch invalid quantity values anyway.
            if (!is_numeric($quantity) || $quantity < 1) {
                $quantity = 1;
            }

            $rules['domainNames'] = ['required', 'array', "size:{$quantity}"];
            // Basic domain regex: allows letters, numbers, dots, hyphens. TLD of at least 2 letters.
            // Does not validate actual domain availability.
            $rules['domainNames.*'] = [
                'required',
                'string',
                'max:255',
                // Regex updated to be more common, allowing subdomains as well.
                // It checks for a structure like domain.tld or subdomain.domain.tld
                'regex:/^(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,63}$/'
                // Example for unique check (if needed later, requires specific table setup):
                // Rule::unique('some_domains_table', 'domain_name_column'),
            ];
        } else {
            // If not a hosting product, ensure domainNames is not passed or is empty.
            // 'sometimes|array' already handles it not being present.
            // If it *is* present but shouldn't be, add a rule:
            // $rules['domainNames'] = ['prohibited']; // Or ['nullable', 'array', 'max:0'] to allow empty array but not with items
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'billing_cycle_id.required' => 'Debe seleccionar un ciclo de facturación.',
            'billing_cycle_id.exists' => 'El ciclo de facturación seleccionado no es válido para este producto.',
            'quantity.required' => 'La cantidad es obligatoria.',
            'quantity.integer' => 'La cantidad debe ser un número entero.',
            'quantity.min' => 'La cantidad debe ser al menos :min.',
            'domainNames.required' => 'Se requieren nombres de dominio para productos de hosting.',
            'domainNames.array' => 'El formato de los nombres de dominio no es válido.',
            'domainNames.size' => 'Debe proporcionar un nombre de dominio para cada unidad de la cantidad seleccionada (:size).',
            'domainNames.*.required' => 'El nombre de dominio es obligatorio.',
            'domainNames.*.string' => 'El nombre de dominio debe ser una cadena de texto.',
            'domainNames.*.max' => 'El nombre de dominio no puede exceder los :max caracteres.',
            'domainNames.*.regex' => 'El formato del nombre de dominio proporcionado no es válido (ej: sudominio.com).',
        ];
    }
}

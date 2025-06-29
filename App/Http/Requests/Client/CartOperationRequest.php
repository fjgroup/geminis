<?php
namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CartOperationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['client', 'reseller']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $action = $this->route()->getActionMethod();

        switch ($action) {
            case 'setDomainForAccount':
                return [
                    'domain_name'    => [
                        'required',
                        'string',
                        'max:255',
                        'regex:/^[a-zA-Z0-9][a-zA-Z0-9-]{0,61}[a-zA-Z0-9]?\.[a-zA-Z]{2,}$/',
                    ],
                    'override_price' => 'nullable|numeric|min:0|max:9999.99',
                    'tld_extension'  => 'required|string|max:10|regex:/^[a-zA-Z]{2,}$/',
                    'product_id'     => [
                        'required',
                        'integer',
                        Rule::exists('products', 'id')->where(function ($query) {
                            $query->where('status', 'active')
                                ->where('is_publicly_available', true);
                        }),
                    ],
                    'pricing_id'     => [
                        'required',
                        'integer',
                        Rule::exists('product_pricings', 'id')->where(function ($query) {
                            if ($this->has('product_id')) {
                                $query->where('product_id', $this->input('product_id'))
                                    ->where('is_active', true);
                            }
                        }),
                    ],
                ];

            case 'setPrimaryServiceForAccount':
                return [
                    'product_id'             => [
                        'required',
                        'integer',
                        Rule::exists('products', 'id')->where(function ($query) {
                            $query->where('status', 'active')
                                ->where('is_publicly_available', true);
                        }),
                    ],
                    'pricing_id'             => [
                        'required',
                        'integer',
                        Rule::exists('product_pricings', 'id')->where(function ($query) {
                            if ($this->has('product_id')) {
                                $query->where('product_id', $this->input('product_id'));
                            }
                        }),
                    ],
                    'configurable_options'   => 'nullable|array|max:20',
                    'configurable_options.*' => 'nullable', // Permitir diferentes tipos de valores
                ];

            case 'removeDomainFromAccount':
            case 'removePrimaryServiceFromAccount':
                return [
                    'account_id' => 'required|string|max:255',
                ];

            default:
                return [];
        }
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'domain_name.regex'        => 'El formato del dominio no es válido. Debe ser como ejemplo.com',
            'tld_extension.regex'      => 'La extensión del dominio debe contener solo letras.',
            'override_price.max'       => 'El precio no puede exceder $9,999.99',
            'product_id.exists'        => 'El producto seleccionado no está disponible.',
            'pricing_id.exists'        => 'El plan de precios seleccionado no es válido para este producto.',
            'configurable_options.max' => 'No se pueden seleccionar más de 20 opciones configurables.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitizar el nombre del dominio
        if ($this->has('domain_name')) {
            $this->merge([
                'domain_name' => strtolower(trim($this->input('domain_name'))),
            ]);
        }

        // Sanitizar la extensión TLD
        if ($this->has('tld_extension')) {
            $this->merge([
                'tld_extension' => strtolower(trim($this->input('tld_extension'))),
            ]);
        }
    }
}

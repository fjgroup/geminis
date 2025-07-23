<?php

namespace App\Domains\Orders\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Request para realizar pedidos en arquitectura hexagonal
 * 
 * Ubicado en Infrastructure layer como adaptador de entrada HTTP
 * Cumple con Single Responsibility Principle - solo valida datos de entrada
 * La lógica de validación compleja debe estar en servicios especializados
 */
class PlaceOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
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
                Rule::exists('payment_methods', 'id')->where('is_active', true)
            ],
            'billing_address' => ['required', 'array'],
            'billing_address.name' => ['required', 'string', 'max:255'],
            'billing_address.company' => ['nullable', 'string', 'max:255'],
            'billing_address.address' => ['required', 'string', 'max:500'],
            'billing_address.city' => ['required', 'string', 'max:100'],
            'billing_address.state' => ['required', 'string', 'max:100'],
            'billing_address.postal_code' => ['required', 'string', 'max:20'],
            'billing_address.country' => ['required', 'string', 'size:2'],
            'billing_address.phone' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'terms_accepted' => ['required', 'accepted'],
            'privacy_accepted' => ['required', 'accepted'],
            'promotional_emails' => ['boolean'],
            'cart_items' => ['required', 'array', 'min:1'],
            'cart_items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'cart_items.*.billing_cycle_id' => ['required', 'integer', 'exists:billing_cycles,id'],
            'cart_items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'cart_items.*.domain_names' => ['nullable', 'array'],
            'cart_items.*.domain_names.*' => [
                'string',
                'max:255',
                'regex:/^(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,63}$/'
            ],
            'cart_items.*.configurable_options' => ['nullable', 'array'],
            'cart_items.*.configurable_options.*' => ['integer', 'exists:configurable_options,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'payment_method_id.required' => 'Debe seleccionar un método de pago.',
            'payment_method_id.exists' => 'El método de pago seleccionado no es válido o no está activo.',
            'billing_address.required' => 'La dirección de facturación es requerida.',
            'billing_address.name.required' => 'El nombre en la dirección de facturación es requerido.',
            'billing_address.address.required' => 'La dirección es requerida.',
            'billing_address.city.required' => 'La ciudad es requerida.',
            'billing_address.state.required' => 'El estado/provincia es requerido.',
            'billing_address.postal_code.required' => 'El código postal es requerido.',
            'billing_address.country.required' => 'El país es requerido.',
            'billing_address.country.size' => 'El código de país debe tener exactamente 2 caracteres.',
            'terms_accepted.required' => 'Debe aceptar los términos y condiciones.',
            'terms_accepted.accepted' => 'Debe aceptar los términos y condiciones.',
            'privacy_accepted.required' => 'Debe aceptar la política de privacidad.',
            'privacy_accepted.accepted' => 'Debe aceptar la política de privacidad.',
            'cart_items.required' => 'Debe tener al menos un producto en el carrito.',
            'cart_items.min' => 'Debe tener al menos un producto en el carrito.',
            'cart_items.*.product_id.required' => 'El ID del producto es requerido.',
            'cart_items.*.product_id.exists' => 'Uno de los productos seleccionados no existe.',
            'cart_items.*.billing_cycle_id.required' => 'El ciclo de facturación es requerido.',
            'cart_items.*.billing_cycle_id.exists' => 'Uno de los ciclos de facturación seleccionados no es válido.',
            'cart_items.*.quantity.required' => 'La cantidad es requerida.',
            'cart_items.*.quantity.min' => 'La cantidad debe ser al menos 1.',
            'cart_items.*.quantity.max' => 'La cantidad no puede exceder 100.',
            'cart_items.*.domain_names.*.regex' => 'Uno de los nombres de dominio no tiene un formato válido.',
            'cart_items.*.configurable_options.*.exists' => 'Una de las opciones configurables seleccionadas no es válida.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'payment_method_id' => 'método de pago',
            'billing_address' => 'dirección de facturación',
            'terms_accepted' => 'términos y condiciones',
            'privacy_accepted' => 'política de privacidad',
            'cart_items' => 'productos del carrito',
        ];
    }

    /**
     * Get order data for creation
     */
    public function getOrderData(): array
    {
        $data = $this->validated();
        $data['client_id'] = Auth::id();
        $data['status'] = 'pending_payment';
        $data['ip_address'] = $this->ip();
        
        return $data;
    }

    /**
     * Get billing address
     */
    public function getBillingAddress(): array
    {
        return $this->input('billing_address');
    }

    /**
     * Get cart items
     */
    public function getCartItems(): array
    {
        return $this->input('cart_items');
    }

    /**
     * Get payment method ID
     */
    public function getPaymentMethodId(): int
    {
        return $this->input('payment_method_id');
    }
}

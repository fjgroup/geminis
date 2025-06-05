<?php

namespace App\Http\Requests\Admin;

use App\Models\ClientService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: Implementar ClientServicePolicy y usarla aquí
        // $clientService = $this->route('client_service'); // Asumiendo que el parámetro de ruta es 'client_service'
        // return $this->user()->can('update', $clientService);
        return true; // Temporalmente true
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $clientServiceId = $this->route('client_service')->id; // Obtener el ID del servicio actual

        return [
            'client_id' => ['required', 'integer', Rule::exists('users', 'id')],

            'product_id' => ['required', 'integer', Rule::exists('products', 'id')],

            'product_pricing_id' => ['required', 'integer', Rule::exists('product_pricings', 'id')],

            'billing_cycle_id' => ['required', 'integer', Rule::exists('billing_cycles', 'id')], // Validar que el ciclo de facturación exista

            'registration_date' => ['required', 'date'],

            'next_due_date' => ['required', 'date', 'after_or_equal:registration_date'],

            'billing_amount' => ['required', 'numeric', 'min:0'],

            'status' => ['required', Rule::in(['pending', 'active', 'suspended', 'terminated', 'cancelled', 'fraud', 'pending_configuration', 'provisioning_failed'])],

            'domain_name' => ['nullable', 'string', 'max:255'],
            // Ejemplo de unicidad ignorando el actual, si domain_name tuviera que ser único:
            // 'domain_name' => ['nullable', 'string', 'max:255', Rule::unique('client_services')->ignore($clientServiceId)],

            'username' => ['nullable', 'string', 'max:255'],

            'password_encrypted' => ['nullable', 'string', 'min:6'], // Si se envía, que tenga un mínimo. Si es vacío, no se actualiza.

            'reseller_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', 'reseller')],

            'server_id' => ['nullable', 'integer', Rule::exists('servers', 'id')], // Cuando la tabla servers exista
            
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * Este método se llama antes de que se apliquen las reglas de validación.
     * Si el campo password_encrypted está presente pero vacío, lo eliminamos del request
     * para que no se intente actualizar la contraseña con un valor vacío.
     * El modelo se encargará de no actualizarlo si no está en los datos validados.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('password_encrypted') && $this->password_encrypted === '') {
            // Si se envió explícitamente vacío, lo convertimos a null para que el cast 'encrypted' no falle
            // y para que la validación 'min:6' no se aplique a una cadena vacía si se quiere borrar la contraseña.
            // O, si no se quiere permitir borrarla, se podría quitar esta lógica y la validación 'nullable'
            // y hacer 'password_encrypted' => ['sometimes', 'required', 'string', 'min:6']
            $this->merge(['password_encrypted' => null]);
        }
    }
}

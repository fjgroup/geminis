<?php

namespace App\Domains\BillingAndPayments\Application\Services;

use App\Domains\BillingAndPayments\Infrastructure\Persistence\Models\PaymentMethod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Servicio para la gestión de métodos de pago
 *
 * Extrae la lógica de negocio del AdminPaymentMethodController aplicando el SRP
 */
class PaymentMethodService
{
    private static array $paymentMethodTypes = [
        'bank' => 'Cuenta Bancaria',
        'wallet' => 'Billetera Digital (ej: Zinli, PayPal)',
        'crypto_wallet' => 'Billetera de Criptomonedas',
    ];

    /**
     * Obtener todos los métodos de pago con filtros
     */
    public function getPaymentMethods(array $filters = []): array
    {
        try {
            $query = PaymentMethod::query();

            // Aplicar filtros
            if (isset($filters['type'])) {
                $query->where('type', $filters['type']);
            }

            if (isset($filters['is_active'])) {
                $query->where('is_active', $filters['is_active']);
            }

            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('platform_name', 'like', '%' . $filters['search'] . '%');
                });
            }

            $paymentMethods = $query->orderBy('name')->get();

            return [
                'success' => true,
                'data' => $paymentMethods
            ];

        } catch (\Exception $e) {
            Log::error('PaymentMethodService - Error obteniendo métodos de pago', [
                'error' => $e->getMessage(),
                'filters' => $filters
            ]);

            return [
                'success' => false,
                'data' => collect(),
                'message' => 'Error al obtener métodos de pago: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener datos para formularios
     */
    public function getFormData(): array
    {
        return [
            'paymentMethodTypes' => self::$paymentMethodTypes,
        ];
    }

    /**
     * Obtener reglas de validación comunes
     */
    public function getCommonValidationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(array_keys(self::$paymentMethodTypes))],
            'instructions' => 'nullable|string',
            'logo_url' => 'nullable|url|max:255',
            'is_active' => 'boolean',
            'account_holder_name' => 'nullable|string|max:255',
        ];
    }

    /**
     * Obtener reglas de validación específicas por tipo
     */
    public function getTypeSpecificValidationRules(string $type): array
    {
        $typeSpecificRules = [];

        switch ($type) {
            case 'bank':
                $typeSpecificRules = [
                    'bank_name' => 'required|string|max:255',
                    'account_number' => 'required|string|max:255',
                    'account_holder_name' => 'required|string|max:255',
                    'identification_number' => 'nullable|string|max:50',
                    'swift_code' => 'nullable|string|max:50',
                    'iban' => 'nullable|string|max:50',
                    'branch_name' => 'nullable|string|max:255',
                    // Campos de otros tipos deben ser nulos
                    'platform_name' => 'nullable|string|max:1',
                    'email_address' => 'nullable|string|max:1',
                    'payment_link' => 'nullable|string|max:1',
                ];
                break;

            case 'wallet':
            case 'paypal_manual':
                $typeSpecificRules = [
                    'platform_name' => 'required|string|max:255',
                    'account_holder_name' => 'required|string|max:255',
                    'email_address' => 'nullable|email|max:255',
                    'payment_link' => 'nullable|url|max:255',
                    // Campos bancarios deben ser nulos
                    'bank_name' => 'nullable|string|max:1',
                    'account_number' => 'nullable|string|max:1',
                    'identification_number' => 'nullable|string|max:1',
                    'swift_code' => 'nullable|string|max:1',
                    'iban' => 'nullable|string|max:1',
                    'branch_name' => 'nullable|string|max:1',
                ];
                break;

            case 'crypto_wallet':
                $typeSpecificRules = [
                    'platform_name' => 'required|string|max:255',
                    'account_number' => 'required|string|max:255', // Dirección de wallet
                    'account_holder_name' => 'nullable|string|max:255',
                    // Campos bancarios deben ser nulos
                    'bank_name' => 'nullable|string|max:1',
                    'identification_number' => 'nullable|string|max:1',
                    'swift_code' => 'nullable|string|max:1',
                    'iban' => 'nullable|string|max:1',
                    'branch_name' => 'nullable|string|max:1',
                    // Campos de wallet deben ser nulos
                    'email_address' => 'nullable|string|max:1',
                    'payment_link' => 'nullable|string|max:1',
                ];
                break;
        }

        return $typeSpecificRules;
    }

    /**
     * Preparar datos validados para guardar
     */
    public function prepareDataForSave(array $validatedData, string $type): array
    {
        $dataToSave = [
            'name' => $validatedData['name'],
            'type' => $validatedData['type'],
            'instructions' => $validatedData['instructions'] ?? null,
            'logo_url' => $validatedData['logo_url'] ?? null,
            'is_active' => $validatedData['is_active'] ?? false,
            'account_holder_name' => $validatedData['account_holder_name'] ?? null,
        ];

        // Inicializar campos con valores por defecto
        $dataToSave = array_merge($dataToSave, [
            'bank_name' => '',
            'account_number' => '',
            'identification_number' => '',
            'swift_code' => null,
            'iban' => null,
            'branch_name' => null,
            'platform_name' => null,
            'email_address' => null,
            'payment_link' => null,
        ]);

        // Configurar campos específicos por tipo
        switch ($type) {
            case 'bank':
                $dataToSave['bank_name'] = $validatedData['bank_name'] ?? '';
                $dataToSave['account_number'] = $validatedData['account_number'] ?? '';
                $dataToSave['identification_number'] = $validatedData['identification_number'] ?? '';
                $dataToSave['swift_code'] = $validatedData['swift_code'] ?? null;
                $dataToSave['iban'] = $validatedData['iban'] ?? null;
                $dataToSave['branch_name'] = $validatedData['branch_name'] ?? null;
                break;

            case 'wallet':
            case 'paypal_manual':
                $dataToSave['platform_name'] = $validatedData['platform_name'] ?? null;
                $dataToSave['email_address'] = $validatedData['email_address'] ?? null;
                $dataToSave['payment_link'] = $validatedData['payment_link'] ?? null;
                break;

            case 'crypto_wallet':
                $dataToSave['platform_name'] = $validatedData['platform_name'] ?? null;
                $dataToSave['account_number'] = $validatedData['account_number'] ?? '';
                break;
        }

        return $dataToSave;
    }

    /**
     * Crear un nuevo método de pago
     */
    public function createPaymentMethod(array $data): array
    {
        try {
            $type = $data['type'];
            $preparedData = $this->prepareDataForSave($data, $type);

            $paymentMethod = PaymentMethod::create($preparedData);

            Log::info('PaymentMethodService - Método de pago creado', [
                'payment_method_id' => $paymentMethod->id,
                'type' => $type,
                'name' => $paymentMethod->name
            ]);

            return [
                'success' => true,
                'data' => $paymentMethod,
                'message' => 'Método de pago creado exitosamente'
            ];

        } catch (\Exception $e) {
            Log::error('PaymentMethodService - Error creando método de pago', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Error al crear el método de pago: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Actualizar un método de pago existente
     */
    public function updatePaymentMethod(PaymentMethod $paymentMethod, array $data): array
    {
        try {
            $type = $data['type'];
            $preparedData = $this->prepareDataForSave($data, $type);

            $paymentMethod->update($preparedData);

            Log::info('PaymentMethodService - Método de pago actualizado', [
                'payment_method_id' => $paymentMethod->id,
                'type' => $type,
                'name' => $paymentMethod->name
            ]);

            return [
                'success' => true,
                'data' => $paymentMethod->fresh(),
                'message' => 'Método de pago actualizado exitosamente'
            ];

        } catch (\Exception $e) {
            Log::error('PaymentMethodService - Error actualizando método de pago', [
                'error' => $e->getMessage(),
                'payment_method_id' => $paymentMethod->id,
                'data' => $data
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Error al actualizar el método de pago: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Eliminar un método de pago
     */
    public function deletePaymentMethod(PaymentMethod $paymentMethod): array
    {
        try {
            // Verificar si el método de pago está siendo usado
            $transactionsCount = $paymentMethod->transactions()->count();

            if ($transactionsCount > 0) {
                return [
                    'success' => false,
                    'message' => "No se puede eliminar el método de pago porque tiene {$transactionsCount} transacciones asociadas"
                ];
            }

            $paymentMethodName = $paymentMethod->name;
            $paymentMethod->delete();

            Log::info('PaymentMethodService - Método de pago eliminado', [
                'payment_method_id' => $paymentMethod->id,
                'name' => $paymentMethodName
            ]);

            return [
                'success' => true,
                'message' => 'Método de pago eliminado exitosamente'
            ];

        } catch (\Exception $e) {
            Log::error('PaymentMethodService - Error eliminando método de pago', [
                'error' => $e->getMessage(),
                'payment_method_id' => $paymentMethod->id
            ]);

            return [
                'success' => false,
                'message' => 'Error al eliminar el método de pago: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener métodos de pago activos para formularios
     */
    public function getActivePaymentMethods(): Collection
    {
        try {
            return PaymentMethod::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'type', 'platform_name']);

        } catch (\Exception $e) {
            Log::error('PaymentMethodService - Error obteniendo métodos de pago activos', [
                'error' => $e->getMessage()
            ]);

            return collect();
        }
    }

    /**
     * Validar tipo de método de pago
     */
    public function isValidType(string $type): bool
    {
        return array_key_exists($type, self::$paymentMethodTypes);
    }

    /**
     * Obtener tipos de métodos de pago disponibles
     */
    public function getAvailableTypes(): array
    {
        return self::$paymentMethodTypes;
    }
}

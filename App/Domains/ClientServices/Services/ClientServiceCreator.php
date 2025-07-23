<?php

namespace App\Domains\ClientServices\Services;

use App\Domains\ClientServices\Models\ClientService;
use App\Domains\ClientServices\DataTransferObjects\CreateClientServiceDTO;
use App\Domains\Users\Models\User;
use App\D;
use App\Domains\Products\Models\Product;
use App\Models\ProductPricing;
use App\Models\BillingCycle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Class ClientServiceCreator
 *
 * Servicio especializado para la creación de servicios de cliente
 * Aplica el principio de Single Responsibility (SRP)
 * Maneja toda la lógica de negocio para crear servicios
 */
class ClientServiceCreator
{
    /**
     * Crear un nuevo servicio de cliente
     *
     * @param CreateClientServiceDTO $dto
     * @return array
     */
    public function createService(CreateClientServiceDTO $dto): array
    {
        try {
            // Validar DTO
            if (!$dto->isValid()) {
                return [
                    'success' => false,
                    'message' => 'Datos del servicio inválidos',
                    'errors' => $dto->getValidationErrors(),
                    'data' => null
                ];
            }

            // Validar dependencias
            $validation = $this->validateDependencies($dto);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validation['errors'],
                    'data' => null
                ];
            }

            // Crear servicio en transacción
            $service = DB::transaction(function () use ($dto) {
                return $this->createServiceRecord($dto);
            });

            Log::info('Servicio de cliente creado exitosamente', [
                'service_id' => $service->id,
                'client_id' => $service->client_id,
                'product_id' => $service->product_id,
                'domain_name' => $service->domain_name,
                'status' => $service->status
            ]);

            return [
                'success' => true,
                'message' => 'Servicio creado exitosamente',
                'errors' => [],
                'data' => $service
            ];

        } catch (\Exception $e) {
            Log::error('Error al crear servicio de cliente', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'dto_data' => $dto->toArray()
            ]);

            return [
                'success' => false,
                'message' => 'Error interno al crear el servicio',
                'errors' => ['general' => 'Error interno del servidor'],
                'data' => null
            ];
        }
    }

    /**
     * Crear servicio automáticamente desde item de factura
     *
     * @param int $clientId
     * @param array $invoiceItemData
     * @return array
     */
    public function createFromInvoiceItem(int $clientId, array $invoiceItemData): array
    {
        try {
            $dto = CreateClientServiceDTO::fromInvoiceItem($clientId, $invoiceItemData);

            // Generar credenciales automáticamente si es necesario
            if ($this->shouldGenerateCredentials($invoiceItemData['product_id'])) {
                $credentials = $this->generateServiceCredentials($invoiceItemData['domain_name'] ?? null);

                $dto = new CreateClientServiceDTO(
                    client_id: $dto->client_id,
                    reseller_id: $dto->reseller_id,
                    product_id: $dto->product_id,
                    product_pricing_id: $dto->product_pricing_id,
                    billing_cycle_id: $dto->billing_cycle_id,
                    domain_name: $dto->domain_name,
                    username: $credentials['username'],
                    password_encrypted: $credentials['password_encrypted'],
                    status: $dto->status,
                    registration_date: $dto->registration_date,
                    next_due_date: $dto->next_due_date,
                    termination_date: $dto->termination_date,
                    billing_amount: $dto->billing_amount,
                    notes: $dto->notes,
                );
            }

            return $this->createService($dto);

        } catch (\Exception $e) {
            Log::error('Error al crear servicio desde item de factura', [
                'client_id' => $clientId,
                'error' => $e->getMessage(),
                'invoice_item_data' => $invoiceItemData
            ]);

            return [
                'success' => false,
                'message' => 'Error al crear servicio desde factura',
                'errors' => ['general' => $e->getMessage()],
                'data' => null
            ];
        }
    }

    /**
     * Crear servicio de prueba
     *
     * @param int $clientId
     * @param int $productId
     * @param array $trialData
     * @return array
     */
    public function createTrialService(int $clientId, int $productId, array $trialData): array
    {
        try {
            $dto = CreateClientServiceDTO::forTrial($clientId, $productId, $trialData);

            // Generar credenciales para el trial
            if ($this->shouldGenerateCredentials($productId)) {
                $credentials = $this->generateServiceCredentials($trialData['domain_name'] ?? null);

                $dto = new CreateClientServiceDTO(
                    client_id: $dto->client_id,
                    reseller_id: $dto->reseller_id,
                    product_id: $dto->product_id,
                    product_pricing_id: $dto->product_pricing_id,
                    billing_cycle_id: $dto->billing_cycle_id,
                    domain_name: $dto->domain_name,
                    username: $credentials['username'],
                    password_encrypted: $credentials['password_encrypted'],
                    status: $dto->status,
                    registration_date: $dto->registration_date,
                    next_due_date: $dto->next_due_date,
                    termination_date: $dto->termination_date,
                    billing_amount: $dto->billing_amount,
                    notes: $dto->notes,
                );
            }

            return $this->createService($dto);

        } catch (\Exception $e) {
            Log::error('Error al crear servicio de prueba', [
                'client_id' => $clientId,
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error al crear servicio de prueba',
                'errors' => ['general' => $e->getMessage()],
                'data' => null
            ];
        }
    }

    /**
     * Crear el registro del servicio en la base de datos
     *
     * @param CreateClientServiceDTO $dto
     * @return ClientService
     */
    private function createServiceRecord(CreateClientServiceDTO $dto): ClientService
    {
        $serviceData = $dto->toArray();

        // Establecer fechas por defecto si no se proporcionaron
        if (empty($serviceData['registration_date'])) {
            $serviceData['registration_date'] = now()->format('Y-m-d');
        }

        if (empty($serviceData['next_due_date'])) {
            // Calcular próxima fecha basada en el ciclo de facturación
            $billingCycle = BillingCycle::find($dto->billing_cycle_id);
            $registrationDate = \Carbon\Carbon::parse($serviceData['registration_date']);

            if ($billingCycle) {
                $serviceData['next_due_date'] = $registrationDate->addDays($billingCycle->days)->format('Y-m-d');
            } else {
                $serviceData['next_due_date'] = $registrationDate->addMonth()->format('Y-m-d');
            }
        }

        return ClientService::create($serviceData);
    }

    /**
     * Validar dependencias del servicio
     *
     * @param CreateClientServiceDTO $dto
     * @return array
     */
    private function validateDependencies(CreateClientServiceDTO $dto): array
    {
        $errors = [];

        // Validar que el cliente existe
        $client = User::find($dto->client_id);
        if (!$client) {
            $errors[] = 'Cliente no encontrado';
        } elseif ($client->status !== 'active') {
            $errors[] = 'Cliente inactivo';
        }

        // Validar que el producto existe
        $product = Product::find($dto->product_id);
        if (!$product) {
            $errors[] = 'Producto no encontrado';
        } elseif ($product->status !== 'active') {
            $errors[] = 'Producto inactivo';
        }

        // Validar que el pricing existe
        $pricing = ProductPricing::find($dto->product_pricing_id);
        if (!$pricing) {
            $errors[] = 'Pricing del producto no encontrado';
        }

        // Validar que el ciclo de facturación existe
        $billingCycle = BillingCycle::find($dto->billing_cycle_id);
        if (!$billingCycle) {
            $errors[] = 'Ciclo de facturación no encontrado';
        }

        // Validar dominio único si se proporciona
        if ($dto->domain_name) {
            $existingService = ClientService::where('domain_name', $dto->domain_name)
                                           ->where('status', '!=', 'cancelled')
                                           ->first();
            if ($existingService) {
                $errors[] = 'El dominio ya está en uso por otro servicio';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Verificar si se deben generar credenciales automáticamente
     *
     * @param int $productId
     * @return bool
     */
    private function shouldGenerateCredentials(int $productId): bool
    {
        $product = Product::find($productId);

        if (!$product) {
            return false;
        }

        // Generar credenciales para productos que requieren acceso
        $productsRequiringCredentials = ['web-hosting', 'vps', 'dedicated-server', 'email-hosting'];

        return in_array($product->type, $productsRequiringCredentials);
    }

    /**
     * Generar credenciales de servicio
     *
     * @param string|null $domainName
     * @return array
     */
    private function generateServiceCredentials(?string $domainName = null): array
    {
        // Generar username basado en el dominio o aleatorio
        if ($domainName) {
            $username = str_replace(['.', '-'], '', $domainName) . '_' . Str::random(4);
        } else {
            $username = 'user_' . Str::random(8);
        }

        // Generar contraseña segura
        $password = Str::random(12);

        return [
            'username' => strtolower($username),
            'password_encrypted' => Hash::make($password),
            'plain_password' => $password // Para enviar al cliente
        ];
    }
}

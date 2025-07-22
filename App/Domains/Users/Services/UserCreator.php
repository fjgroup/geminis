<?php

namespace App\Domains\Users\Services;

use App\Domains\Users\Models\User;
use App\Domains\Users\DataTransferObjects\CreateUserDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Class UserCreator
 * 
 * Servicio especializado para la creación de usuarios
 * Aplica el principio de Single Responsibility (SRP)
 * Maneja toda la lógica de negocio para crear usuarios
 */
class UserCreator
{
    /**
     * Crear un nuevo usuario
     * 
     * @param CreateUserDTO $dto
     * @return array
     */
    public function createUser(CreateUserDTO $dto): array
    {
        try {
            // Validar DTO
            if (!$dto->isValid()) {
                return [
                    'success' => false,
                    'message' => 'Datos de usuario inválidos',
                    'errors' => $dto->getValidationErrors(),
                    'data' => null
                ];
            }

            // Verificar que el email sea único
            if ($this->emailExists($dto->email)) {
                return [
                    'success' => false,
                    'message' => 'El email ya está en uso',
                    'errors' => ['email' => 'Este email ya está registrado'],
                    'data' => null
                ];
            }

            // Validar reglas de negocio
            $businessErrors = $this->validateBusinessRules($dto);
            if (!empty($businessErrors)) {
                return [
                    'success' => false,
                    'message' => 'Errores de validación de negocio',
                    'errors' => $businessErrors,
                    'data' => null
                ];
            }

            // Crear usuario en transacción
            $user = DB::transaction(function () use ($dto) {
                return $this->createUserRecord($dto);
            });

            Log::info('Usuario creado exitosamente', [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'reseller_id' => $user->reseller_id
            ]);

            return [
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'errors' => [],
                'data' => $user
            ];

        } catch (\Exception $e) {
            Log::error('Error al crear usuario', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'dto_data' => $dto->toArray()
            ]);

            return [
                'success' => false,
                'message' => 'Error interno al crear el usuario',
                'errors' => ['general' => 'Error interno del servidor'],
                'data' => null
            ];
        }
    }

    /**
     * Crear un cliente para un reseller específico
     * 
     * @param CreateUserDTO $dto
     * @param int $resellerId
     * @return array
     */
    public function createResellerClient(CreateUserDTO $dto, int $resellerId): array
    {
        // Verificar que el reseller existe y está activo
        $reseller = User::where('id', $resellerId)
                       ->where('role', 'reseller')
                       ->where('status', 'active')
                       ->first();

        if (!$reseller) {
            return [
                'success' => false,
                'message' => 'Reseller no encontrado o inactivo',
                'errors' => ['reseller' => 'El reseller especificado no existe o está inactivo'],
                'data' => null
            ];
        }

        // Verificar límites del reseller
        $limitCheck = $this->checkResellerLimits($reseller);
        if (!$limitCheck['can_create']) {
            return [
                'success' => false,
                'message' => $limitCheck['reason'],
                'errors' => ['reseller_limit' => $limitCheck['reason']],
                'data' => null
            ];
        }

        // Crear DTO específico para cliente de reseller
        $clientDto = CreateUserDTO::fromResellerClientRequest($dto->toArray(), $resellerId);
        
        return $this->createUser($clientDto);
    }

    /**
     * Crear múltiples usuarios en lote
     * 
     * @param array $dtos Array de CreateUserDTO
     * @return array
     */
    public function createUsersBatch(array $dtos): array
    {
        $results = [];
        $successCount = 0;
        $errorCount = 0;

        try {
            DB::transaction(function () use ($dtos, &$results, &$successCount, &$errorCount) {
                foreach ($dtos as $index => $dto) {
                    if (!$dto instanceof CreateUserDTO) {
                        $results[$index] = [
                            'success' => false,
                            'message' => 'DTO inválido en posición ' . $index,
                            'data' => null
                        ];
                        $errorCount++;
                        continue;
                    }

                    $result = $this->createUser($dto);
                    $results[$index] = $result;

                    if ($result['success']) {
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                }
            });

            return [
                'success' => $errorCount === 0,
                'message' => "Procesados: {$successCount} exitosos, {$errorCount} errores",
                'data' => [
                    'results' => $results,
                    'summary' => [
                        'total' => count($dtos),
                        'success' => $successCount,
                        'errors' => $errorCount
                    ]
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Error en creación de usuarios en lote', [
                'error' => $e->getMessage(),
                'total_users' => count($dtos)
            ]);

            return [
                'success' => false,
                'message' => 'Error en la creación en lote',
                'data' => null
            ];
        }
    }

    /**
     * Crear el registro del usuario en la base de datos
     * 
     * @param CreateUserDTO $dto
     * @return User
     */
    private function createUserRecord(CreateUserDTO $dto): User
    {
        $userData = $dto->toArray();

        // Hashear contraseña
        $userData['password'] = Hash::make($userData['password']);

        // Crear usuario
        $user = User::create($userData);

        // Crear perfil de reseller si es necesario
        if ($dto->isReseller() && $dto->getResellerProfileData()) {
            $this->createResellerProfile($user, $dto->getResellerProfileData());
        }

        return $user;
    }

    /**
     * Crear perfil de reseller
     * 
     * @param User $user
     * @param array $profileData
     * @return void
     */
    private function createResellerProfile(User $user, array $profileData): void
    {
        $user->resellerProfile()->create([
            'commission_rate' => $profileData['commission_rate'] ?? 0,
            'max_clients' => $profileData['max_clients'] ?? null,
            'allowed_products' => $profileData['allowed_products'] ?? null,
        ]);
    }

    /**
     * Verificar si un email ya existe
     * 
     * @param string $email
     * @return bool
     */
    private function emailExists(string $email): bool
    {
        return User::where('email', $email)->exists();
    }

    /**
     * Validar reglas de negocio adicionales
     * 
     * @param CreateUserDTO $dto
     * @return array
     */
    private function validateBusinessRules(CreateUserDTO $dto): array
    {
        $errors = [];

        // Validar que el reseller existe si se especifica
        if ($dto->reseller_id) {
            $reseller = User::where('id', $dto->reseller_id)
                           ->where('role', 'reseller')
                           ->where('status', 'active')
                           ->first();
            
            if (!$reseller) {
                $errors[] = 'El reseller especificado no existe o está inactivo';
            }
        }

        // Validar que solo clientes pueden tener reseller_id
        if ($dto->reseller_id && $dto->role !== 'client') {
            $errors[] = 'Solo los clientes pueden tener un reseller asignado';
        }

        // Validar que los resellers no pueden tener reseller_id
        if ($dto->role === 'reseller' && $dto->reseller_id) {
            $errors[] = 'Los resellers no pueden tener un reseller asignado';
        }

        return $errors;
    }

    /**
     * Verificar límites del reseller
     * 
     * @param User $reseller
     * @return array
     */
    private function checkResellerLimits(User $reseller): array
    {
        $profile = $reseller->resellerProfile;
        
        if (!$profile) {
            return ['can_create' => true];
        }

        // Verificar límite de clientes
        if ($profile->max_clients) {
            $currentClients = $reseller->clients()->count();
            
            if ($currentClients >= $profile->max_clients) {
                return [
                    'can_create' => false,
                    'reason' => "El reseller ha alcanzado su límite de {$profile->max_clients} clientes"
                ];
            }
        }

        return ['can_create' => true];
    }
}

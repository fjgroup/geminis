<?php

namespace App\Domains\Users\UseCases;

use App\Domains\Users\Models\User;
use App\Domains\Users\Repositories\IUserRepository;
use App\Domains\Users\Events\UserCreated;
use App\Domains\Users\DataTransferObjects\CreateUserDTO;
use App\Domains\Users\ValueObjects\UserRole;
use App\Domains\Shared\ValueObjects\Email;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Class CreateUserUseCase
 * 
 * Caso de uso para crear usuarios
 * Encapsula toda la lógica de negocio para creación de usuarios
 * Aplica principios de Arquitectura Hexagonal - Use Cases
 */
class CreateUserUseCase
{
    public function __construct(
        private IUserRepository $userRepository
    ) {}

    /**
     * Ejecutar caso de uso de creación de usuario
     * 
     * @param CreateUserDTO $dto
     * @return array
     */
    public function execute(CreateUserDTO $dto): array
    {
        try {
            // Validar datos de entrada
            $validation = $this->validateInput($dto);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validation['errors'],
                    'data' => null
                ];
            }

            // Verificar reglas de negocio
            $businessValidation = $this->validateBusinessRules($dto);
            if (!$businessValidation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Reglas de negocio no cumplidas',
                    'errors' => $businessValidation['errors'],
                    'data' => null
                ];
            }

            // Crear usuario
            $user = $this->createUser($dto);

            // Disparar evento de dominio
            Event::dispatch(new UserCreated($user, [
                'created_by' => $dto->created_by ?? 'system',
                'source' => $dto->source ?? 'manual',
            ]));

            Log::info('Usuario creado exitosamente', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'created_by' => $dto->created_by ?? 'system'
            ]);

            return [
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'errors' => [],
                'data' => $user
            ];

        } catch (\Exception $e) {
            Log::error('Error en CreateUserUseCase', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'dto_data' => $dto->toArray()
            ]);

            return [
                'success' => false,
                'message' => 'Error interno al crear usuario',
                'errors' => ['general' => 'Error interno del servidor'],
                'data' => null
            ];
        }
    }

    /**
     * Validar datos de entrada
     * 
     * @param CreateUserDTO $dto
     * @return array
     */
    private function validateInput(CreateUserDTO $dto): array
    {
        $errors = [];

        // Validar DTO
        if (!$dto->isValid()) {
            $errors = array_merge($errors, $dto->getValidationErrors());
        }

        // Validar email único
        try {
            $email = new Email($dto->email);
            if ($this->userRepository->emailExists($email)) {
                $errors[] = 'El email ya está en uso';
            }
        } catch (\InvalidArgumentException $e) {
            $errors[] = 'Email inválido: ' . $e->getMessage();
        }

        // Validar rol
        try {
            $role = new UserRole($dto->role);
        } catch (\InvalidArgumentException $e) {
            $errors[] = 'Rol inválido: ' . $e->getMessage();
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Validar reglas de negocio
     * 
     * @param CreateUserDTO $dto
     * @return array
     */
    private function validateBusinessRules(CreateUserDTO $dto): array
    {
        $errors = [];
        $role = new UserRole($dto->role);

        // Validar creación de reseller
        if ($role->isReseller()) {
            $errors = array_merge($errors, $this->validateResellerCreation($dto));
        }

        // Validar creación de cliente por reseller
        if ($role->isClient() && !empty($dto->reseller_id)) {
            $errors = array_merge($errors, $this->validateClientCreationByReseller($dto));
        }

        // Validar límites de administradores
        if ($role->isAdmin()) {
            $errors = array_merge($errors, $this->validateAdminCreation($dto));
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Validar creación de reseller
     * 
     * @param CreateUserDTO $dto
     * @return array
     */
    private function validateResellerCreation(CreateUserDTO $dto): array
    {
        $errors = [];

        // Verificar límites de resellers (ejemplo: máximo 100)
        $resellerCount = $this->userRepository->countByRole(UserRole::reseller());
        if ($resellerCount >= 100) {
            $errors[] = 'Se ha alcanzado el límite máximo de resellers';
        }

        // Verificar que tenga información de empresa
        if (empty($dto->company_name)) {
            $errors[] = 'Los resellers deben tener nombre de empresa';
        }

        return $errors;
    }

    /**
     * Validar creación de cliente por reseller
     * 
     * @param CreateUserDTO $dto
     * @return array
     */
    private function validateClientCreationByReseller(CreateUserDTO $dto): array
    {
        $errors = [];

        // Verificar que el reseller existe y está activo
        $reseller = $this->userRepository->findById($dto->reseller_id);
        if (!$reseller) {
            $errors[] = 'Reseller no encontrado';
        } elseif ($reseller->status !== 'active') {
            $errors[] = 'El reseller no está activo';
        } elseif ($reseller->role !== 'reseller') {
            $errors[] = 'El usuario especificado no es un reseller';
        }

        // Verificar límites de clientes por reseller (ejemplo: máximo 1000)
        if ($reseller) {
            $clientCount = $this->userRepository->findClientsByReseller($reseller->id)->count();
            if ($clientCount >= 1000) {
                $errors[] = 'El reseller ha alcanzado el límite máximo de clientes';
            }
        }

        return $errors;
    }

    /**
     * Validar creación de administrador
     * 
     * @param CreateUserDTO $dto
     * @return array
     */
    private function validateAdminCreation(CreateUserDTO $dto): array
    {
        $errors = [];

        // Verificar límites de administradores (ejemplo: máximo 10)
        $adminCount = $this->userRepository->countByRole(UserRole::admin());
        if ($adminCount >= 10) {
            $errors[] = 'Se ha alcanzado el límite máximo de administradores';
        }

        return $errors;
    }

    /**
     * Crear usuario en el repositorio
     * 
     * @param CreateUserDTO $dto
     * @return User
     */
    private function createUser(CreateUserDTO $dto): User
    {
        $userData = $dto->toArray();
        
        // Hash de la contraseña
        $userData['password'] = Hash::make($dto->password);
        
        // Establecer valores por defecto
        $userData['status'] = $userData['status'] ?? 'active';
        $userData['language_code'] = $userData['language_code'] ?? 'es';
        $userData['currency_code'] = $userData['currency_code'] ?? 'USD';

        return $this->userRepository->create($userData);
    }
}

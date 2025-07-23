<?php

namespace App\Commands\User;

use App\Commands\CommandInterface;
use App\Contracts\User\UserDeletionServiceInterface;
use App\Domains\Users\Infrastructure\Persistence\Models\User;

use Illuminate\Support\Facades\Log;

/**
 * Class DeleteUserCommand
 * 
 * Comando para eliminar usuarios de manera segura
 * Implementa Command Pattern
 */
class DeleteUserCommand implements CommandInterface
{
    private User $user;
    private UserDeletionServiceInterface $deletionService;

    /**
     * Constructor
     *
     * @param User $user
     * @param UserDeletionServiceInterface $deletionService
     */
    public function __construct(User $user, UserDeletionServiceInterface $deletionService)
    {
        $this->user = $user;
        $this->deletionService = $deletionService;
    }

    /**
     * Ejecutar el comando
     *
     * @return array
     */
    public function execute(): array
    {
        try {
            // Validar antes de ejecutar
            $validation = $this->validate();
            if (!$validation['can_execute']) {
                return [
                    'success' => false,
                    'message' => $validation['reason'],
                    'command' => $this->getDescription()
                ];
            }

            // Ejecutar eliminación
            $result = $this->deletionService->deleteUser($this->user);

            // Log del comando ejecutado
            Log::info('Comando ejecutado', [
                'command' => $this->getDescription(),
                'user_id' => $this->user->id,
                'result' => $result['success']
            ]);

            return array_merge($result, [
                'command' => $this->getDescription()
            ]);

        } catch (\Exception $e) {
            Log::error('Error ejecutando comando', [
                'command' => $this->getDescription(),
                'user_id' => $this->user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error interno ejecutando comando',
                'command' => $this->getDescription(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validar si el comando puede ser ejecutado
     *
     * @return array
     */
    public function validate(): array
    {
        // Verificar que el usuario existe
        if (!$this->user->exists) {
            return [
                'can_execute' => false,
                'reason' => 'El usuario no existe'
            ];
        }

        // Verificar si puede ser eliminado
        $canDelete = $this->deletionService->canUserBeDeleted($this->user);
        
        return [
            'can_execute' => $canDelete['can_delete'],
            'reason' => $canDelete['reason'] ?? 'Usuario puede ser eliminado'
        ];
    }

    /**
     * Obtener descripción del comando
     *
     * @return string
     */
    public function getDescription(): string
    {
        return "Eliminar usuario ID: {$this->user->id} ({$this->user->email})";
    }

    /**
     * Obtener el usuario objetivo
     *
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }
}

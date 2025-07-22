<?php

namespace App\Contracts\User;

use App\Models\User;

/**
 * Interface UserDeletionServiceInterface
 * 
 * Contrato para servicios de eliminación de usuarios
 * Cumple con Interface Segregation Principle (ISP)
 */
interface UserDeletionServiceInterface
{
    /**
     * Eliminar un usuario y manejar todas las dependencias
     *
     * @param User $user
     * @return array
     */
    public function deleteUser(User $user): array;

    /**
     * Verificar si un usuario puede ser eliminado
     *
     * @param User $user
     * @return array
     */
    public function canUserBeDeleted(User $user): array;
}

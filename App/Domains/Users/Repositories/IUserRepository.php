<?php

namespace App\Domains\Users\Repositories;

use App\Domains\Users\Models\User;
use App\Domains\Users\ValueObjects\UserRole;
use App\Domains\Shared\ValueObjects\Email;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Interface IUserRepository
 * 
 * Puerto (Port) para acceso a datos de usuarios
 * Define el contrato para persistencia de usuarios
 * Aplica principios de Arquitectura Hexagonal - Puertos
 */
interface IUserRepository
{
    /**
     * Encontrar usuario por ID
     * 
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User;

    /**
     * Encontrar usuario por email
     * 
     * @param Email $email
     * @return User|null
     */
    public function findByEmail(Email $email): ?User;

    /**
     * Encontrar usuarios por rol
     * 
     * @param UserRole $role
     * @return Collection
     */
    public function findByRole(UserRole $role): Collection;

    /**
     * Encontrar clientes de un reseller
     * 
     * @param int $resellerId
     * @return Collection
     */
    public function findClientsByReseller(int $resellerId): Collection;

    /**
     * Obtener usuarios paginados
     * 
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Crear nuevo usuario
     * 
     * @param array $data
     * @return User
     */
    public function create(array $data): User;

    /**
     * Actualizar usuario
     * 
     * @param User $user
     * @param array $data
     * @return User
     */
    public function update(User $user, array $data): User;

    /**
     * Eliminar usuario
     * 
     * @param User $user
     * @return bool
     */
    public function delete(User $user): bool;

    /**
     * Verificar si email existe
     * 
     * @param Email $email
     * @param int|null $excludeId
     * @return bool
     */
    public function emailExists(Email $email, ?int $excludeId = null): bool;

    /**
     * Contar usuarios por rol
     * 
     * @param UserRole $role
     * @return int
     */
    public function countByRole(UserRole $role): int;

    /**
     * Obtener usuarios activos
     * 
     * @return Collection
     */
    public function getActive(): Collection;

    /**
     * Obtener usuarios inactivos
     * 
     * @return Collection
     */
    public function getInactive(): Collection;

    /**
     * Buscar usuarios por término
     * 
     * @param string $term
     * @param int $limit
     * @return Collection
     */
    public function search(string $term, int $limit = 10): Collection;

    /**
     * Obtener usuarios con servicios activos
     * 
     * @return Collection
     */
    public function getWithActiveServices(): Collection;

    /**
     * Obtener usuarios con facturas pendientes
     * 
     * @return Collection
     */
    public function getWithPendingInvoices(): Collection;

    /**
     * Obtener estadísticas de usuarios
     * 
     * @return array
     */
    public function getStatistics(): array;

    /**
     * Obtener usuarios creados en un rango de fechas
     * 
     * @param \DateTimeInterface $from
     * @param \DateTimeInterface $to
     * @return Collection
     */
    public function getCreatedBetween(\DateTimeInterface $from, \DateTimeInterface $to): Collection;

    /**
     * Obtener usuarios que no han iniciado sesión en X días
     * 
     * @param int $days
     * @return Collection
     */
    public function getInactiveForDays(int $days): Collection;

    /**
     * Verificar si usuario puede ser eliminado
     * 
     * @param User $user
     * @return bool
     */
    public function canBeDeleted(User $user): bool;

    /**
     * Obtener dependencias de un usuario
     * 
     * @param User $user
     * @return array
     */
    public function getDependencies(User $user): array;
}

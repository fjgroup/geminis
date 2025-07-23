<?php

namespace App\Domains\BillingAndPayments\Interfaces;

use App\Domains\BillingAndPayments\Domain\Entities\Transaction;
use App\Domains\Users\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface para el repositorio de transacciones
 * 
 * Cumple con el principio de inversión de dependencias (DIP)
 * Define el contrato para operaciones de persistencia de transacciones
 */
interface TransactionRepositoryInterface
{
    /**
     * Crear una nueva transacción
     *
     * @param array $data
     * @return Transaction
     */
    public function create(array $data): Transaction;

    /**
     * Encontrar transacción por ID
     *
     * @param int $id
     * @return Transaction|null
     */
    public function findById(int $id): ?Transaction;

    /**
     * Actualizar una transacción
     *
     * @param Transaction $transaction
     * @param array $data
     * @return Transaction
     */
    public function update(Transaction $transaction, array $data): Transaction;

    /**
     * Eliminar una transacción
     *
     * @param Transaction $transaction
     * @return bool
     */
    public function delete(Transaction $transaction): bool;

    /**
     * Obtener transacciones de un cliente
     *
     * @param User $client
     * @param array $filters
     * @return Collection
     */
    public function getByClient(User $client, array $filters = []): Collection;

    /**
     * Obtener transacciones paginadas
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Buscar transacciones por término
     *
     * @param string $searchTerm
     * @param array $filters
     * @param int $limit
     * @return Collection
     */
    public function search(string $searchTerm, array $filters = [], int $limit = 10): Collection;

    /**
     * Obtener transacciones por estado
     *
     * @param string $status
     * @param array $filters
     * @return Collection
     */
    public function getByStatus(string $status, array $filters = []): Collection;

    /**
     * Obtener transacciones por rango de fechas
     *
     * @param \DateTime $from
     * @param \DateTime $to
     * @param array $filters
     * @return Collection
     */
    public function getByDateRange(\DateTime $from, \DateTime $to, array $filters = []): Collection;

    /**
     * Obtener total de transacciones por cliente
     *
     * @param User $client
     * @param string|null $status
     * @return float
     */
    public function getTotalByClient(User $client, ?string $status = null): float;

    /**
     * Obtener estadísticas de transacciones
     *
     * @param array $filters
     * @return array
     */
    public function getStatistics(array $filters = []): array;
}

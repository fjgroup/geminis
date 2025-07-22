<?php

namespace App\Domains\BillingAndPayments\Infrastructure\Persistence;

use App\Domains\BillingAndPayments\Interfaces\TransactionRepositoryInterface;
use App\Domains\BillingAndPayments\Domain\Entities\Transaction;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Repositorio para transacciones
 * 
 * Implementa TransactionRepositoryInterface
 * Cumple con el principio de inversión de dependencias (DIP)
 */
class TransactionRepository implements TransactionRepositoryInterface
{
    /**
     * Crear una nueva transacción
     *
     * @param array $data
     * @return Transaction
     */
    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    /**
     * Encontrar transacción por ID
     *
     * @param int $id
     * @return Transaction|null
     */
    public function findById(int $id): ?Transaction
    {
        return Transaction::with(['client', 'paymentMethod', 'invoice'])->find($id);
    }

    /**
     * Actualizar una transacción
     *
     * @param Transaction $transaction
     * @param array $data
     * @return Transaction
     */
    public function update(Transaction $transaction, array $data): Transaction
    {
        $transaction->update($data);
        return $transaction->fresh(['client', 'paymentMethod', 'invoice']);
    }

    /**
     * Eliminar una transacción
     *
     * @param Transaction $transaction
     * @return bool
     */
    public function delete(Transaction $transaction): bool
    {
        return $transaction->delete();
    }

    /**
     * Obtener transacciones de un cliente
     *
     * @param User $client
     * @param array $filters
     * @return Collection
     */
    public function getByClient(User $client, array $filters = []): Collection
    {
        $query = $client->transactions()->with(['paymentMethod', 'invoice']);

        $this->applyFilters($query, $filters);

        return $query->orderBy('transaction_date', 'desc')->get();
    }

    /**
     * Obtener transacciones paginadas
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Transaction::with(['client', 'paymentMethod', 'invoice']);

        $this->applyFilters($query, $filters);

        return $query->orderBy('transaction_date', 'desc')->paginate($perPage);
    }

    /**
     * Buscar transacciones por término
     *
     * @param string $searchTerm
     * @param array $filters
     * @param int $limit
     * @return Collection
     */
    public function search(string $searchTerm, array $filters = [], int $limit = 10): Collection
    {
        $query = Transaction::with(['client', 'paymentMethod', 'invoice'])
            ->where(function ($q) use ($searchTerm) {
                $q->where('gateway_transaction_id', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                  ->orWhereHas('client', function ($clientQuery) use ($searchTerm) {
                      $clientQuery->where('name', 'LIKE', "%{$searchTerm}%")
                                  ->orWhere('email', 'LIKE', "%{$searchTerm}%");
                  });
            });

        $this->applyFilters($query, $filters);

        return $query->limit($limit)->get();
    }

    /**
     * Obtener transacciones por estado
     *
     * @param string $status
     * @param array $filters
     * @return Collection
     */
    public function getByStatus(string $status, array $filters = []): Collection
    {
        $query = Transaction::with(['client', 'paymentMethod', 'invoice'])
            ->where('status', $status);

        $this->applyFilters($query, $filters);

        return $query->orderBy('transaction_date', 'desc')->get();
    }

    /**
     * Obtener transacciones por rango de fechas
     *
     * @param \DateTime $from
     * @param \DateTime $to
     * @param array $filters
     * @return Collection
     */
    public function getByDateRange(\DateTime $from, \DateTime $to, array $filters = []): Collection
    {
        $query = Transaction::with(['client', 'paymentMethod', 'invoice'])
            ->whereBetween('transaction_date', [$from, $to]);

        $this->applyFilters($query, $filters);

        return $query->orderBy('transaction_date', 'desc')->get();
    }

    /**
     * Obtener total de transacciones por cliente
     *
     * @param User $client
     * @param string|null $status
     * @return float
     */
    public function getTotalByClient(User $client, ?string $status = null): float
    {
        $query = $client->transactions();

        if ($status) {
            $query->where('status', $status);
        }

        return $query->sum('amount') ?? 0.0;
    }

    /**
     * Obtener estadísticas de transacciones
     *
     * @param array $filters
     * @return array
     */
    public function getStatistics(array $filters = []): array
    {
        $query = Transaction::query();
        $this->applyFilters($query, $filters);

        return [
            'total_transactions' => $query->count(),
            'total_amount' => $query->sum('amount'),
            'completed_transactions' => $query->where('status', 'completed')->count(),
            'pending_transactions' => $query->where('status', 'pending')->count(),
            'failed_transactions' => $query->where('status', 'failed')->count(),
            'average_amount' => $query->avg('amount'),
        ];
    }

    /**
     * Aplicar filtros a la consulta
     *
     * @param mixed $query
     * @param array $filters
     */
    private function applyFilters($query, array $filters): void
    {
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['date_from'])) {
            $query->where('transaction_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('transaction_date', '<=', $filters['date_to']);
        }

        if (isset($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        if (isset($filters['payment_method_id'])) {
            $query->where('payment_method_id', $filters['payment_method_id']);
        }

        if (isset($filters['amount_min'])) {
            $query->where('amount', '>=', $filters['amount_min']);
        }

        if (isset($filters['amount_max'])) {
            $query->where('amount', '<=', $filters['amount_max']);
        }
    }
}

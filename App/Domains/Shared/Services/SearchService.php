<?php

namespace App\Domains\Shared\Services;

use App\Domains\Shared\Interfaces\SearchServiceInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Servicio compartido para funcionalidades de búsqueda
 *
 * Elimina duplicación de código en controladores que implementan búsqueda
 * Cumple con el principio DRY (Don't Repeat Yourself)
 * Implementa SearchServiceInterface (DIP)
 */
class SearchService implements SearchServiceInterface
{
    /**
     * Realizar búsqueda genérica con autocompletado
     *
     * @param Builder $query Query base
     * @param string $searchTerm Término de búsqueda
     * @param array $searchFields Campos donde buscar
     * @param array $selectFields Campos a seleccionar
     * @param int $limit Límite de resultados
     * @param callable|null $formatter Función para formatear resultados
     * @return Collection
     */
    public function searchWithAutocomplete(
        Builder $query,
        string $searchTerm,
        array $searchFields,
        array $selectFields = ['*'],
        int $limit = 10,
        ?callable $formatter = null
    ): Collection {
        try {
            // Validar longitud mínima del término de búsqueda
            if (strlen($searchTerm) < 2) {
                return collect();
            }

            // Aplicar búsqueda en los campos especificados
            $query->where(function ($q) use ($searchFields, $searchTerm) {
                foreach ($searchFields as $field) {
                    $q->orWhere($field, 'LIKE', "%{$searchTerm}%");
                }
            });

            // Seleccionar campos específicos si se proporcionan
            if ($selectFields !== ['*']) {
                $query->select($selectFields);
            }

            // Aplicar límite y obtener resultados
            $results = $query->limit($limit)->get();

            // Aplicar formateador si se proporciona
            if ($formatter && is_callable($formatter)) {
                return $results->map($formatter);
            }

            return $results;

        } catch (\Exception $e) {
            Log::error('Error en SearchService::searchWithAutocomplete', [
                'error' => $e->getMessage(),
                'searchTerm' => $searchTerm,
                'searchFields' => $searchFields
            ]);

            return collect();
        }
    }

    /**
     * Formatear resultados para autocompletado estándar
     *
     * @param mixed $item Item a formatear
     * @param string $valueField Campo para el valor
     * @param string $labelField Campo para la etiqueta
     * @param string|null $extraField Campo extra para mostrar
     * @return array
     */
    public function formatForAutocomplete(
        $item,
        string $valueField = 'id',
        string $labelField = 'name',
        ?string $extraField = null
    ): array {
        $label = $item->{$labelField};
        
        if ($extraField && isset($item->{$extraField})) {
            $label .= " ({$item->{$extraField}})";
        }

        return [
            'value' => $item->{$valueField},
            'label' => $label
        ];
    }

    /**
     * Buscar usuarios con roles específicos
     *
     * @param string $searchTerm
     * @param string|array $roles
     * @param int $limit
     * @return Collection
     */
    public function searchUsers(string $searchTerm, $roles = null, int $limit = 10): Collection
    {
        $query = \App\Domains\Users\Models\User::query();

        // Filtrar por roles si se especifica
        if ($roles) {
            if (is_array($roles)) {
                $query->whereIn('role', $roles);
            } else {
                $query->where('role', $roles);
            }
        }

        return $this->searchWithAutocomplete(
            $query,
            $searchTerm,
            ['name', 'email', 'company_name'],
            ['id', 'name', 'email', 'company_name'],
            $limit,
            function ($user) {
                return $this->formatForAutocomplete($user, 'id', 'name', 'email');
            }
        );
    }

    /**
     * Buscar productos activos
     *
     * @param string $searchTerm
     * @param int $limit
     * @return Collection
     */
    public function searchProducts(string $searchTerm, int $limit = 10): Collection
    {
        $query = \App\Domains\Products\Models\Product::where('status', 'active');

        return $this->searchWithAutocomplete(
            $query,
            $searchTerm,
            ['name', 'slug'],
            ['id', 'name', 'slug'],
            $limit,
            function ($product) {
                return $this->formatForAutocomplete($product, 'id', 'name', 'slug');
            }
        );
    }

    /**
     * Buscar facturas de un cliente
     *
     * @param string $searchTerm
     * @param int $clientId
     * @param int $limit
     * @return Collection
     */
    public function searchInvoices(string $searchTerm, int $clientId, int $limit = 10): Collection
    {
        $query = \App\Domains\Invoices\Models\Invoice::where('client_id', $clientId);

        return $this->searchWithAutocomplete(
            $query,
            $searchTerm,
            ['invoice_number', 'description'],
            ['id', 'invoice_number', 'total_amount', 'status', 'issue_date'],
            $limit
        );
    }

    /**
     * Buscar transacciones
     *
     * @param string $searchTerm
     * @param array $filters
     * @param int $limit
     * @return Collection
     */
    public function searchTransactions(string $searchTerm, array $filters = [], int $limit = 10): Collection
    {
        $query = \App\Domains\BillingAndPayments\Models\Transaction::with(['client', 'paymentMethod']);

        // Aplicar filtros adicionales
        foreach ($filters as $field => $value) {
            if ($value !== null) {
                $query->where($field, $value);
            }
        }

        return $this->searchWithAutocomplete(
            $query,
            $searchTerm,
            ['gateway_transaction_id', 'description'],
            ['id', 'amount', 'currency_code', 'status', 'transaction_date'],
            $limit,
            function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'label' => "#{$transaction->id} - {$transaction->client->name} - {$transaction->amount} {$transaction->currency_code}",
                    'value' => $transaction->id,
                    'transaction' => $transaction
                ];
            }
        );
    }
}

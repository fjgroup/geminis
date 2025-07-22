<?php

namespace App\Domains\Shared\Interfaces;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Domains\Users\Models\User;

/**
 * Interface para el servicio de búsqueda
 * 
 * Cumple con el principio de inversión de dependencias (DIP)
 * Define el contrato para operaciones de búsqueda
 */
interface SearchServiceInterface
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
    ): Collection;

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
    ): array;

    /**
     * Buscar usuarios con roles específicos
     *
     * @param string $searchTerm
     * @param string|array $roles
     * @param int $limit
     * @return Collection
     */
    public function searchUsers(string $searchTerm, $roles = null, int $limit = 10): Collection;

    /**
     * Buscar productos activos
     *
     * @param string $searchTerm
     * @param int $limit
     * @return Collection
     */
    public function searchProducts(string $searchTerm, int $limit = 10): Collection;

    /**
     * Buscar facturas de un cliente
     *
     * @param string $searchTerm
     * @param int $clientId
     * @param int $limit
     * @return Collection
     */
    public function searchInvoices(string $searchTerm, int $clientId, int $limit = 10): Collection;

    /**
     * Buscar transacciones
     *
     * @param string $searchTerm
     * @param array $filters
     * @param int $limit
     * @return Collection
     */
    public function searchTransactions(string $searchTerm, array $filters = [], int $limit = 10): Collection;
}

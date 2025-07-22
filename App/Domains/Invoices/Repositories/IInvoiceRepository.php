<?php

namespace App\Domains\Invoices\Repositories;

use App\Domains\Invoices\Models\Invoice;
use App\Domains\Shared\ValueObjects\Money;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Interface IInvoiceRepository
 * 
 * Puerto (Port) para acceso a datos de facturas
 * Define el contrato para persistencia de facturas
 * Aplica principios de Arquitectura Hexagonal - Puertos
 */
interface IInvoiceRepository
{
    /**
     * Encontrar factura por ID
     * 
     * @param int $id
     * @return Invoice|null
     */
    public function findById(int $id): ?Invoice;

    /**
     * Encontrar factura por número
     * 
     * @param string $invoiceNumber
     * @return Invoice|null
     */
    public function findByNumber(string $invoiceNumber): ?Invoice;

    /**
     * Encontrar facturas por cliente
     * 
     * @param int $clientId
     * @return Collection
     */
    public function findByClient(int $clientId): Collection;

    /**
     * Encontrar facturas por reseller
     * 
     * @param int $resellerId
     * @return Collection
     */
    public function findByReseller(int $resellerId): Collection;

    /**
     * Encontrar facturas por estado
     * 
     * @param string $status
     * @return Collection
     */
    public function findByStatus(string $status): Collection;

    /**
     * Obtener facturas paginadas
     * 
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Crear nueva factura
     * 
     * @param array $data
     * @return Invoice
     */
    public function create(array $data): Invoice;

    /**
     * Actualizar factura
     * 
     * @param Invoice $invoice
     * @param array $data
     * @return Invoice
     */
    public function update(Invoice $invoice, array $data): Invoice;

    /**
     * Eliminar factura
     * 
     * @param Invoice $invoice
     * @return bool
     */
    public function delete(Invoice $invoice): bool;

    /**
     * Marcar factura como pagada
     * 
     * @param Invoice $invoice
     * @param Money $paidAmount
     * @param string $paymentMethod
     * @param string|null $transactionId
     * @return Invoice
     */
    public function markAsPaid(
        Invoice $invoice,
        Money $paidAmount,
        string $paymentMethod,
        ?string $transactionId = null
    ): Invoice;

    /**
     * Obtener facturas vencidas
     * 
     * @return Collection
     */
    public function getOverdue(): Collection;

    /**
     * Obtener facturas pendientes
     * 
     * @return Collection
     */
    public function getPending(): Collection;

    /**
     * Obtener facturas pagadas en un rango de fechas
     * 
     * @param \DateTimeInterface $from
     * @param \DateTimeInterface $to
     * @return Collection
     */
    public function getPaidBetween(\DateTimeInterface $from, \DateTimeInterface $to): Collection;

    /**
     * Obtener próximo número de factura
     * 
     * @param string|null $prefix
     * @return string
     */
    public function getNextInvoiceNumber(?string $prefix = null): string;

    /**
     * Verificar si número de factura existe
     * 
     * @param string $invoiceNumber
     * @param int|null $excludeId
     * @return bool
     */
    public function invoiceNumberExists(string $invoiceNumber, ?int $excludeId = null): bool;

    /**
     * Obtener total de ingresos por período
     * 
     * @param \DateTimeInterface $from
     * @param \DateTimeInterface $to
     * @param string $currency
     * @return Money
     */
    public function getTotalRevenue(\DateTimeInterface $from, \DateTimeInterface $to, string $currency = 'USD'): Money;

    /**
     * Obtener estadísticas de facturas
     * 
     * @return array
     */
    public function getStatistics(): array;

    /**
     * Buscar facturas por término
     * 
     * @param string $term
     * @param int $limit
     * @return Collection
     */
    public function search(string $term, int $limit = 10): Collection;

    /**
     * Obtener facturas que vencen pronto
     * 
     * @param int $days
     * @return Collection
     */
    public function getDueSoon(int $days = 7): Collection;

    /**
     * Obtener facturas con items específicos
     * 
     * @param int $productId
     * @return Collection
     */
    public function getWithProduct(int $productId): Collection;

    /**
     * Verificar si factura puede ser cancelada
     * 
     * @param Invoice $invoice
     * @return bool
     */
    public function canBeCancelled(Invoice $invoice): bool;

    /**
     * Verificar si factura puede ser editada
     * 
     * @param Invoice $invoice
     * @return bool
     */
    public function canBeEdited(Invoice $invoice): bool;

    /**
     * Obtener facturas recurrentes
     * 
     * @return Collection
     */
    public function getRecurring(): Collection;

    /**
     * Obtener resumen financiero por cliente
     * 
     * @param int $clientId
     * @return array
     */
    public function getClientFinancialSummary(int $clientId): array;

    /**
     * Obtener resumen financiero por reseller
     * 
     * @param int $resellerId
     * @return array
     */
    public function getResellerFinancialSummary(int $resellerId): array;
}

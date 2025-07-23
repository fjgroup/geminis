<?php

namespace App\Domains\ClientServices\Application\Services;

use App\Domains\ClientServices\Infrastructure\Persistence\Models\ClientService;
use App\Domains\Invoices\Infrastructure\Persistence\Models\Invoice;
use App\Domains\Invoices\Infrastructure\Persistence\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Servicio de consultas para ClientService
 * 
 * Cumple con Single Responsibility Principle - maneja consultas complejas
 * Ubicado en Application layer según arquitectura hexagonal
 */
class ClientServiceQueryService
{
    /**
     * Get renewal invoices for a client service
     *
     * @param ClientService $clientService
     * @return Collection
     */
    public function getRenewalInvoices(ClientService $clientService): Collection
    {
        return Invoice::whereHas('items', function ($query) use ($clientService) {
            $query->where('client_service_id', $clientService->id)
                  ->where('item_type', 'renewal');
        })->with(['items' => function ($query) use ($clientService) {
            $query->where('client_service_id', $clientService->id)
                  ->where('item_type', 'renewal');
        }])->get();
    }

    /**
     * Get services by status
     *
     * @param string $status
     * @return Collection
     */
    public function getServicesByStatus(string $status): Collection
    {
        return ClientService::where('status', $status)
            ->with(['client', 'product', 'billingCycle'])
            ->get();
    }

    /**
     * Get services due for renewal
     *
     * @param int $daysAhead Number of days to look ahead
     * @return Collection
     */
    public function getServicesDueForRenewal(int $daysAhead = 7): Collection
    {
        $dueDate = now()->addDays($daysAhead);
        
        return ClientService::where('status', 'active')
            ->where('next_due_date', '<=', $dueDate)
            ->with(['client', 'product', 'billingCycle'])
            ->orderBy('next_due_date')
            ->get();
    }

    /**
     * Get overdue services
     *
     * @return Collection
     */
    public function getOverdueServices(): Collection
    {
        return ClientService::where('status', 'active')
            ->where('next_due_date', '<', now())
            ->with(['client', 'product', 'billingCycle'])
            ->orderBy('next_due_date')
            ->get();
    }

    /**
     * Get services by client
     *
     * @param int $clientId
     * @param string|null $status
     * @return Collection
     */
    public function getServicesByClient(int $clientId, ?string $status = null): Collection
    {
        $query = ClientService::where('client_id', $clientId)
            ->with(['product', 'billingCycle']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get services by reseller
     *
     * @param int $resellerId
     * @param string|null $status
     * @return Collection
     */
    public function getServicesByReseller(int $resellerId, ?string $status = null): Collection
    {
        $query = ClientService::where('reseller_id', $resellerId)
            ->with(['client', 'product', 'billingCycle']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get services by product
     *
     * @param int $productId
     * @param string|null $status
     * @return Collection
     */
    public function getServicesByProduct(int $productId, ?string $status = null): Collection
    {
        $query = ClientService::where('product_id', $productId)
            ->with(['client', 'billingCycle']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get service statistics
     *
     * @return array
     */
    public function getServiceStatistics(): array
    {
        return [
            'total' => ClientService::count(),
            'active' => ClientService::where('status', 'active')->count(),
            'suspended' => ClientService::where('status', 'suspended')->count(),
            'terminated' => ClientService::where('status', 'terminated')->count(),
            'pending' => ClientService::where('status', 'pending')->count(),
            'cancelled' => ClientService::where('status', 'cancelled')->count(),
            'fraud' => ClientService::where('status', 'fraud')->count(),
            'due_for_renewal' => $this->getServicesDueForRenewal()->count(),
            'overdue' => $this->getOverdueServices()->count(),
        ];
    }

    /**
     * Search services
     *
     * @param string $search
     * @param array $filters
     * @return Collection
     */
    public function searchServices(string $search, array $filters = []): Collection
    {
        $query = ClientService::with(['client', 'product', 'billingCycle']);

        // Search in domain name, username, or client name/email
        $query->where(function ($q) use ($search) {
            $q->where('domain_name', 'like', "%{$search}%")
              ->orWhere('username', 'like', "%{$search}%")
              ->orWhereHas('client', function ($clientQuery) use ($search) {
                  $clientQuery->where('name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
              });
        });

        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['reseller_id'])) {
            $query->where('reseller_id', $filters['reseller_id']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}

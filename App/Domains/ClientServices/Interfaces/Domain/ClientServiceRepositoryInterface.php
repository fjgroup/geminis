<?php

namespace App\Domains\ClientServices\Interfaces\Domain;

use App\Domains\ClientServices\Domain\Entities\ClientService;
use App\Domains\Shared\Domain\ValueObjects\Id;

/**
 * Interfaz del repositorio de servicios de cliente
 */
interface ClientServiceRepositoryInterface
{
    public function save(ClientService $clientService): ClientService;
    
    public function findById(Id $id): ?ClientService;
    
    public function findByClientId(Id $clientId): array;
    
    public function findByProductId(Id $productId): array;
    
    public function delete(Id $id): bool;
    
    public function findActiveServices(): array;
    
    public function findExpiredServices(): array;
}

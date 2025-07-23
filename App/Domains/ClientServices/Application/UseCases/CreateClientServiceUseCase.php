<?php

namespace App\Domains\ClientServices\Application\UseCases;

use App\Domains\ClientServices\Application\Commands\CreateClientServiceCommand;
use App\Domains\ClientServices\Domain\Entities\ClientService;
use App\Domains\ClientServices\Interfaces\Domain\ClientServiceRepositoryInterface;
use App\Domains\Shared\Domain\ValueObjects\Id;
use App\Domains\ClientServices\Domain\ValueObjects\ServiceStatus;
use App\Domains\ClientServices\Domain\ValueObjects\ServicePrice;

/**
 * Caso de uso para crear un nuevo servicio de cliente
 */
class CreateClientServiceUseCase
{
    public function __construct(
        private ClientServiceRepositoryInterface $clientServiceRepository
    ) {}

    public function execute(CreateClientServiceCommand $command): ClientService
    {
        $clientService = new ClientService(
            id: Id::generate(),
            clientId: new Id($command->clientId),
            productId: new Id($command->productId),
            status: ServiceStatus::pending(),
            price: new ServicePrice($command->price, $command->currency),
            nextDueDate: $command->nextDueDate,
            resellerId: $command->resellerId ? new Id($command->resellerId) : null
        );

        return $this->clientServiceRepository->save($clientService);
    }
}

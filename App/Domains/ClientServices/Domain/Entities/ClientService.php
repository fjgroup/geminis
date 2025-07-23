<?php

namespace App\Domains\ClientServices\Domain\Entities;

use App\Domains\Shared\Domain\ValueObjects\Id;
use App\Domains\ClientServices\Domain\ValueObjects\ServiceStatus;
use App\Domains\ClientServices\Domain\ValueObjects\ServicePrice;
use Carbon\Carbon;

/**
 * Entidad de dominio ClientService
 * 
 * Representa un servicio contratado por un cliente
 * Contiene la lógica de negocio pura sin dependencias de infraestructura
 */
class ClientService
{
    public function __construct(
        private Id $id,
        private Id $clientId,
        private Id $productId,
        private ServiceStatus $status,
        private ServicePrice $price,
        private Carbon $nextDueDate,
        private ?Id $resellerId = null,
        private ?Carbon $createdAt = null,
        private ?Carbon $updatedAt = null
    ) {}

    // Getters
    public function getId(): Id
    {
        return $this->id;
    }

    public function getClientId(): Id
    {
        return $this->clientId;
    }

    public function getProductId(): Id
    {
        return $this->productId;
    }

    public function getStatus(): ServiceStatus
    {
        return $this->status;
    }

    public function getPrice(): ServicePrice
    {
        return $this->price;
    }

    public function getNextDueDate(): Carbon
    {
        return $this->nextDueDate;
    }

    public function getResellerId(): ?Id
    {
        return $this->resellerId;
    }

    // Métodos de negocio
    public function activate(): void
    {
        $this->status = ServiceStatus::active();
    }

    public function suspend(): void
    {
        $this->status = ServiceStatus::suspended();
    }

    public function terminate(): void
    {
        $this->status = ServiceStatus::terminated();
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function isSuspended(): bool
    {
        return $this->status->isSuspended();
    }

    public function isTerminated(): bool
    {
        return $this->status->isTerminated();
    }

    public function updateNextDueDate(Carbon $newDueDate): void
    {
        $this->nextDueDate = $newDueDate;
    }

    public function changePrice(ServicePrice $newPrice): void
    {
        $this->price = $newPrice;
    }
}

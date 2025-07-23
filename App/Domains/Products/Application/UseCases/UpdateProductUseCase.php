<?php

namespace App\Domains\Products\Application\UseCases;

use App\Domains\Products\Application\Commands\UpdateProductCommand;
use App\Domains\Products\Domain\Entities\Product;
use App\Domains\Products\Interfaces\Domain\ProductRepositoryInterface;
use App\Domains\Shared\Application\Services\EventBus;

/**
 * Use Case - UpdateProductUseCase
 * 
 * Actualiza un producto existente
 */
final readonly class UpdateProductUseCase
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private EventBus $eventBus
    ) {}

    public function execute(UpdateProductCommand $command): Product
    {
        // 1. Buscar producto existente
        $product = $this->productRepository->findById($command->id);
        
        if (!$product) {
            throw new \DomainException('Product not found');
        }

        // 2. Actualizar información
        $product->updateInfo($command->name, $command->description);

        // 3. Cambiar precio si es diferente
        if (!$product->getPrice()->equals($command->price)) {
            $product->changePrice($command->price);
        }

        // 4. Persistir cambios
        $this->productRepository->save($product);

        // 5. Publicar eventos
        $this->publishDomainEvents($product);

        return $product;
    }

    private function publishDomainEvents(Product $product): void
    {
        $events = $product->getDomainEvents();
        
        foreach ($events as $event) {
            $this->eventBus->publish($event);
        }

        $product->clearDomainEvents();
    }
}

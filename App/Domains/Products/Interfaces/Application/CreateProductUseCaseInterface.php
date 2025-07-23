<?php

namespace App\Domains\Products\Interfaces\Application;

use App\Domains\Products\Application\Commands\CreateProductCommand;
use App\Domains\Products\Domain\Entities\Product;

/**
 * Interface para el caso de uso CreateProduct
 */
interface CreateProductUseCaseInterface
{
    public function execute(CreateProductCommand $command): Product;
}

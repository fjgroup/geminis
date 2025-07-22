<?php

namespace App\Domains\Products;

use App\Domains\Products\Services\ProductCreator;
use App\Domains\Products\Services\ProductUpdater;
use App\Domains\Products\Services\ProductManagementService;
use Illuminate\Support\ServiceProvider;

/**
 * Class ProductServiceProvider
 * 
 * Service Provider para el dominio de productos
 * Registra todos los servicios y dependencias del dominio
 * Prepara para futura implementación de interfaces (Fase 4)
 */
class ProductServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar servicios especializados del dominio
        $this->app->singleton(ProductCreator::class);
        $this->app->singleton(ProductUpdater::class);
        
        // Registrar servicio de gestión principal (mantener compatibilidad)
        $this->app->singleton(ProductManagementService::class);

        // En el futuro (Fase 4), aquí se registrarán las interfaces:
        // $this->app->bind(IProductRepository::class, EloquentProductRepository::class);
        // $this->app->bind(ICreateProductUseCase::class, ProductCreator::class);
        // $this->app->bind(IUpdateProductUseCase::class, ProductUpdater::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Aquí se pueden cargar configuraciones específicas del dominio
        // Por ejemplo: migraciones, rutas, vistas, etc.
        
        // Cargar migraciones del dominio (si las hubiera)
        // $this->loadMigrationsFrom(__DIR__.'/Infrastructure/Persistence/Migrations');
        
        // Cargar rutas específicas del dominio (si las hubiera)
        // $this->loadRoutesFrom(__DIR__.'/Infrastructure/Http/routes.php');
        
        // Registrar observers del modelo Product
        $this->registerModelObservers();
        
        // Registrar eventos del dominio
        $this->registerDomainEvents();
    }

    /**
     * Registrar observers para los modelos del dominio
     */
    private function registerModelObservers(): void
    {
        // En el futuro se pueden registrar observers para eventos del modelo
        // Product::observe(ProductObserver::class);
    }

    /**
     * Registrar eventos específicos del dominio
     */
    private function registerDomainEvents(): void
    {
        // En el futuro se pueden registrar eventos de dominio
        // Event::listen(ProductCreated::class, SendProductCreatedNotification::class);
        // Event::listen(ProductUpdated::class, UpdateProductCache::class);
    }

    /**
     * Obtener lista de servicios proporcionados por este provider
     * 
     * @return array
     */
    public function provides(): array
    {
        return [
            ProductCreator::class,
            ProductUpdater::class,
            ProductManagementService::class,
        ];
    }

    /**
     * Verificar si el dominio está correctamente configurado
     * 
     * @return bool
     */
    public function isDomainConfigured(): bool
    {
        return class_exists(ProductCreator::class) &&
               class_exists(ProductUpdater::class) &&
               class_exists(ProductManagementService::class);
    }

    /**
     * Obtener información del dominio
     * 
     * @return array
     */
    public function getDomainInfo(): array
    {
        return [
            'name' => 'Products',
            'description' => 'Dominio para gestión de productos',
            'version' => '1.0.0',
            'services' => [
                'ProductCreator' => 'Servicio para creación de productos',
                'ProductUpdater' => 'Servicio para actualización de productos',
                'ProductManagementService' => 'Servicio de gestión general (compatibilidad)',
            ],
            'models' => [
                'Product' => 'Modelo principal de productos',
            ],
            'dtos' => [
                'CreateProductDTO' => 'DTO para creación de productos',
                'UpdateProductDTO' => 'DTO para actualización de productos',
            ],
        ];
    }
}

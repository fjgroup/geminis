<?php

namespace App\Domains\ClientServices;

use App\Domains\ClientServices\Services\ClientServiceCreator;
use App\Domains\ClientServices\Services\ClientServiceManagementService;
use App\Services\ClientServiceService;
use App\Services\ClientServiceBusinessService;
use Illuminate\Support\ServiceProvider;

/**
 * Class ClientServiceServiceProvider
 * 
 * Service Provider para el dominio de servicios de cliente
 * Registra todos los servicios y dependencias del dominio
 * Prepara para futura implementación de interfaces (Fase 4)
 */
class ClientServiceServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar servicios especializados del dominio
        $this->app->singleton(ClientServiceCreator::class);
        
        // Registrar servicio de gestión principal (mantener compatibilidad)
        $this->app->singleton(ClientServiceManagementService::class);

        // Registrar servicios de soporte (mantener en App\Services por ahora)
        $this->app->singleton(ClientServiceService::class);
        $this->app->singleton(ClientServiceBusinessService::class);

        // En el futuro (Fase 4), aquí se registrarán las interfaces:
        // $this->app->bind(IClientServiceRepository::class, EloquentClientServiceRepository::class);
        // $this->app->bind(ICreateClientServiceUseCase::class, ClientServiceCreator::class);
        // $this->app->bind(IManageClientServiceUseCase::class, ClientServiceManager::class);
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
        
        // Registrar observers del modelo ClientService
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
        // ClientService::observe(ClientServiceObserver::class);
    }

    /**
     * Registrar eventos específicos del dominio
     */
    private function registerDomainEvents(): void
    {
        // En el futuro se pueden registrar eventos de dominio
        // Event::listen(ClientServiceCreated::class, SendWelcomeEmail::class);
        // Event::listen(ClientServiceActivated::class, ProvisionService::class);
        // Event::listen(ClientServiceSuspended::class, SendSuspensionNotice::class);
        // Event::listen(ClientServiceCancelled::class, CleanupResources::class);
    }

    /**
     * Obtener lista de servicios proporcionados por este provider
     * 
     * @return array
     */
    public function provides(): array
    {
        return [
            ClientServiceCreator::class,
            ClientServiceManagementService::class,
            ClientServiceService::class,
            ClientServiceBusinessService::class,
        ];
    }

    /**
     * Verificar si el dominio está correctamente configurado
     * 
     * @return bool
     */
    public function isDomainConfigured(): bool
    {
        return class_exists(ClientServiceCreator::class) &&
               class_exists(ClientServiceManagementService::class) &&
               class_exists(ClientServiceService::class) &&
               class_exists(ClientServiceBusinessService::class);
    }

    /**
     * Obtener información del dominio
     * 
     * @return array
     */
    public function getDomainInfo(): array
    {
        return [
            'name' => 'ClientServices',
            'description' => 'Dominio para gestión de servicios de clientes y aprovisionamiento',
            'version' => '1.0.0',
            'services' => [
                'ClientServiceCreator' => 'Servicio para creación de servicios',
                'ClientServiceManagementService' => 'Servicio de gestión general (compatibilidad)',
                'ClientServiceService' => 'Servicio del lado del cliente',
                'ClientServiceBusinessService' => 'Servicio de lógica de negocio',
            ],
            'models' => [
                'ClientService' => 'Modelo principal de servicios de cliente',
            ],
            'dtos' => [
                'CreateClientServiceDTO' => 'DTO para creación de servicios',
            ],
        ];
    }
}

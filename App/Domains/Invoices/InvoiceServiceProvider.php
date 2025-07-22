<?php

namespace App\Domains\Invoices;

use App\Domains\Invoices\Services\InvoiceGenerator;
use App\Domains\Invoices\Services\InvoiceManagementService;
use App\Services\InvoiceValidationService;
use App\Services\InvoiceNumberService;
use Illuminate\Support\ServiceProvider;

/**
 * Class InvoiceServiceProvider
 * 
 * Service Provider para el dominio de facturas
 * Registra todos los servicios y dependencias del dominio
 * Prepara para futura implementación de interfaces (Fase 4)
 */
class InvoiceServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar servicios especializados del dominio
        $this->app->singleton(InvoiceGenerator::class);
        
        // Registrar servicio de gestión principal (mantener compatibilidad)
        $this->app->singleton(InvoiceManagementService::class);

        // Registrar servicios de soporte (mantener en App\Services por ahora)
        $this->app->singleton(InvoiceValidationService::class);
        $this->app->singleton(InvoiceNumberService::class);

        // En el futuro (Fase 4), aquí se registrarán las interfaces:
        // $this->app->bind(IInvoiceRepository::class, EloquentInvoiceRepository::class);
        // $this->app->bind(IGenerateInvoiceUseCase::class, InvoiceGenerator::class);
        // $this->app->bind(IValidateInvoiceUseCase::class, InvoiceValidator::class);
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
        
        // Registrar observers del modelo Invoice
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
        // Invoice::observe(InvoiceObserver::class);
        // InvoiceItem::observe(InvoiceItemObserver::class);
    }

    /**
     * Registrar eventos específicos del dominio
     */
    private function registerDomainEvents(): void
    {
        // En el futuro se pueden registrar eventos de dominio
        // Event::listen(InvoiceGenerated::class, SendInvoiceNotification::class);
        // Event::listen(InvoicePaid::class, ActivateServices::class);
        // Event::listen(InvoiceOverdue::class, SendOverdueNotification::class);
    }

    /**
     * Obtener lista de servicios proporcionados por este provider
     * 
     * @return array
     */
    public function provides(): array
    {
        return [
            InvoiceGenerator::class,
            InvoiceManagementService::class,
            InvoiceValidationService::class,
            InvoiceNumberService::class,
        ];
    }

    /**
     * Verificar si el dominio está correctamente configurado
     * 
     * @return bool
     */
    public function isDomainConfigured(): bool
    {
        return class_exists(InvoiceGenerator::class) &&
               class_exists(InvoiceManagementService::class) &&
               class_exists(InvoiceValidationService::class) &&
               class_exists(InvoiceNumberService::class);
    }

    /**
     * Obtener información del dominio
     * 
     * @return array
     */
    public function getDomainInfo(): array
    {
        return [
            'name' => 'Invoices',
            'description' => 'Dominio para gestión de facturación y generación de facturas',
            'version' => '1.0.0',
            'services' => [
                'InvoiceGenerator' => 'Servicio para generación de facturas',
                'InvoiceManagementService' => 'Servicio de gestión general (compatibilidad)',
                'InvoiceValidationService' => 'Servicio de validación de facturas',
                'InvoiceNumberService' => 'Servicio de generación de números de factura',
            ],
            'models' => [
                'Invoice' => 'Modelo principal de facturas',
                'InvoiceItem' => 'Modelo de items de factura',
            ],
            'dtos' => [
                'CreateInvoiceDTO' => 'DTO para creación de facturas',
                'InvoiceItemDTO' => 'DTO para items de factura',
            ],
        ];
    }
}

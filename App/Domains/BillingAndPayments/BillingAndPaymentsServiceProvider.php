<?php

namespace App\Domains\BillingAndPayments;

use Illuminate\Support\ServiceProvider;

/**
 * Service Provider para el dominio BillingAndPayments
 * 
 * Registra todos los servicios, repositorios e interfaces
 * relacionados con facturación y pagos
 */
class BillingAndPaymentsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar servicios del dominio
        $this->registerServices();
        
        // Registrar repositorios
        $this->registerRepositories();
        
        // Registrar interfaces
        $this->registerInterfaces();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar observers si los hay
        $this->registerObservers();
        
        // Registrar policies si las hay
        $this->registerPolicies();
    }

    /**
     * Registrar servicios del dominio
     */
    private function registerServices(): void
    {
        // Registrar servicios como singletons
        $this->app->singleton(\App\Domains\BillingAndPayments\Services\TransactionService::class);

        // $this->app->singleton(PaymentMethodService::class);
    }

    /**
     * Registrar repositorios
     */
    private function registerRepositories(): void
    {
        // Registrar repositorios con sus interfaces
        $this->app->bind(
            \App\Domains\BillingAndPayments\Interfaces\TransactionRepositoryInterface::class,
            \App\Domains\BillingAndPayments\Infrastructure\Persistence\TransactionRepository::class
        );

        // $this->app->bind(PaymentMethodRepositoryInterface::class, PaymentMethodRepository::class);
    }

    /**
     * Registrar interfaces
     */
    private function registerInterfaces(): void
    {
        // Aquí se registrarán las interfaces cuando se creen
    }

    /**
     * Registrar observers
     */
    private function registerObservers(): void
    {
        // Aquí se registrarán los observers cuando se creen
    }

    /**
     * Registrar policies
     */
    private function registerPolicies(): void
    {
        // Aquí se registrarán las policies cuando se creen
    }
}

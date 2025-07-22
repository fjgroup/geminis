<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Class DomainServiceProvider
 *
 * Service Provider principal para registrar todos los Service Providers de dominio
 * Facilita la gestión centralizada de dominios y prepara para arquitectura hexagonal
 */
class DomainServiceProvider extends ServiceProvider
{
    /**
     * Lista de Service Providers de dominio a registrar
     *
     * @var array
     */
    protected array $domainProviders = [
        \App\Domains\Shared\SharedServiceProvider::class,
        \App\Domains\Products\ProductServiceProvider::class,
        \App\Domains\Users\UserServiceProvider::class,
        \App\Domains\Invoices\InvoiceServiceProvider::class,
        \App\Domains\ClientServices\ClientServiceServiceProvider::class,
        \App\Domains\BillingAndPayments\BillingAndPaymentsServiceProvider::class,
        \App\Domains\Orders\OrderServiceProvider::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar todos los Service Providers de dominio
        foreach ($this->domainProviders as $provider) {
            $this->app->register($provider);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Aquí se pueden cargar configuraciones globales de dominios
        // Por ejemplo, rutas compartidas, middleware específico, etc.
    }

    /**
     * Registrar un nuevo Service Provider de dominio
     *
     * @param string $providerClass
     * @return void
     */
    public function registerDomainProvider(string $providerClass): void
    {
        if (!in_array($providerClass, $this->domainProviders)) {
            $this->domainProviders[] = $providerClass;
            $this->app->register($providerClass);
        }
    }

    /**
     * Obtener lista de dominios registrados
     *
     * @return array
     */
    public function getRegisteredDomains(): array
    {
        return array_map(function ($provider) {
            // Extraer nombre del dominio del namespace
            $parts = explode('\\', $provider);
            return $parts[2] ?? 'Unknown';
        }, $this->domainProviders);
    }
}

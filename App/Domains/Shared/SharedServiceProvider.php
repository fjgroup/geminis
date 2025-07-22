<?php

namespace App\Domains\Shared;

use Illuminate\Support\ServiceProvider;

/**
 * Class SharedServiceProvider
 * 
 * Service Provider para elementos compartidos entre dominios
 * Registra Value Objects, servicios compartidos y utilidades
 * Aplica principios de DDD - Shared Kernel
 */
class SharedServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar servicios compartidos
        $this->registerSharedServices();

        // Registrar SearchService como singleton con su interfaz
        $this->app->singleton(\App\Domains\Shared\Services\SearchService::class);
        $this->app->bind(
            \App\Domains\Shared\Interfaces\SearchServiceInterface::class,
            \App\Domains\Shared\Services\SearchService::class
        );

        // En el futuro se pueden registrar más servicios compartidos
        // $this->app->singleton(IEventBus::class, EventBus::class);
        // $this->app->singleton(ILogger::class, DomainLogger::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar Value Objects como singletons si es necesario
        // Configurar servicios compartidos
        
        // En el futuro se pueden registrar eventos globales
        // Event::listen('*', DomainEventLogger::class);
    }

    /**
     * Registrar servicios compartidos
     */
    private function registerSharedServices(): void
    {
        // Los Value Objects no necesitan registro ya que son inmutables
        // Pero se pueden registrar factories o builders si es necesario
        
        // Ejemplo de registro de factory para Money
        $this->app->bind('money.factory', function () {
            return new class {
                public function create(float $amount, string $currency = 'USD') {
                    return new \App\Domains\Shared\ValueObjects\Money($amount, $currency);
                }
                
                public function zero(string $currency = 'USD') {
                    return \App\Domains\Shared\ValueObjects\Money::zero($currency);
                }
                
                public function fromString(string $moneyString) {
                    return \App\Domains\Shared\ValueObjects\Money::fromString($moneyString);
                }
            };
        });

        // Ejemplo de registro de factory para Email
        $this->app->bind('email.factory', function () {
            return new class {
                public function create(string $email) {
                    return new \App\Domains\Shared\ValueObjects\Email($email);
                }
                
                public function fromString(string $email) {
                    return \App\Domains\Shared\ValueObjects\Email::fromString($email);
                }
            };
        });
    }

    /**
     * Obtener lista de servicios proporcionados por este provider
     * 
     * @return array
     */
    public function provides(): array
    {
        return [
            'money.factory',
            'email.factory',
        ];
    }

    /**
     * Obtener información del dominio compartido
     * 
     * @return array
     */
    public function getDomainInfo(): array
    {
        return [
            'name' => 'Shared',
            'description' => 'Elementos compartidos entre dominios (Shared Kernel)',
            'version' => '1.0.0',
            'value_objects' => [
                'Money' => 'Value Object para representar dinero',
                'Email' => 'Value Object para representar emails',
            ],
            'factories' => [
                'money.factory' => 'Factory para crear objetos Money',
                'email.factory' => 'Factory para crear objetos Email',
            ],
        ];
    }
}

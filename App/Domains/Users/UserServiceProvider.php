<?php

namespace App\Domains\Users;

use App\Domains\Users\Services\UserCreator;
use App\Domains\Users\Services\UserManagementService;
use Illuminate\Support\ServiceProvider;

/**
 * Class UserServiceProvider
 * 
 * Service Provider para el dominio de usuarios
 * Registra todos los servicios y dependencias del dominio
 * Prepara para futura implementación de interfaces (Fase 4)
 */
class UserServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar servicios especializados del dominio
        $this->app->singleton(UserCreator::class);
        
        // Registrar servicio de gestión principal (mantener compatibilidad)
        $this->app->singleton(UserManagementService::class);

        // En el futuro (Fase 4), aquí se registrarán las interfaces:
        // $this->app->bind(IUserRepository::class, EloquentUserRepository::class);
        // $this->app->bind(ICreateUserUseCase::class, UserCreator::class);
        // $this->app->bind(IUpdateUserUseCase::class, UserUpdater::class);
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
        
        // Registrar observers del modelo User
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
        // User::observe(UserObserver::class);
    }

    /**
     * Registrar eventos específicos del dominio
     */
    private function registerDomainEvents(): void
    {
        // En el futuro se pueden registrar eventos de dominio
        // Event::listen(UserCreated::class, SendWelcomeEmail::class);
        // Event::listen(UserRoleChanged::class, UpdateUserPermissions::class);
    }

    /**
     * Obtener lista de servicios proporcionados por este provider
     * 
     * @return array
     */
    public function provides(): array
    {
        return [
            UserCreator::class,
            UserManagementService::class,
        ];
    }

    /**
     * Verificar si el dominio está correctamente configurado
     * 
     * @return bool
     */
    public function isDomainConfigured(): bool
    {
        return class_exists(UserCreator::class) &&
               class_exists(UserManagementService::class);
    }

    /**
     * Obtener información del dominio
     * 
     * @return array
     */
    public function getDomainInfo(): array
    {
        return [
            'name' => 'Users',
            'description' => 'Dominio para gestión de usuarios, autenticación y roles',
            'version' => '1.0.0',
            'services' => [
                'UserCreator' => 'Servicio para creación de usuarios',
                'UserManagementService' => 'Servicio de gestión general (compatibilidad)',
            ],
            'models' => [
                'User' => 'Modelo principal de usuarios',
            ],
            'dtos' => [
                'CreateUserDTO' => 'DTO para creación de usuarios',
                'UpdateUserDTO' => 'DTO para actualización de usuarios',
            ],
        ];
    }
}

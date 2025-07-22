<?php
namespace App\Providers;

use App\Contracts\CartRepositoryInterface;
use App\Contracts\ProductRepositoryInterface;
use App\Contracts\User\UserFormattingServiceInterface;
use App\Contracts\User\UserRoleServiceInterface;
use App\Contracts\User\UserDeletionServiceInterface;
use App\Contracts\Invoice\InvoiceNumberServiceInterface;
use App\Contracts\Invoice\InvoiceValidationServiceInterface;
use App\Contracts\ClientService\ClientServiceBusinessServiceInterface;
use App\Repositories\CartRepository;
use App\Repositories\ProductRepository;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\ClientCheckoutService;
use App\Services\ClientInvoiceService;
use App\Services\ClientServiceBusinessService;
use App\Services\ClientServiceManagementService;
use App\Services\ClientServiceService;
use App\Services\FundAdditionService;
use App\Services\ImpersonationService;
use App\Services\InvoiceManagementService;
use App\Services\InvoiceNumberService;
use App\Services\InvoiceService;
use App\Services\InvoiceValidationService;
use App\Services\PaymentMethodService;
use App\Services\PerformanceOptimizationService;
use App\Services\PricingCalculatorService;
use App\Services\ProductManagementService;
use App\Services\ProductService;
use App\Services\TransactionManagementService;
use App\Services\UserDeletionService;
use App\Services\UserFormattingService;
use App\Services\UserManagementService;
use App\Services\UserRoleService;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

/**
 * Class ServicesServiceProvider
 *
 * Service Provider dedicado para la configuración de servicios de negocio
 * Centraliza todos los bindings de servicios y repositorios
 */
class ServicesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registrar interfaces de repositorios
        $this->registerRepositoryInterfaces();

        // Registrar interfaces de servicios
        $this->registerServiceInterfaces();

        // Registrar servicios de negocio
        $this->registerBusinessServices();

        // Registrar servicios especializados
        $this->registerSpecializedServices();

        // Configurar servicios condicionales
        $this->registerConditionalServices();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Configurar observadores de servicios si es necesario
        $this->configureServiceObservers();

        // Configurar macros de servicios
        $this->configureServiceMacros();
    }

    /**
     * Registrar interfaces de repositorios
     */
    private function registerRepositoryInterfaces(): void
    {
        $this->app->bind(CartRepositoryInterface::class, CartRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
    }

    /**
     * Registrar interfaces de servicios
     */
    private function registerServiceInterfaces(): void
    {
        // User services
        $this->app->bind(UserFormattingServiceInterface::class, UserFormattingService::class);
        $this->app->bind(UserRoleServiceInterface::class, UserRoleService::class);
        $this->app->bind(UserDeletionServiceInterface::class, UserDeletionService::class);

        // Invoice services
        $this->app->bind(InvoiceNumberServiceInterface::class, InvoiceNumberService::class);
        $this->app->bind(InvoiceValidationServiceInterface::class, InvoiceValidationService::class);

        // ClientService services
        $this->app->bind(ClientServiceBusinessServiceInterface::class, ClientServiceBusinessService::class);
    }

    /**
     * Registrar servicios de negocio principales
     */
    private function registerBusinessServices(): void
    {
        // Servicios principales como singletons para mejor performance
        $this->app->singleton(CartService::class, function ($app) {
            return new CartService(
                $app->make(CartRepositoryInterface::class),
                $app->make(ProductRepositoryInterface::class)
            );
        });

        $this->app->singleton(ProductService::class, function ($app) {
            return new ProductService(
                $app->make(ProductRepositoryInterface::class)
            );
        });

        $this->app->singleton(UserService::class);
        $this->app->singleton(InvoiceService::class);
        $this->app->singleton(InvoiceManagementService::class);
        $this->app->singleton(PricingCalculatorService::class);
    }

    /**
     * Registrar servicios especializados
     */
    private function registerSpecializedServices(): void
    {
        // Servicios de gestión administrativa
        $this->app->singleton(ClientServiceManagementService::class);
        $this->app->singleton(ClientServiceService::class);
        $this->app->singleton(ClientServiceBusinessService::class);
        $this->app->singleton(ClientCheckoutService::class);
        $this->app->singleton(ClientInvoiceService::class);
        $this->app->singleton(FundAdditionService::class);
        $this->app->singleton(InvoiceNumberService::class);
        $this->app->singleton(InvoiceValidationService::class);
        $this->app->singleton(PaymentMethodService::class);
        $this->app->singleton(PerformanceOptimizationService::class);
        $this->app->singleton(ImpersonationService::class);
        $this->app->singleton(TransactionManagementService::class);
        $this->app->singleton(ProductManagementService::class);
        $this->app->singleton(UserManagementService::class);
        $this->app->singleton(UserFormattingService::class);
        $this->app->singleton(UserRoleService::class);
        $this->app->singleton(UserDeletionService::class);

        // Servicio de checkout con todas sus dependencias
        $this->app->singleton(CheckoutService::class, function ($app) {
            return new CheckoutService(
                $app->make(PricingCalculatorService::class),
                $app->make(UserService::class),
                $app->make(InvoiceService::class)
            );
        });
    }

    /**
     * Registrar servicios condicionales
     */
    private function registerConditionalServices(): void
    {
        // Servicios que solo se cargan en ciertos entornos
        if ($this->app->environment('local', 'testing')) {
            $this->registerDevelopmentServices();
        }

        if ($this->app->environment('production')) {
            $this->registerProductionServices();
        }
    }

    /**
     * Registrar servicios para desarrollo
     */
    private function registerDevelopmentServices(): void
    {
        // Servicios específicos para desarrollo
        // Por ejemplo: servicios de debugging, logging extendido, etc.
    }

    /**
     * Registrar servicios para producción
     */
    private function registerProductionServices(): void
    {
        // Servicios específicos para producción
        // Por ejemplo: servicios de monitoreo, cache optimizado, etc.
    }

    /**
     * Configurar observadores de servicios
     */
    private function configureServiceObservers(): void
    {
        // Configurar observadores para eventos de servicios
        // Por ejemplo: logging automático, cache invalidation, etc.
    }

    /**
     * Configurar macros de servicios
     */
    private function configureServiceMacros(): void
    {
        // Configurar macros útiles para los servicios
        // Por ejemplo: métodos de conveniencia, helpers, etc.
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            // Interfaces
            CartRepositoryInterface::class,
            ProductRepositoryInterface::class,

            // Servicios principales
            CartService::class,
            ProductService::class,
            UserService::class,
            InvoiceService::class,
            InvoiceManagementService::class,
            PricingCalculatorService::class,

            // Servicios especializados
            CheckoutService::class,
            ClientServiceManagementService::class,
            ClientServiceService::class,
            ClientServiceBusinessService::class,
            ClientCheckoutService::class,
            ClientInvoiceService::class,
            FundAdditionService::class,
            InvoiceNumberService::class,
            InvoiceValidationService::class,
            PaymentMethodService::class,
            PerformanceOptimizationService::class,
            ImpersonationService::class,
            TransactionManagementService::class,
            ProductManagementService::class,
            UserManagementService::class,
            UserFormattingService::class,
            UserRoleService::class,
            UserDeletionService::class,
        ];
    }
}

<?php
namespace App\Providers;

// use App\Models\Order; // Removed
// use App\Observers\OrderObserver; // Removed
// use App\Models\ClientService; // Removed
// use App\Observers\ClientServiceObserver; // Removed

// Nuevas dependencias para la refactorización
use App\Interfaces\PaymentGatewayInterface;
use App\Models\Invoice;
use App\Observers\InvoiceObserver;
use App\Services\PaypalGatewayService;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Binding existente
        $this->app->bind(PaymentGatewayInterface::class, PaypalGatewayService::class);

        // Nota: Los servicios de negocio ahora se registran en ServicesServiceProvider
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        Invoice::observe(InvoiceObserver::class);
        // Order::observe(OrderObserver::class); // Removed
        // ClientService::observe(ClientServiceObserver::class); // Removed
    }
}

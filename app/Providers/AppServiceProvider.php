<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use App\Models\Invoice;
use App\Observers\InvoiceObserver;
// use App\Models\Order; // Removed
// use App\Observers\OrderObserver; // Removed
use App\Models\ClientService;
use App\Observers\ClientServiceObserver;
use App\Interfaces\PaymentGatewayInterface;
use App\Services\PaypalGatewayService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGatewayInterface::class, PaypalGatewayService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        Invoice::observe(InvoiceObserver::class);
        // Order::observe(OrderObserver::class); // Removed
        ClientService::observe(ClientServiceObserver::class);
    }
}

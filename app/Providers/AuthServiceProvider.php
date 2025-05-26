<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate; // Descomentar si usas Gates directamente
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Order;
use App\Models\User;
use App\Policies\OrderPolicy;
use App\Policies\UserPolicy;
use App\Models\Product;
use App\Policies\ProductPolicy;
use App\Models\ProductPricing;
use App\Policies\ProductPricingPolicy;

use App\Models\Invoice;
use App\Policies\InvoicePolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Product::class => ProductPolicy::class,
        ProductPricing::class => ProductPricingPolicy::class,
        ProductPricing::class => ProductPricingPolicy::class,
        Order::class => OrderPolicy::class,
        Invoice::class => InvoicePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        //
    }
}

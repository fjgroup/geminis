<?php

namespace App\Domains\ClientServices\Infrastructure\Persistence\Models;

use App\Domains\Products\Infrastructure\Persistence\Models\BillingCycle;
use App\Domains\Users\Infrastructure\Persistence\Models\User;
use App\Domains\Products\Infrastructure\Persistence\Models\Product;
use App\Domains\Products\Infrastructure\Persistence\Models\ProductPricing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

// use App\Models\OrderItem; // Removed

class ClientService extends Model
{
    use HasFactory, SoftDeletes; // HasResellerScope temporalmente deshabilitado

    protected $fillable = [
        'client_id',
        'reseller_id',
        // 'order_id', // Removed
        // 'order_item_id', // Removed
        'product_id',
        'product_pricing_id',
        'billing_cycle_id',
        'domain_name',
        'username',
        'password_encrypted',
        // 'server_id',
        'status',
        'registration_date',
        'next_due_date',
        'termination_date',
        'billing_amount',
        'notes',
    ];

    protected $casts = [
        'registration_date'  => 'date',
        'next_due_date'      => 'date',
        'termination_date'   => 'date',
        'password_encrypted' => 'encrypted', // Laravel encriptará/desencriptará automáticamente este campo
        'billing_amount'     => 'decimal:2',
    ];

    /**
     * Get the client that owns the service.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Get the reseller associated with the service, if any.
     */
    public function reseller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reseller_id');
    }

    /**
     * Get the product associated with the service.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product pricing associated with the service.
     */
    public function productPricing(): BelongsTo
    {
        return $this->belongsTo(ProductPricing::class);
    }

    /**
     * Get the billing cycle associated with the service.
     */
    public function billingCycle(): BelongsTo
    {
        return $this->belongsTo(BillingCycle::class);
    }

    // public function server(): BelongsTo
    // {
    //     return $this->belongsTo(Server::class);
    // }

    // TODO: Definir relación configurableOptionsSelected() (muchos a muchos con ConfigurableOption)
    // a través de la tabla client_service_configurable_options.

    /**
     * Scope for active services
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for services by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}

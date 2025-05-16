<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientService extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'reseller_id',
        'order_id',
        'product_id',
        'product_pricing_id',
        'domain_name',
        'username',
        'password_encrypted',
        'server_id',
        'status',
        'registration_date',
        'next_due_date',
        'termination_date',
        'billing_amount',
        'notes',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'next_due_date' => 'date',
        'termination_date' => 'date',
        'password_encrypted' => 'encrypted', // Laravel encriptará/desencriptará automáticamente este campo
        'billing_amount' => 'decimal:2',
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
     * Get the product pricing (billing cycle) associated with the service.
     */
    public function productPricing(): BelongsTo
    {
        return $this->belongsTo(ProductPricing::class);
    }

    // TODO: Definir las siguientes relaciones cuando los modelos existan y estén listos:
    // public function order(): BelongsTo
    // {
    //     return $this->belongsTo(Order::class);
    // }

    // public function server(): BelongsTo
    // {
    //     return $this->belongsTo(Server::class);
    // }

    // TODO: Definir relación configurableOptionsSelected() (muchos a muchos con ConfigurableOption)
    // a través de la tabla client_service_configurable_options.
}

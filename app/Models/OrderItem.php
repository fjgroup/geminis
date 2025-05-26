<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_pricing_id',
        'item_type',
        'description',
        'quantity',
        'unit_price',
        'setup_fee',
        'total_price',
        'domain_name',
        'registration_period_years',
        'client_service_id',
    ];

    /**
     * Get the order that this item belongs to.
     */
    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Order::class);
    }

    /**
     * Get the product associated with this item.
     */
    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    /**
     * Get the product pricing associated with this item.
     */
    public function productPricing(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\ProductPricing::class);
    }

    /**
     * Get the client service associated with this item.
     */
    public function clientService(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\ClientService::class)->nullable();
    }
}

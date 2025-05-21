<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;



class ProductPricing extends Model
{
    protected $fillable = [
        'product_id',
        'billing_cycle_id',
        'price',
        'setup_fee',
        'currency_code',
        'is_active',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }


    /**
     * Get the billing cycle that the product pricing belongs to.
     */
    public function billingCycle():BelongsTo
    {
        return $this->belongsTo(BillingCycle::class);
    }
}

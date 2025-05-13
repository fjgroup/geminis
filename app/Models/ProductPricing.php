<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPricing extends Model
{
    protected $fillable = [
        'product_id',
        'billing_cycle',
        'price',
        'setup_fee',
        'currency_code',
        'is_active',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

   
}

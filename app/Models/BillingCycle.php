<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class BillingCycle extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'days',
    ];

    /**
     * Get the product pricings for the billing cycle.
     */
    public function productPricings():HasMany
    {
        return $this->hasMany(ProductPricing::class);
    }
}

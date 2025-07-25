﻿<?php

namespace App\Domains\Products\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent para BillingCycle en arquitectura hexagonal
 * 
 * Este es el modelo REAL que se conecta a la base de datos
 * Ubicado en Infrastructure layer como adaptador de persistencia
 */
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
    public function productPricings(): HasMany
    {
        return $this->hasMany(ProductPricing::class);
    }

    /**
     * Get the discount percentages for this billing cycle.
     */
    public function discountPercentages(): HasMany
    {
        return $this->hasMany(DiscountPercentage::class);
    }

    /**
     * Get the discount percentage for a specific product in this billing cycle.
     */
    public function getDiscountForProduct($productId)
    {
        return DiscountPercentage::getDiscountForProductAndCycle($productId, $this->id);
    }

    /**
     * Scope to order billing cycles by days (shortest first).
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('days', 'asc');
    }
}

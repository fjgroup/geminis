<?php

namespace App\Domains\Products\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent para DiscountPercentage en arquitectura hexagonal
 * 
 * Este es el modelo REAL que se conecta a la base de datos
 * Ubicado en Infrastructure layer como adaptador de persistencia
 */
class DiscountPercentage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'percentage',
        'is_active',
        'product_id',
        'billing_cycle_id',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'is_active'  => 'boolean',
    ];

    /**
     * Get the product that this discount applies to.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the billing cycle that this discount applies to.
     */
    public function billingCycle(): BelongsTo
    {
        return $this->belongsTo(BillingCycle::class);
    }

    /**
     * Scope to get active discounts only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get discounts for a specific product and billing cycle.
     */
    public function scopeForProductAndCycle($query, $productId, $billingCycleId)
    {
        return $query->where('product_id', $productId)
            ->where('billing_cycle_id', $billingCycleId);
    }

    /**
     * Get the discount percentage for a specific product and billing cycle.
     */
    public static function getDiscountForProductAndCycle($productId, $billingCycleId)
    {
        $discount = static::active()
            ->forProductAndCycle($productId, $billingCycleId)
            ->first();

        return $discount ? $discount->percentage : 0;
    }
}

<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'discount_percentage_id',
    ];

    /**
     * Get the product pricings for the billing cycle.
     */
    public function productPricings(): HasMany
    {
        return $this->hasMany(ProductPricing::class);
    }

    /**
     * Get the discount percentage for this billing cycle.
     */
    public function discountPercentage(): BelongsTo
    {
        return $this->belongsTo(DiscountPercentage::class);
    }

    /**
     * Get the discount percentage value or 0 if none.
     */
    public function getDiscountPercentageAttribute(): float
    {
        return $this->discountPercentage?->percentage ?? 0.0;
    }
}

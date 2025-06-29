<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscountPercentage extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'percentage',
        'is_active',
        'applicable_product_types',
    ];

    protected $casts = [
        'percentage'               => 'decimal:2',
        'is_active'                => 'boolean',
        'applicable_product_types' => 'array',
    ];

    /**
     * Relación con billing cycles
     */
    public function billingCycles(): HasMany
    {
        return $this->hasMany(BillingCycle::class);
    }

    /**
     * Scope para descuentos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Verificar si el descuento aplica a un tipo de producto
     */
    public function appliesToProductType(string $productType): bool
    {
        if (empty($this->applicable_product_types)) {
            return true; // Si no hay restricciones, aplica a todos
        }

        return in_array($productType, $this->applicable_product_types);
    }
}

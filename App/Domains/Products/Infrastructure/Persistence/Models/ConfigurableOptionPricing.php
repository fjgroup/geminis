<?php

namespace App\Domains\Products\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent para ConfigurableOptionPricing en arquitectura hexagonal
 * 
 * Este es el modelo REAL que se conecta a la base de datos
 * Ubicado en Infrastructure layer como adaptador de persistencia
 */
class ConfigurableOptionPricing extends Model
{
    use HasFactory;

    protected $fillable = [
        'configurable_option_id',
        'billing_cycle_id',
        'price',
        'setup_fee',
        'currency_code',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'price'     => 'decimal:2',
        'setup_fee' => 'decimal:2',
        'is_active' => 'boolean',
        'metadata'  => 'array',
    ];

    /**
     * Get the configurable option that this pricing belongs to.
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(ConfigurableOption::class, 'configurable_option_id');
    }

    /**
     * Get the billing cycle that this option pricing is linked to.
     */
    public function billingCycle(): BelongsTo
    {
        return $this->belongsTo(BillingCycle::class, 'billing_cycle_id');
    }

    /**
     * Scope for active pricings
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific currency
     */
    public function scopeCurrency($query, $currency)
    {
        return $query->where('currency_code', $currency);
    }
}

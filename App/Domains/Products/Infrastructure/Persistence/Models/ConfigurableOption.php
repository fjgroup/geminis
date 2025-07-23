<?php

namespace App\Domains\Products\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent para ConfigurableOption en arquitectura hexagonal
 *
 * Este es el modelo REAL que se conecta a la base de datos
 * Ubicado en Infrastructure layer como adaptador de persistencia
 */
class ConfigurableOption extends Model
{
    use HasFactory;

    protected $table = 'configurable_options';

    protected $fillable = [
        'group_id',
        'name',
        'slug',
        'value',
        'description',
        'option_type',
        'is_required',
        'is_active',
        'min_value',
        'max_value',
        'display_order',
        'metadata',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active'   => 'boolean',
        'min_value'   => 'decimal:2',
        'max_value'   => 'decimal:2',
        'metadata'    => 'array',
    ];

    /**
     * Get the group that owns the configurable option.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ConfigurableOptionGroup::class, 'group_id');
    }

    /**
     * Get the pricings for this configurable option.
     */
    public function pricings(): HasMany
    {
        return $this->hasMany(ConfigurableOptionPricing::class);
    }

    /**
     * Scope for active options
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for required options
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope for ordered display
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    /**
     * Check if this option requires a quantity
     */
    public function requiresQuantity(): bool
    {
        return $this->option_type === 'quantity';
    }

    /**
     * Get the price for a specific billing cycle
     */
    public function getPriceForBillingCycle($billingCycleId): ?float
    {
        $pricing = $this->pricings()
            ->where('billing_cycle_id', $billingCycleId)
            ->where('is_active', true)
            ->first();

        return $pricing?->price;
    }
}

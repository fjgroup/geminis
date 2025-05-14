<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; 

class ConfigurableOption extends Model

{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'name',
        'value',
        'display_order',
    ];

    /**
     * Get the group that owns the configurable option.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ConfigurableOptionGroup::class, 'group_id');
    }

    // TODO: Definir la relación pricings() cuando se cree el modelo ConfigurableOptionPricing
    // public function pricings(): HasMany
    // {
    //     return $this->hasMany(ConfigurableOptionPricing::class);
    // }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConfigurableOptionGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'description',
        'display_order',
    ];

    /**
     * Get the product that owns the configurable option group.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the configurable options for the group.
     */
    public function configurableOptions(): HasMany
    {
        return $this->hasMany(ConfigurableOption::class, 'group_id'); // Ajusta 'group_id' si es necesario
    }
}


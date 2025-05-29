<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


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
     * Get the product that this group is specifically assigned to (if any).
     */
    public function productOwner(): BelongsTo // Nombre de relación más descriptivo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * The products that belong to the configurable option group.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_configurable_option_groups')
            ->withPivot('display_order')
            ->withTimestamps();
    }

    /**
     * Get the configurable options for the group.
     */
    public function options(): HasMany
    {
        return $this->hasMany(ConfigurableOption::class, 'group_id'); // Ajusta 'group_id' si es necesario
    }
}

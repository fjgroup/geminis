<?php

namespace App\Domains\Products\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent para ConfigurableOptionGroup en arquitectura hexagonal
 *
 * Este es el modelo REAL que se conecta a la base de datos
 * Ubicado en Infrastructure layer como adaptador de persistencia
 */
class ConfigurableOptionGroup extends Model
{
    use HasFactory;

    protected $table = 'configurable_option_groups';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'display_order',
        'is_active',
        'is_required',
        'metadata',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'is_required' => 'boolean',
        'metadata'    => 'array',
    ];

    /**
     * The products that belong to the configurable option group.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_configurable_option_groups')
            ->withPivot('display_order', 'base_quantity', 'is_required')
            ->withTimestamps();
    }

    /**
     * Get the configurable options for the group.
     */
    public function options(): HasMany
    {
        return $this->hasMany(ConfigurableOption::class, 'group_id');
    }

    /**
     * Scope for active groups
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for required groups
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
}

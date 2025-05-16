<?php

namespace App\Models;
use App\Models\ProductPricing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;



class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'module_name',
        'owner_id',
        'status',
        'is_publicly_available',
        'is_resellable_by_default',
        'display_order',
      //  'welcome_email_template_id',
    ];

    // protected $casts = [
    //     'is_publicly_available' => 'boolean',
    //     'is_resellable_by_default' => 'boolean',
    // ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function configurableOptionGroups(): BelongsToMany
    {
        return $this->belongsToMany(ConfigurableOptionGroup::class, 'product_configurable_option_groups')
            ->withPivot('display_order')
            ->withTimestamps();
    }

    public function pricings(): HasMany
    {
        return $this->hasMany(ProductPricing::class);
    }
    // Otros métodos y relaciones del modelo Product...
}

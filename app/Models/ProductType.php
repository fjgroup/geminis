<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'requires_domain',
        'creates_service_instance',
        'description',
    ];

    protected $casts = [
        'requires_domain' => 'boolean',
        'creates_service_instance' => 'boolean',
    ];

    /**
     * Get the products associated with this product type.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'product_type_id'); // Assuming 'product_type_id' foreign key on Products table
    }
}

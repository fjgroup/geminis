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
        'status',
        'requires_domain',
        'creates_service_instance',
        'is_publicly_available',
        'supports_configurable_options',
        'supports_billing_cycles',
        'supports_discounts',
        'description',
        'display_order',
        'metadata',
    ];

    protected $casts = [
        'requires_domain'               => 'boolean',
        'creates_service_instance'      => 'boolean',
        'is_publicly_available'         => 'boolean',
        'supports_configurable_options' => 'boolean',
        'supports_billing_cycles'       => 'boolean',
        'supports_discounts'            => 'boolean',
        'metadata'                      => 'array',
    ];

    /**
     * Get the products associated with this product type.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'product_type_id'); // Assuming 'product_type_id' foreign key on Products table
    }

    /**
     * Scope for active product types
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for publicly available product types
     */
    public function scopePublic($query)
    {
        return $query->where('is_publicly_available', true);
    }

    /**
     * Scope for product types that support discounts
     */
    public function scopeSupportsDiscounts($query)
    {
        return $query->where('supports_discounts', true);
    }

    /**
     * Scope for ordered display
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }
}

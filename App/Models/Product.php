<?php
namespace App\Models;

use App\Models\ProductPricing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes; // HasResellerScope temporalmente deshabilitado

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
        'product_type_id',
        'auto_setup',
        'requires_approval',
        'setup_fee',
        'stock_quantity',
        'track_stock',
        // Base resources (dynamic)
        'base_resources',
        // Landing page fields
        'landing_page_slug',
        'landing_page_description',
        'landing_page_image',
        'features_list',
        'call_to_action_text',
        'metadata',
        //  'welcome_email_template_id',
    ];

    protected $casts = [
        'is_publicly_available'    => 'boolean',
        'is_resellable_by_default' => 'boolean',
        'auto_setup'               => 'boolean',
        'requires_approval'        => 'boolean',
        'track_stock'              => 'boolean',
        'setup_fee'                => 'decimal:2',
        'features_list'            => 'array',
        'metadata'                 => 'array',
        'base_resources'           => 'array',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function configurableOptionGroups(): BelongsToMany
    {
        return $this->belongsToMany(ConfigurableOptionGroup::class, 'product_configurable_option_groups')
            ->withPivot('display_order', 'base_quantity', 'is_required')
            ->withTimestamps()
            ->orderBy('product_configurable_option_groups.display_order');
    }

    public function pricings(): HasMany
    {
        return $this->hasMany(ProductPricing::class);
    }

    /**
     * Get the product type that this product belongs to.
     */
    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }

    /**
     * Get the discount percentages for this product.
     */
    public function discountPercentages(): HasMany
    {
        return $this->hasMany(DiscountPercentage::class);
    }

    /**
     * Get the discount percentage for a specific billing cycle.
     */
    public function getDiscountForCycle($billingCycleId)
    {
        return DiscountPercentage::getDiscountForProductAndCycle($this->id, $billingCycleId);
    }

    // Otros métodos y relaciones del modelo Product...
}

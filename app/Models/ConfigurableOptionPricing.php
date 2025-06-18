<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfigurableOptionPricing extends Model
{
    use HasFactory;

    protected $fillable = ['configurable_option_id', 'product_pricing_id', 'price', 'setup_fee'];

    // Si el nombre de la tabla no sigue la convención plural (configurable_option_pricings), descomenta:
    // protected $table = 'configurable_option_pricing';

    /**
     * Get the configurable option that this pricing belongs to.
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(ConfigurableOption::class, 'configurable_option_id');
    }

    /**
     * Get the product pricing that this option pricing is linked to.
     */
    public function productPricing(): BelongsTo
    {
        return $this->belongsTo(ProductPricing::class, 'product_pricing_id');
    }
}
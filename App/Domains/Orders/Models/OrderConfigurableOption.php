<?php
namespace App\Domains\Orders\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Products\Models\Product;
use App\Domains\Products\Models\ConfigurableOption;
use App\Domains\Products\Models\ConfigurableOptionGroup;
use App\Domains\Products\Models\BillingCycle;

class OrderConfigurableOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'cart_item_id',
        'product_id',
        'client_email',
        'configurable_option_id',
        'configurable_option_group_id',
        'option_name',
        'group_name',
        'quantity',
        'option_value',
        'unit_price',
        'total_price',
        'currency_code',
        'billing_cycle_id',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'quantity'     => 'decimal:2',
        'unit_price'   => 'decimal:2',
        'total_price'  => 'decimal:2',
        'option_value' => 'array',
        'metadata'     => 'array',
        'is_active'    => 'boolean',
    ];

    // Relaciones
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function configurableOption(): BelongsTo
    {
        return $this->belongsTo(ConfigurableOption::class);
    }

    public function configurableOptionGroup(): BelongsTo
    {
        return $this->belongsTo(ConfigurableOptionGroup::class);
    }

    public function billingCycle(): BelongsTo
    {
        return $this->belongsTo(BillingCycle::class);
    }

    // Métodos de utilidad
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->total_price, 2) . ' ' . $this->currency_code;
    }

    public function getDisplayNameAttribute(): string
    {
        $display = $this->group_name . ': ' . $this->option_name;

        if ($this->quantity > 1) {
            $display .= ' (Cantidad: ' . $this->quantity . ')';
        }

        return $display;
    }
}

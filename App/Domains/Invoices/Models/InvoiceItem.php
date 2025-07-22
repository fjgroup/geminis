<?php

namespace App\Domains\Invoices\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Products\Models\Product;
use App\Models\ProductPricing;
use App\Models\ClientService;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'client_service_id',
        // 'order_item_id', // Eliminado
        'description',
        'quantity',
        'unit_price',
        'total_price',
        'taxable',
        'product_id',
        'product_pricing_id',
        'setup_fee',
        'domain_name',
        'registration_period_years',
        'item_type',
    ];


    /**
     * Get the invoice that owns the invoice item.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the product associated with this item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the product pricing associated with this item.
     */
    public function productPricing(): BelongsTo
    {
        return $this->belongsTo(ProductPricing::class, 'product_pricing_id');
    }

    /**
     * Get the client service associated with the invoice item (for items from a service).
    */

        public function clientService(): BelongsTo
    {
          return $this->belongsTo(related: ClientService::class);
    }
}


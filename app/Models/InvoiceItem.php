<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
 // Added

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'client_service_id',
        'order_item_id',
        'description',
        'quantity',
        'unit_price',
        'total_price',
        'taxable',
    ];

    /**
     * Get the invoice that owns the invoice item.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the order item associated with the invoice item (for items from an order).
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class)->nullable();
    }

    public function clientService(): BelongsTo
    {
        return $this->belongsTo(ClientService::class)->nullable();
    }
}

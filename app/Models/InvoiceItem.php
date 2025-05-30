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
    public function orderItem(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\OrderItem::class);
    }
    /**
     * Get the client service associated with the invoice item (for items from a service).
    */

        public function clientService(): BelongsTo
    {
          return $this->belongsTo(related: ClientService::class);
    }
}


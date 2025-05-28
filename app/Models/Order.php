<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes; // Added SoftDeletes
use Illuminate\Database\Eloquent\Factories\HasFactory; // Added HasFactory

class Order extends Model
{
    use HasFactory, SoftDeletes; // Added traits

    protected $fillable = [
        'client_id',
        'reseller_id',
        'order_number',
        'invoice_id',
        'order_date',
        'status',
        'total_amount',
        'currency_code',
        'payment_gateway_slug',
        'ip_address',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'datetime',
    ];

    public function client():BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function reseller():BelongsTo
    {
        return $this->belongsTo(User::class, 'reseller_id');
    }

    public function invoice():BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function items():HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}

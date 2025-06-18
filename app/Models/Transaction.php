<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'client_id',
        'reseller_id',
        'payment_method_id',
        'gateway_slug',
        'gateway_transaction_id',
        'type',
        'amount',
        'currency_code',
        'status',
        'fees_amount',
        'description',
        'transaction_date',
        'admin_notes', // Added for rejection reasons or other admin remarks
        // created_at and updated_at are handled by default
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fees_amount' => 'decimal:2',
        'transaction_date' => 'datetime',
        // 'type' and 'status' could be cast to custom Enum classes in Laravel 9+ if defined
    ];

    /**
     * Get the invoice that this transaction belongs to.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the client user associated with this transaction.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Get the reseller user associated with this transaction (if any).
     */
    public function reseller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reseller_id');
    }

    /**
     * Get the payment method used for this transaction.
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }
    
    // Consider adding a relationship for related_transaction_id if that column is added later
    // public function relatedTransaction(): BelongsTo
    // {
    //     return $this->belongsTo(Transaction::class, 'related_transaction_id');
    // }
}

<?php

namespace App\Domains\BillingAndPayments\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent para Transaction en arquitectura hexagonal
 *
 * Este es el modelo REAL que se conecta a la base de datos
 * Ubicado en Infrastructure layer como adaptador de persistencia
 */
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
        'admin_notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fees_amount' => 'decimal:2',
        'transaction_date' => 'datetime',
    ];

    /**
     * Get the invoice that this transaction belongs to.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Invoices\Infrastructure\Persistence\Models\Invoice::class);
    }

    /**
     * Get the client user associated with this transaction.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Users\Infrastructure\Persistence\Models\User::class, 'client_id');
    }

    /**
     * Get the reseller user associated with this transaction (if any).
     */
    public function reseller(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Users\Infrastructure\Persistence\Models\User::class, 'reseller_id');
    }

    /**
     * Get the payment method used for this transaction.
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}

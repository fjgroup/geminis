<?php

namespace App\Domains\Invoices\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Domains\Users\Models\User;
use App\Models\ClientService;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'reseller_id',
        'invoice_number',
        'issue_date',
        'due_date',
        'paid_date',
        'status',
        'paypal_order_id',
        'subtotal',
        'tax1_name',
        'tax1_rate',
        'tax1_amount',
        'tax2_name',
        'tax2_rate',
        'tax2_amount',
        'total_amount',
        'currency_code',
        'notes_to_client',
        'admin_notes',
        'requested_date',
        'ip_address',
        'payment_gateway_slug',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
        'requested_date' => 'datetime',
    ];

    /**
     * Generate a unique invoice number.
     *
     * @deprecated Use InvoiceNumberService::generateNextInvoiceNumber() instead
     * @return string
     */
    public static function generateInvoiceNumber(): string
    {
        $invoiceNumberService = app(\App\Services\InvoiceNumberService::class);
        return $invoiceNumberService->generateNextInvoiceNumber('INV-' . now()->format('Ymd') . '-');
    }


    /**
     * Get the client that owns the invoice.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Get the reseller that owns the invoice.
     */
    public function reseller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reseller_id');
    }

    /**
     * Get the invoice items for the invoice.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get the transactions for the invoice.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Check if the invoice can be cancelled as a new service.
     *
     * @deprecated Use InvoiceValidationService::canInvoiceBeCancelledAsNewService() instead
     * @return bool
     */
    public function isCancellableAsNewService(): bool
    {
        $validationService = app(\App\Services\InvoiceValidationService::class);
        $result = $validationService->canInvoiceBeCancelledAsNewService($this);

        return $result['can_cancel'] ?? false;
    }
}

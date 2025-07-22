<?php

namespace App\Domains\ClientServices\Models;

use App\Models\BillingCycle;
use App\Domains\Users\Models\User;
use App\Domains\Products\Models\Product;
use App\Models\ProductPricing;
use App\Domains\Invoices\Models\InvoiceItem;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// use App\Models\OrderItem; // Removed

class ClientService extends Model
{
    use HasFactory, SoftDeletes; // HasResellerScope temporalmente deshabilitado

    protected $fillable = [
        'client_id',
        'reseller_id',
        // 'order_id', // Removed
        // 'order_item_id', // Removed
        'product_id',
        'product_pricing_id',
        'billing_cycle_id',
        'domain_name',
        'username',
        'password_encrypted',
        // 'server_id',
        'status',
        'registration_date',
        'next_due_date',
        'termination_date',
        'billing_amount',
        'notes',
    ];

    protected $casts = [
        'registration_date'  => 'date',
        'next_due_date'      => 'date',
        'termination_date'   => 'date',
        'password_encrypted' => 'encrypted', // Laravel encriptará/desencriptará automáticamente este campo
        'billing_amount'     => 'decimal:2',
    ];

    /**
     * Get the client that owns the service.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Get the reseller associated with the service, if any.
     */
    public function reseller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reseller_id');
    }

    /**
     * Get the product associated with the service.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product pricing associated with the service.
     */
    public function productPricing(): BelongsTo
    {
        return $this->belongsTo(ProductPricing::class);
    }

    /**
     * Get the billing cycle associated with the service.
     */
    public function billingCycle(): BelongsTo
    {
        return $this->belongsTo(BillingCycle::class);
    }

    // public function server(): BelongsTo
    // {
    //     return $this->belongsTo(Server::class);
    // }

    // TODO: Definir relación configurableOptionsSelected() (muchos a muchos con ConfigurableOption)
    // a través de la tabla client_service_configurable_options.
    /**
     * Get the possible enum values for a given column.
     *
     * @deprecated Use ClientServiceBusinessService::getEnumValues() instead
     * @param string $column
     * @return array
     */
    public static function getPossibleEnumValues(string $column): array
    {
        $businessService = app(\App\Services\ClientServiceBusinessService::class);
        return $businessService->getEnumValues($column);
    }

    /**
     * Get invoices associated with this client service where item_type is 'renewal'.
     * These are potential invoices generated for the renewal of this service.
     */
    public function renewalInvoices(): HasManyThrough
    {
        return $this->hasManyThrough(
            Invoice::class,                                 // The final model we want to access
            InvoiceItem::class,                             // The intermediate model
            'client_service_id',                            // Foreign key on InvoiceItem table (links to ClientService)
            'id',                                           // Foreign key on Invoice table (links to InvoiceItem's invoice_id)
            'id',                                           // Local key on ClientService table
            'invoice_id'                                    // Local key on InvoiceItem table
        )->where('invoice_items.item_type', 'renewal'); // Filter items to be of type 'renewal'
    }

    /**
     * Extends the service's next due date based on the provided billing cycle.
     *
     * @deprecated Use ClientServiceBusinessService::extendServiceRenewal() instead
     * @param BillingCycle $billingCycle The billing cycle used for renewal.
     * @return bool True on success, false on failure.
     */
    public function extendRenewal(BillingCycle $billingCycle): bool
    {
        $businessService = app(\App\Services\ClientServiceBusinessService::class);
        $result = $businessService->extendServiceRenewal($this, $billingCycle);

        return $result['success'] ?? false;
    }
}

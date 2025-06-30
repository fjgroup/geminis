<?php
namespace App\Models;

use App\Models\BillingCycle;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory; // ¡Añadir esta línea!
use Illuminate\Database\Eloquent\Model;                // ¡Añadir esta línea!
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
     * @param string $column
     * @return array
     */
    public static function getPossibleEnumValues(string $column): array
    {
        $instance       = new static;
        $tableName      = $instance->getTable();
        $connectionName = $instance->getConnectionName(); // Usa la conexión del modelo

        try {
            $columnDetails = DB::connection($connectionName)
                ->select("SHOW COLUMNS FROM `{$tableName}` WHERE Field = '{$column}'");

            if (empty($columnDetails) || ! isset($columnDetails[0]->Type)) {
                Log::error("EnumHelper: Column '{$column}' not found or 'Type' not set for table '{$tableName}'.");
                return [];
            }

            $type = $columnDetails[0]->Type;

            if (! preg_match('/^enum\((.*)\)$/', $type, $matches)) {
                Log::warning("EnumHelper: Column '{$column}' in table '{$tableName}' is not a standard ENUM type. Type found: {$type}");
                return [];
            }

            if (! isset($matches[1])) {
                Log::error("EnumHelper: Could not parse ENUM values for column '{$column}' in table '{$tableName}'. Type: {$type}");
                return [];
            }

            $enum = [];
            foreach (explode(',', $matches[1]) as $value) {
                $v      = trim($value, "'");
                $enum[] = ['value' => $v, 'label' => ucfirst(str_replace('_', ' ', $v))];
            }
            return $enum;

        } catch (\Exception $e) {
            Log::error("EnumHelper: Exception while fetching ENUM values for column '{$column}' in table '{$tableName}': " . $e->getMessage());
            return [];
        }
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
     * @param BillingCycle $billingCycle The billing cycle used for renewal.
     * @return bool True on success, false on failure.
     */
    public function extendRenewal(BillingCycle $billingCycle): bool
    {
        // Ensure next_due_date is a Carbon instance
        $currentDueDate = Carbon::parse($this->next_due_date);

        // Add the number of days from the billing cycle
        // The 'days' attribute in BillingCycle stores the duration of the cycle in days.
        $newDueDate = $currentDueDate->addDays($billingCycle->days);

        $this->next_due_date = $newDueDate;
        return $this->save();
    }
}

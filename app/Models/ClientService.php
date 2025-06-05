<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log; // ¡Añadir esta línea!
use Illuminate\Support\Facades\DB; // ¡Añadir esta línea!
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\OrderItem; // Added

class ClientService extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'reseller_id',
        'order_id',
        'order_item_id', // Added
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
        'registration_date' => 'date',
        'next_due_date' => 'date',
        'termination_date' => 'date',
        'password_encrypted' => 'encrypted', // Laravel encriptará/desencriptará automáticamente este campo
        'billing_amount' => 'decimal:2',
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

    /**
     * Get the order item associated with the service.
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }


   // TODO: Definir las siguientes relaciones cuando los modelos existan y estén listos:
    // public function order(): BelongsTo // Already have an order_id, so this could be direct or via orderItem->order
    // {
    //     // If order_id directly on client_services is primary link:
    //     // return $this->belongsTo(Order::class);
    //     // If through orderItem:
    //     // return $this->orderItem ? $this->orderItem->order() : null; // This is not how you define a relationship.
    //     // Best to keep a direct order_id if it's always present, or rely on orderItem->order.
    //     // For now, assuming order_id is directly on client_services and is the primary link.
    //     // If ClientService is ALWAYS created from an OrderItem, then order_id might be redundant if order_item_id is present.
    //     // But the Job populates order_id directly too.
    //     return $this->belongsTo(Order::class); // Assuming direct order_id link is maintained
    // }

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
        $instance = new static;
        $tableName = $instance->getTable();
        $connectionName = $instance->getConnectionName(); // Usa la conexión del modelo

        try {
            $columnDetails = DB::connection($connectionName)
                                ->select("SHOW COLUMNS FROM `{$tableName}` WHERE Field = '{$column}'");

            if (empty($columnDetails) || !isset($columnDetails[0]->Type)) {
                Log::error("EnumHelper: Column '{$column}' not found or 'Type' not set for table '{$tableName}'.");
                return [];
            }

            $type = $columnDetails[0]->Type;

            if (!preg_match('/^enum\((.*)\)$/', $type, $matches)) {
                Log::warning("EnumHelper: Column '{$column}' in table '{$tableName}' is not a standard ENUM type. Type found: {$type}");
                return [];
            }

            if (!isset($matches[1])) {
                Log::error("EnumHelper: Could not parse ENUM values for column '{$column}' in table '{$tableName}'. Type: {$type}");
                return [];
            }

            $enum = [];
            foreach (explode(',', $matches[1]) as $value) {
                $v = trim($value, "'");
                $enum[] = ['value' => $v, 'label' => ucfirst(str_replace('_', ' ', $v))];
            }
            return $enum;

        } catch (\Exception $e) {
            Log::error("EnumHelper: Exception while fetching ENUM values for column '{$column}' in table '{$tableName}': " . $e->getMessage());
            return [];
        }
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log; // ¡Añadir esta línea!
use Illuminate\Support\Facades\DB; // ¡Añadir esta línea!
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientService extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'reseller_id',
        'order_id',
        'product_id',
        'product_pricing_id',
        'domain_name',
        'username',
        'password_encrypted',
        'server_id',
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
     * Get the product pricing (billing cycle) associated with the service.
     */
    public function productPricing(): BelongsTo
    {
        return $this->belongsTo(ProductPricing::class);
    }

    // TODO: Definir las siguientes relaciones cuando los modelos existan y estén listos:
    // public function order(): BelongsTo
    // {
    //     return $this->belongsTo(Order::class);
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

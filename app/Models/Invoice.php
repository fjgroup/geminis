<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\ClientService;
use Illuminate\Support\Carbon; // Import Carbon
use Illuminate\Support\Facades\DB; // Import DB for potential raw queries if needed, or for transactions
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str; // Import Str if using Str::random or other Str helpers

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
     * Example: INV-YYYYMMDD-XXXX (XXXX is a 4-digit padded number)
     *
     * @return string
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . Carbon::now()->format('Ymd') . '-';
        $nextNumber = 1;

        // Lock la tabla o usar una transacción para evitar race conditions si la concurrencia es muy alta.
        // Para la mayoría de los casos, ordenar por ID desc podría ser suficiente si la creación es secuencial.
        // Una solución más robusta podría usar una secuencia de BD o una tabla dedicada para contadores.

        // Obtener la última factura creada hoy con el mismo prefijo para determinar el siguiente número secuencial.
        // Usar `orderBy('invoice_number', 'desc')` puede ser problemático si el padding no es consistente
        // o si hay caracteres no numéricos. Ordenar por ID es más seguro si son auto-incrementales y se crean en orden.
        $latestInvoiceToday = self::where('invoice_number', 'LIKE', $prefix . '%')
                                ->orderBy('id', 'desc')
                                ->first();

        if ($latestInvoiceToday) {
            // Extraer la parte numérica del último número de factura
            // Asume formato $prefixNNNN
            $lastNumberStr = substr($latestInvoiceToday->invoice_number, strlen($prefix));

            if (is_numeric($lastNumberStr)) {
                $nextNumber = (int)$lastNumberStr + 1;
            } else {
                // Esto podría ocurrir si el formato del número de factura cambia o hay datos corruptos.
                // Como fallback, podríamos contar cuántas facturas hay hoy con ese prefijo.
                // Sin embargo, esto no es a prueba de race conditions sin bloqueos.
                // Por simplicidad y asumiendo que el formato se mantiene con str_pad, este fallback es menos probable.
                // Considerar loguear una advertencia aquí.
                Log::warning('Formato de número de factura inesperado al generar nuevo número.', [
                    'last_invoice_number' => $latestInvoiceToday->invoice_number,
                    'extracted_part' => $lastNumberStr
                ]);
                // Fallback a un número basado en el conteo + 1 (menos preciso que la secuencia)
                $countTodayWithPrefix = self::where('invoice_number', 'LIKE', $prefix . '%')->count();
                $nextNumber = $countTodayWithPrefix + 1;
            }
        }
        // Si no hay facturas hoy con ese prefijo, $nextNumber permanece 1.

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
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
     * @return bool
     */
    public function isCancellableAsNewService(): bool
    {
        if ($this->status !== 'unpaid') {
            return false;
        }

        $this->loadMissing(['items', 'items.clientService']);

        foreach ($this->items as $item) {
            // Allow cancellation if item types are 'new_service' or 'web-hosting'
            if (!in_array($item->item_type, ['new_service', 'web-hosting'])) {
                return false;
            }

            if ($item->client_service_id !== null) {
                if ($item->clientService) {
                    $status = $item->clientService->status;
                    if ($status === 'active' || $status === 'suspended') {
                        return false;
                    }
                }
            }
        }

        return true;
    }
}

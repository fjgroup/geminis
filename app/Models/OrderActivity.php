<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderActivity extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order_activities';

    /**
     * Indicates if the model should be timestamped.
     * Only 'created_at' is used for this model.
     *
     * @var bool
     */
    public const UPDATED_AT = null; // No updated_at column

    protected $fillable = [
        'order_id',
        'user_id', // The user who performed the action (client or admin)
        'type',
        'details',
        // 'created_at' is typically handled by Laravel automatically
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'details' => 'array', // Casts JSON 'details' to PHP array and vice-versa
        'created_at' => 'datetime',
    ];

    /**
     * Get the order that this activity belongs to.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user who performed the action, if any.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class); // Assumes user_id is the foreign key
    }
}

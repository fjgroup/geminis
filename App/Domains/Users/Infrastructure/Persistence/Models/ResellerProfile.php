<?php

namespace App\Domains\Users\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent para ResellerProfile en arquitectura hexagonal
 *
 * Este es el modelo REAL que se conecta a la base de datos
 * Ubicado en Infrastructure layer como adaptador de persistencia
 */
class ResellerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'brand_name',
        'custom_domain',
        'logo_url',
        'support_email',
        'terms_url',
        'allow_custom_products',
    ];

    protected $casts = [
        'allow_custom_products' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

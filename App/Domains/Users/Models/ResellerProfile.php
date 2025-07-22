<?php

namespace App\Domains\Users\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

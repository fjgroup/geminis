<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'reseller_id',
        'company_name',
        'phone_number',
        'address_line1',
        'address_line2',
        'city',
        'state_province',
        'postal_code',
        'country',
        'status',
        'language_code',
        'currency_code',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the clients for the reseller.
     * (A user with role 'reseller' can have many client users)
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<User>
     */
    public function clients()
    {
        return $this->hasMany(User::class, 'reseller_id');
    }

    /**
     * Get the reseller that this client user belongsTo.
     * (A user with role 'client' can belong to one reseller user)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, User>
     */
    public function reseller()
    {
        return $this->belongsTo(User::class, 'reseller_id');
    }

    // Example for a future relationship (ResellerProfile)
    // public function resellerProfile()
    // {
    //     return $this->hasOne(ResellerProfile::class, 'user_id');
    // }
}

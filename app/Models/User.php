<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
            'balance' => 'decimal:2', // Added balance cast
        ];
    }

    /**
     * Get the invoices for the user.
     */
    public function invoices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Invoice::class, 'client_id');
    }

    /**
     * Get the user's balance formatted as currency.
     */
    public function getFormattedBalanceAttribute(): string
    {
        $balance = $this->attributes['balance'] ?? 0;
        // Use the user's specific currency_code if available, otherwise default to USD
        $currencyCode = $this->currency_code ?? 'USD'; 

        if (class_exists('NumberFormatter')) {
            $locale = config('app.locale', 'en_US'); // Use app's locale
            // Construct locale string specific for currency, e.g., en_US@currency=USD
            // This helps ensure the correct currency symbol and formatting for the given code.
            // However, NumberFormatter often infers well from just locale + currency code.
            $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
            return $formatter->formatCurrency($balance, $currencyCode);
        }
        
        // Fallback basic formatting if NumberFormatter is not available
        return $currencyCode . ' ' . number_format($balance, 2);
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            // Si el usuario tiene un perfil de revendedor, eliminarlo también.
            // Esto funcionará tanto para soft deletes como para hard deletes del User,
            // asumiendo que ResellerProfile no usa SoftDeletes.
            // Si ResellerProfile usara SoftDeletes, $user->resellerProfile()->delete() lo haría soft delete.
            if ($user->resellerProfile) {
                $user->resellerProfile->delete(); // Hard delete para ResellerProfile
            }

            // Opcional: Si un revendedor es eliminado, ¿qué pasa con sus clientes?
            // Podrías desasociarlos o reasignarlos. Ejemplo:
         if ($user->role === 'reseller') {
                 $user->clients()->update(['reseller_id' => null]); // Desasociar clientes
             }
        });
    }

    /**
     * Get the clients for the reseller.
     * (A user with role 'reseller' can have many client users)
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<User>
     */
        public function clients(): HasMany
    {
        return $this->hasMany(User::class, 'reseller_id');
    }

    /**
     * Get the reseller that this client user belongsTo.
     * (A user with role 'client' can belong to one reseller user)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, User>
     */
        public function reseller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reseller_id');
    }

    //Example for a future relationship (ResellerProfile)
        public function resellerProfile():HasOne
    {
        return $this->hasOne(ResellerProfile::class, 'user_id');
    }

    /**
     * Get the client services for the user.
     */
        public function clientServices(): HasMany
    {
        return $this->hasMany(ClientService::class, 'client_id');
    }
    /**
     * Get the orders for the user.
     */
        public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'client_id');
    }

    /**
     * Check if the user has a specific role.
     * @param string $roleName  @return bool
     */
        public function hasRole(string $roleName): bool
    {
        return $this->role === $roleName;
    }

    /**
     * Check if the user has the 'admin' role.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

}

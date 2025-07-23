<?php

namespace App\Domains\Users\Infrastructure\Persistence\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Domains\Users\Infrastructure\Persistence\Models\ResellerProfile;
use App\Domains\ClientServices\Infrastructure\Persistence\Models\ClientService;
use App\Domains\Invoices\Infrastructure\Persistence\Models\Invoice;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes; // HasResellerScope temporalmente deshabilitado

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
        'phone',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'company_logo',
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
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'formatted_balance',
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
            'last_login_at'     => 'datetime',
            'password'          => 'hashed',
            'balance'           => 'decimal:2', // Added balance cast
        ];
    }

    /**
     * Get the invoices for the user.
     */
    public function invoices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Domains\Invoices\Models\Invoice::class, 'client_id');
    }

    /**
     * Get the user's balance formatted as currency.
     *
     * @deprecated Use UserFormattingService::formatBalance() instead
     */
    public function getFormattedBalanceAttribute(): string
    {
        $formattingService = app(\App\Services\UserFormattingService::class);
        return $formattingService->formatBalance($this);
    }

    /**
     * The "booted" method of the model.
     *
     * @deprecated Complex deletion logic moved to UserDeletionService
     */
    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            // La lógica de eliminación compleja se maneja ahora en UserDeletionService
            // Este hook se mantiene para compatibilidad, pero se recomienda usar el servicio directamente
            $deletionService = app(\App\Services\UserDeletionService::class);

            // Solo ejecutar validaciones básicas aquí
            $canDelete = $deletionService->canUserBeDeleted($user);
            if (!$canDelete['can_delete']) {
                throw new \Exception($canDelete['reason']);
            }
        });
    }

    /**
     * Check if the user has a specific role.
     *
     * @deprecated Use UserRoleService::hasRole() instead
     * @param string $roleName
     * @return bool
     */
    public function hasRole(string $roleName): bool
    {
        $roleService = app(\App\Services\UserRoleService::class);
        return $roleService->hasRole($this, $roleName);
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
    public function resellerProfile(): HasOne
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
     * Check if the user has the 'admin' role.
     *
     * @deprecated Use UserRoleService::isAdmin() instead
     * @return bool
     */
    public function isAdmin(): bool
    {
        $roleService = app(\App\Services\UserRoleService::class);
        return $roleService->isAdmin($this);
    }

}

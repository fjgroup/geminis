<?php

namespace App\Traits;

use App\Scopes\ResellerScope;
use Illuminate\Database\Eloquent\Builder;

trait HasResellerScope
{
    /**
     * Boot the trait and add the global scope
     */
    protected static function bootHasResellerScope(): void
    {
        static::addGlobalScope(new ResellerScope);
    }

    /**
     * Scope to bypass reseller filtering (only for admins)
     */
    public function scopeWithoutResellerScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope(ResellerScope::class);
    }

    /**
     * Scope to filter by specific reseller
     */
    public function scopeForReseller(Builder $query, int $resellerId): Builder
    {
        return $query->withoutGlobalScope(ResellerScope::class)
                    ->where('reseller_id', $resellerId);
    }

    /**
     * Scope to get only platform products (for resellers)
     */
    public function scopePlatformProducts(Builder $query): Builder
    {
        return $query->withoutGlobalScope(ResellerScope::class)
                    ->whereNull('owner_id')
                    ->where('is_resellable_by_default', true);
    }

    /**
     * Scope to get only reseller's own products
     */
    public function scopeOwnProducts(Builder $query, int $resellerId): Builder
    {
        return $query->withoutGlobalScope(ResellerScope::class)
                    ->where('owner_id', $resellerId);
    }
}

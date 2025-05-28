<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization; // Added for consistency

class UserPolicy
{
    use HandlesAuthorization; // Added

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $actingUser): bool
    {
        return $actingUser->role === 'admin' || $actingUser->role === 'reseller';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $actingUser, User $targetUser): bool
    {
        if ($actingUser->role === 'admin') {
            return true;
        }
        if ($actingUser->role === 'reseller' && $targetUser->reseller_id === $actingUser->id) {
            return true; // Reseller can view their own client
        }
        // Optional: Allow user to view themselves, though typically ProfileController handles this.
        // if ($actingUser->id === $targetUser->id) {
        //     return true;
        // }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $actingUser): bool
    {
        return $actingUser->role === 'admin' || $actingUser->role === 'reseller';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $actingUser, User $targetUser): bool
    {
        if ($actingUser->role === 'admin') {
            // Optional: Admin self-edit restriction
            // if ($actingUser->id === $targetUser->id) { return false; /* or allow specific fields */ }
            return true;
        }
        if ($actingUser->role === 'reseller' && $targetUser->reseller_id === $actingUser->id) {
            return true; // Reseller can update their own client
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $actingUser, User $targetUser): bool
    {
        if ($actingUser->role === 'admin') {
            return !($actingUser->id === $targetUser->id); // Admin cannot delete self
        }
        if ($actingUser->role === 'reseller' && $targetUser->reseller_id === $actingUser->id) {
            // Optional: Prevent reseller from deleting a client if they have active services, etc.
            return true; // Reseller can delete their own client
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $actingUser, User $targetUser): bool // Parameter name changed for consistency
    {
        return $actingUser->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $actingUser, User $targetUser): bool // Parameter name changed for consistency
    {
        // Considera restringir esto aún más, ej. solo un superadmin
        return $actingUser->role === 'admin';
    }
}
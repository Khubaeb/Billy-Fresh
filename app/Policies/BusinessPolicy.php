<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BusinessPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view their businesses
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Business  $business
     * @return bool
     */
    public function view(User $user, Business $business): bool
    {
        // User can view if they're associated with the business
        return $user->businesses()->where('businesses.id', $business->id)->exists();
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create businesses
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Business  $business
     * @return bool
     */
    public function update(User $user, Business $business): bool
    {
        // User can update if they're associated with the business with an admin role (assumed to be role_id = 1)
        return $user->businesses()
            ->where('businesses.id', $business->id)
            ->wherePivot('role_id', 1)
            ->exists();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Business  $business
     * @return bool
     */
    public function delete(User $user, Business $business): bool
    {
        // Only admin users can delete a business
        return $user->businesses()
            ->where('businesses.id', $business->id)
            ->wherePivot('role_id', 1)
            ->exists();
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Business  $business
     * @return bool
     */
    public function restore(User $user, Business $business): bool
    {
        // Only admin users can restore a business
        return $user->businesses()
            ->where('businesses.id', $business->id)
            ->wherePivot('role_id', 1)
            ->exists();
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Business  $business
     * @return bool
     */
    public function forceDelete(User $user, Business $business): bool
    {
        // Only admin users can permanently delete a business
        return $user->businesses()
            ->where('businesses.id', $business->id)
            ->wherePivot('role_id', 1)
            ->exists();
    }

    /**
     * Determine whether the user can update the business settings.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Business  $business
     * @return bool
     */
    public function updateSettings(User $user, Business $business): bool
    {
        // Only admin users can update business settings
        return $user->businesses()
            ->where('businesses.id', $business->id)
            ->wherePivot('role_id', 1)
            ->exists();
    }

    /**
     * Determine whether the user can manage users for the business.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Business  $business
     * @return bool
     */
    public function manageUsers(User $user, Business $business): bool
    {
        // Only admin users can manage business users
        return $user->businesses()
            ->where('businesses.id', $business->id)
            ->wherePivot('role_id', 1)
            ->exists();
    }
}

<?php

namespace App\Policies;

use App\Models\TaxRate;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaxRatePolicy
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
        return true; // All authenticated users can view their tax rates
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TaxRate  $taxRate
     * @return bool
     */
    public function view(User $user, TaxRate $taxRate): bool
    {
        // User can view the tax rate if they're associated with the business
        return $user->businesses()->where('businesses.id', $taxRate->business_id)->exists();
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // Users need at least one business to create tax rates, but this check
        // is handled in the controller logic
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TaxRate  $taxRate
     * @return bool
     */
    public function update(User $user, TaxRate $taxRate): bool
    {
        // User can update if they're associated with the business as an admin (role_id 1)
        return $user->businesses()
            ->where('businesses.id', $taxRate->business_id)
            ->wherePivot('role_id', 1)
            ->exists();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TaxRate  $taxRate
     * @return bool
     */
    public function delete(User $user, TaxRate $taxRate): bool
    {
        // Only admin users can delete a tax rate
        return $user->businesses()
            ->where('businesses.id', $taxRate->business_id)
            ->wherePivot('role_id', 1)
            ->exists();
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TaxRate  $taxRate
     * @return bool
     */
    public function restore(User $user, TaxRate $taxRate): bool
    {
        // Only admin users can restore a tax rate
        return $user->businesses()
            ->where('businesses.id', $taxRate->business_id)
            ->wherePivot('role_id', 1)
            ->exists();
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TaxRate  $taxRate
     * @return bool
     */
    public function forceDelete(User $user, TaxRate $taxRate): bool
    {
        // Only admin users can permanently delete a tax rate
        return $user->businesses()
            ->where('businesses.id', $taxRate->business_id)
            ->wherePivot('role_id', 1)
            ->exists();
    }

    /**
     * Determine whether the user can set a tax rate as default.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TaxRate  $taxRate
     * @return bool
     */
    public function setDefault(User $user, TaxRate $taxRate): bool
    {
        // Only admin users can set a tax rate as default
        return $user->businesses()
            ->where('businesses.id', $taxRate->business_id)
            ->wherePivot('role_id', 1)
            ->exists();
    }
}

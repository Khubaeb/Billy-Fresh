<?php

namespace App\Policies;

use App\Models\RecurringBilling;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RecurringBillingPolicy
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
        return true; // All authenticated users can view their recurring billings
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RecurringBilling  $recurringBilling
     * @return bool
     */
    public function view(User $user, RecurringBilling $recurringBilling): bool
    {
        // User can view if they own it or if they own the business associated with it
        return $user->id === $recurringBilling->user_id || 
               ($recurringBilling->business_id && $recurringBilling->business->user_id === $user->id) ||
               ($user->businesses()->where('id', $recurringBilling->business_id)->exists());
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create recurring billings
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RecurringBilling  $recurringBilling
     * @return bool
     */
    public function update(User $user, RecurringBilling $recurringBilling): bool
    {
        // User can update if they own it or if they own the business associated with it
        return $user->id === $recurringBilling->user_id || 
               ($recurringBilling->business_id && $recurringBilling->business->user_id === $user->id) ||
               ($user->businesses()->where('id', $recurringBilling->business_id)->exists());
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RecurringBilling  $recurringBilling
     * @return bool
     */
    public function delete(User $user, RecurringBilling $recurringBilling): bool
    {
        // User can delete if they own it or if they own the business associated with it
        return $user->id === $recurringBilling->user_id || 
               ($recurringBilling->business_id && $recurringBilling->business->user_id === $user->id) ||
               ($user->businesses()->where('id', $recurringBilling->business_id)->exists());
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RecurringBilling  $recurringBilling
     * @return bool
     */
    public function restore(User $user, RecurringBilling $recurringBilling): bool
    {
        // User can restore if they own it or if they own the business associated with it
        return $user->id === $recurringBilling->user_id || 
               ($recurringBilling->business_id && $recurringBilling->business->user_id === $user->id) ||
               ($user->businesses()->where('id', $recurringBilling->business_id)->exists());
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RecurringBilling  $recurringBilling
     * @return bool
     */
    public function forceDelete(User $user, RecurringBilling $recurringBilling): bool
    {
        // User can force delete if they own it or if they own the business associated with it
        return $user->id === $recurringBilling->user_id || 
               ($recurringBilling->business_id && $recurringBilling->business->user_id === $user->id) ||
               ($user->businesses()->where('id', $recurringBilling->business_id)->exists());
    }

    /**
     * Determine whether the user can update the status of the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RecurringBilling  $recurringBilling
     * @return bool
     */
    public function updateStatus(User $user, RecurringBilling $recurringBilling): bool
    {
        // User can update status if they can update the recurring billing
        return $this->update($user, $recurringBilling);
    }

    /**
     * Determine whether the user can generate invoices from the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\RecurringBilling  $recurringBilling
     * @return bool
     */
    public function generateInvoice(User $user, RecurringBilling $recurringBilling): bool
    {
        // User can generate invoices if they can update the recurring billing
        return $this->update($user, $recurringBilling);
    }
}

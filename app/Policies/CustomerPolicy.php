<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CustomerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view the customer list
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Customer $customer): bool
    {
        // User can view if they own the customer or if they own the business associated with the customer
        return $user->id === $customer->user_id || 
               ($customer->business && $customer->business->user_id === $user->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create customers
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Customer $customer): bool
    {
        // User can update if they own the customer or if they own the business associated with the customer
        return $user->id === $customer->user_id || 
               ($customer->business && $customer->business->user_id === $user->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Customer $customer): bool
    {
        // User can delete if they own the customer or if they own the business associated with the customer
        return $user->id === $customer->user_id || 
               ($customer->business && $customer->business->user_id === $user->id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Customer $customer): bool 
    {
        // User can restore if they own the customer or if they own the business associated with the customer
        return $user->id === $customer->user_id || 
               ($customer->business && $customer->business->user_id === $user->id);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Customer $customer): bool
    {
        // Only the customer owner can permanently delete
        return $user->id === $customer->user_id;
    }
}

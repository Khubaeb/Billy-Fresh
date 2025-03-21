<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExpensePolicy
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
        return true; // All authenticated users can view their expenses
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Expense  $expense
     * @return bool
     */
    public function view(User $user, Expense $expense): bool
    {
        // User can view expense if they own it or if they own the business associated with it
        return $user->id === $expense->user_id || 
               ($expense->business_id && $expense->business->user_id === $user->id) ||
               ($user->businesses()->where('id', $expense->business_id)->exists());
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create expenses
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Expense  $expense
     * @return bool
     */
    public function update(User $user, Expense $expense): bool
    {
        // User can update expense if they own it or if they own the business associated with it
        return $user->id === $expense->user_id || 
               ($expense->business_id && $expense->business->user_id === $user->id) ||
               ($user->businesses()->where('id', $expense->business_id)->exists());
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Expense  $expense
     * @return bool
     */
    public function delete(User $user, Expense $expense): bool
    {
        // User can delete expense if they own it or if they own the business associated with it
        return $user->id === $expense->user_id || 
               ($expense->business_id && $expense->business->user_id === $user->id) ||
               ($user->businesses()->where('id', $expense->business_id)->exists());
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Expense  $expense
     * @return bool
     */
    public function restore(User $user, Expense $expense): bool
    {
        // User can restore expense if they own it or if they own the business associated with it
        return $user->id === $expense->user_id || 
               ($expense->business_id && $expense->business->user_id === $user->id) ||
               ($user->businesses()->where('id', $expense->business_id)->exists());
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Expense  $expense
     * @return bool
     */
    public function forceDelete(User $user, Expense $expense): bool
    {
        // User can force delete expense if they own it or if they own the business associated with it
        return $user->id === $expense->user_id || 
               ($expense->business_id && $expense->business->user_id === $user->id) ||
               ($user->businesses()->where('id', $expense->business_id)->exists());
    }
}

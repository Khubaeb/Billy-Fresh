<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
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
        return true; // All authenticated users can view their invoices
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Invoice  $invoice
     * @return bool
     */
    public function view(User $user, Invoice $invoice): bool
    {
        // User can view invoice if they own it or if they own the business associated with it
        return $user->id === $invoice->user_id || 
               ($invoice->business_id && $invoice->business->user_id === $user->id) ||
               ($user->businesses()->where('id', $invoice->business_id)->exists());
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create invoices
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Invoice  $invoice
     * @return bool
     */
    public function update(User $user, Invoice $invoice): bool
    {
        // User can update invoice if they own it or if they own the business associated with it
        return $user->id === $invoice->user_id || 
               ($invoice->business_id && $invoice->business->user_id === $user->id) ||
               ($user->businesses()->where('id', $invoice->business_id)->exists());
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Invoice  $invoice
     * @return bool
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        // User can delete invoice if they own it or if they own the business associated with it
        return $user->id === $invoice->user_id || 
               ($invoice->business_id && $invoice->business->user_id === $user->id) ||
               ($user->businesses()->where('id', $invoice->business_id)->exists());
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Invoice  $invoice
     * @return bool
     */
    public function restore(User $user, Invoice $invoice): bool
    {
        // User can restore invoice if they own it or if they own the business associated with it
        return $user->id === $invoice->user_id || 
               ($invoice->business_id && $invoice->business->user_id === $user->id) ||
               ($user->businesses()->where('id', $invoice->business_id)->exists());
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Invoice  $invoice
     * @return bool
     */
    public function forceDelete(User $user, Invoice $invoice): bool
    {
        // User can force delete invoice if they own it or if they own the business associated with it
        return $user->id === $invoice->user_id || 
               ($invoice->business_id && $invoice->business->user_id === $user->id) ||
               ($user->businesses()->where('id', $invoice->business_id)->exists());
    }

    /**
     * Determine whether the user can mark the invoice as sent.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Invoice  $invoice
     * @return bool
     */
    public function markAsSent(User $user, Invoice $invoice): bool
    {
        // User can mark as sent if they can update the invoice
        return $this->update($user, $invoice);
    }

    /**
     * Determine whether the user can record a payment for the invoice.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Invoice  $invoice
     * @return bool
     */
    public function recordPayment(User $user, Invoice $invoice): bool
    {
        // User can record payment if they can update the invoice
        return $this->update($user, $invoice);
    }
}

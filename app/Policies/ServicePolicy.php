<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ServicePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Any authenticated user can view a list of their own services
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Service $service): bool
    {
        // Users can only view services they own
        return $user->id === $service->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Any authenticated user can create a service
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Service $service): bool
    {
        // Users can only update services they own
        return $user->id === $service->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Service $service): bool
    {
        // Users can only delete services they own
        return $user->id === $service->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Service $service): bool
    {
        // Users can only restore services they own
        return $user->id === $service->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Service $service): bool
    {
        // Users can only force delete services they own
        return $user->id === $service->user_id;
    }
}

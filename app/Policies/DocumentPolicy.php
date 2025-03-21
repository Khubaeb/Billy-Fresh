<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DocumentPolicy
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
        return true; // All authenticated users can view their documents
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Document  $document
     * @return bool
     */
    public function view(User $user, Document $document): bool
    {
        // User can view the document if they're associated with the business
        return $user->businesses()->where('businesses.id', $document->business_id)->exists();
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // Users need at least one business to create documents, but this check
        // is handled in the controller logic
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Document  $document
     * @return bool
     */
    public function update(User $user, Document $document): bool
    {
        // User can update if they're associated with the business as an admin (role_id 1)
        return $user->businesses()
            ->where('businesses.id', $document->business_id)
            ->wherePivot('role_id', 1)
            ->exists();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Document  $document
     * @return bool
     */
    public function delete(User $user, Document $document): bool
    {
        // User can delete if they're associated with the business as an admin (role_id 1)
        return $user->businesses()
            ->where('businesses.id', $document->business_id)
            ->wherePivot('role_id', 1)
            ->exists();
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Document  $document
     * @return bool
     */
    public function restore(User $user, Document $document): bool
    {
        // User can restore if they're associated with the business as an admin (role_id 1)
        return $user->businesses()
            ->where('businesses.id', $document->business_id)
            ->wherePivot('role_id', 1)
            ->exists();
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Document  $document
     * @return bool
     */
    public function forceDelete(User $user, Document $document): bool
    {
        // User can permanently delete if they're associated with the business as an admin (role_id 1)
        return $user->businesses()
            ->where('businesses.id', $document->business_id)
            ->wherePivot('role_id', 1)
            ->exists();
    }

    /**
     * Determine whether the user can download the document.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Document  $document
     * @return bool
     */
    public function download(User $user, Document $document): bool
    {
        // User can download if they're associated with the business
        return $user->businesses()->where('businesses.id', $document->business_id)->exists();
    }

    /**
     * Determine whether the user can batch upload documents.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function batchUpload(User $user): bool
    {
        // Anyone with at least one business can batch upload
        return $user->businesses()->exists();
    }
}

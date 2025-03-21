<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserRole extends Pivot
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Get the user that owns the role.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the role that is owned.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the business that the role belongs to.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}

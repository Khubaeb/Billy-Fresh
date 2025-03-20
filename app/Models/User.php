<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the businesses that the user belongs to.
     */
    public function businesses()
    {
        return $this->belongsToMany(Business::class, 'user_roles')
            ->withPivot('role_id')
            ->using(UserRole::class);
    }

    /**
     * Get the roles that the user has.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot('business_id')
            ->using(UserRole::class);
    }

    /**
     * Check if the user has a specific role.
     *
     * @param string $role
     * @param int|null $businessId
     * @return bool
     */
    public function hasRole($role, $businessId = null)
    {
        $query = $this->roles()->where('name', $role);
        
        if ($businessId) {
            $query->wherePivot('business_id', $businessId);
        }
        
        return $query->exists();
    }

    /**
     * Get all user roles.
     */
    public function userRoles()
    {
        return $this->hasMany(UserRole::class);
    }
}

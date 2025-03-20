<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'business_id',
        'name',
        'email',
        'phone',
        'company',
        'tax_number',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country',
        'notes',
        'website',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the customer.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the business that owns the customer.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the full address as a string.
     */
    public function getFullAddressAttribute(): string
    {
        $address = [];
        
        if (!empty($this->address_line1)) {
            $address[] = $this->address_line1;
        }
        
        if (!empty($this->address_line2)) {
            $address[] = $this->address_line2;
        }
        
        $cityState = [];
        if (!empty($this->city)) {
            $cityState[] = $this->city;
        }
        
        if (!empty($this->state)) {
            $cityState[] = $this->state;
        }
        
        if (!empty($cityState)) {
            $address[] = implode(', ', $cityState);
        }
        
        if (!empty($this->postal_code)) {
            $address[] = $this->postal_code;
        }
        
        if (!empty($this->country)) {
            $address[] = $this->country;
        }
        
        return implode(', ', $address);
    }
}

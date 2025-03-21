<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'business_id',
        'customer_id',
        'type',
        'card_last_four',
        'expiry_date',
        'holder_name',
        'is_default',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expiry_date' => 'date',
        'is_default' => 'boolean',
    ];

    /**
     * Get the business that owns the payment method.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the customer that owns the payment method.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the invoices that use this payment method.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the recurring billings that use this payment method.
     */
    public function recurringBillings()
    {
        return $this->hasMany(RecurringBilling::class);
    }

    /**
     * Get a masked version of the card number.
     *
     * @return string
     */
    public function getMaskedCardNumberAttribute()
    {
        if (!$this->card_last_four) {
            return '';
        }

        return '•••• •••• •••• ' . $this->card_last_four;
    }

    /**
     * Format the expiry date as MM/YY.
     *
     * @return string|null
     */
    public function getFormattedExpiryDateAttribute()
    {
        if (!$this->expiry_date) {
            return null;
        }

        return $this->expiry_date->format('m/y');
    }

    /**
     * Scope a query to only include payment methods for a specific customer.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $customerId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope a query to only include default payment methods.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope a query to only include payment methods of a specific type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'full_name',
        'email',
        'phone',
        'identification_number',
        'company_name',
        'address',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country',
        'notes',
        'website',
        'status',
        'next_contact_date',
        'category',
        'tax_number',
        'is_active'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'next_contact_date' => 'date'
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
     * Get the invoices for the customer.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the payment methods for the customer.
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /**
     * Get the recurring billings for the customer.
     */
    public function recurringBillings(): HasMany
    {
        return $this->hasMany(RecurringBilling::class);
    }

    /**
     * Get the expenses related to the customer.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get the documents associated with this customer.
     */
    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Get name attribute.
     * This is for backward compatibility with existing views that use 'name'.
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->full_name;
    }

    /**
     * Set name attribute.
     * This is for backward compatibility with existing forms that use 'name'.
     *
     * @param string $value
     * @return void
     */
    public function setNameAttribute($value)
    {
        $this->attributes['full_name'] = $value;
    }

    /**
     * Get company attribute.
     * This is for backward compatibility with existing views that use 'company'.
     *
     * @return string|null
     */
    public function getCompanyAttribute()
    {
        return $this->company_name;
    }

    /**
     * Set company attribute.
     * This is for backward compatibility with existing forms that use 'company'.
     *
     * @param string $value
     * @return void
     */
    public function setCompanyAttribute($value)
    {
        $this->attributes['company_name'] = $value;
    }

    /**
     * Scope a query to only include active customers.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include customers for a specific business.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $businessId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    /**
     * Get the total amount of unpaid invoices for this customer.
     *
     * @return float
     */
    public function getTotalUnpaidAmount()
    {
        return $this->invoices()
            ->whereIn('status', ['draft', 'sent', 'overdue'])
            ->sum('amount_due');
    }
}

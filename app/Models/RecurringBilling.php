<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class RecurringBilling extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'customer_id',
        'business_id',
        'service_id',
        'payment_method_id',
        'name',
        'description',
        'amount',
        'currency',
        'frequency',
        'interval',
        'start_date',
        'end_date',
        'next_billing_date',
        'last_billed_date',
        'status',
        'billing_count',
        'is_active',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'interval' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'next_billing_date' => 'date',
        'last_billed_date' => 'date',
        'billing_count' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that created the recurring billing.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the customer for the recurring billing.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the business that owns the recurring billing.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the service associated with the recurring billing.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the payment method used for the recurring billing.
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get the invoices generated from this recurring billing.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'recurring_billing_id');
    }

    /**
     * Calculate the next billing date based on frequency and interval.
     *
     * @param \Carbon\Carbon|null $fromDate
     * @return \Carbon\Carbon
     */
    public function calculateNextBillingDate(Carbon $fromDate = null)
    {
        $fromDate = $fromDate ?? ($this->last_billed_date ?? $this->start_date);
        
        switch ($this->frequency) {
            case 'daily':
                return $fromDate->copy()->addDays($this->interval);
            case 'weekly':
                return $fromDate->copy()->addWeeks($this->interval);
            case 'monthly':
                return $fromDate->copy()->addMonths($this->interval);
            case 'quarterly':
                return $fromDate->copy()->addMonths($this->interval * 3);
            case 'yearly':
                return $fromDate->copy()->addYears($this->interval);
            default:
                return $fromDate->copy()->addMonths($this->interval);
        }
    }

    /**
     * Update the next billing date.
     *
     * @return $this
     */
    public function updateNextBillingDate()
    {
        $this->next_billing_date = $this->calculateNextBillingDate();
        $this->save();
        
        return $this;
    }

    /**
     * Record a successful billing.
     *
     * @param \App\Models\Invoice $invoice
     * @return $this
     */
    public function recordBilling(Invoice $invoice = null)
    {
        $this->last_billed_date = now();
        $this->billing_count++;
        $this->updateNextBillingDate();
        
        // If an end date is set and we've passed it, mark as completed
        if ($this->end_date && $this->next_billing_date->isAfter($this->end_date)) {
            $this->status = 'completed';
        }
        
        $this->save();
        
        return $this;
    }

    /**
     * Pause the recurring billing.
     *
     * @return $this
     */
    public function pause()
    {
        $this->status = 'paused';
        $this->save();
        
        return $this;
    }

    /**
     * Resume the recurring billing.
     *
     * @return $this
     */
    public function resume()
    {
        $this->status = 'active';
        $this->save();
        
        return $this;
    }

    /**
     * Cancel the recurring billing.
     *
     * @return $this
     */
    public function cancel()
    {
        $this->status = 'cancelled';
        $this->save();
        
        return $this;
    }

    /**
     * Check if the recurring billing is currently active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->status === 'active' && $this->is_active;
    }

    /**
     * Scope a query to only include active recurring billings.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('is_active', true);
    }

    /**
     * Scope a query to only include recurring billings for a specific business.
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
     * Scope a query to only include recurring billings for a specific customer.
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
     * Scope a query to only include recurring billings with a specific status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include recurring billings due for processing.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Carbon\Carbon|null $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDue($query, Carbon $date = null)
    {
        $date = $date ?? now();
        return $query->active()
                    ->where('next_billing_date', '<=', $date);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Invoice extends Model
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
        'payment_method_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'reference',
        'notes',
        'terms',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'amount_paid',
        'amount_due',
        'status',
        'currency',
        'tax_type',
        'tax_rate',
        'discount_type',
        'discount_rate',
        'document_path',
        'sent_at',
        'paid_at',
        'is_recurring'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'amount_due' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
        'is_recurring' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'is_overdue',
        'payment_status',
    ];

    /**
     * Get the user that created the invoice.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the customer associated with the invoice.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the business associated with the invoice.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the payment method associated with the invoice.
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get the recurring billing associated with the invoice.
     */
    public function recurringBilling(): BelongsTo
    {
        return $this->belongsTo(RecurringBilling::class);
    }

    /**
     * Get the items for the invoice.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get the payments for the invoice.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }

    /**
     * Get the expenses billed to this invoice.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get the documents associated with this invoice.
     */
    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Determine if the invoice is overdue.
     *
     * @return bool
     */
    public function getIsOverdueAttribute(): bool
    {
        if ($this->status === 'paid') {
            return false;
        }

        return $this->due_date && Carbon::parse($this->due_date)->isPast();
    }

    /**
     * Get the payment status label.
     *
     * @return string
     */
    public function getPaymentStatusAttribute(): string
    {
        if ($this->amount_due <= 0) {
            return 'Paid';
        }

        if ($this->amount_paid > 0) {
            return 'Partially Paid';
        }

        if ($this->is_overdue) {
            return 'Overdue';
        }

        if ($this->status === 'sent') {
            return 'Sent';
        }

        return 'Draft';
    }

    /**
     * Scope a query to only include invoices for a specific business.
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
     * Scope a query to only include invoices for a specific customer.
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
     * Scope a query to only include invoices with a specific status.
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
     * Scope a query to include only overdue invoices.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'paid')
            ->where('due_date', '<', now())
            ->where('amount_due', '>', 0);
    }

    /**
     * Calculate total amount for the invoice based on items.
     *
     * @return void
     */
    public function calculateTotals()
    {
        $subtotal = $this->items->sum('subtotal');
        $taxAmount = $this->items->sum('tax_amount');
        $discountAmount = $this->items->sum('discount_amount');
        
        $totalAmount = $subtotal + $taxAmount - $discountAmount;
        
        $this->subtotal = $subtotal;
        $this->tax_amount = $taxAmount;
        $this->discount_amount = $discountAmount;
        $this->total_amount = $totalAmount;
        $this->amount_due = $totalAmount - $this->amount_paid;
        
        return $this;
    }

    /**
     * Record a payment for this invoice.
     *
     * @param float $amount
     * @param string $paymentMethod
     * @param array $paymentData
     * @return InvoicePayment
     */
    public function recordPayment($amount, $paymentMethod, $paymentData = [])
    {
        $payment = new InvoicePayment([
            'invoice_id' => $this->id,
            'user_id' => $paymentData['user_id'] ?? auth()->id(),
            'payment_date' => $paymentData['payment_date'] ?? now(),
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'reference' => $paymentData['reference'] ?? null,
            'notes' => $paymentData['notes'] ?? null,
        ]);
        
        $payment->save();
        
        // Update invoice paid amount
        $this->amount_paid += $amount;
        $this->amount_due = $this->total_amount - $this->amount_paid;
        
        // Mark as paid if fully paid
        if ($this->amount_due <= 0) {
            $this->status = 'paid';
            $this->paid_at = now();
        } else if ($this->amount_paid > 0) {
            $this->status = 'partially_paid';
        }
        
        $this->save();
        
        return $payment;
    }
}

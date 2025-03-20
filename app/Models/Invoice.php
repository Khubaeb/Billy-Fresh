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
        'invoice_number',
        'invoice_date',
        'due_date',
        'reference',
        'notes',
        'terms',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total',
        'amount_paid',
        'amount_due',
        'status',
        'currency',
        'tax_type',
        'tax_rate',
        'discount_type',
        'discount_rate',
        'sent_at',
        'paid_at',
        'is_recurring',
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
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'amount_due' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
        'is_recurring' => 'boolean',
    ];

    /**
     * Get the user that owns the invoice.
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
     * Get the expenses associated with the invoice.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Determine if the invoice is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid' || $this->amount_due <= 0;
    }

    /**
     * Determine if the invoice is partially paid.
     */
    public function isPartiallyPaid(): bool
    {
        return $this->amount_paid > 0 && $this->amount_due > 0;
    }

    /**
     * Determine if the invoice is overdue.
     */
    public function isOverdue(): bool
    {
        return Carbon::now()->gt($this->due_date) && $this->amount_due > 0;
    }

    /**
     * Scope a query to only include invoices with a specific status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include overdue invoices.
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', Carbon::now())
            ->whereRaw('amount_due > 0')
            ->whereNotIn('status', ['paid', 'cancelled']);
    }

    /**
     * Scope a query to only include invoices due within the next X days.
     */
    public function scopeDueSoon($query, int $days = 7)
    {
        $today = Carbon::now();
        return $query->whereBetween('due_date', [$today, $today->copy()->addDays($days)])
            ->whereRaw('amount_due > 0')
            ->whereNotIn('status', ['paid', 'cancelled']);
    }
}

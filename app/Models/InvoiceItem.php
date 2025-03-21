<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'invoice_id',
        'service_id',
        'name',
        'description',
        'quantity',
        'unit_price',
        'tax_rate',
        'discount_rate',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2'
    ];

    /**
     * Get the invoice that owns the item.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the service associated with the item.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the total attribute.
     * This is for backward compatibility with existing views that use 'total'.
     *
     * @return float
     */
    public function getTotalAttribute()
    {
        return $this->total_amount;
    }

    /**
     * Set total attribute.
     * This is for backward compatibility with existing forms that use 'total'.
     *
     * @param float $value
     * @return void
     */
    public function setTotalAttribute($value)
    {
        $this->attributes['total_amount'] = $value;
    }

    /**
     * Calculate the subtotal, tax amount, discount amount, and total amount.
     *
     * @return $this
     */
    public function calculateAmounts()
    {
        // Calculate subtotal
        $this->subtotal = $this->quantity * $this->unit_price;

        // Calculate tax amount
        if ($this->tax_rate > 0) {
            $this->tax_amount = $this->subtotal * ($this->tax_rate / 100);
        } else {
            $this->tax_amount = 0;
        }

        // Calculate discount amount
        if ($this->discount_rate > 0) {
            $this->discount_amount = $this->subtotal * ($this->discount_rate / 100);
        } else {
            $this->discount_amount = 0;
        }

        // Calculate total
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount;

        return $this;
    }
}

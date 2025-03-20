<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
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
        'description',
        'sku',
        'price',
        'cost',
        'tax_rate',
        'unit',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the service.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the business associated with the service.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the invoice items for this service.
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Calculate the profit margin as a percentage.
     *
     * @return float|null
     */
    public function getMarginAttribute(): ?float
    {
        if (!$this->cost || !$this->price || $this->price <= 0) {
            return null;
        }

        return (($this->price - $this->cost) / $this->price) * 100;
    }

    /**
     * Calculate the profit amount.
     *
     * @return float|null
     */
    public function getProfitAttribute(): ?float
    {
        if ($this->cost === null) {
            return null;
        }

        return $this->price - $this->cost;
    }

    /**
     * Get the formatted price.
     *
     * @return string
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2);
    }

    /**
     * Scope a query to only include active services.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive services.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
}

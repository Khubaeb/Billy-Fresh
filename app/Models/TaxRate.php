<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'business_id',
        'name',
        'percentage',
        'is_default',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'percentage' => 'decimal:2',
        'is_default' => 'boolean',
    ];

    /**
     * Get the business that owns the tax rate.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the services that use this tax rate.
     */
    public function services()
    {
        return $this->hasMany(Service::class, 'tax_rate', 'percentage');
    }

    /**
     * Get the invoices that use this tax rate.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'tax_rate', 'percentage');
    }

    /**
     * Get the formatted percentage (with % symbol).
     *
     * @return string
     */
    public function getFormattedPercentageAttribute()
    {
        return number_format($this->percentage, 2) . '%';
    }

    /**
     * Calculate the tax amount for a given subtotal.
     *
     * @param float $subtotal
     * @return float
     */
    public function calculateTaxAmount($subtotal)
    {
        return $subtotal * ($this->percentage / 100);
    }

    /**
     * Scope a query to only include default tax rates.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope a query to only include tax rates for a specific business.
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
     * Scope a query to only include tax rates with a percentage in a specific range.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param float $min
     * @param float $max
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePercentageBetween($query, $min, $max)
    {
        return $query->whereBetween('percentage', [$min, $max]);
    }
}

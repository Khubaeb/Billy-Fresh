<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    use HasFactory;

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
        'color',
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
     * Get the business that owns the expense category.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the user that created the expense category.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the expenses for the category.
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class, 'category_id');
    }

    /**
     * Get the total expenses for this category within a date range.
     *
     * @param string $startDate
     * @param string $endDate
     * @return float
     */
    public function getTotalExpenses($startDate = null, $endDate = null)
    {
        $query = $this->expenses();

        if ($startDate && $endDate) {
            $query->whereBetween('expense_date', [$startDate, $endDate]);
        } else if ($startDate) {
            $query->where('expense_date', '>=', $startDate);
        } else if ($endDate) {
            $query->where('expense_date', '<=', $endDate);
        }

        return $query->sum('amount');
    }

    /**
     * Scope a query to only include active categories.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include expense categories for a specific business.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $businessId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'business_id',
        'parent_id',
        'account_type',
        'account_number',
        'name',
        'description',
        'balance',
        'is_active',
        'is_system',
        'display_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    /**
     * Account types constants
     */
    const TYPE_ASSET = 'asset';
    const TYPE_LIABILITY = 'liability';
    const TYPE_EQUITY = 'equity';
    const TYPE_INCOME = 'income';
    const TYPE_EXPENSE = 'expense';

    /**
     * Get the business that owns the account.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the parent account.
     */
    public function parent()
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    /**
     * Get the child accounts.
     */
    public function children()
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    /**
     * Get all ledger entries for this account.
     */
    public function ledgerEntries()
    {
        return $this->hasMany(LedgerEntry::class);
    }

    /**
     * Get the bank account associated with this account.
     */
    public function bankAccount()
    {
        return $this->hasOne(BankAccount::class);
    }

    /**
     * Scope a query to only include accounts of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('account_type', $type);
    }

    /**
     * Scope a query to only include active accounts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include top-level accounts.
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Check if this account is a bank account.
     */
    public function isBankAccount()
    {
        return $this->bankAccount()->exists();
    }

    /**
     * Check if this account has children.
     */
    public function hasChildren()
    {
        return $this->children()->count() > 0;
    }

    /**
     * Calculate the current balance of this account.
     */
    public function calculateBalance()
    {
        $debits = $this->ledgerEntries()->sum('debit_amount');
        $credits = $this->ledgerEntries()->sum('credit_amount');

        switch ($this->account_type) {
            case self::TYPE_ASSET:
            case self::TYPE_EXPENSE:
                // Debit increases, credit decreases
                $balance = $debits - $credits;
                break;
            case self::TYPE_LIABILITY:
            case self::TYPE_EQUITY:
            case self::TYPE_INCOME:
                // Credit increases, debit decreases
                $balance = $credits - $debits;
                break;
            default:
                $balance = 0;
        }

        return $balance;
    }

    /**
     * Update the balance of this account.
     */
    public function updateBalance()
    {
        $this->balance = $this->calculateBalance();
        $this->save();

        return $this;
    }

    /**
     * Get account types for dropdown.
     */
    public static function getAccountTypes()
    {
        return [
            self::TYPE_ASSET => __('Asset'),
            self::TYPE_LIABILITY => __('Liability'),
            self::TYPE_EQUITY => __('Equity'),
            self::TYPE_INCOME => __('Income'),
            self::TYPE_EXPENSE => __('Expense'),
        ];
    }
}

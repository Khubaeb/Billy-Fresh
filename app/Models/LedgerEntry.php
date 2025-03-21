<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LedgerEntry extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'debit_amount',
        'credit_amount',
        'description',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
        'metadata' => 'json',
    ];

    /**
     * Get the journal entry that the ledger entry belongs to.
     */
    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    /**
     * Get the account that the ledger entry belongs to.
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Scope a query to only include debit entries.
     */
    public function scopeDebits($query)
    {
        return $query->where('debit_amount', '>', 0);
    }

    /**
     * Scope a query to only include credit entries.
     */
    public function scopeCredits($query)
    {
        return $query->where('credit_amount', '>', 0);
    }

    /**
     * Scope a query to only include entries for a specific account.
     */
    public function scopeForAccount($query, $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    /**
     * Check if this is a debit entry.
     */
    public function isDebit()
    {
        return $this->debit_amount > 0;
    }

    /**
     * Check if this is a credit entry.
     */
    public function isCredit()
    {
        return $this->credit_amount > 0;
    }

    /**
     * Get the amount of this entry (regardless of whether it's a debit or credit).
     */
    public function getAmount()
    {
        return $this->isDebit() ? $this->debit_amount : $this->credit_amount;
    }

    /**
     * Create a debit entry.
     */
    public static function createDebit($journalEntryId, $accountId, $amount, $description = null, $metadata = null)
    {
        return self::create([
            'journal_entry_id' => $journalEntryId,
            'account_id' => $accountId,
            'debit_amount' => $amount,
            'credit_amount' => 0,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Create a credit entry.
     */
    public static function createCredit($journalEntryId, $accountId, $amount, $description = null, $metadata = null)
    {
        return self::create([
            'journal_entry_id' => $journalEntryId,
            'account_id' => $accountId,
            'debit_amount' => 0,
            'credit_amount' => $amount,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}

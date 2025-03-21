<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'business_id',
        'user_id',
        'entry_date',
        'reference',
        'description',
        'status',
        'is_recurring',
        'recurrence_pattern',
        'document_id',
        'approved_by',
        'approved_at',
        'entry_type',
        'source_type',
        'source_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'entry_date' => 'date',
        'is_recurring' => 'boolean',
        'recurrence_pattern' => 'json',
        'approved_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_APPROVED = 'approved';
    const STATUS_POSTED = 'posted';
    const STATUS_REJECTED = 'rejected';

    /**
     * Entry type constants
     */
    const TYPE_MANUAL = 'manual';
    const TYPE_SYSTEM = 'system';
    const TYPE_RECURRING = 'recurring';
    const TYPE_IMPORTED = 'imported';

    /**
     * Get the business that owns the journal entry.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the user that created the journal entry.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user that approved the journal entry.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the document associated with the journal entry.
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the ledger entries for this journal entry.
     */
    public function ledgerEntries()
    {
        return $this->hasMany(LedgerEntry::class);
    }

    /**
     * Get the bank transaction associated with this journal entry.
     */
    public function bankTransaction()
    {
        return $this->hasOne(BankTransaction::class);
    }

    /**
     * Get the source of this journal entry (polymorphic relationship).
     */
    public function source()
    {
        $sourceType = $this->source_type;
        $sourceId = $this->source_id;
        
        if (!$sourceType || !$sourceId) {
            return null;
        }
        
        // Map source_type string to model class name
        $modelMap = [
            'invoice' => Invoice::class,
            'expense' => Expense::class,
            'payment' => InvoicePayment::class,
            'bank_transaction' => BankTransaction::class,
        ];
        
        if (!isset($modelMap[$sourceType])) {
            return null;
        }
        
        $modelClass = $modelMap[$sourceType];
        return $modelClass::find($sourceId);
    }

    /**
     * Scope a query to only include entries of a specific status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include entries of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('entry_type', $type);
    }

    /**
     * Scope a query to only include entries for a specific date range.
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('entry_date', [$startDate, $endDate]);
    }

    /**
     * Check if this journal entry is balanced.
     */
    public function isBalanced()
    {
        $totalDebits = $this->ledgerEntries()->sum('debit_amount');
        $totalCredits = $this->ledgerEntries()->sum('credit_amount');
        
        // Using bccomp to compare decimal values with precision
        return bccomp($totalDebits, $totalCredits, 2) === 0;
    }

    /**
     * Calculate the total amount of the journal entry.
     */
    public function getTotalAmount()
    {
        // We can use either debits or credits as they should be equal
        return $this->ledgerEntries()->sum('debit_amount');
    }

    /**
     * Mark the journal entry as approved.
     */
    public function approve($userId)
    {
        $this->status = self::STATUS_APPROVED;
        $this->approved_by = $userId;
        $this->approved_at = now();
        $this->save();
        
        return $this;
    }

    /**
     * Mark the journal entry as posted.
     */
    public function post()
    {
        if ($this->status !== self::STATUS_APPROVED && $this->status !== self::STATUS_DRAFT) {
            return false;
        }
        
        if (!$this->isBalanced()) {
            return false;
        }
        
        $this->status = self::STATUS_POSTED;
        $this->save();
        
        // Update account balances
        foreach ($this->ledgerEntries as $entry) {
            $entry->account->updateBalance();
        }
        
        return true;
    }

    /**
     * Get status list for dropdown.
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_DRAFT => __('Draft'),
            self::STATUS_APPROVED => __('Approved'),
            self::STATUS_POSTED => __('Posted'),
            self::STATUS_REJECTED => __('Rejected'),
        ];
    }

    /**
     * Get entry type list for dropdown.
     */
    public static function getEntryTypeList()
    {
        return [
            self::TYPE_MANUAL => __('Manual'),
            self::TYPE_SYSTEM => __('System'),
            self::TYPE_RECURRING => __('Recurring'),
            self::TYPE_IMPORTED => __('Imported'),
        ];
    }
}

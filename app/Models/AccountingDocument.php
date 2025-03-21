<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingDocument extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'business_id',
        'user_id',
        'document_type',
        'name',
        'file_path',
        'document_date',
        'reference_number',
        'source_type',
        'source_id',
        'amount',
        'is_expense',
        'category',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'document_date' => 'date',
        'amount' => 'decimal:2',
        'is_expense' => 'boolean',
    ];

    /**
     * Document type constants
     */
    const TYPE_INVOICE = 'invoice';
    const TYPE_RECEIPT = 'receipt';
    const TYPE_BANK_STATEMENT = 'bank_statement';
    const TYPE_TAX_DOCUMENT = 'tax_document';
    const TYPE_EXPENSE = 'expense';
    const TYPE_PAYMENT = 'payment';
    const TYPE_OTHER = 'other';

    /**
     * Get the business that owns this document.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the user that uploaded this document.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the source entity.
     */
    public function source()
    {
        if (!$this->source_type || !$this->source_id) {
            return null;
        }
        
        $modelMap = [
            'invoice' => Invoice::class,
            'expense' => Expense::class,
            'payment' => InvoicePayment::class,
        ];
        
        if (!isset($modelMap[$this->source_type])) {
            return null;
        }
        
        $modelClass = $modelMap[$this->source_type];
        return $modelClass::find($this->source_id);
    }

    /**
     * Scope a query to only include documents of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('document_type', $type);
    }

    /**
     * Scope a query to only include documents for a specific date range.
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('document_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include expense documents.
     */
    public function scopeExpenses($query)
    {
        return $query->where('is_expense', true);
    }

    /**
     * Scope a query to only include revenue documents.
     */
    public function scopeRevenue($query)
    {
        return $query->where('is_expense', false);
    }

    /**
     * Scope a query to only include documents of a specific category.
     */
    public function scopeInCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get the file URL.
     */
    public function getFileUrl()
    {
        return url('storage/' . $this->file_path);
    }

    /**
     * Create a document from an invoice.
     */
    public static function createFromInvoice(Invoice $invoice, $filePath)
    {
        return self::create([
            'business_id' => $invoice->business_id,
            'user_id' => $invoice->user_id,
            'document_type' => self::TYPE_INVOICE,
            'name' => 'Invoice #' . $invoice->invoice_number,
            'file_path' => $filePath,
            'document_date' => $invoice->invoice_date,
            'reference_number' => $invoice->invoice_number,
            'source_type' => 'invoice',
            'source_id' => $invoice->id,
            'amount' => $invoice->total_amount,
            'is_expense' => false,
            'category' => 'sales',
        ]);
    }

    /**
     * Create a document from an expense.
     */
    public static function createFromExpense(Expense $expense, $filePath)
    {
        return self::create([
            'business_id' => $expense->business_id,
            'user_id' => $expense->user_id,
            'document_type' => self::TYPE_EXPENSE,
            'name' => 'Expense - ' . $expense->description,
            'file_path' => $filePath,
            'document_date' => $expense->expense_date,
            'reference_number' => $expense->reference,
            'source_type' => 'expense',
            'source_id' => $expense->id,
            'amount' => $expense->amount,
            'is_expense' => true,
            'category' => $expense->category_id ? ExpenseCategory::find($expense->category_id)->name : null,
        ]);
    }

    /**
     * Create a document from a payment.
     */
    public static function createFromPayment(InvoicePayment $payment, $filePath)
    {
        $invoice = $payment->invoice;
        
        return self::create([
            'business_id' => $invoice->business_id,
            'user_id' => $payment->user_id,
            'document_type' => self::TYPE_PAYMENT,
            'name' => 'Payment - Invoice #' . $invoice->invoice_number,
            'file_path' => $filePath,
            'document_date' => $payment->payment_date,
            'reference_number' => $payment->reference,
            'source_type' => 'payment',
            'source_id' => $payment->id,
            'amount' => $payment->amount,
            'is_expense' => false,
            'category' => 'payment',
        ]);
    }

    /**
     * Get document types list for dropdown.
     */
    public static function getDocumentTypes()
    {
        return [
            self::TYPE_INVOICE => 'Invoice',
            self::TYPE_RECEIPT => 'Receipt',
            self::TYPE_BANK_STATEMENT => 'Bank Statement',
            self::TYPE_TAX_DOCUMENT => 'Tax Document',
            self::TYPE_EXPENSE => 'Expense',
            self::TYPE_PAYMENT => 'Payment',
            self::TYPE_OTHER => 'Other',
        ];
    }
}

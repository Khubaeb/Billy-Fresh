<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingExport extends Model
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
        'export_type',
        'start_date',
        'end_date',
        'period_type',
        'format',
        'status',
        'file_path',
        'parameters',
        'download_token',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'parameters' => 'json',
        'completed_at' => 'datetime',
    ];

    /**
     * Export type constants
     */
    const TYPE_INCOME_STATEMENT = 'income_statement';
    const TYPE_ACCOUNT_CARD = 'account_card';
    const TYPE_VAT_PAYMENTS = 'vat_payments';
    const TYPE_PROFIT_LOSS = 'profit_loss';
    const TYPE_ADVANCED_PAYMENTS = 'advanced_payments';
    const TYPE_CENTRALIZED_CARD = 'centralized_card';

    /**
     * Period type constants
     */
    const PERIOD_MONTH = 'month';
    const PERIOD_BIMONTHLY = 'bimonthly';
    const PERIOD_QUARTER = 'quarter';
    const PERIOD_YEAR = 'year';
    const PERIOD_CUSTOM = 'custom';

    /**
     * Format constants
     */
    const FORMAT_PDF = 'pdf';
    const FORMAT_CSV = 'csv';
    const FORMAT_EXCEL = 'excel';
    const FORMAT_UNIFORM = 'uniform'; // Uniform structure for accounting software

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    /**
     * Get the business that owns this export.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the user that created this export.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the template used for this export, if any.
     */
    public function template()
    {
        return $this->belongsTo(ExportTemplate::class, 'template_id');
    }

    /**
     * Scope a query to only include exports of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('export_type', $type);
    }

    /**
     * Scope a query to only include exports with a specific status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include exports for a specific date range.
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->where(function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->orWhere(function ($query) use ($startDate, $endDate) {
                    $query->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                });
        });
    }

    /**
     * Mark this export as processing.
     */
    public function markAsProcessing()
    {
        $this->status = self::STATUS_PROCESSING;
        $this->save();
        
        return $this;
    }

    /**
     * Mark this export as completed.
     */
    public function markAsCompleted($filePath = null)
    {
        $this->status = self::STATUS_COMPLETED;
        if ($filePath) {
            $this->file_path = $filePath;
        }
        $this->completed_at = now();
        $this->save();
        
        return $this;
    }

    /**
     * Mark this export as failed.
     */
    public function markAsFailed()
    {
        $this->status = self::STATUS_FAILED;
        $this->save();
        
        return $this;
    }

    /**
     * Generate a download token for this export.
     */
    public function generateDownloadToken()
    {
        $this->download_token = md5($this->id . time() . rand(10000, 99999));
        $this->save();
        
        return $this->download_token;
    }

    /**
     * Get period types list for dropdown.
     */
    public static function getPeriodTypes()
    {
        return [
            self::PERIOD_MONTH => 'Month',
            self::PERIOD_BIMONTHLY => 'Bimonthly',
            self::PERIOD_QUARTER => 'Quarter',
            self::PERIOD_YEAR => 'Year',
            self::PERIOD_CUSTOM => 'Custom Range',
        ];
    }

    /**
     * Get export types list for dropdown.
     */
    public static function getExportTypes()
    {
        return [
            self::TYPE_INCOME_STATEMENT => 'Income Statement',
            self::TYPE_ACCOUNT_CARD => 'Account Card',
            self::TYPE_VAT_PAYMENTS => 'VAT Payments',
            self::TYPE_PROFIT_LOSS => 'Profit & Loss',
            self::TYPE_ADVANCED_PAYMENTS => 'Advanced Payments',
            self::TYPE_CENTRALIZED_CARD => 'Centralized Card',
        ];
    }

    /**
     * Get format list for dropdown.
     */
    public static function getFormats()
    {
        return [
            self::FORMAT_PDF => 'PDF',
            self::FORMAT_CSV => 'CSV',
            self::FORMAT_EXCEL => 'Excel',
            self::FORMAT_UNIFORM => 'Uniform Structure (for accounting software)',
        ];
    }
}

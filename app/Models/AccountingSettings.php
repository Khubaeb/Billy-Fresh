<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingSettings extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'business_id',
        'accounting_office_name',
        'accounting_contact_number',
        'accounting_email',
        'accounting_software',
        'export_format_preference',
        'include_attachments',
        'auto_export_enabled',
        'auto_export_frequency',
        'auto_export_settings',
        'account_code_mapping',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'include_attachments' => 'boolean',
        'auto_export_enabled' => 'boolean',
        'auto_export_settings' => 'json',
        'account_code_mapping' => 'json',
    ];

    /**
     * Accounting software constants
     */
    const SOFTWARE_QUICKBOOKS = 'quickbooks';
    const SOFTWARE_XERO = 'xero';
    const SOFTWARE_SAGE = 'sage';
    const SOFTWARE_WAVE = 'wave';
    const SOFTWARE_FRESHBOOKS = 'freshbooks';
    const SOFTWARE_OTHER = 'other';

    /**
     * Export frequency constants
     */
    const FREQUENCY_DAILY = 'daily';
    const FREQUENCY_WEEKLY = 'weekly';
    const FREQUENCY_MONTHLY = 'monthly';

    /**
     * Get the business that owns these settings.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the export template that is the default for this business.
     */
    public function defaultTemplates()
    {
        return $this->hasMany(ExportTemplate::class, 'business_id', 'business_id')
            ->where('is_default', true);
    }

    /**
     * Get the preferred export format.
     */
    public function getPreferredFormat()
    {
        return $this->export_format_preference ?: AccountingExport::FORMAT_PDF;
    }

    /**
     * Check if auto-export is due based on frequency settings.
     */
    public function isAutoExportDue()
    {
        if (!$this->auto_export_enabled) {
            return false;
        }

        $lastExport = AccountingExport::where('business_id', $this->business_id)
            ->where('status', AccountingExport::STATUS_COMPLETED)
            ->orderBy('completed_at', 'desc')
            ->first();

        if (!$lastExport) {
            return true; // No exports yet, so it's due
        }

        $lastExportDate = $lastExport->completed_at;
        $now = now();

        switch ($this->auto_export_frequency) {
            case self::FREQUENCY_DAILY:
                return $lastExportDate->diffInDays($now) >= 1;
            case self::FREQUENCY_WEEKLY:
                return $lastExportDate->diffInWeeks($now) >= 1;
            case self::FREQUENCY_MONTHLY:
                return $lastExportDate->diffInMonths($now) >= 1;
            default:
                return false;
        }
    }

    /**
     * Get the account code for a specific internal account.
     */
    public function getAccountCode($internalAccountId)
    {
        $mapping = $this->account_code_mapping ?: [];
        return $mapping[$internalAccountId] ?? null;
    }

    /**
     * Set the account code for a specific internal account.
     */
    public function setAccountCode($internalAccountId, $accountCode)
    {
        $mapping = $this->account_code_mapping ?: [];
        $mapping[$internalAccountId] = $accountCode;
        $this->account_code_mapping = $mapping;
        $this->save();

        return $this;
    }

    /**
     * Get accounting software list for dropdown.
     */
    public static function getAccountingSoftwareList()
    {
        return [
            self::SOFTWARE_QUICKBOOKS => 'QuickBooks',
            self::SOFTWARE_XERO => 'Xero',
            self::SOFTWARE_SAGE => 'Sage',
            self::SOFTWARE_WAVE => 'Wave',
            self::SOFTWARE_FRESHBOOKS => 'FreshBooks',
            self::SOFTWARE_OTHER => 'Other',
        ];
    }

    /**
     * Get export frequency list for dropdown.
     */
    public static function getExportFrequencyList()
    {
        return [
            self::FREQUENCY_DAILY => 'Daily',
            self::FREQUENCY_WEEKLY => 'Weekly',
            self::FREQUENCY_MONTHLY => 'Monthly',
        ];
    }
}

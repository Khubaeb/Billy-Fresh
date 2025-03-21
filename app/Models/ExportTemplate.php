<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExportTemplate extends Model
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
        'export_type',
        'settings',
        'is_default',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'settings' => 'json',
        'is_default' => 'boolean',
    ];

    /**
     * Get the business that owns this template.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Scope a query to only include templates of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('export_type', $type);
    }

    /**
     * Scope a query to only include default templates.
     */
    public function scopeDefaultTemplates($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Set this template as the default for its type.
     */
    public function setAsDefault()
    {
        // First unset any other default template for this type
        self::where('business_id', $this->business_id)
            ->where('export_type', $this->export_type)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);
        
        // Set this one as default
        $this->is_default = true;
        $this->save();
        
        return $this;
    }

    /**
     * Apply this template's settings to an export.
     */
    public function applyToExport(AccountingExport $export)
    {
        $settings = $this->settings ?: [];
        $parameters = $export->parameters ?: [];
        
        // Merge template settings with export parameters (export parameters take precedence)
        $mergedParameters = array_merge($settings, $parameters);
        
        $export->parameters = $mergedParameters;
        $export->save();
        
        return $export;
    }

    /**
     * Create a new export using this template.
     */
    public function createExport($businessId, $userId, $startDate, $endDate, $format = null, $additionalParameters = [])
    {
        $export = new AccountingExport([
            'business_id' => $businessId,
            'user_id' => $userId,
            'export_type' => $this->export_type,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'format' => $format ?: AccountingExport::FORMAT_PDF,
            'status' => AccountingExport::STATUS_PENDING,
            'parameters' => $additionalParameters,
        ]);
        
        $export->save();
        
        // Apply template settings to the export
        return $this->applyToExport($export);
    }

    /**
     * Get a specific setting from this template.
     */
    public function getSetting($key, $default = null)
    {
        $settings = $this->settings ?: [];
        return $settings[$key] ?? $default;
    }

    /**
     * Set a specific setting for this template.
     */
    public function setSetting($key, $value)
    {
        $settings = $this->settings ?: [];
        $settings[$key] = $value;
        $this->settings = $settings;
        $this->save();
        
        return $this;
    }
}

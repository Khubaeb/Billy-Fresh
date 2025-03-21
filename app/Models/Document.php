<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'business_id',
        'documentable_id',
        'documentable_type',
        'name',
        'path',
        'type',
        'size',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'size' => 'integer',
    ];

    /**
     * Get the business that owns the document.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the parent documentable model (e.g., invoice, expense).
     */
    public function documentable()
    {
        return $this->morphTo();
    }

    /**
     * Get the URL for the document.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return Storage::url($this->path);
    }

    /**
     * Get the file size formatted in human-readable format.
     *
     * @return string
     */
    public function getFormattedSizeAttribute()
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = $this->size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * Determine if the document is an image.
     *
     * @return bool
     */
    public function getIsImageAttribute()
    {
        return in_array($this->type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml']);
    }

    /**
     * Determine if the document is a PDF.
     *
     * @return bool
     */
    public function getIsPdfAttribute()
    {
        return $this->type === 'application/pdf';
    }

    /**
     * Scope a query to only include documents of a specific type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include documents for a specific business.
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
     * Scope a query to only include documents for a specific entity.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForEntity($query, $type, $id)
    {
        return $query->where('documentable_type', $type)
                    ->where('documentable_id', $id);
    }
}

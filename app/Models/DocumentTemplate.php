<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentTemplate extends Model
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
        'type',
        'content',
        'is_default',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get the business that owns the document template.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Scope a query to only include default templates.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope a query to only include templates of a specific type.
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
     * Scope a query to only include templates for a specific business.
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
     * Render the template with the given data.
     *
     * @param array $data
     * @return string
     */
    public function render(array $data = [])
    {
        $content = $this->content;
        
        // Simple template engine: replace {{variable}} with the value from $data
        preg_match_all('/\{\{(.*?)\}\}/', $content, $matches);
        
        foreach ($matches[1] as $index => $variable) {
            $variableName = trim($variable);
            $value = data_get($data, $variableName, '');
            $content = str_replace($matches[0][$index], $value, $content);
        }
        
        return $content;
    }

    /**
     * Get the default template for a type and business.
     *
     * @param string $type
     * @param int $businessId
     * @return self|null
     */
    public static function getDefault($type, $businessId)
    {
        return static::where('type', $type)
            ->where('business_id', $businessId)
            ->where('is_default', true)
            ->first();
    }

    /**
     * Make this template the default for its type within the business.
     *
     * @return self
     */
    public function makeDefault()
    {
        // Reset all other templates of this type
        static::where('type', $this->type)
            ->where('business_id', $this->business_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);
        
        // Set this template as default
        $this->is_default = true;
        $this->save();
        
        return $this;
    }
}

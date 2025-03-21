<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'settable_type',
        'settable_id',
        'key',
        'value',
    ];

    /**
     * Get the parent settable model (e.g., business, user).
     */
    public function settable()
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include settings for a specific entity.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForEntity($query, $type, $id)
    {
        return $query->where('settable_type', $type)
                    ->where('settable_id', $id);
    }

    /**
     * Scope a query to only include settings with a specific key.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $key
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForKey($query, $key)
    {
        return $query->where('key', $key);
    }

    /**
     * Get a setting value for a specific entity.
     *
     * @param string $type The entity type
     * @param int $id The entity ID
     * @param string $key The setting key
     * @param mixed $default The default value if the setting doesn't exist
     * @return mixed
     */
    public static function getValue($type, $id, $key, $default = null)
    {
        $setting = static::forEntity($type, $id)
                        ->forKey($key)
                        ->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value for a specific entity.
     *
     * @param string $type The entity type
     * @param int $id The entity ID
     * @param string $key The setting key
     * @param mixed $value The value to set
     * @return Setting
     */
    public static function setValue($type, $id, $key, $value)
    {
        $setting = static::forEntity($type, $id)
                        ->forKey($key)
                        ->first();

        if ($setting) {
            $setting->value = $value;
            $setting->save();
            return $setting;
        }

        return static::create([
            'settable_type' => $type,
            'settable_id' => $id,
            'key' => $key,
            'value' => $value,
        ]);
    }

    /**
     * Get all settings for a specific entity as a key-value array.
     *
     * @param string $type The entity type
     * @param int $id The entity ID
     * @return array
     */
    public static function getAllForEntity($type, $id)
    {
        $settings = static::forEntity($type, $id)->get();
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->value;
        }

        return $result;
    }

    /**
     * Delete a setting for a specific entity.
     *
     * @param string $type The entity type
     * @param int $id The entity ID
     * @param string $key The setting key
     * @return bool
     */
    public static function deleteValue($type, $id, $key)
    {
        return static::forEntity($type, $id)
                    ->forKey($key)
                    ->delete();
    }
}

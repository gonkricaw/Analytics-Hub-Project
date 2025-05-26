<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemConfiguration extends Model
{
    use HasFactory;

    protected $table = 'idnbi_system_configurations';

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Get configuration value, with automatic JSON decoding if needed.
     */
    public function getValueAttribute($value)
    {
        if ($this->type === 'json' && $value) {
            return json_decode($value, true);
        }
        
        return $value;
    }

    /**
     * Set configuration value, with automatic JSON encoding if needed.
     */
    public function setValueAttribute($value)
    {
        if ($this->type === 'json') {
            $this->attributes['value'] = json_encode($value);
        } else {
            $this->attributes['value'] = $value;
        }
    }

    /**
     * Scope for public configurations.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Get configuration by key.
     */
    public static function getByKey($key, $default = null)
    {
        $config = static::where('key', $key)->first();
        return $config ? $config->value : $default;
    }

    /**
     * Set configuration by key.
     */
    public static function setByKey($key, $value, $type = 'text', $description = null, $isPublic = false)
    {
        // Handle JSON encoding for array/object values
        if ($type === 'json' && (is_array($value) || is_object($value))) {
            $value = json_encode($value);
        }

        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description,
                'is_public' => $isPublic,
            ]
        );
    }
}

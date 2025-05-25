<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TermsAndConditions extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'idnbi_terms_and_conditions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'content',
        'version',
        'is_active',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who created these terms.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to filter active terms.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by version.
     */
    public function scopeByVersion($query, string $version)
    {
        return $query->where('version', $version);
    }

    /**
     * Get the current active terms and conditions.
     */
    public static function getCurrent(): ?self
    {
        return self::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Activate these terms and deactivate all others.
     */
    public function activate(): bool
    {
        // Deactivate all other terms
        self::where('id', '!=', $this->id)->update(['is_active' => false]);
        
        // Activate this terms
        return $this->update(['is_active' => true]);
    }

    /**
     * Get the latest version number.
     */
    public static function getLatestVersion(): string
    {
        $latest = self::orderBy('created_at', 'desc')->first();
        
        if (!$latest) {
            return '1.0';
        }
        
        // Extract numeric part and increment
        preg_match('/(\d+)\.(\d+)/', $latest->version, $matches);
        $major = (int) ($matches[1] ?? 1);
        $minor = (int) ($matches[2] ?? 0);
        
        return $major . '.' . ($minor + 1);
    }
}

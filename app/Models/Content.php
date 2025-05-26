<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Content extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'idnbi_contents';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'slug',
        'type',
        'custom_content',
        'embed_url_original',
        'embed_url_uuid',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    /**
     * The attributes that should be hidden.
     */
    protected $hidden = [
        'embed_url_original', // Hide sensitive URL for security
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'embed_url_uuid' => 'string',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($content) {
            // Auto-generate slug if not provided
            if (!$content->slug) {
                $content->slug = Str::slug($content->title);
            }

            // Generate UUID for embed_url type
            if ($content->type === 'embed_url' && !$content->embed_url_uuid) {
                $content->embed_url_uuid = Str::uuid();
            }

            // Encrypt embed URL if provided
            if ($content->embed_url_original) {
                $content->embed_url_original = encrypt($content->embed_url_original);
            }
        });

        static::updating(function ($content) {
            // Update slug if title changed
            if ($content->isDirty('title') && !$content->isDirty('slug')) {
                $content->slug = Str::slug($content->title);
            }

            // Encrypt embed URL if changed
            if ($content->isDirty('embed_url_original') && $content->embed_url_original) {
                $content->embed_url_original = encrypt($content->embed_url_original);
            }
        });
    }

    /**
     * Get the user who created this content.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Get the user who last updated this content.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    /**
     * Get menus that link to this content.
     */
    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class, 'content_id');
    }

    /**
     * Scope to get content by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get custom content only.
     */
    public function scopeCustom($query)
    {
        return $query->where('type', 'custom');
    }

    /**
     * Scope to get embed URL content only.
     */
    public function scopeEmbedUrl($query)
    {
        return $query->where('type', 'embed_url');
    }

    /**
     * Get decrypted embed URL.
     */
    public function getDecryptedEmbedUrl(): ?string
    {
        if ($this->type === 'embed_url' && $this->embed_url_original) {
            try {
                return decrypt($this->embed_url_original);
            } catch (\Exception $e) {
                \Log::error('Failed to decrypt embed URL for content ID: ' . $this->id);
                return null;
            }
        }
        return null;
    }

    /**
     * Check if content is accessible by user (placeholder for future RBAC).
     */
    public function isAccessibleBy(User $user): bool
    {
        // For now, all content is accessible to authenticated users
        // This can be extended with more complex permission checks
        return true;
    }

    /**
     * Get the route key name for implicit model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Resolve route binding by slug or UUID.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('slug', $value)
            ->orWhere('embed_url_uuid', $value)
            ->firstOrFail();
    }
}

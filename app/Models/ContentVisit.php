<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentVisit extends Model
{
    use HasFactory;

    protected $table = 'idnbi_content_visits';

    protected $fillable = [
        'user_id',
        'content_id',
        'menu_id',
        'page_type',
        'page_title',
        'page_url',
        'ip_address',
        'user_agent',
        'visited_at',
        'duration_seconds',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
        'duration_seconds' => 'integer',
    ];

    /**
     * Get the user that made the visit.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the content that was visited.
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    /**
     * Get the menu that was visited.
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Scope for visits within date range.
     */
    public function scopeWithinDays($query, $days = 30)
    {
        return $query->where('visited_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for content visits only.
     */
    public function scopeContentOnly($query)
    {
        return $query->where('page_type', 'content')->whereNotNull('content_id');
    }

    /**
     * Scope for menu visits only.
     */
    public function scopeMenuOnly($query)
    {
        return $query->where('page_type', 'menu')->whereNotNull('menu_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends Model
{
    use HasFactory;

    protected $table = 'idnbi_user_sessions';

    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'login_at',
        'logout_at',
        'last_activity_at',
        'is_active',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for active sessions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for sessions within date range.
     */
    public function scopeWithinDays($query, $days = 15)
    {
        return $query->where('login_at', '>=', now()->subDays($days));
    }

    /**
     * Get session duration in minutes.
     */
    public function getDurationAttribute()
    {
        if ($this->logout_at) {
            return $this->login_at->diffInMinutes($this->logout_at);
        }
        
        return $this->login_at->diffInMinutes($this->last_activity_at);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpBlock extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'idnbi_ip_blocks';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'ip_address',
        'reason',
        'blocked_at',
        'unblocked_at',
        'unblocked_by',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'blocked_at' => 'datetime',
        'unblocked_at' => 'datetime',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who unblocked this IP.
     */
    public function unblockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unblocked_by');
    }

    /**
     * Scope to filter active blocks.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by IP address.
     */
    public function scopeByIpAddress($query, string $ipAddress)
    {
        return $query->where('ip_address', $ipAddress);
    }

    /**
     * Check if IP is currently blocked.
     */
    public static function isBlocked(string $ipAddress): bool
    {
        return self::where('ip_address', $ipAddress)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Block an IP address.
     */
    public static function blockIp(string $ipAddress, string $reason): self
    {
        return self::create([
            'ip_address' => $ipAddress,
            'reason' => $reason,
            'blocked_at' => now(),
            'is_active' => true,
        ]);
    }

    /**
     * Unblock an IP address.
     */
    public function unblock(int $unblockedByUserId): bool
    {
        return $this->update([
            'unblocked_at' => now(),
            'unblocked_by' => $unblockedByUserId,
            'is_active' => false,
        ]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Notification Model
 * 
 * Represents system notifications that can be sent to users
 * 
 * @property int $id
 * @property string $title
 * @property string $content
 * @property int $created_by_user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Notification extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'idnbi_notifications';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'content',
        'created_by_user_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who created this notification.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Get the users who have received this notification.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'idnbi_user_notifications', 'notification_id', 'user_id')
                    ->withPivot('read_at')
                    ->withTimestamps();
    }

    /**
     * Get the users who have read this notification.
     */
    public function readUsers(): BelongsToMany
    {
        return $this->users()->whereNotNull('idnbi_user_notifications.read_at');
    }

    /**
     * Get the users who haven't read this notification.
     */
    public function unreadUsers(): BelongsToMany
    {
        return $this->users()->whereNull('idnbi_user_notifications.read_at');
    }

    /**
     * Scope to get notifications by creator.
     */
    public function scopeByCreator($query, int $userId)
    {
        return $query->where('created_by_user_id', $userId);
    }

    /**
     * Scope to get recent notifications.
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Send this notification to all users.
     */
    public function sendToAllUsers(): int
    {
        $userIds = User::pluck('id')->toArray();
        $this->users()->syncWithoutDetaching($userIds);
        
        return count($userIds);
    }

    /**
     * Send this notification to specific users.
     */
    public function sendToUsers(array $userIds): int
    {
        $this->users()->syncWithoutDetaching($userIds);
        
        return count($userIds);
    }

    /**
     * Mark as read for a specific user.
     */
    public function markAsReadFor(User $user): bool
    {
        return $this->users()->updateExistingPivot($user->id, [
            'read_at' => now()
        ]) > 0;
    }

    /**
     * Check if notification has been read by a specific user.
     */
    public function isReadBy(User $user): bool
    {
        $pivot = $this->users()->where('user_id', $user->id)->first()?->pivot;
        
        return $pivot && $pivot->read_at !== null;
    }

    /**
     * Get notification statistics.
     */
    public function getStatistics(): array
    {
        $totalUsers = $this->users()->count();
        $readUsers = $this->readUsers()->count();
        
        return [
            'total_recipients' => $totalUsers,
            'read_count' => $readUsers,
            'unread_count' => $totalUsers - $readUsers,
            'read_percentage' => $totalUsers > 0 ? round(($readUsers / $totalUsers) * 100, 2) : 0,
        ];
    }
}

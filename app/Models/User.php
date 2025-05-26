<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'idnbi_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_photo_path',
        'invited_by',
        'last_active_at',
        'temporary_password_used',
        'terms_accepted_at',
        'terms_accepted_version',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<string>
     */
    protected $appends = [
        'avatar',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_active_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'temporary_password_used' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the avatar attribute (alias for profile_photo_path).
     */
    public function getAvatarAttribute(): ?string
    {
        return $this->profile_photo_path;
    }

    /**
     * Get the user who invited this user.
     */
    public function invitedBy()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Get the users invited by this user.
     */
    public function invitedUsers()
    {
        return $this->hasMany(User::class, 'invited_by');
    }

    /**
     * Get the failed login attempts for this user.
     */
    public function failedLoginAttempts()
    {
        return $this->hasMany(FailedLoginAttempt::class);
    }

    /**
     * Get the IP blocks unblocked by this user.
     */
    public function unblockedIps()
    {
        return $this->hasMany(IpBlock::class, 'unblocked_by');
    }

    /**
     * Get the terms and conditions created by this user.
     */
    public function createdTerms()
    {
        return $this->hasMany(TermsAndConditions::class, 'created_by');
    }

    /**
     * Check if user needs to change password (temporary password used).
     */
    public function needsPasswordChange(): bool
    {
        return $this->temporary_password_used;
    }

    /**
     * Update user's last active timestamp.
     */
    public function updateLastActive(): bool
    {
        return $this->update(['last_active_at' => now()]);
    }

    /**
     * Check if user has been inactive for specified minutes.
     */
    public function isInactive(int $minutes = 30): bool
    {
        if (!$this->last_active_at) {
            return true;
        }

        return $this->last_active_at->diffInMinutes(now()) >= $minutes;
    }

    /**
     * Get the notifications created by this user.
     */
    public function createdNotifications()
    {
        return $this->hasMany(Notification::class, 'created_by_user_id');
    }

    /**
     * Get the notifications for this user through the pivot table.
     */
    public function notifications(): BelongsToMany
    {
        return $this->belongsToMany(Notification::class, 'idnbi_user_notifications', 'user_id', 'notification_id')
                    ->withPivot('read_at')
                    ->withTimestamps()
                    ->orderBy('idnbi_notifications.created_at', 'desc');
    }

    /**
     * Get unread notifications for this user.
     */
    public function unreadNotifications(): BelongsToMany
    {
        return $this->notifications()->wherePivot('read_at', null);
    }

    /**
     * Get read notifications for this user.
     */
    public function readNotifications(): BelongsToMany
    {
        return $this->notifications()->whereNotNull('idnbi_user_notifications.read_at');
    }

    /**
     * Get the email templates created by this user.
     */
    public function createdEmailTemplates()
    {
        return $this->hasMany(EmailTemplate::class, 'created_by_user_id');
    }

    /**
     * Mark a notification as read for this user.
     */
    public function markNotificationAsRead(int $notificationId): bool
    {
        return $this->notifications()
                    ->wherePivot('notification_id', $notificationId)
                    ->wherePivot('read_at', null)
                    ->updateExistingPivot($notificationId, ['read_at' => now()]) > 0;
    }

    /**
     * Mark all notifications as read for this user.
     */
    public function markAllNotificationsAsRead(): bool
    {
        return $this->notifications()
                    ->wherePivot('read_at', null)
                    ->get()
                    ->each(function ($notification) {
                        $this->markNotificationAsRead($notification->id);
                    });
    }

    /**
     * Get count of unread notifications for this user.
     */
    public function getUnreadNotificationsCountAttribute(): int
    {
        return $this->unreadNotifications()->count();
    }

    /**
     * Get the roles that belong to this user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'idnbi_role_user', 'user_id', 'role_id')
                    ->withTimestamps();
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRole(string|Role $role): bool
    {
        if ($role instanceof Role) {
            return $this->roles()->where('idnbi_roles.id', $role->id)->exists();
        }
        
        return $this->roles()->where('name', $role)->exists();
    }

    /**
     * Check if the user has all specified roles.
     */
    public function hasAllRoles(array $roleNames): bool
    {
        $userRoles = $this->roles()->pluck('name')->toArray();
        return empty(array_diff($roleNames, $userRoles));
    }

    /**
     * Check if the user has any of the specified roles.
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles()->whereIn('name', $roleNames)->exists();
    }

    /**
     * Check if the user has a specific permission.
     */
    public function hasPermission(string|\App\Models\Permission $permission): bool
    {
        if ($permission instanceof \App\Models\Permission) {
            return $this->roles()
                        ->whereHas('permissions', function ($query) use ($permission) {
                            $query->where('idnbi_permissions.id', $permission->id);
                        })
                        ->exists();
        }
        
        return $this->roles()
                    ->whereHas('permissions', function ($query) use ($permission) {
                        $query->where('name', $permission);
                    })
                    ->exists();
    }

    /**
     * Check if the user has any of the specified permissions.
     */
    public function hasAnyPermission(array $permissionNames): bool
    {
        return $this->roles()
                    ->whereHas('permissions', function ($query) use ($permissionNames) {
                        $query->whereIn('name', $permissionNames);
                    })
                    ->exists();
    }

    /**
     * Get all permissions for this user through their roles.
     */
    public function getAllPermissions()
    {
        return \App\Models\Permission::whereHas('roles', function ($query) {
            $query->whereIn('idnbi_roles.id', $this->roles()->pluck('idnbi_roles.id'));
        })->get();
    }

    /**
     * Sync roles to this user.
     */
    public function syncRoles(array $roleIds): void
    {
        $this->roles()->sync($roleIds);
    }
}

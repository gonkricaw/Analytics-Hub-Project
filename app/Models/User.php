<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Role Model
 * 
 * Represents user roles in the RBAC system
 * 
 * @property int $id
 * @property string $name
 * @property string $display_name
 * @property string|null $description
 * @property string $color
 * @property bool $is_system
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Role extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'idnbi_roles';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'color',
        'is_system',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_system' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the users that belong to this role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'idnbi_role_user', 'role_id', 'user_id')
                    ->withTimestamps();
    }

    /**
     * Get the permissions that belong to this role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'idnbi_permission_role', 'role_id', 'permission_id')
                    ->withTimestamps();
    }

    /**
     * Check if the role has a specific permission.
     */
    public function hasPermission(string|Permission $permission): bool
    {
        if ($permission instanceof Permission) {
            return $this->permissions()->where('idnbi_permissions.id', $permission->id)->exists();
        }
        
        return $this->permissions()->where('name', $permission)->exists();
    }

    /**
     * Sync permissions to this role.
     */
    public function syncPermissions(array $permissionIds): void
    {
        $this->permissions()->sync($permissionIds);
    }
}

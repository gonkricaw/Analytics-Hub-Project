<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Permission Model
 * 
 * Represents individual permissions in the RBAC system
 * 
 * @property int $id
 * @property string $name
 * @property string $display_name
 * @property string|null $description
 * @property string|null $group
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Permission extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'idnbi_permissions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'group',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the roles that have this permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'idnbi_permission_role', 'permission_id', 'role_id')
                    ->withTimestamps();
    }
}

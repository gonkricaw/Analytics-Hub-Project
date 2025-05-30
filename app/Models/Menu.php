<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Menu extends Model
{
    use SoftDeletes, HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'idnbi_menus';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'parent_id',
        'name',
        'type',
        'icon',
        'route_or_url',
        'content_id',
        'order',
        'role_permissions_required',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'role_permissions_required' => 'array',
        'order' => 'integer',
    ];

    /**
     * Get the parent menu item.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    /**
     * Get the children menu items.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('order');
    }

    /**
     * Get all descendants (children and their children recursively).
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get the associated content.
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class, 'content_id');
    }

    /**
     * Scope to get only root menu items (no parent).
     */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to get menu items by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to order by position.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Check if menu is accessible by user.
     */
    public function isAccessibleBy(User $user): bool
    {
        if (empty($this->role_permissions_required)) {
            return true;
        }

        // Check if user has any of the required permissions or roles
        foreach ($this->role_permissions_required as $requirement) {
            if ($user->hasPermissionTo($requirement) || $user->hasRole($requirement)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the full hierarchy path.
     */
    public function getHierarchyPath(): string
    {
        $path = [$this->name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }

        return implode(' > ', $path);
    }
}

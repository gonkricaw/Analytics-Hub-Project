<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RBACSeeder::class);
    }

    #[Test]
    public function role_can_be_created_with_valid_data()
    {
        $role = Role::create([
            'name' => 'test_role',
            'display_name' => 'Test Role',
            'description' => 'A test role for unit testing',
            'color' => '#FF5722',
            'is_system' => false
        ]);

        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals('test_role', $role->name);
        $this->assertEquals('Test Role', $role->display_name);
        $this->assertEquals('A test role for unit testing', $role->description);
        $this->assertEquals('#FF5722', $role->color);
        $this->assertFalse($role->is_system);
        $this->assertNotNull($role->created_at);
        $this->assertNotNull($role->updated_at);
    }

    #[Test]
    public function role_name_must_be_unique()
    {
        Role::create([
            'name' => 'duplicate_role',
            'display_name' => 'Duplicate Role',
            'description' => 'First role',
            'color' => '#FF5722',
            'is_system' => false
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Role::create([
            'name' => 'duplicate_role',
            'display_name' => 'Another Duplicate Role',
            'description' => 'Second role with same name',
            'color' => '#FF5722',
            'is_system' => false
        ]);
    }

    #[Test]
    public function role_has_many_permissions_relationship()
    {
        $role = Role::where('name', 'admin')->first();
        $permission = Permission::where('name', 'users.view')->first();
        
        $this->assertTrue($role->permissions()->exists());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $role->permissions);
    }

    #[Test]
    public function role_has_many_users_relationship()
    {
        $role = Role::where('name', 'admin')->first();
        $user = User::factory()->create();
        
        $user->roles()->attach($role);
        
        $this->assertTrue($role->users()->exists());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $role->users);
        $this->assertContains($user->id, $role->users->pluck('id'));
    }

    #[Test]
    public function role_can_check_if_it_has_permission()
    {
        $role = Role::where('name', 'admin')->first();
        $permission = Permission::where('name', 'users.view')->first();
        
        // Admin role should have users.view permission from seeder
        $this->assertTrue($role->hasPermission('users.view'));
        $this->assertTrue($role->hasPermission($permission));
        
        // Admin role should not have a non-existent permission
        $this->assertFalse($role->hasPermission('non.existent.permission'));
    }

    #[Test]
    public function role_can_assign_and_revoke_permissions()
    {
        $role = Role::create([
            'name' => 'test_role_permissions',
            'display_name' => 'Test Role Permissions',
            'description' => 'Testing permission assignment',
            'color' => '#FF5722',
            'is_system' => false
        ]);

        $permission = Permission::where('name', 'users.view')->first();
        
        // Initially should not have permission
        $this->assertFalse($role->hasPermission('users.view'));
        
        // Assign permission
        $role->permissions()->attach($permission);
        $role->refresh();
        
        $this->assertTrue($role->hasPermission('users.view'));
        
        // Revoke permission
        $role->permissions()->detach($permission);
        $role->refresh();
        
        $this->assertFalse($role->hasPermission('users.view'));
    }

    #[Test]
    public function role_can_sync_permissions()
    {
        $role = Role::create([
            'name' => 'test_role_sync',
            'display_name' => 'Test Role Sync',
            'description' => 'Testing permission sync',
            'color' => '#FF5722',
            'is_system' => false
        ]);

        $permissions = Permission::whereIn('name', ['users.view', 'users.create'])->get();
        $permissionIds = $permissions->pluck('id')->toArray();
        
        // Sync permissions
        $role->permissions()->sync($permissionIds);
        $role->refresh();
        
        $this->assertTrue($role->hasPermission('users.view'));
        $this->assertTrue($role->hasPermission('users.create'));
        $this->assertEquals(2, $role->permissions->count());
        
        // Sync with different permissions
        $newPermission = Permission::where('name', 'roles.view')->first();
        $role->permissions()->sync([$newPermission->id]);
        $role->refresh();
        
        $this->assertFalse($role->hasPermission('users.view'));
        $this->assertFalse($role->hasPermission('users.create'));
        $this->assertTrue($role->hasPermission('roles.view'));
        $this->assertEquals(1, $role->permissions->count());
    }

    #[Test]
    public function system_roles_can_be_identified()
    {
        $superAdminRole = Role::where('name', 'super_admin')->first();
        $this->assertTrue($superAdminRole->is_system);
        
        $customRole = Role::create([
            'name' => 'custom_role',
            'display_name' => 'Custom Role',
            'description' => 'A custom role',
            'color' => '#FF5722',
            'is_system' => false
        ]);
        
        $this->assertFalse($customRole->is_system);
    }

    #[Test]
    public function role_fillable_attributes_work_correctly()
    {
        $data = [
            'name' => 'fillable_test',
            'display_name' => 'Fillable Test',
            'description' => 'Testing fillable attributes',
            'color' => '#FF5722',
            'is_system' => false
        ];

        $role = new Role();
        $role->fill($data);

        $this->assertEquals('fillable_test', $role->name);
        $this->assertEquals('Fillable Test', $role->display_name);
        $this->assertEquals('Testing fillable attributes', $role->description);
        $this->assertEquals('#FF5722', $role->color);
        $this->assertFalse($role->is_system);
    }

    #[Test]
    public function role_can_be_soft_deleted()
    {
        $role = Role::create([
            'name' => 'soft_delete_test',
            'display_name' => 'Soft Delete Test',
            'description' => 'Testing soft delete',
            'color' => '#FF5722',
            'is_system' => false
        ]);

        $roleId = $role->id;
        $role->delete();

        $this->assertSoftDeleted('idnbi_roles', ['id' => $roleId]);
        $this->assertNull(Role::find($roleId));
        $this->assertNotNull(Role::withTrashed()->find($roleId));
    }
}

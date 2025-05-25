<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class UserRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RBACSeeder::class);
    }

    #[Test]
    public function user_can_have_multiple_roles()
    {
        $user = User::factory()->create();
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        
        $user->roles()->attach([$adminRole->id, $managerRole->id]);
        
        $this->assertTrue($user->roles()->exists());
        $this->assertEquals(2, $user->roles->count());
        $this->assertContains($adminRole->id, $user->roles->pluck('id'));
        $this->assertContains($managerRole->id, $user->roles->pluck('id'));
    }

    #[Test]
    public function user_can_check_if_has_role()
    {
        $user = User::factory()->create();
        $adminRole = Role::where('name', 'admin')->first();
        
        // Initially should not have role
        $this->assertFalse($user->hasRole('admin'));
        $this->assertFalse($user->hasRole($adminRole));
        
        // Assign role
        $user->roles()->attach($adminRole);
        $user->refresh();
        
        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->hasRole($adminRole));
    }

    #[Test]
    public function user_can_check_if_has_any_role()
    {
        $user = User::factory()->create();
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        
        $this->assertFalse($user->hasAnyRole(['admin', 'manager']));
        
        $user->roles()->attach($adminRole);
        $user->refresh();
        
        $this->assertTrue($user->hasAnyRole(['admin', 'manager']));
        $this->assertTrue($user->hasAnyRole(['admin', 'non_existent']));
        $this->assertFalse($user->hasAnyRole(['manager', 'analyst']));
    }

    #[Test]
    public function user_can_check_if_has_all_roles()
    {
        $user = User::factory()->create();
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        
        $this->assertFalse($user->hasAllRoles(['admin', 'manager']));
        
        $user->roles()->attach($adminRole);
        $user->refresh();
        
        $this->assertFalse($user->hasAllRoles(['admin', 'manager']));
        
        $user->roles()->attach($managerRole);
        $user->refresh();
        
        $this->assertTrue($user->hasAllRoles(['admin', 'manager']));
    }

    #[Test]
    public function user_can_check_permission_through_role()
    {
        $user = User::factory()->create();
        $adminRole = Role::where('name', 'admin')->first();
        
        // User without role should not have permission
        $this->assertFalse($user->hasPermission('users.view'));
        
        // Assign admin role which has users.view permission
        $user->roles()->attach($adminRole);
        $user->refresh();
        
        $this->assertTrue($user->hasPermission('users.view'));
    }

    #[Test]
    public function user_permission_check_works_with_permission_object()
    {
        $user = User::factory()->create();
        $adminRole = Role::where('name', 'admin')->first();
        $permission = Permission::where('name', 'users.view')->first();
        
        $this->assertFalse($user->hasPermission($permission));
        
        $user->roles()->attach($adminRole);
        $user->refresh();
        
        $this->assertTrue($user->hasPermission($permission));
    }

    #[Test]
    public function user_can_have_permissions_from_multiple_roles()
    {
        $user = User::factory()->create();
        $adminRole = Role::where('name', 'admin')->first();
        $analystRole = Role::where('name', 'analyst')->first();
        
        $user->roles()->attach([$adminRole->id, $analystRole->id]);
        $user->refresh();
        
        // Should have permissions from both roles
        $this->assertTrue($user->hasPermission('users.view')); // Admin permission
        $this->assertTrue($user->hasPermission('analytics.view')); // Analyst permission
    }

    #[Test]
    public function user_inherits_super_admin_permissions()
    {
        $user = User::factory()->create();
        $superAdminRole = Role::where('name', 'super_admin')->first();
        
        $user->roles()->attach($superAdminRole);
        $user->refresh();
        
        // Super admin should have all permissions
        $allPermissions = Permission::all();
        foreach ($allPermissions as $permission) {
            $this->assertTrue($user->hasPermission($permission->name), 
                "Super admin should have permission: {$permission->name}");
        }
    }

    #[Test]
    public function user_role_assignment_updates_timestamps()
    {
        $user = User::factory()->create();
        $role = Role::where('name', 'admin')->first();
        
        $user->roles()->attach($role);
        
        $pivot = $user->roles()->where('role_id', $role->id)->first()->pivot;
        $this->assertNotNull($pivot->created_at);
        $this->assertNotNull($pivot->updated_at);
    }

    #[Test]
    public function user_can_sync_roles()
    {
        $user = User::factory()->create();
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $analystRole = Role::where('name', 'analyst')->first();
        
        // Initially assign admin and manager roles
        $user->roles()->sync([$adminRole->id, $managerRole->id]);
        $user->refresh();
        
        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('manager'));
        $this->assertFalse($user->hasRole('analyst'));
        $this->assertEquals(2, $user->roles->count());
        
        // Sync to only analyst role
        $user->roles()->sync([$analystRole->id]);
        $user->refresh();
        
        $this->assertFalse($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('manager'));
        $this->assertTrue($user->hasRole('analyst'));
        $this->assertEquals(1, $user->roles->count());
    }

    #[Test]
    public function user_role_detachment_works_correctly()
    {
        $user = User::factory()->create();
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        
        $user->roles()->attach([$adminRole->id, $managerRole->id]);
        $user->refresh();
        
        $this->assertEquals(2, $user->roles->count());
        
        // Detach one role
        $user->roles()->detach($adminRole);
        $user->refresh();
        
        $this->assertFalse($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('manager'));
        $this->assertEquals(1, $user->roles->count());
        
        // Detach all roles
        $user->roles()->detach();
        $user->refresh();
        
        $this->assertEquals(0, $user->roles->count());
    }
}

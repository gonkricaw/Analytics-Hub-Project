<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Permission;
use App\Models\Role;
use App\Policies\PermissionPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class PermissionPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected $policy;
    protected $superAdminUser;
    protected $adminUser;
    protected $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(\Database\Seeders\RBACSeeder::class);
        
        $this->policy = new PermissionPolicy();
        $this->superAdminUser = User::where('email', 'superadmin@indonetanalytics.com')->first();
        $this->adminUser = User::where('email', 'admin@indonetanalytics.com')->first();
        $this->regularUser = User::factory()->create();
    }

    #[Test]
    public function super_admin_can_view_any_permissions()
    {
        $this->assertTrue($this->policy->viewAny($this->superAdminUser));
    }

    #[Test]
    public function admin_can_view_permissions_with_proper_permission()
    {
        $this->assertTrue($this->policy->viewAny($this->adminUser));
    }

    #[Test]
    public function regular_user_cannot_view_permissions()
    {
        $this->assertFalse($this->policy->viewAny($this->regularUser));
    }

    #[Test]
    public function super_admin_can_view_specific_permission()
    {
        $permission = Permission::first();
        
        $this->assertTrue($this->policy->view($this->superAdminUser, $permission));
    }

    #[Test]
    public function admin_can_view_specific_permission_with_proper_permission()
    {
        $permission = Permission::first();
        
        $this->assertTrue($this->policy->view($this->adminUser, $permission));
    }

    #[Test]
    public function regular_user_cannot_view_specific_permission()
    {
        $permission = Permission::first();
        
        $this->assertFalse($this->policy->view($this->regularUser, $permission));
    }

    #[Test]
    public function super_admin_can_create_permissions()
    {
        $this->assertTrue($this->policy->create($this->superAdminUser));
    }

    #[Test]
    public function admin_cannot_create_permissions()
    {
        $this->assertFalse($this->policy->create($this->adminUser));
    }

    #[Test]
    public function regular_user_cannot_create_permissions()
    {
        $this->assertFalse($this->policy->create($this->regularUser));
    }

    #[Test]
    public function super_admin_can_update_permissions()
    {
        $permission = Permission::first();
        
        $this->assertTrue($this->policy->update($this->superAdminUser, $permission));
    }

    #[Test]
    public function admin_cannot_update_permissions()
    {
        $permission = Permission::first();
        
        $this->assertFalse($this->policy->update($this->adminUser, $permission));
    }

    #[Test]
    public function regular_user_cannot_update_permissions()
    {
        $permission = Permission::first();
        
        $this->assertFalse($this->policy->update($this->regularUser, $permission));
    }

    #[Test]
    public function super_admin_can_delete_permissions()
    {
        $permission = Permission::first();
        
        $this->assertTrue($this->policy->delete($this->superAdminUser, $permission));
    }

    #[Test]
    public function admin_cannot_delete_permissions()
    {
        $permission = Permission::first();
        
        $this->assertFalse($this->policy->delete($this->adminUser, $permission));
    }

    #[Test]
    public function regular_user_cannot_delete_permissions()
    {
        $permission = Permission::first();
        
        $this->assertFalse($this->policy->delete($this->regularUser, $permission));
    }

    #[Test]
    public function super_admin_can_restore_permissions()
    {
        $permission = Permission::first();
        
        $this->assertTrue($this->policy->restore($this->superAdminUser, $permission));
    }

    #[Test]
    public function admin_cannot_restore_permissions()
    {
        $permission = Permission::first();
        
        $this->assertFalse($this->policy->restore($this->adminUser, $permission));
    }

    #[Test]
    public function super_admin_can_force_delete_permissions()
    {
        $permission = Permission::first();
        
        $this->assertTrue($this->policy->forceDelete($this->superAdminUser, $permission));
    }

    #[Test]
    public function admin_cannot_force_delete_permissions()
    {
        $permission = Permission::first();
        
        $this->assertFalse($this->policy->forceDelete($this->adminUser, $permission));
    }

    #[Test]
    public function policy_respects_user_permission_system()
    {
        // Create a user with specific permission
        $userWithPermission = User::factory()->create();
        $role = Role::create([
            'name' => 'test_permissions_viewer',
            'display_name' => 'Test Permissions Viewer',
            'description' => 'Can view permissions only',
            'color' => '#FF5722',
            'is_system' => false
        ]);
        
        $viewPermission = Permission::where('name', 'permissions.view')->first();
        $role->permissions()->attach($viewPermission);
        $userWithPermission->roles()->attach($role);
        
        // User should be able to view permissions
        $this->assertTrue($this->policy->viewAny($userWithPermission));
        
        // But should not be able to create/update/delete
        $this->assertFalse($this->policy->create($userWithPermission));
        
        $permission = Permission::first();
        $this->assertFalse($this->policy->update($userWithPermission, $permission));
        $this->assertFalse($this->policy->delete($userWithPermission, $permission));
    }
}

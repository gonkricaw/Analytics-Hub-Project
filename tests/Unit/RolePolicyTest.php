<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Policies\RolePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class RolePolicyTest extends TestCase
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
        
        $this->policy = new RolePolicy();
        $this->superAdminUser = User::where('email', 'superadmin@indonetanalytics.com')->first();
        $this->adminUser = User::where('email', 'admin@indonetanalytics.com')->first();
        $this->regularUser = User::factory()->create();
    }

    #[Test]
    public function super_admin_can_view_any_roles()
    {
        $this->assertTrue($this->policy->viewAny($this->superAdminUser));
    }

    #[Test]
    public function admin_can_view_roles_with_proper_permission()
    {
        $this->assertTrue($this->policy->viewAny($this->adminUser));
    }

    #[Test]
    public function regular_user_cannot_view_roles()
    {
        $this->assertFalse($this->policy->viewAny($this->regularUser));
    }

    #[Test]
    public function super_admin_can_view_specific_role()
    {
        $role = Role::first();
        
        $this->assertTrue($this->policy->view($this->superAdminUser, $role));
    }

    #[Test]
    public function admin_can_view_specific_role_with_proper_permission()
    {
        $role = Role::first();
        
        $this->assertTrue($this->policy->view($this->adminUser, $role));
    }

    #[Test]
    public function regular_user_cannot_view_specific_role()
    {
        $role = Role::first();
        
        $this->assertFalse($this->policy->view($this->regularUser, $role));
    }

    #[Test]
    public function super_admin_can_create_roles()
    {
        $this->assertTrue($this->policy->create($this->superAdminUser));
    }

    #[Test]
    public function admin_can_create_roles_with_proper_permission()
    {
        $this->assertTrue($this->policy->create($this->adminUser));
    }

    #[Test]
    public function regular_user_cannot_create_roles()
    {
        $this->assertFalse($this->policy->create($this->regularUser));
    }

    #[Test]
    public function super_admin_can_update_any_role()
    {
        $role = Role::where('is_system', false)->first();
        
        $this->assertTrue($this->policy->update($this->superAdminUser, $role));
    }

    #[Test]
    public function admin_can_update_non_system_roles()
    {
        $customRole = Role::create([
            'name' => 'custom_test_role',
            'display_name' => 'Custom Test Role',
            'description' => 'A custom role for testing',
            'color' => '#FF5722',
            'is_system' => false
        ]);
        
        $this->assertTrue($this->policy->update($this->adminUser, $customRole));
    }

    #[Test]
    public function admin_cannot_update_system_roles()
    {
        $systemRole = Role::where('is_system', true)->first();
        
        $this->assertFalse($this->policy->update($this->adminUser, $systemRole));
    }

    #[Test]
    public function super_admin_cannot_update_system_roles()
    {
        $systemRole = Role::where('is_system', true)->first();
        
        $this->assertFalse($this->policy->update($this->superAdminUser, $systemRole));
    }

    #[Test]
    public function regular_user_cannot_update_roles()
    {
        $role = Role::first();
        
        $this->assertFalse($this->policy->update($this->regularUser, $role));
    }

    #[Test]
    public function super_admin_can_delete_non_system_roles()
    {
        $customRole = Role::create([
            'name' => 'deletable_test_role',
            'display_name' => 'Deletable Test Role',
            'description' => 'A role that can be deleted',
            'color' => '#FF5722',
            'is_system' => false
        ]);
        
        $this->assertTrue($this->policy->delete($this->superAdminUser, $customRole));
    }

    #[Test]
    public function super_admin_cannot_delete_system_roles()
    {
        $systemRole = Role::where('is_system', true)->first();
        
        $this->assertFalse($this->policy->delete($this->superAdminUser, $systemRole));
    }

    #[Test]
    public function admin_cannot_delete_roles()
    {
        $role = Role::where('is_system', false)->first();
        
        $this->assertFalse($this->policy->delete($this->adminUser, $role));
    }

    #[Test]
    public function regular_user_cannot_delete_roles()
    {
        $role = Role::first();
        
        $this->assertFalse($this->policy->delete($this->regularUser, $role));
    }

    #[Test]
    public function super_admin_can_assign_permissions_to_roles()
    {
        $role = Role::where('is_system', false)->first();
        
        $this->assertTrue($this->policy->assignPermissions($this->superAdminUser, $role));
    }

    #[Test]
    public function admin_can_assign_non_restricted_permissions()
    {
        $role = Role::where('is_system', false)->first();
        
        $this->assertTrue($this->policy->assignPermissions($this->adminUser, $role));
    }

    #[Test]
    public function admin_cannot_assign_permissions_to_system_roles()
    {
        $systemRole = Role::where('is_system', true)->first();
        
        $this->assertFalse($this->policy->assignPermissions($this->adminUser, $systemRole));
    }

    #[Test]
    public function regular_user_cannot_assign_permissions()
    {
        $role = Role::first();
        
        $this->assertFalse($this->policy->assignPermissions($this->regularUser, $role));
    }

    #[Test]
    public function admin_cannot_assign_super_admin_permissions()
    {
        // Create a test role
        $testRole = Role::create([
            'name' => 'permission_test_role',
            'display_name' => 'Permission Test Role',
            'description' => 'For testing permission assignment',
            'color' => '#FF5722',
            'is_system' => false
        ]);

        // Get super admin restricted permissions
        $restrictedPermissions = [
            'permissions.create',
            'permissions.delete',
            'roles.create',
            'roles.delete'
        ];

        foreach ($restrictedPermissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            if ($permission) {
                $this->assertFalse(
                    $this->policy->canAssignPermission($this->adminUser, $testRole, $permission),
                    "Admin should not be able to assign {$permissionName}"
                );
            }
        }
    }

    #[Test]
    public function super_admin_can_assign_any_permission()
    {
        $testRole = Role::create([
            'name' => 'super_admin_test_role',
            'display_name' => 'Super Admin Test Role',
            'description' => 'For testing super admin permission assignment',
            'color' => '#FF5722',
            'is_system' => false
        ]);

        $allPermissions = Permission::all();

        foreach ($allPermissions as $permission) {
            $this->assertTrue(
                $this->policy->canAssignPermission($this->superAdminUser, $testRole, $permission),
                "Super admin should be able to assign {$permission->name}"
            );
        }
    }

    #[Test]
    public function policy_respects_user_permission_system()
    {
        // Create a user with specific role permissions
        $userWithRolePermissions = User::factory()->create();
        $role = Role::create([
            'name' => 'test_role_manager',
            'display_name' => 'Test Role Manager',
            'description' => 'Can manage roles but not create/delete',
            'color' => '#FF5722',
            'is_system' => false
        ]);
        
        $viewPermission = Permission::where('name', 'roles.view')->first();
        $updatePermission = Permission::where('name', 'roles.update')->first();
        
        $role->permissions()->attach([$viewPermission->id, $updatePermission->id]);
        $userWithRolePermissions->roles()->attach($role);
        
        // User should be able to view and update roles
        $this->assertTrue($this->policy->viewAny($userWithRolePermissions));
        
        $testRole = Role::where('is_system', false)->first();
        $this->assertTrue($this->policy->update($userWithRolePermissions, $testRole));
        
        // But should not be able to create or delete
        $this->assertFalse($this->policy->create($userWithRolePermissions));
        $this->assertFalse($this->policy->delete($userWithRolePermissions, $testRole));
    }
}

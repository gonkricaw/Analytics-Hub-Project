<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Laravel\Sanctum\Sanctum;

class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $superAdminUser;
    protected $adminUser;
    protected $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(\Database\Seeders\RBACSeeder::class);
        
        $this->superAdminUser = User::where('email', 'superadmin@indonetanalytics.com')->first();
        $this->adminUser = User::where('email', 'admin@indonetanalytics.com')->first();
        $this->regularUser = User::factory()->create();
    }

    #[Test]
    public function super_admin_can_view_all_roles()
    {
        Sanctum::actingAs($this->superAdminUser);

        $response = $this->getJson('/api/admin/roles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'display_name',
                        'description',
                        'color',
                        'is_system',
                        'permissions_count',
                        'users_count',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'current_page',
                'last_page',
                'per_page',
                'total'
            ]);
    }

    #[Test]
    public function admin_can_view_roles_with_proper_authorization()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/admin/roles');

        $response->assertStatus(200);
    }

    #[Test]
    public function unauthorized_user_cannot_view_roles()
    {
        Sanctum::actingAs($this->regularUser);

        $response = $this->getJson('/api/admin/roles');

        $response->assertStatus(403);
    }

    #[Test]
    public function super_admin_can_create_role()
    {
        Sanctum::actingAs($this->superAdminUser);

        $roleData = [
            'name' => 'test_role',
            'display_name' => 'Test Role',
            'description' => 'A test role for feature testing',
            'color' => '#FF5722',
            'is_system' => false
        ];

        $response = $this->postJson('/api/admin/roles', $roleData);

        $response->assertStatus(201)
            ->assertJsonFragment($roleData);

        $this->assertDatabaseHas('idnbi_roles', $roleData);
    }

    #[Test]
    public function admin_can_create_role_with_proper_permissions()
    {
        Sanctum::actingAs($this->adminUser);

        $roleData = [
            'name' => 'admin_created_role',
            'display_name' => 'Admin Created Role',
            'description' => 'Role created by admin',
            'color' => '#2196F3',
            'is_system' => false
        ];

        $response = $this->postJson('/api/admin/roles', $roleData);

        $response->assertStatus(201);
    }

    #[Test]
    public function role_creation_validates_required_fields()
    {
        Sanctum::actingAs($this->superAdminUser);

        $response = $this->postJson('/api/admin/roles', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'display_name', 'description', 'color']);
    }

    #[Test]
    public function role_creation_validates_unique_name()
    {
        Sanctum::actingAs($this->superAdminUser);

        $existingRole = Role::first();

        $roleData = [
            'name' => $existingRole->name,
            'display_name' => 'Duplicate Role',
            'description' => 'Attempting to create duplicate',
            'color' => '#FF5722',
            'is_system' => false
        ];

        $response = $this->postJson('/api/admin/roles', $roleData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function super_admin_can_view_single_role_with_permissions()
    {
        Sanctum::actingAs($this->superAdminUser);

        $role = Role::with('permissions')->first();

        $response = $this->getJson("/api/admin/roles/{$role->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'display_name',
                'description',
                'color',
                'is_system',
                'permissions' => [
                    '*' => [
                        'id',
                        'name',
                        'display_name',
                        'description',
                        'group'
                    ]
                ],
                'created_at',
                'updated_at'
            ]);
    }

    #[Test]
    public function super_admin_can_update_role()
    {
        Sanctum::actingAs($this->superAdminUser);

        $role = Role::create([
            'name' => 'updatable_role',
            'display_name' => 'Updatable Role',
            'description' => 'Original description',
            'color' => '#FF5722',
            'is_system' => false
        ]);

        $updateData = [
            'display_name' => 'Updated Role',
            'description' => 'Updated description',
            'color' => '#2196F3'
        ];

        $response = $this->putJson("/api/admin/roles/{$role->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment($updateData);

        $this->assertDatabaseHas('idnbi_roles', array_merge(
            ['id' => $role->id],
            $updateData
        ));
    }

    #[Test]
    public function system_roles_cannot_be_updated()
    {
        Sanctum::actingAs($this->superAdminUser);

        $systemRole = Role::where('is_system', true)->first();

        $updateData = [
            'display_name' => 'Attempted Update',
            'description' => 'Should not work'
        ];

        $response = $this->putJson("/api/admin/roles/{$systemRole->id}", $updateData);

        $response->assertStatus(403);
    }

    #[Test]
    public function super_admin_can_delete_non_system_role()
    {
        Sanctum::actingAs($this->superAdminUser);

        $role = Role::create([
            'name' => 'deletable_role',
            'display_name' => 'Deletable Role',
            'description' => 'Can be deleted',
            'color' => '#FF5722',
            'is_system' => false
        ]);

        $response = $this->deleteJson("/api/admin/roles/{$role->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('idnbi_roles', ['id' => $role->id]);
    }

    #[Test]
    public function system_roles_cannot_be_deleted()
    {
        Sanctum::actingAs($this->superAdminUser);

        $systemRole = Role::where('is_system', true)->first();

        $response = $this->deleteJson("/api/admin/roles/{$systemRole->id}");

        $response->assertStatus(403);
    }

    #[Test]
    public function super_admin_can_assign_permissions_to_role()
    {
        Sanctum::actingAs($this->superAdminUser);

        $role = Role::create([
            'name' => 'permission_test_role',
            'display_name' => 'Permission Test Role',
            'description' => 'For testing permission assignment',
            'color' => '#FF5722',
            'is_system' => false
        ]);

        $permissions = Permission::take(3)->get();
        $permissionIds = $permissions->pluck('id')->toArray();

        $response = $this->postJson("/api/admin/roles/{$role->id}/permissions", [
            'permission_ids' => $permissionIds
        ]);

        $response->assertStatus(200);

        foreach ($permissions as $permission) {
            $this->assertTrue($role->fresh()->hasPermission($permission->name));
        }
    }

    #[Test]
    public function admin_cannot_assign_super_admin_permissions()
    {
        Sanctum::actingAs($this->adminUser);

        $role = Role::where('name', 'manager')->first();
        $superAdminPermissions = Permission::whereIn('name', [
            'permissions.create',
            'permissions.delete',
            'roles.create'
        ])->get();

        $response = $this->postJson("/api/admin/roles/{$role->id}/permissions", [
            'permission_ids' => $superAdminPermissions->pluck('id')->toArray()
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function role_search_works_correctly()
    {
        Sanctum::actingAs($this->superAdminUser);

        $response = $this->getJson('/api/admin/roles?search=admin');

        $response->assertStatus(200);

        $roles = $response->json('data');
        foreach ($roles as $role) {
            $this->assertTrue(
                str_contains(strtolower($role['name']), 'admin') ||
                str_contains(strtolower($role['display_name']), 'admin') ||
                str_contains(strtolower($role['description']), 'admin')
            );
        }
    }

    #[Test]
    public function role_system_filter_works_correctly()
    {
        Sanctum::actingAs($this->superAdminUser);

        $response = $this->getJson('/api/admin/roles?is_system=true');

        $response->assertStatus(200);

        $roles = $response->json('data');
        foreach ($roles as $role) {
            $this->assertTrue($role['is_system']);
        }
    }

    #[Test]
    public function role_pagination_works_correctly()
    {
        Sanctum::actingAs($this->superAdminUser);

        $response = $this->getJson('/api/admin/roles?per_page=3');

        $response->assertStatus(200)
            ->assertJsonFragment(['per_page' => 3]);

        $this->assertLessThanOrEqual(3, count($response->json('data')));
    }

    #[Test]
    public function unauthenticated_user_cannot_access_roles()
    {
        $response = $this->getJson('/api/admin/roles');

        $response->assertStatus(401);
    }

    #[Test]
    public function role_not_found_returns_404()
    {
        Sanctum::actingAs($this->superAdminUser);

        $response = $this->getJson('/api/admin/roles/99999');

        $response->assertStatus(404);
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Laravel\Sanctum\Sanctum;

class PermissionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $superAdminUser;
    protected $adminUser;
    protected $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(\Database\Seeders\RBACSeeder::class);
        
        // Create test users with different roles
        $this->superAdminUser = User::where('email', 'superadmin@indonetanalytics.com')->first();
        $this->adminUser = User::where('email', 'admin@indonetanalytics.com')->first();
        $this->regularUser = User::factory()->create();
    }

    #[Test]
    public function super_admin_can_view_all_permissions()
    {
        Sanctum::actingAs($this->superAdminUser);

        $response = $this->getJson('/api/admin/permissions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'display_name',
                            'description',
                            'group',
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'current_page',
                    'last_page',
                    'per_page',
                    'total'
                ],
                'message'
            ]);
    }

    #[Test]
    public function admin_can_view_permissions_with_proper_authorization()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/admin/permissions');

        $response->assertStatus(200);
    }

    #[Test]
    public function unauthorized_user_cannot_view_permissions()
    {
        Sanctum::actingAs($this->regularUser);

        $response = $this->getJson('/api/admin/permissions');

        $response->assertStatus(403);
    }

    #[Test]
    public function super_admin_can_create_permission()
    {
        Sanctum::actingAs($this->superAdminUser);

        $permissionData = [
            'name' => 'test.permission',
            'display_name' => 'Test Permission',
            'description' => 'A test permission for feature testing',
            'group' => 'testing'
        ];

        $response = $this->postJson('/api/admin/permissions', $permissionData);

        $response->assertStatus(201)
            ->assertJsonFragment($permissionData);

        $this->assertDatabaseHas('idnbi_permissions', $permissionData);
    }

    #[Test]
    public function admin_cannot_create_permission_without_proper_authorization()
    {
        Sanctum::actingAs($this->adminUser);

        $permissionData = [
            'name' => 'test.permission',
            'display_name' => 'Test Permission',
            'description' => 'A test permission',
            'group' => 'testing'
        ];

        $response = $this->postJson('/api/admin/permissions', $permissionData);

        $response->assertStatus(403);
    }

    #[Test]
    public function permission_creation_validates_required_fields()
    {
        Sanctum::actingAs($this->superAdminUser);

        $response = $this->postJson('/api/admin/permissions', []);        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'display_name', 'group']);
    }

    #[Test]
    public function permission_creation_validates_unique_name()
    {
        Sanctum::actingAs($this->superAdminUser);

        $existingPermission = Permission::first();

        $permissionData = [
            'name' => $existingPermission->name,
            'display_name' => 'Duplicate Permission',
            'description' => 'Attempting to create duplicate',
            'group' => 'testing'
        ];

        $response = $this->postJson('/api/admin/permissions', $permissionData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function super_admin_can_view_single_permission()
    {
        Sanctum::actingAs($this->superAdminUser);

        $permission = Permission::first();

        $response = $this->getJson("/api/admin/permissions/{$permission->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $permission->id,
                'name' => $permission->name,
                'display_name' => $permission->display_name
            ]);
    }

    #[Test]
    public function super_admin_can_update_permission()
    {
        Sanctum::actingAs($this->superAdminUser);

        $permission = Permission::create([
            'name' => 'updatable.permission',
            'display_name' => 'Updatable Permission',
            'description' => 'Original description',
            'group' => 'testing'
        ]);

        $updateData = [
            'display_name' => 'Updated Permission',
            'description' => 'Updated description',
            'group' => 'updated'
        ];

        $response = $this->putJson("/api/admin/permissions/{$permission->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment($updateData);

        $this->assertDatabaseHas('idnbi_permissions', array_merge(
            ['id' => $permission->id],
            $updateData
        ));
    }

    #[Test]
    public function super_admin_can_delete_permission()
    {
        Sanctum::actingAs($this->superAdminUser);

        $permission = Permission::create([
            'name' => 'deletable.permission',
            'display_name' => 'Deletable Permission',
            'description' => 'Can be deleted',
            'group' => 'testing'
        ]);

        $response = $this->deleteJson("/api/admin/permissions/{$permission->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('idnbi_permissions', ['id' => $permission->id]);
    }    #[Test]
    public function permission_search_works_correctly()
    {
        Sanctum::actingAs($this->superAdminUser);

        $response = $this->getJson('/api/admin/permissions?search=users');

        $response->assertStatus(200);

        $permissions = $response->json('data.data');
        foreach ($permissions as $permission) {
            $this->assertTrue(
                str_contains(strtolower($permission['name']), 'users') ||
                str_contains(strtolower($permission['display_name']), 'users') ||
                str_contains(strtolower($permission['description']), 'users')
            );
        }
    }

    #[Test]
    public function permission_group_filter_works_correctly()
    {
        Sanctum::actingAs($this->superAdminUser);

        $response = $this->getJson('/api/admin/permissions?group=users');        $response->assertStatus(200);

        $permissions = $response->json('data.data');
        foreach ($permissions as $permission) {
            $this->assertEquals('users', $permission['group']);
        }
    }

    #[Test]
    public function permission_pagination_works_correctly()
    {
        Sanctum::actingAs($this->superAdminUser);

        $response = $this->getJson('/api/admin/permissions?per_page=5');        $response->assertStatus(200)
            ->assertJsonFragment(['per_page' => 5]);

        $this->assertLessThanOrEqual(5, count($response->json('data.data')));
    }

    #[Test]
    public function unauthenticated_user_cannot_access_permissions()
    {
        $response = $this->getJson('/api/admin/permissions');

        $response->assertStatus(401);
    }

    #[Test]
    public function permission_not_found_returns_404()
    {
        Sanctum::actingAs($this->superAdminUser);

        $response = $this->getJson('/api/admin/permissions/99999');

        $response->assertStatus(404);
    }
}

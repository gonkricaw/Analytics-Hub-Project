<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;

class UserRoleControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $superAdminUser;
    protected $adminUser;
    protected $regularUser;
    protected $testUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(\Database\Seeders\RBACSeeder::class);
        
        $this->superAdminUser = User::where('email', 'superadmin@indonetanalytics.com')->first();
        $this->adminUser = User::where('email', 'admin@indonetanalytics.com')->first();
        $this->regularUser = User::factory()->create();
        $this->testUser = User::factory()->create();
    }

    #[Test]
    public function super_admin_can_view_all_users_with_roles()
    {
        Sanctum::actingAs($this->superAdminUser);

        $response = $this->getJson('/api/admin/user-roles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'avatar',
                        'roles' => [
                            '*' => [
                                'id',
                                'name',
                                'display_name',
                                'color'
                            ]
                        ],
                        'created_at'
                    ]
                ],
                'current_page',
                'last_page',
                'per_page',
                'total'
            ]);
    }

    #[Test]
    public function admin_can_view_users_with_proper_authorization()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/admin/user-roles');

        $response->assertStatus(200);
    }

    #[Test]
    public function unauthorized_user_cannot_view_user_roles()
    {
        Sanctum::actingAs($this->regularUser);

        $response = $this->getJson('/api/admin/user-roles');

        $response->assertStatus(403);
    }

    #[Test]
    public function super_admin_can_get_specific_user_roles()
    {
        Sanctum::actingAs($this->superAdminUser);

        $response = $this->getJson("/api/admin/user-roles/{$this->adminUser->id}/roles");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'display_name',
                        'description',
                        'color',
                        'is_system'
                    ]
                ]
            ]);
    }

    #[Test]
    public function super_admin_can_assign_role_to_user()
    {
        Sanctum::actingAs($this->superAdminUser);

        $managerRole = Role::where('name', 'manager')->first();

        $response = $this->postJson("/api/admin/user-roles/{$this->testUser->id}/assign", [
            'role_id' => $managerRole->id
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Role assigned successfully'
            ]);

        $this->assertTrue($this->testUser->fresh()->hasRole('manager'));
    }

    #[Test]
    public function admin_can_assign_non_super_admin_roles()
    {
        Sanctum::actingAs($this->adminUser);

        $analystRole = Role::where('name', 'analyst')->first();

        $response = $this->postJson("/api/admin/user-roles/{$this->testUser->id}/assign", [
            'role_id' => $analystRole->id
        ]);

        $response->assertStatus(200);
        $this->assertTrue($this->testUser->fresh()->hasRole('analyst'));
    }

    #[Test]
    public function admin_cannot_assign_super_admin_role()
    {
        Sanctum::actingAs($this->adminUser);

        $superAdminRole = Role::where('name', 'super_admin')->first();

        $response = $this->postJson("/api/admin/user-roles/{$this->testUser->id}/assign", [
            'role_id' => $superAdminRole->id
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function admin_cannot_assign_admin_role()
    {
        Sanctum::actingAs($this->adminUser);

        $adminRole = Role::where('name', 'admin')->first();

        $response = $this->postJson("/api/admin/user-roles/{$this->testUser->id}/assign", [
            'role_id' => $adminRole->id
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function role_assignment_validates_required_fields()
    {
        Sanctum::actingAs($this->superAdminUser);

        $response = $this->postJson("/api/admin/user-roles/{$this->testUser->id}/assign", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role_id']);
    }

    #[Test]
    public function role_assignment_validates_role_exists()
    {
        Sanctum::actingAs($this->superAdminUser);

        $response = $this->postJson("/api/admin/user-roles/{$this->testUser->id}/assign", [
            'role_id' => 99999
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role_id']);
    }

    #[Test]
    public function cannot_assign_duplicate_role()
    {
        Sanctum::actingAs($this->superAdminUser);

        $managerRole = Role::where('name', 'manager')->first();
        
        // First assignment should work
        $this->testUser->roles()->attach($managerRole);

        // Second assignment should fail gracefully
        $response = $this->postJson("/api/admin/user-roles/{$this->testUser->id}/assign", [
            'role_id' => $managerRole->id
        ]);

        $response->assertStatus(400)
            ->assertJsonFragment([
                'message' => 'User already has this role'
            ]);
    }

    #[Test]
    public function super_admin_can_remove_role_from_user()
    {
        Sanctum::actingAs($this->superAdminUser);

        $managerRole = Role::where('name', 'manager')->first();
        $this->testUser->roles()->attach($managerRole);

        $response = $this->deleteJson("/api/admin/user-roles/{$this->testUser->id}/remove", [
            'role_id' => $managerRole->id
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Role removed successfully'
            ]);

        $this->assertFalse($this->testUser->fresh()->hasRole('manager'));
    }

    #[Test]
    public function admin_can_remove_non_admin_roles()
    {
        Sanctum::actingAs($this->adminUser);

        $analystRole = Role::where('name', 'analyst')->first();
        $this->testUser->roles()->attach($analystRole);

        $response = $this->deleteJson("/api/admin/user-roles/{$this->testUser->id}/remove", [
            'role_id' => $analystRole->id
        ]);

        $response->assertStatus(200);
        $this->assertFalse($this->testUser->fresh()->hasRole('analyst'));
    }

    #[Test]
    public function admin_cannot_remove_admin_or_super_admin_roles()
    {
        Sanctum::actingAs($this->adminUser);

        $adminRole = Role::where('name', 'admin')->first();

        $response = $this->deleteJson("/api/admin/user-roles/{$this->superAdminUser->id}/remove", [
            'role_id' => $adminRole->id
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function cannot_remove_role_user_does_not_have()
    {
        Sanctum::actingAs($this->superAdminUser);

        $managerRole = Role::where('name', 'manager')->first();

        $response = $this->deleteJson("/api/admin/user-roles/{$this->testUser->id}/remove", [
            'role_id' => $managerRole->id
        ]);

        $response->assertStatus(400)
            ->assertJsonFragment([
                'message' => 'User does not have this role'
            ]);
    }

    #[Test]
    public function super_admin_can_sync_user_roles()
    {
        Sanctum::actingAs($this->superAdminUser);

        $roles = Role::whereIn('name', ['manager', 'analyst'])->get();
        $roleIds = $roles->pluck('id')->toArray();

        $response = $this->putJson("/api/admin/user-roles/{$this->testUser->id}/sync", [
            'role_ids' => $roleIds
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'User roles synchronized successfully'
            ]);

        $this->assertTrue($this->testUser->fresh()->hasRole('manager'));
        $this->assertTrue($this->testUser->fresh()->hasRole('analyst'));
        $this->assertEquals(2, $this->testUser->fresh()->roles->count());
    }

    #[Test]
    public function admin_cannot_sync_restricted_roles()
    {
        Sanctum::actingAs($this->adminUser);

        $superAdminRole = Role::where('name', 'super_admin')->first();
        $adminRole = Role::where('name', 'admin')->first();

        $response = $this->putJson("/api/admin/user-roles/{$this->testUser->id}/sync", [
            'role_ids' => [$superAdminRole->id, $adminRole->id]
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function user_roles_search_works_correctly()
    {
        Sanctum::actingAs($this->superAdminUser);

        $response = $this->getJson('/api/admin/user-roles?search=admin');

        $response->assertStatus(200);

        $users = $response->json('data');
        foreach ($users as $user) {
            $this->assertTrue(
                str_contains(strtolower($user['name']), 'admin') ||
                str_contains(strtolower($user['email']), 'admin')
            );
        }
    }

    #[Test]
    public function user_roles_role_filter_works_correctly()
    {
        Sanctum::actingAs($this->superAdminUser);

        $adminRole = Role::where('name', 'admin')->first();

        $response = $this->getJson("/api/admin/user-roles?role={$adminRole->id}");

        $response->assertStatus(200);

        $users = $response->json('data');
        foreach ($users as $user) {
            $userRoleIds = collect($user['roles'])->pluck('id')->toArray();
            $this->assertContains($adminRole->id, $userRoleIds);
        }
    }

    #[Test]
    public function user_roles_pagination_works_correctly()
    {
        Sanctum::actingAs($this->superAdminUser);

        $response = $this->getJson('/api/admin/user-roles?per_page=2');

        $response->assertStatus(200)
            ->assertJsonFragment(['per_page' => 2]);

        $this->assertLessThanOrEqual(2, count($response->json('data')));
    }

    #[Test]
    public function unauthenticated_user_cannot_access_user_roles()
    {
        $response = $this->getJson('/api/admin/user-roles');

        $response->assertStatus(401);
    }

    #[Test]
    public function user_not_found_returns_404()
    {
        Sanctum::actingAs($this->superAdminUser);

        $response = $this->getJson('/api/admin/user-roles/99999/roles');

        $response->assertStatus(404);
    }
}

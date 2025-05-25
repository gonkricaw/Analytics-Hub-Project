<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RBACSeeder::class);
    }

    #[Test]
    public function permission_can_be_created_with_valid_data()
    {
        $permission = Permission::create([
            'name' => 'test.permission',
            'display_name' => 'Test Permission',
            'description' => 'A test permission for unit testing',
            'group' => 'testing'
        ]);

        $this->assertInstanceOf(Permission::class, $permission);
        $this->assertEquals('test.permission', $permission->name);
        $this->assertEquals('Test Permission', $permission->display_name);
        $this->assertEquals('A test permission for unit testing', $permission->description);
        $this->assertEquals('testing', $permission->group);
        $this->assertNotNull($permission->created_at);
        $this->assertNotNull($permission->updated_at);
    }

    #[Test]
    public function permission_name_must_be_unique()
    {
        Permission::create([
            'name' => 'duplicate.permission',
            'display_name' => 'Duplicate Permission',
            'description' => 'First permission',
            'group' => 'testing'
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Permission::create([
            'name' => 'duplicate.permission',
            'display_name' => 'Another Duplicate Permission',
            'description' => 'Second permission with same name',
            'group' => 'testing'
        ]);
    }

    #[Test]
    public function permission_has_many_roles_relationship()
    {
        $permission = Permission::where('name', 'users.view')->first();
        $role = Role::where('name', 'admin')->first();
        
        // Check if the relationship already exists, if not, attach it
        if (!$role->permissions()->where('permission_id', $permission->id)->exists()) {
            $role->permissions()->attach($permission);
        }
        
        $this->assertTrue($permission->roles()->exists());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $permission->roles);
        $this->assertContains($role->id, $permission->roles->pluck('id'));
    }

    #[Test]
    public function permission_can_be_searched_by_group()
    {
        $userPermissions = Permission::where('group', 'users')->get();
        
        $this->assertGreaterThan(0, $userPermissions->count());
        foreach ($userPermissions as $permission) {
            $this->assertEquals('users', $permission->group);
        }
    }

    #[Test]
    public function permission_fillable_attributes_work_correctly()
    {
        $data = [
            'name' => 'fillable.test',
            'display_name' => 'Fillable Test',
            'description' => 'Testing fillable attributes',
            'group' => 'testing'
        ];

        $permission = new Permission();
        $permission->fill($data);

        $this->assertEquals('fillable.test', $permission->name);
        $this->assertEquals('Fillable Test', $permission->display_name);
        $this->assertEquals('Testing fillable attributes', $permission->description);
        $this->assertEquals('testing', $permission->group);
    }

    #[Test]
    public function permission_timestamps_are_automatically_managed()
    {
        $permission = Permission::create([
            'name' => 'timestamp.test',
            'display_name' => 'Timestamp Test',
            'description' => 'Testing timestamps',
            'group' => 'testing'
        ]);

        $this->assertNotNull($permission->created_at);
        $this->assertNotNull($permission->updated_at);
        $this->assertEquals($permission->created_at, $permission->updated_at);

        $originalUpdatedAt = $permission->updated_at;
        sleep(1);
        
        $permission->update(['description' => 'Updated description']);
        
        $this->assertNotEquals($originalUpdatedAt, $permission->fresh()->updated_at);
    }

    #[Test]
    public function permission_can_be_soft_deleted()
    {
        $permission = Permission::create([
            'name' => 'soft.delete.test',
            'display_name' => 'Soft Delete Test',
            'description' => 'Testing soft delete',
            'group' => 'testing'
        ]);

        $permissionId = $permission->id;
        $permission->delete();

        $this->assertSoftDeleted('idnbi_permissions', ['id' => $permissionId]);
        $this->assertNull(Permission::find($permissionId));
        $this->assertNotNull(Permission::withTrashed()->find($permissionId));
    }
}

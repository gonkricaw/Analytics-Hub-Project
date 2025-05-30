<?php

namespace Tests\Feature;

use App\Models\SystemConfiguration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SystemConfigurationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);

        // Create users
        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
        ]);
        $this->admin->assignRole('admin');

        $this->user = User::factory()->create([
            'email' => 'user@example.com',
        ]);
        $this->user->assignRole('user');

        // Create test configurations
        SystemConfiguration::create([
            'key' => 'test_string_config',
            'value' => 'Test String Value',
            'type' => 'string',
            'description' => 'Test string configuration',
            'is_public' => true,
        ]);

        SystemConfiguration::create([
            'key' => 'test_json_config',
            'value' => ['test' => 'value', 'number' => 123],
            'type' => 'json',
            'description' => 'Test JSON configuration',
            'is_public' => false,
        ]);

        SystemConfiguration::create([
            'key' => 'test_boolean_config',
            'value' => true,
            'type' => 'boolean',
            'description' => 'Test boolean configuration',
            'is_public' => true,
        ]);
    }

    public function test_admin_can_get_all_configurations()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/system-configurations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'key',
                        'value',
                        'type',
                        'description',
                        'is_public',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'message',
            ])
            ->assertJson([
                'success' => true,
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_admin_can_get_grouped_configurations()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/system-configurations/grouped');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'test' => [
                        '*' => [
                            'id',
                            'key',
                            'value',
                            'type',
                            'description',
                            'is_public',
                        ],
                    ],
                ],
                'message',
            ])
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_admin_can_get_specific_configuration()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/system-configurations/test_string_config');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'key',
                    'value',
                    'type',
                    'description',
                    'is_public',
                ],
                'message',
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'key' => 'test_string_config',
                    'value' => 'Test String Value',
                    'type' => 'string',
                ],
            ]);
    }

    public function test_admin_can_create_configuration()
    {
        $configurationData = [
            'key' => 'new_test_config',
            'value' => 'New Test Value',
            'type' => 'string',
            'description' => 'A new test configuration',
            'is_public' => true,
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/system-configurations', $configurationData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'key' => 'new_test_config',
                    'value' => 'New Test Value',
                    'type' => 'string',
                ],
            ]);

        $this->assertDatabaseHas('idnbi_system_configurations', [
            'key' => 'new_test_config',
            'value' => 'New Test Value',
            'type' => 'string',
        ]);
    }

    public function test_admin_can_update_configuration()
    {
        $updateData = [
            'value' => 'Updated Test Value',
            'description' => 'Updated description',
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson('/api/admin/system-configurations/test_string_config', $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'key' => 'test_string_config',
                    'value' => 'Updated Test Value',
                ],
            ]);

        $this->assertDatabaseHas('idnbi_system_configurations', [
            'key' => 'test_string_config',
            'value' => 'Updated Test Value',
        ]);
    }

    public function test_admin_can_delete_configuration()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson('/api/admin/system-configurations/test_string_config');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('idnbi_system_configurations', [
            'key' => 'test_string_config',
        ]);
    }

    public function test_admin_can_bulk_update_configurations()
    {
        $bulkData = [
            'configurations' => [
                [
                    'key' => 'test_string_config',
                    'value' => 'Bulk Updated String',
                ],
                [
                    'key' => 'test_boolean_config',
                    'value' => false,
                ],
            ],
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/system-configurations/bulk-update', $bulkData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('idnbi_system_configurations', [
            'key' => 'test_string_config',
            'value' => 'Bulk Updated String',
        ]);

        $this->assertDatabaseHas('idnbi_system_configurations', [
            'key' => 'test_boolean_config',
            'value' => false,
        ]);
    }

    public function test_admin_can_upload_file_configuration()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test-logo.jpg');

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson('/api/admin/system-configurations/test_string_config', [
                'value' => $file,
                'type' => 'file',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Check that file was stored
        $config = SystemConfiguration::where('key', 'test_string_config')->first();
        Storage::disk('public')->assertExists($config->value);
    }

    public function test_public_configurations_are_accessible_without_authentication()
    {
        $response = $this->getJson('/api/system-configurations/public');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'message',
            ])
            ->assertJson([
                'success' => true,
            ]);

        // Should only include public configurations
        $publicConfigs = $response->json('data');
        $this->assertArrayHasKey('test_string_config', $publicConfigs);
        $this->assertArrayHasKey('test_boolean_config', $publicConfigs);
        $this->assertArrayNotHasKey('test_json_config', $publicConfigs);
    }

    public function test_non_admin_cannot_access_admin_endpoints()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/admin/system-configurations');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_admin_endpoints()
    {
        $response = $this->getJson('/api/admin/system-configurations');

        $response->assertStatus(401);
    }

    public function test_validation_errors_for_invalid_configuration_data()
    {
        $invalidData = [
            'key' => '', // Required
            'value' => '', // Required
            'type' => 'invalid_type', // Invalid type
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/system-configurations', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['key', 'value', 'type']);
    }

    public function test_duplicate_key_validation()
    {
        $duplicateData = [
            'key' => 'test_string_config', // Already exists
            'value' => 'Duplicate Value',
            'type' => 'string',
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/system-configurations', $duplicateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['key']);
    }

    public function test_json_configuration_validation()
    {
        $jsonData = [
            'key' => 'test_json_new',
            'value' => '{"valid": "json"}',
            'type' => 'json',
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/system-configurations', $jsonData);

        $response->assertStatus(201);

        $invalidJsonData = [
            'key' => 'test_json_invalid',
            'value' => 'invalid json',
            'type' => 'json',
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/system-configurations', $invalidJsonData);

        $response->assertStatus(422);
    }

    public function test_file_deletion_when_updating_file_configuration()
    {
        Storage::fake('public');

        // Create a file configuration
        $config = SystemConfiguration::create([
            'key' => 'test_file_config',
            'value' => 'old-file.jpg',
            'type' => 'file',
            'description' => 'Test file configuration',
            'is_public' => true,
        ]);

        // Create the old file
        Storage::disk('public')->put('old-file.jpg', 'old file content');

        $newFile = UploadedFile::fake()->image('new-file.jpg');

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson('/api/admin/system-configurations/test_file_config', [
                'value' => $newFile,
                'type' => 'file',
            ]);

        $response->assertStatus(200);

        // Check that old file was deleted and new file exists
        Storage::disk('public')->assertMissing('old-file.jpg');
        
        $updatedConfig = SystemConfiguration::where('key', 'test_file_config')->first();
        Storage::disk('public')->assertExists($updatedConfig->value);
    }

    public function test_configuration_filtering()
    {
        // Test filtering by type
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/system-configurations?type=string');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        foreach ($data as $config) {
            $this->assertEquals('string', $config['type']);
        }

        // Test filtering by public status
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/system-configurations?is_public=true');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        foreach ($data as $config) {
            $this->assertTrue($config['is_public']);
        }
    }

    public function test_configuration_not_found()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/system-configurations/non_existent_key');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'System configuration not found',
            ]);
    }
}

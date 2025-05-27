<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\EmailTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class EmailTemplateManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user
        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
            'name' => 'Test Admin'
        ]);
        
        Sanctum::actingAs($this->adminUser);
    }

    /** @test */
    public function it_can_list_email_templates()
    {
        // Create some test templates
        EmailTemplate::factory()->count(3)->create([
            'created_by_user_id' => $this->adminUser->id
        ]);

        $response = $this->getJson('/api/admin/email-templates');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => [
                             'id',
                             'name',
                             'type',
                             'subject',
                             'is_active',
                             'created_at'
                         ]
                     ],
                     'total',
                     'per_page',
                     'current_page'
                 ]);
    }

    /** @test */
    public function it_can_create_email_template()
    {
        $templateData = [
            'name' => 'Test Template',
            'type' => EmailTemplate::TYPE_GENERAL,
            'subject' => 'Test Subject {{app_name}}',
            'html_content' => '<h1>Hello {{user_name}}</h1>',
            'text_content' => 'Hello {{user_name}}',
            'description' => 'Test template description',
            'is_active' => true
        ];

        $response = $this->postJson('/api/admin/email-templates', $templateData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'name',
                         'type',
                         'subject',
                         'html_content',
                         'text_content',
                         'is_active'
                     ]
                 ]);

        $this->assertDatabaseHas('idnbi_email_templates', [
            'name' => 'Test Template',
            'type' => EmailTemplate::TYPE_GENERAL,
            'created_by_user_id' => $this->adminUser->id
        ]);
    }

    /** @test */
    public function it_can_update_email_template()
    {
        $template = EmailTemplate::factory()->create([
            'created_by_user_id' => $this->adminUser->id,
            'name' => 'Original Name'
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'subject' => 'Updated Subject'
        ];

        $response = $this->putJson("/api/admin/email-templates/{$template->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('idnbi_email_templates', [
            'id' => $template->id,
            'name' => 'Updated Name',
            'subject' => 'Updated Subject'
        ]);
    }

    /** @test */
    public function it_can_toggle_template_status()
    {
        $template = EmailTemplate::factory()->create([
            'created_by_user_id' => $this->adminUser->id,
            'is_active' => true
        ]);

        $response = $this->postJson("/api/admin/email-templates/{$template->id}/toggle-status");

        $response->assertStatus(200);

        $this->assertDatabaseHas('idnbi_email_templates', [
            'id' => $template->id,
            'is_active' => false
        ]);
    }

    /** @test */
    public function it_can_preview_template_with_sample_data()
    {
        $template = EmailTemplate::factory()->create([
            'created_by_user_id' => $this->adminUser->id,
            'subject' => 'Hello {{user_name}}',
            'html_content' => '<p>Welcome {{user_name}} to {{app_name}}</p>'
        ]);

        $previewData = [
            'sample_data' => [
                'user_name' => 'John Doe',
                'app_name' => 'Test App'
            ]
        ];

        $response = $this->postJson("/api/admin/email-templates/{$template->id}/preview", $previewData);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'subject',
                     'html_content',
                     'text_content'
                 ]);

        $responseData = $response->json();
        $this->assertStringContains('John Doe', $responseData['subject']);
        $this->assertStringContains('John Doe', $responseData['html_content']);
        $this->assertStringContains('Test App', $responseData['html_content']);
    }

    /** @test */
    public function it_can_clone_template()
    {
        $template = EmailTemplate::factory()->create([
            'created_by_user_id' => $this->adminUser->id,
            'name' => 'Original Template'
        ]);

        $response = $this->postJson("/api/admin/email-templates/{$template->id}/clone");

        $response->assertStatus(201);

        $this->assertDatabaseHas('idnbi_email_templates', [
            'name' => 'Original Template (Copy)',
            'type' => $template->type,
            'created_by_user_id' => $this->adminUser->id
        ]);
    }

    /** @test */
    public function it_can_delete_template()
    {
        $template = EmailTemplate::factory()->create([
            'created_by_user_id' => $this->adminUser->id
        ]);

        $response = $this->deleteJson("/api/admin/email-templates/{$template->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('idnbi_email_templates', [
            'id' => $template->id
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_template()
    {
        $response = $this->postJson('/api/admin/email-templates', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'type', 'subject', 'html_content']);
    }

    /** @test */
    public function it_can_get_available_template_types()
    {
        $response = $this->getJson('/api/admin/email-templates/types');

        $response->assertStatus(200)
                 ->assertJson([
                     'invitation' => 'User Invitation',
                     'password_reset' => 'Password Reset',
                     'welcome' => 'Welcome Message',
                     'notification' => 'Notification',
                     'general' => 'General'
                 ]);
    }
}

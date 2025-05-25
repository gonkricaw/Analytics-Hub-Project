<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserInvitationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test admin can invite a new user
     */
    public function test_admin_can_invite_user(): void
    {
        $admin = $this->actingAsAuthenticatedUser();

        $response = $this->postJson('/api/admin/invitations', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'invited_by', 'temporary_password_used'],
                    'temporary_password',
                ]
            ]);

        // Verify user was created
        $this->assertDatabaseHas('idnbi_users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'invited_by' => $admin->id,
            'temporary_password_used' => true,
        ]);
    }

    /**
     * Test invitation validation
     */
    public function test_invitation_validation(): void
    {
        $admin = $this->actingAsAuthenticatedUser();

        $response = $this->postJson('/api/admin/invitations', [
            'name' => '',
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email']);
    }

    /**
     * Test cannot invite user with existing email
     */
    public function test_cannot_invite_user_with_existing_email(): void
    {
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);
        $admin = $this->actingAsAuthenticatedUser();

        $response = $this->postJson('/api/admin/invitations', [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test admin can view all invitations
     */
    public function test_admin_can_view_all_invitations(): void
    {
        $admin = $this->actingAsAuthenticatedUser();
        $invitedUser = User::factory()->create(['invited_by' => $admin->id]);

        $response = $this->getJson('/api/admin/invitations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => ['id', 'name', 'email', 'invited_by']
                    ]
                ]
            ]);
    }

    /**
     * Test admin can view their own invitations
     */
    public function test_admin_can_view_own_invitations(): void
    {
        $admin = $this->actingAsAuthenticatedUser();
        $otherAdmin = User::factory()->create();
        
        $myInvitedUser = User::factory()->create(['invited_by' => $admin->id]);
        $otherInvitedUser = User::factory()->create(['invited_by' => $otherAdmin->id]);

        $response = $this->getJson('/api/admin/invitations/mine');

        $response->assertStatus(200);
        
        $responseData = $response->json();
        $this->assertCount(1, $responseData['data']['data']);
        $this->assertEquals($myInvitedUser->id, $responseData['data']['data'][0]['id']);
    }

    /**
     * Test admin can resend invitation
     */
    public function test_admin_can_resend_invitation(): void
    {
        $admin = $this->actingAsAuthenticatedUser();
        $invitedUser = User::factory()->create([
            'invited_by' => $admin->id,
            'temporary_password_used' => true,
        ]);

        $response = $this->postJson("/api/admin/invitations/{$invitedUser->id}/resend");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Invitation resent successfully.',
            ]);
    }

    /**
     * Test admin cannot resend invitation for user they didn't invite
     */
    public function test_admin_cannot_resend_invitation_for_other_admin_user(): void
    {
        $admin = $this->actingAsAuthenticatedUser();
        $otherAdmin = User::factory()->create();
        $invitedUser = User::factory()->create(['invited_by' => $otherAdmin->id]);

        $response = $this->postJson("/api/admin/invitations/{$invitedUser->id}/resend");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'You can only resend invitations for users you invited.',
            ]);
    }

    /**
     * Test admin can cancel invitation
     */
    public function test_admin_can_cancel_invitation(): void
    {
        $admin = $this->actingAsAuthenticatedUser();
        $invitedUser = User::factory()->create([
            'invited_by' => $admin->id,
            'temporary_password_used' => true,
            'last_active_at' => null,
        ]);

        $response = $this->deleteJson("/api/admin/invitations/{$invitedUser->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Invitation cancelled successfully.',
            ]);

        // Verify user was deleted
        $this->assertDatabaseMissing('idnbi_users', [
            'id' => $invitedUser->id,
        ]);
    }
}

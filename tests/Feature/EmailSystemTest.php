<?php

namespace Tests\Feature;

use App\Mail\UserInvitation;
use App\Mail\PasswordReset;
use App\Mail\WelcomeUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed terms and conditions
        $this->artisan('db:seed', ['--class' => 'TermsAndConditionsSeeder']);
    }

    public function test_it_sends_user_invitation_email()
    {
        Mail::fake();

        // Create admin user
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'last_active_at' => now(),
        ]);

        // Send invitation
        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/admin/invitations', [
                'name' => 'John Doe',
                'email' => 'john@test.com',
            ]);

        $response->assertStatus(201);

        // Assert email was queued (since our mail implements ShouldQueue)
        Mail::assertQueued(UserInvitation::class, function ($mail) {
            return $mail->hasTo('john@test.com');
        });
    }

    public function test_it_sends_password_reset_email()
    {
        Mail::fake();

        // Create user
        $user = User::factory()->create([
            'email' => 'user@test.com',
            'last_active_at' => now(),
        ]);

        // Request password reset
        $response = $this->postJson('/api/forgot-password', [
            'email' => 'user@test.com',
        ]);

        $response->assertStatus(200);

        // Assert email was queued (since our mail implements ShouldQueue)
        Mail::assertQueued(PasswordReset::class, function ($mail) {
            return $mail->hasTo('user@test.com');
        });
    }

    public function test_it_sends_welcome_email_after_password_change()
    {
        Mail::fake();

        // Create user with temporary password
        $user = User::factory()->create([
            'email' => 'user@test.com',
            'password' => Hash::make('temp123'),
            'temporary_password_used' => true,
            'last_active_at' => now(), // Set to prevent inactivity logout
        ]);

        // Change password
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/change-password', [
                'new_password' => 'newpassword123',
                'new_password_confirmation' => 'newpassword123',
                'is_initial_change' => true,
            ]);

        $response->assertStatus(200);

        // Assert welcome email was queued (since our mail implements ShouldQueue)
        Mail::assertQueued(WelcomeUser::class, function ($mail) {
            return $mail->hasTo('user@test.com');
        });
    }

    public function test_it_handles_email_sending_failures_gracefully()
    {
        // Use real mail driver to test error handling
        config(['mail.default' => 'array']);

        // Create admin user
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'last_active_at' => now(),
        ]);

        // Mock mail failure by using invalid configuration
        config(['mail.mailers.smtp.host' => 'invalid-host']);
        config(['mail.default' => 'smtp']);

        // Attempt to send invitation
        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/admin/invitations', [
                'name' => 'John Doe',
                'email' => 'john@test.com',
            ]);

        // Should return error if email fails
        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'Failed to send invitation email. Please try again.',
        ]);

        // User should not be created if email fails
        $this->assertDatabaseMissing('idnbi_users', [
            'email' => 'john@test.com',
        ]);
    }

    public function test_it_does_not_reveal_email_existence_in_password_reset()
    {
        Mail::fake();

        // Request password reset for non-existent email
        $response = $this->postJson('/api/forgot-password', [
            'email' => 'nonexistent@test.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'If an account with that email exists, we have sent password reset instructions.',
        ]);

        // Assert no email was sent
        Mail::assertNothingSent();
    }

    public function test_it_validates_password_reset_token_expiration()
    {
        // Create user
        $user = User::factory()->create([
            'email' => 'user@test.com',
            'last_active_at' => now(),
        ]);

        // Create expired reset token (2 hours ago)
        $expiredTime = now()->subHours(2)->format('Y-m-d H:i:s');
        \DB::table('idnbi_password_reset_tokens')->insert([
            'email' => 'user@test.com',
            'token' => Hash::make('expired-token'),
            'created_at' => $expiredTime,
        ]);

        // Attempt to reset password with expired token
        $response = $this->postJson('/api/reset-password', [
            'token' => 'expired-token',
            'email' => 'user@test.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Reset token has expired. Please request a new one.',
        ]);

        // Token should be deleted
        $this->assertDatabaseMissing('idnbi_password_reset_tokens', [
            'email' => 'user@test.com',
        ]);
    }
}

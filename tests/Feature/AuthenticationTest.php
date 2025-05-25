<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\IpBlock;
use App\Models\FailedLoginAttempt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test successful login
     */
    public function test_successful_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                    'needs_password_change',
                    'needs_terms_acceptance',
                ]
            ]);
    }

    /**
     * Test login with invalid credentials
     */
    public function test_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials.',
            ]);

        // Check that failed attempt was logged
        $this->assertDatabaseHas('idnbi_failed_login_attempts', [
            'email' => 'test@example.com',
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test login validation
     */
    public function test_login_validation(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'invalid-email',
            'password' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * Test blocked IP cannot login
     */
    public function test_blocked_ip_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Block the current IP
        IpBlock::blockIp('127.0.0.1', 'Test block');

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(423)
            ->assertJson([
                'success' => false,
                'message' => 'Your IP address has been blocked due to suspicious activity. Please contact the administrator.',
            ]);
    }

    /**
     * Test successful logout
     */
    public function test_successful_logout(): void
    {
        $user = $this->actingAsAuthenticatedUser();

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully.',
            ]);
    }

    /**
     * Test password change
     */
    public function test_password_change(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);
        
        $this->actingAsAuthenticatedUser($user);

        $response = $this->postJson('/api/change-password', [
            'current_password' => 'old-password',
            'new_password' => 'new-password',
            'new_password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Password changed successfully.',
            ]);

        // Verify password was actually changed
        $user->refresh();
        $this->assertTrue(Hash::check('new-password', $user->password));
    }

    /**
     * Test initial password change (temporary password)
     */
    public function test_initial_password_change(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('temp-password'),
            'temporary_password_used' => true,
        ]);
        
        $this->actingAsAuthenticatedUser($user);

        $response = $this->postJson('/api/change-password', [
            'is_initial_change' => true,
            'new_password' => 'new-password',
            'new_password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(200);

        // Verify temporary password flag was cleared
        $user->refresh();
        $this->assertFalse($user->temporary_password_used);
    }

    /**
     * Test user profile update
     */
    public function test_user_profile_update(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);
        
        $this->actingAsAuthenticatedUser($user);

        $response = $this->postJson('/api/update-profile', [
            'name' => 'New Name',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully.',
            ]);

        // Verify name was updated
        $user->refresh();
        $this->assertEquals('New Name', $user->name);
    }
}

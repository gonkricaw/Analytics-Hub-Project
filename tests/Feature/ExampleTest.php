<?php

namespace Tests\Feature;

use App\Models\TermsAndConditions;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // Create a user and terms to ensure data exists
        $user = User::factory()->create();
        TermsAndConditions::create([
            'content' => 'Test terms and conditions content',
            'version' => '1.0',
            'is_active' => true,
            'created_by' => $user->id,
        ]);
        
        // Test the terms-and-conditions API endpoint
        $response = $this->get('/api/terms-and-conditions/current');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }
}

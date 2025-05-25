<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Temporarily disable CheckUserActivity middleware for tests
        $this->withoutMiddleware([
            \App\Http\Middleware\CheckUserActivity::class,
        ]);
    }

    protected function actingAsAuthenticatedUser($user = null)
    {
        if (!$user) {
            $user = \App\Models\User::factory()->create();
        }
        
        Sanctum::actingAs($user, ['*']);
        
        return $user;
    }
}

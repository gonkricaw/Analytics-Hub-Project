<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed RBAC system first
        $this->call(RBACSeeder::class);

        // Create additional test users if needed
        // User::factory(10)->create();
    }
}

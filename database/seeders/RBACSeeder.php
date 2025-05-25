<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class RBACSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting RBAC setup...');

        // First, seed permissions
        $this->call(PermissionsSeeder::class);

        // Then, seed roles and assign permissions
        $this->call(RolesSeeder::class);

        // Create default admin users if they don't exist
        $this->createDefaultUsers();

        $this->command->info('RBAC setup completed successfully!');
    }

    /**
     * Create default admin users for the system
     */
    private function createDefaultUsers(): void
    {
        $defaultUsers = [
            [
                'name' => 'Super Administrator',
                'email' => 'superadmin@indonetanalytics.com',
                'password' => Hash::make('SuperAdmin@2025'),
                'role' => 'super_admin',
                'email_verified_at' => now(),
                'terms_accepted_at' => now(),
            ],
            [
                'name' => 'System Administrator',
                'email' => 'admin@indonetanalytics.com',
                'password' => Hash::make('Admin@2025'),
                'role' => 'admin',
                'email_verified_at' => now(),
                'terms_accepted_at' => now(),
            ],
            [
                'name' => 'Data Manager',
                'email' => 'manager@indonetanalytics.com',
                'password' => Hash::make('Manager@2025'),
                'role' => 'manager',
                'email_verified_at' => now(),
                'terms_accepted_at' => now(),
            ]
        ];

        foreach ($defaultUsers as $userData) {
            $role = Role::where('name', $userData['role'])->first();
            
            if (!$role) {
                $this->command->error("Role '{$userData['role']}' not found!");
                continue;
            }

            unset($userData['role']);

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            // Assign role to user
            if (!$user->hasRole($role->name)) {
                $user->roles()->attach($role->id);
                $this->command->info("User '{$user->name}' created/updated with role '{$role->display_name}'");
            } else {
                $this->command->info("User '{$user->name}' already has role '{$role->display_name}'");
            }
        }

        $this->command->line('');
        $this->command->info('Default user credentials:');
        $this->command->line('Super Admin: superadmin@indonetanalytics.com / SuperAdmin@2025');
        $this->command->line('Admin: admin@indonetanalytics.com / Admin@2025');
        $this->command->line('Manager: manager@indonetanalytics.com / Manager@2025');
        $this->command->line('');
        $this->command->warn('Please change these passwords after first login!');
    }
}

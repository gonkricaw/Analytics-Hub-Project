<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

/**
 * SuperAdminSeeder Class
 * 
 * Creates a Super Administrator user with full system access.
 * This seeder creates/updates a Super Admin user and role with all available permissions.
 * 
 * Usage: php artisan db:seed --class=SuperAdminSeeder
 * 
 * @author AI Code Assistant
 * @version 1.0
 * @created 2025-05-29
 */
class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This method is idempotent and can be run multiple times without causing duplicates.
     * It will create or update the Super Admin user and role with all permissions.
     */
    public function run(): void
    {
        $this->command->info('Starting Super Admin setup...');

        // Step 1: Create or update the Super Admin role
        $superAdminRole = $this->createSuperAdminRole();

        // Step 2: Assign all permissions to the Super Admin role
        $this->assignAllPermissionsToRole($superAdminRole);

        // Step 3: Create or update the Super Admin user
        $superAdminUser = $this->createSuperAdminUser();

        // Step 4: Assign the Super Admin role to the user
        $this->assignRoleToUser($superAdminUser, $superAdminRole);

        $this->command->info('Super Admin setup completed successfully!');
        $this->displayCredentials();
    }

    /**
     * Create or update the Super Admin role.
     * 
     * @return Role The Super Admin role instance
     */
    private function createSuperAdminRole(): Role
    {
        $role = Role::updateOrCreate(
            ['name' => 'Super Admin'],
            [
                'display_name' => 'Super Administrator',
                'description' => 'Super Administrator with full system access and all permissions',
                'color' => '#DC2626',
                'is_system' => true,
            ]
        );

        $this->command->info("Super Admin role '{$role->display_name}' created/updated successfully.");
        
        return $role;
    }

    /**
     * Assign all existing permissions to the Super Admin role.
     * 
     * @param Role $role The Super Admin role
     * @return void
     */
    private function assignAllPermissionsToRole(Role $role): void
    {
        // Fetch all existing permissions from the database
        $allPermissions = Permission::all();
        
        if ($allPermissions->isEmpty()) {
            $this->command->warn('No permissions found in the database. Consider running PermissionsSeeder first.');
            return;
        }

        // Extract permission IDs for sync operation
        $allPermissionIds = $allPermissions->pluck('id')->toArray();

        // Sync all permissions to the Super Admin role
        // Using sync() ensures idempotency - it will remove old permissions and add new ones
        $role->permissions()->sync($allPermissionIds);

        $this->command->info("Assigned {$allPermissions->count()} permissions to Super Admin role.");
    }

    /**
     * Create or update the Super Admin user.
     * 
     * @return User The Super Admin user instance
     */
    private function createSuperAdminUser(): User
    {
        $user = User::updateOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('SuperSecret123!'),
                'email_verified_at' => now(),
                'terms_accepted_at' => now(),
                'terms_accepted_version' => '1.0',
            ]
        );

        $this->command->info("Super Admin user '{$user->name}' created/updated successfully.");
        
        return $user;
    }

    /**
     * Assign the Super Admin role to the user.
     * 
     * @param User $user The Super Admin user
     * @param Role $role The Super Admin role
     * @return void
     */
    private function assignRoleToUser(User $user, Role $role): void
    {
        // Check if user already has the Super Admin role
        if ($user->hasRole($role->name)) {
            $this->command->info("User '{$user->name}' already has the '{$role->display_name}' role.");
            return;
        }

        // Sync the Super Admin role to the user (replaces any existing roles)
        // This ensures the Super Admin user only has the Super Admin role
        $user->roles()->sync([$role->id]);

        $this->command->info("Assigned '{$role->display_name}' role to user '{$user->name}'.");
    }

    /**
     * Display the Super Admin credentials for reference.
     * 
     * @return void
     */
    private function displayCredentials(): void
    {
        $this->command->line('');
        $this->command->line('=== SUPER ADMIN CREDENTIALS ===');
        $this->command->info('Email: superadmin@example.com');
        $this->command->info('Password: SuperSecret123!');
        $this->command->line('================================');
        $this->command->line('');
        $this->command->warn('🚨 SECURITY WARNING: Please change this password immediately after first login in production environments!');
        $this->command->warn('🔒 This password is intended for development and initial setup only.');
        $this->command->line('');
    }
}

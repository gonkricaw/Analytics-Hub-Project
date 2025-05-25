<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define roles with their permissions
        $rolesData = [
            'super_admin' => [
                'display_name' => 'Super Administrator',
                'description' => 'Full system access with all permissions',
                'color' => '#DC2626',
                'is_system' => true,
                'permissions' => 'all' // Will get all permissions
            ],
            'admin' => [
                'display_name' => 'Administrator',
                'description' => 'Administrator with user and content management access',
                'color' => '#EA580C',
                'is_system' => false,
                'permissions' => [
                    'users.view', 'users.create', 'users.update', 'users.manage',
                    'roles.view', 'roles.create', 'roles.update', 'roles.assign_permissions',
                    'permissions.view',
                    'user_roles.view', 'user_roles.assign', 'user_roles.remove',
                    'analytics.view', 'analytics.create', 'analytics.update', 'analytics.delete', 'analytics.export',
                    'data.view', 'data.import', 'data.export',
                    'admin.view', 'admin.logs',
                    'terms.view', 'terms.manage',
                    'ip_blocks.view', 'ip_blocks.manage',
                    'invitations.view', 'invitations.send', 'invitations.manage'
                ]
            ],
            'manager' => [
                'display_name' => 'Manager',
                'description' => 'Manager with analytics and data access',
                'color' => '#D97706',
                'is_system' => false,
                'permissions' => [
                    'users.view',
                    'analytics.view', 'analytics.create', 'analytics.update', 'analytics.export',
                    'data.view', 'data.export',
                    'admin.view',
                    'terms.view'
                ]
            ],
            'analyst' => [
                'display_name' => 'Data Analyst',
                'description' => 'Data analyst with analytics viewing and basic data access',
                'color' => '#059669',
                'is_system' => false,
                'permissions' => [
                    'analytics.view', 'analytics.create', 'analytics.export',
                    'data.view', 'data.export',
                    'terms.view'
                ]
            ],
            'viewer' => [
                'display_name' => 'Viewer',
                'description' => 'Read-only access to analytics and basic data',
                'color' => '#0284C7',
                'is_system' => false,
                'permissions' => [
                    'analytics.view',
                    'data.view',
                    'terms.view'
                ]
            ],
            'user' => [
                'display_name' => 'Regular User',
                'description' => 'Basic user with limited analytics access',
                'color' => '#6366F1',
                'is_system' => false,
                'permissions' => [
                    'analytics.view',
                    'terms.view'
                ]
            ]
        ];

        foreach ($rolesData as $roleName => $roleData) {
            // Create or find the role
            $role = Role::firstOrCreate(
                ['name' => $roleName],
                [
                    'display_name' => $roleData['display_name'],
                    'description' => $roleData['description'],
                    'color' => $roleData['color'],
                    'is_system' => $roleData['is_system']
                ]
            );

            // Assign permissions to role
            if ($roleData['permissions'] === 'all') {
                // Super admin gets all permissions
                $permissions = Permission::all();
                $role->permissions()->sync($permissions->pluck('id'));
            } else {
                // Other roles get specific permissions
                $permissions = Permission::whereIn('name', $roleData['permissions'])->get();
                $role->permissions()->sync($permissions->pluck('id'));
            }

            $this->command->info("Role '{$role->display_name}' created/updated with " . count($role->permissions) . " permissions.");
        }

        $this->command->info('Roles seeded successfully!');
    }
}

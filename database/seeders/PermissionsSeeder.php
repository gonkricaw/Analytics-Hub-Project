<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // User Management Permissions
            ['name' => 'users.view', 'display_name' => 'View Users', 'description' => 'View user information and listings', 'group' => 'users'],
            ['name' => 'users.create', 'display_name' => 'Create Users', 'description' => 'Create new user accounts', 'group' => 'users'],
            ['name' => 'users.update', 'display_name' => 'Update Users', 'description' => 'Update user information', 'group' => 'users'],
            ['name' => 'users.delete', 'display_name' => 'Delete Users', 'description' => 'Delete user accounts', 'group' => 'users'],
            ['name' => 'users.manage', 'display_name' => 'Manage Users', 'description' => 'Full user management capabilities', 'group' => 'users'],

            // Role Management Permissions
            ['name' => 'roles.view', 'display_name' => 'View Roles', 'description' => 'View role information and listings', 'group' => 'roles'],
            ['name' => 'roles.create', 'display_name' => 'Create Roles', 'description' => 'Create new roles', 'group' => 'roles'],
            ['name' => 'roles.update', 'display_name' => 'Update Roles', 'description' => 'Update role information', 'group' => 'roles'],
            ['name' => 'roles.delete', 'display_name' => 'Delete Roles', 'description' => 'Delete roles', 'group' => 'roles'],
            ['name' => 'roles.restore', 'display_name' => 'Restore Roles', 'description' => 'Restore deleted roles', 'group' => 'roles'],
            ['name' => 'roles.force_delete', 'display_name' => 'Force Delete Roles', 'description' => 'Permanently delete roles', 'group' => 'roles'],
            ['name' => 'roles.assign_permissions', 'display_name' => 'Assign Permissions to Roles', 'description' => 'Assign permissions to roles', 'group' => 'roles'],

            // Permission Management Permissions
            ['name' => 'permissions.view', 'display_name' => 'View Permissions', 'description' => 'View permission information and listings', 'group' => 'permissions'],
            ['name' => 'permissions.create', 'display_name' => 'Create Permissions', 'description' => 'Create new permissions', 'group' => 'permissions'],
            ['name' => 'permissions.update', 'display_name' => 'Update Permissions', 'description' => 'Update permission information', 'group' => 'permissions'],
            ['name' => 'permissions.delete', 'display_name' => 'Delete Permissions', 'description' => 'Delete permissions', 'group' => 'permissions'],
            ['name' => 'permissions.restore', 'display_name' => 'Restore Permissions', 'description' => 'Restore deleted permissions', 'group' => 'permissions'],
            ['name' => 'permissions.force_delete', 'display_name' => 'Force Delete Permissions', 'description' => 'Permanently delete permissions', 'group' => 'permissions'],

            // User Role Management Permissions
            ['name' => 'user_roles.view', 'display_name' => 'View User Roles', 'description' => 'View user role assignments', 'group' => 'user_roles'],
            ['name' => 'user_roles.assign', 'display_name' => 'Assign User Roles', 'description' => 'Assign roles to users', 'group' => 'user_roles'],
            ['name' => 'user_roles.remove', 'display_name' => 'Remove User Roles', 'description' => 'Remove roles from users', 'group' => 'user_roles'],

            // Analytics Hub Specific Permissions
            ['name' => 'analytics.view', 'display_name' => 'View Analytics', 'description' => 'View analytics dashboards and reports', 'group' => 'analytics'],
            ['name' => 'analytics.create', 'display_name' => 'Create Analytics', 'description' => 'Create analytics reports and dashboards', 'group' => 'analytics'],
            ['name' => 'analytics.update', 'display_name' => 'Update Analytics', 'description' => 'Update analytics reports and dashboards', 'group' => 'analytics'],
            ['name' => 'analytics.delete', 'display_name' => 'Delete Analytics', 'description' => 'Delete analytics reports and dashboards', 'group' => 'analytics'],
            ['name' => 'analytics.export', 'display_name' => 'Export Analytics', 'description' => 'Export analytics data and reports', 'group' => 'analytics'],

            // Data Management Permissions
            ['name' => 'data.view', 'display_name' => 'View Data', 'description' => 'View raw data and datasets', 'group' => 'data'],
            ['name' => 'data.import', 'display_name' => 'Import Data', 'description' => 'Import data into the system', 'group' => 'data'],
            ['name' => 'data.export', 'display_name' => 'Export Data', 'description' => 'Export data from the system', 'group' => 'data'],
            ['name' => 'data.manage', 'display_name' => 'Manage Data', 'description' => 'Full data management capabilities', 'group' => 'data'],

            // System Administration Permissions
            ['name' => 'admin.view', 'display_name' => 'Admin Panel Access', 'description' => 'Access to admin panel', 'group' => 'admin'],
            ['name' => 'admin.settings', 'display_name' => 'System Settings', 'description' => 'Manage system settings and configuration', 'group' => 'admin'],
            ['name' => 'admin.logs', 'display_name' => 'View System Logs', 'description' => 'View system logs and audit trails', 'group' => 'admin'],
            ['name' => 'admin.maintenance', 'display_name' => 'System Maintenance', 'description' => 'Perform system maintenance tasks', 'group' => 'admin'],

            // Terms and Conditions Management
            ['name' => 'terms.view', 'display_name' => 'View Terms', 'description' => 'View terms and conditions', 'group' => 'terms'],
            ['name' => 'terms.manage', 'display_name' => 'Manage Terms', 'description' => 'Manage terms and conditions', 'group' => 'terms'],

            // IP Block Management
            ['name' => 'ip_blocks.view', 'display_name' => 'View IP Blocks', 'description' => 'View IP block information', 'group' => 'security'],
            ['name' => 'ip_blocks.manage', 'display_name' => 'Manage IP Blocks', 'description' => 'Manage IP blocks and security settings', 'group' => 'security'],

            // Invitation Management
            ['name' => 'invitations.view', 'display_name' => 'View Invitations', 'description' => 'View user invitations', 'group' => 'invitations'],
            ['name' => 'invitations.send', 'display_name' => 'Send Invitations', 'description' => 'Send user invitations', 'group' => 'invitations'],
            ['name' => 'invitations.manage', 'display_name' => 'Manage Invitations', 'description' => 'Full invitation management', 'group' => 'invitations'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        $this->command->info('Permissions seeded successfully!');
    }
}

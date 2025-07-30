<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Core\app\Models\Admin;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles first
        $superAdminRole = Role::findOrCreate('Super Admin', 'admin');
        $adminRole = Role::findOrCreate('Admin', 'admin');

        // Auto-discover and create permissions from Filament resources
        $this->createPermissionsFromResources();

        // Get all existing permissions
        $allPermissions = Permission::where('guard_name', 'admin')->pluck('name')->toArray();

        // Assign all permissions to Super Admin
        $superAdminRole->syncPermissions($allPermissions);

        // Assign limited permissions to Admin (just admin management)
        $adminPermissions = [];
        foreach ($allPermissions as $permission) {
            if (str_contains($permission, '_admin') || str_contains($permission, 'access_')) {
                $adminPermissions[] = $permission;
            }
        }
        $adminRole->syncPermissions($adminPermissions);

        // Create default Super Admin user
        $admin = Admin::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('password'),
            ]
        );

        // Assign Super Admin role (check if not already assigned)
        if (!$admin->hasRole('Super Admin')) {
            $admin->assignRole('Super Admin');
        }

        // Create a regular admin user
        $regularAdmin = Admin::firstOrCreate(
            ['email' => 'user@admin.com'],
            [
                'name' => 'Admin User',
                'email' => 'user@admin.com',
                'password' => Hash::make('password'),
            ]
        );

        // Assign Admin role (check if not already assigned)
        if (!$regularAdmin->hasRole('Admin')) {
            $regularAdmin->assignRole('Admin');
        }
    }

    /**
     * Auto-discover and create permissions from Filament resources
     */
    private function createPermissionsFromResources(): void
    {
        $this->command->info('🔍 Auto-discovering permissions from Filament resources...');

        // Standard CRUD permissions
        $crudActions = [
            'view_any',
            'view',
            'create',
            'update',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
            'restore',
            'restore_any',
            'replicate',
        ];

        // Define all known resources and their permissions
        $resources = [
            'admin' => $crudActions,
            'user' => $crudActions,
            'module_role' => $crudActions,
            'project' => $crudActions,
            'project_team_member' => $crudActions,
            'project_workspace_content' => $crudActions,
            'project_module' => $crudActions,
            'application_template' => $crudActions,
        ];

        // Special permissions for modules and AI platform
        $specialPermissions = [
            'view_module_editor',
            'create_modules',
            'generate_modules',
            'manage_module_builder',
            'access_ai_platform',
            'view_application_templates',
            'create_projects_from_templates',
            'generate_modules_from_templates',
            'access_project_workspace',
            'manage_project_teams',
            'approve_workspace_content',
            'access_product_owner_workspace',
            'access_database_backend_workspace',
            'access_project_manager_workspace',
            'install_modules',
            'manage_generated_modules',
            'view_ai_platform_dashboard',
        ];

        // Create CRUD permissions for all resources
        foreach ($resources as $resource => $actions) {
            foreach ($actions as $action) {
                $permissionName = "{$action}_{$resource}";
                try {
                    Permission::firstOrCreate([
                        'name' => $permissionName,
                        'guard_name' => 'admin'
                    ]);
                } catch (\Exception $e) {
                    // Skip if permission already exists or there's an error
                }
            }
        }

        // Create special permissions
        foreach ($specialPermissions as $permissionName) {
            try {
                Permission::firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => 'admin'
                ]);
            } catch (\Exception $e) {
                // Skip if permission already exists or there's an error
            }
        }

        $this->command->info('✅ Permissions auto-discovery completed!');
    }
}

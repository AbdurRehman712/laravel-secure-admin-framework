<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Modules\Core\app\Models\Admin;

class AiPlatformPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create AI Platform permissions
        $permissions = [
            'access_ai_platform' => 'Access AI Development Platform',
            'view_application_templates' => 'View Application Templates',
            'create_projects_from_templates' => 'Create Projects from Templates',
            'generate_modules_from_templates' => 'Generate Modules from Templates',
            'access_project_workspace' => 'Access Project Workspace',
            'manage_project_teams' => 'Manage Project Teams',
            'approve_workspace_content' => 'Approve Workspace Content',
            'generate_modules' => 'Generate Laravel Modules',
            'view_ai_platform_dashboard' => 'View AI Platform Dashboard',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'admin'
            ]);
        }

        // Create or update AI Platform Admin role
        $aiPlatformAdminRole = Role::firstOrCreate([
            'name' => 'ai_platform_admin',
            'guard_name' => 'admin'
        ]);

        // Assign all AI platform permissions to the role
        $aiPlatformAdminRole->syncPermissions(array_keys($permissions));

        // Create or update Project Manager role
        $projectManagerRole = Role::firstOrCreate([
            'name' => 'project_manager',
            'guard_name' => 'admin'
        ]);

        // Assign specific permissions to project manager
        $projectManagerPermissions = [
            'access_ai_platform',
            'view_application_templates',
            'create_projects_from_templates',
            'access_project_workspace',
            'manage_project_teams',
            'view_ai_platform_dashboard',
        ];
        $projectManagerRole->syncPermissions($projectManagerPermissions);

        // Assign AI Platform Admin role to super admin
        $superAdmin = Admin::where('email', 'admin@admin.com')->first();
        if ($superAdmin) {
            $superAdmin->assignRole('ai_platform_admin');
            // Also directly assign the permission to ensure it works
            $superAdmin->givePermissionTo('access_ai_platform');
            echo "✅ AI Platform Admin role assigned to super admin\n";
        }

        // Also assign to any existing super_admin role users
        $superAdminRole = Role::where('name', 'super_admin')->where('guard_name', 'admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo(array_keys($permissions));
            echo "✅ AI Platform permissions added to super_admin role\n";
        }

        // Ensure Super Admin role has all permissions
        $superAdminRole = Role::where('name', 'Super Admin')->where('guard_name', 'admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo(array_keys($permissions));
            echo "✅ AI Platform permissions added to Super Admin role\n";
        }

        echo "🎉 AI Platform permissions seeded successfully!\n";
        echo "📋 Created " . count($permissions) . " permissions\n";
        echo "👥 Created/updated 2 roles\n";
    }
}

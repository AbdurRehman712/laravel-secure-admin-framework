<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Modules\Core\app\Models\Admin;

class ProjectRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create AI Platform permissions
        $permissions = [
            // Project permissions
            'view_projects',
            'create_projects',
            'edit_projects',
            'delete_projects',
            'manage_project_teams',
            
            // Workspace permissions
            'access_project_workspace',
            'create_workspace_content',
            'edit_workspace_content',
            'delete_workspace_content',
            'approve_workspace_content',
            
            // Role-specific permissions
            'access_product_owner_workspace',
            'access_designer_workspace',
            'access_database_admin_workspace',
            'access_frontend_developer_workspace',
            'access_backend_developer_workspace',
            'access_devops_workspace',
            
            // Module generation permissions
            'generate_modules',
            'install_modules',
            'manage_generated_modules',
            
            // AI Platform dashboard
            'view_ai_platform_dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'admin'
            ]);
        }

        // Create AI Platform roles
        $roles = [
            'ai_platform_admin' => [
                'display_name' => 'AI Platform Administrator',
                'description' => 'Full access to AI Development Platform',
                'permissions' => $permissions, // All permissions
            ],
            'project_manager' => [
                'display_name' => 'Project Manager',
                'description' => 'Can manage projects and teams',
                'permissions' => [
                    'view_projects',
                    'create_projects',
                    'edit_projects',
                    'manage_project_teams',
                    'access_project_workspace',
                    'view_ai_platform_dashboard',
                    'approve_workspace_content',
                ],
            ],
            'product_owner' => [
                'display_name' => 'Product Owner',
                'description' => 'Defines requirements and user stories',
                'permissions' => [
                    'view_projects',
                    'access_project_workspace',
                    'access_product_owner_workspace',
                    'create_workspace_content',
                    'edit_workspace_content',
                ],
            ],
            'designer' => [
                'display_name' => 'Designer',
                'description' => 'Creates UI/UX designs and wireframes',
                'permissions' => [
                    'view_projects',
                    'access_project_workspace',
                    'access_designer_workspace',
                    'create_workspace_content',
                    'edit_workspace_content',
                ],
            ],
            'database_admin' => [
                'display_name' => 'Database Administrator',
                'description' => 'Designs database schemas and structures',
                'permissions' => [
                    'view_projects',
                    'access_project_workspace',
                    'access_database_admin_workspace',
                    'create_workspace_content',
                    'edit_workspace_content',
                ],
            ],
            'frontend_developer' => [
                'display_name' => 'Frontend Developer',
                'description' => 'Develops user interfaces and components',
                'permissions' => [
                    'view_projects',
                    'access_project_workspace',
                    'access_frontend_developer_workspace',
                    'create_workspace_content',
                    'edit_workspace_content',
                    'generate_modules',
                ],
            ],
            'backend_developer' => [
                'display_name' => 'Backend Developer',
                'description' => 'Develops server-side logic and APIs',
                'permissions' => [
                    'view_projects',
                    'access_project_workspace',
                    'access_backend_developer_workspace',
                    'create_workspace_content',
                    'edit_workspace_content',
                    'generate_modules',
                    'install_modules',
                ],
            ],
            'devops_engineer' => [
                'display_name' => 'DevOps Engineer',
                'description' => 'Manages deployment and infrastructure',
                'permissions' => [
                    'view_projects',
                    'access_project_workspace',
                    'access_devops_workspace',
                    'create_workspace_content',
                    'edit_workspace_content',
                    'install_modules',
                    'manage_generated_modules',
                ],
            ],
        ];

        foreach ($roles as $roleName => $roleData) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'admin'
            ]);

            // Sync permissions
            $role->syncPermissions($roleData['permissions']);

            $this->command->info("Created role: {$roleData['display_name']}");
        }

        // Assign AI Platform Admin role to Super Admin
        $superAdmin = Admin::where('email', 'admin@admin.com')->first();
        if ($superAdmin) {
            $superAdmin->assignRole('ai_platform_admin');
            $this->command->info("Assigned AI Platform Admin role to Super Admin");
        }

        // Create sample team members for testing
        $this->createSampleTeamMembers();

        $this->command->info('Project roles seeder completed successfully!');
    }

    private function createSampleTeamMembers(): void
    {
        $teamMembers = [
            [
                'name' => 'John Product',
                'email' => 'product.owner@test.com',
                'role' => 'product_owner',
            ],
            [
                'name' => 'Jane Designer',
                'email' => 'designer@test.com',
                'role' => 'designer',
            ],
            [
                'name' => 'Bob Database',
                'email' => 'database.admin@test.com',
                'role' => 'database_admin',
            ],
            [
                'name' => 'Alice Frontend',
                'email' => 'frontend.dev@test.com',
                'role' => 'frontend_developer',
            ],
            [
                'name' => 'Charlie Backend',
                'email' => 'backend.dev@test.com',
                'role' => 'backend_developer',
            ],
            [
                'name' => 'David DevOps',
                'email' => 'devops@test.com',
                'role' => 'devops_engineer',
            ],
        ];

        foreach ($teamMembers as $memberData) {
            $admin = Admin::firstOrCreate([
                'email' => $memberData['email']
            ], [
                'name' => $memberData['name'],
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);

            $admin->assignRole($memberData['role']);
            $this->command->info("Created team member: {$memberData['name']} ({$memberData['role']})");
        }
    }
}

<?php

namespace Modules\ERDDesigner\database\seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ERDDesignerPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create ERD Designer permissions
        $permissions = [
            // ERD Project permissions
            'view_erd_projects' => 'View ERD Projects',
            'create_erd_projects' => 'Create ERD Projects',
            'edit_erd_projects' => 'Edit ERD Projects',
            'delete_erd_projects' => 'Delete ERD Projects',
            'export_erd_projects' => 'Export ERD Projects',
            'import_erd_projects' => 'Import ERD Projects',
            
            // ERD Table permissions
            'view_erd_tables' => 'View ERD Tables',
            'create_erd_tables' => 'Create ERD Tables',
            'edit_erd_tables' => 'Edit ERD Tables',
            'delete_erd_tables' => 'Delete ERD Tables',
            
            // ERD Field permissions
            'view_erd_fields' => 'View ERD Fields',
            'create_erd_fields' => 'Create ERD Fields',
            'edit_erd_fields' => 'Edit ERD Fields',
            'delete_erd_fields' => 'Delete ERD Fields',
            
            // ERD Relationship permissions
            'view_erd_relationships' => 'View ERD Relationships',
            'create_erd_relationships' => 'Create ERD Relationships',
            'edit_erd_relationships' => 'Edit ERD Relationships',
            'delete_erd_relationships' => 'Delete ERD Relationships',
            
            // Advanced permissions
            'manage_erd_versions' => 'Manage ERD Project Versions',
            'share_erd_projects' => 'Share ERD Projects',
            'access_erd_designer' => 'Access ERD Designer',
            'export_sql_from_erd' => 'Export SQL from ERD',
            'import_sql_to_erd' => 'Import SQL to ERD',
            'integrate_erd_module_builder' => 'Integrate ERD with Module Builder',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'admin'
            ]);
        }

        // Create or update roles
        $this->createERDDesignerRoles();
    }

    /**
     * Create ERD Designer specific roles
     */
    private function createERDDesignerRoles(): void
    {
        // ERD Viewer Role
        $erdViewer = Role::firstOrCreate([
            'name' => 'erd_viewer',
            'guard_name' => 'admin'
        ]);

        $erdViewer->syncPermissions([
            'access_erd_designer',
            'view_erd_projects',
            'view_erd_tables',
            'view_erd_fields',
            'view_erd_relationships',
        ]);

        // ERD Designer Role
        $erdDesigner = Role::firstOrCreate([
            'name' => 'erd_designer',
            'guard_name' => 'admin'
        ]);

        $erdDesigner->syncPermissions([
            'access_erd_designer',
            'view_erd_projects',
            'create_erd_projects',
            'edit_erd_projects',
            'view_erd_tables',
            'create_erd_tables',
            'edit_erd_tables',
            'view_erd_fields',
            'create_erd_fields',
            'edit_erd_fields',
            'view_erd_relationships',
            'create_erd_relationships',
            'edit_erd_relationships',
            'export_erd_projects',
            'import_erd_projects',
            'export_sql_from_erd',
            'import_sql_to_erd',
        ]);

        // ERD Administrator Role
        $erdAdmin = Role::firstOrCreate([
            'name' => 'erd_administrator',
            'guard_name' => 'admin'
        ]);

        $erdAdmin->syncPermissions([
            'access_erd_designer',
            'view_erd_projects',
            'create_erd_projects',
            'edit_erd_projects',
            'delete_erd_projects',
            'export_erd_projects',
            'import_erd_projects',
            'view_erd_tables',
            'create_erd_tables',
            'edit_erd_tables',
            'delete_erd_tables',
            'view_erd_fields',
            'create_erd_fields',
            'edit_erd_fields',
            'delete_erd_fields',
            'view_erd_relationships',
            'create_erd_relationships',
            'edit_erd_relationships',
            'delete_erd_relationships',
            'manage_erd_versions',
            'share_erd_projects',
            'export_sql_from_erd',
            'import_sql_to_erd',
            'integrate_erd_module_builder',
        ]);

        // Assign ERD permissions to existing roles
        $this->assignToExistingRoles();
    }

    /**
     * Assign ERD permissions to existing roles
     */
    private function assignToExistingRoles(): void
    {
        // Give super admin all ERD permissions
        $superAdmin = Role::where('name', 'super_admin')->where('guard_name', 'admin')->first();
        if ($superAdmin) {
            $erdPermissions = Permission::where('name', 'like', '%erd%')->where('guard_name', 'admin')->get();
            $superAdmin->givePermissionTo($erdPermissions);
        }

        // Also try Super Admin (with capital letters)
        $superAdmin = Role::where('name', 'Super Admin')->where('guard_name', 'admin')->first();
        if ($superAdmin) {
            $erdPermissions = Permission::where('name', 'like', '%erd%')->where('guard_name', 'admin')->get();
            $superAdmin->givePermissionTo($erdPermissions);
        }

        // Give admin basic ERD permissions
        $admin = Role::where('name', 'admin')->where('guard_name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo([
                'access_erd_designer',
                'view_erd_projects',
                'create_erd_projects',
                'edit_erd_projects',
                'export_erd_projects',
                'import_erd_projects',
                'view_erd_tables',
                'create_erd_tables',
                'edit_erd_tables',
                'view_erd_fields',
                'create_erd_fields',
                'edit_erd_fields',
                'view_erd_relationships',
                'create_erd_relationships',
                'edit_erd_relationships',
                'export_sql_from_erd',
                'import_sql_to_erd',
                'integrate_erd_module_builder',
            ]);
        }

        // Give developer ERD designer permissions
        $developer = Role::where('name', 'developer')->where('guard_name', 'admin')->first();
        if ($developer) {
            $developer->givePermissionTo([
                'access_erd_designer',
                'view_erd_projects',
                'create_erd_projects',
                'edit_erd_projects',
                'export_erd_projects',
                'import_erd_projects',
                'view_erd_tables',
                'create_erd_tables',
                'edit_erd_tables',
                'view_erd_fields',
                'create_erd_fields',
                'edit_erd_fields',
                'view_erd_relationships',
                'create_erd_relationships',
                'edit_erd_relationships',
                'export_sql_from_erd',
                'import_sql_to_erd',
                'integrate_erd_module_builder',
            ]);
        }
    }
}

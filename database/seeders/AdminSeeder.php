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

        // Ensure module permissions are registered
        \App\Services\ModulePermissionService::registerAllPermissions();
        
        // Get module-based permissions
        $allModulePermissions = [];
        $modules = \App\Services\ModulePermissionService::getModulesWithPermissions();
        
        foreach ($modules as $moduleName => $permissions) {
            foreach ($permissions as $permission) {
                $allModulePermissions[] = $permission['name'];
            }
        }

        // Assign all module permissions to Super Admin
        $superAdminRole->syncPermissions($allModulePermissions);
        
        // Assign limited permissions to Admin (just admin management)
        $adminPermissions = [];
        foreach ($allModulePermissions as $permission) {
            if (str_contains($permission, '_admin')) {
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
}

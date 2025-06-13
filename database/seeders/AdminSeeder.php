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

        // Create permissions
        $permissions = [
            'view_admins',
            'create_admins',
            'edit_admins',
            'delete_admins',
            'view_roles',
            'create_roles',
            'edit_roles',
            'delete_roles',
            'view_permissions',
            'create_permissions',
            'edit_permissions',
            'delete_permissions',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'admin');
        }

        // Assign all permissions to Super Admin
        $superAdminRole->syncPermissions($permissions);
        
        // Assign limited permissions to Admin
        $adminRole->syncPermissions([
            'view_admins',
            'create_admins',
            'edit_admins',
        ]);

        // Create default Super Admin user
        $admin = Admin::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('password'),
            ]
        );

        // Assign Super Admin role
        $admin->assignRole('Super Admin');

        // Create a regular admin user
        $regularAdmin = Admin::firstOrCreate(
            ['email' => 'user@admin.com'],
            [
                'name' => 'Admin User',
                'email' => 'user@admin.com',
                'password' => Hash::make('password'),
            ]
        );

        // Assign Admin role
        $regularAdmin->assignRole('Admin');
    }
}

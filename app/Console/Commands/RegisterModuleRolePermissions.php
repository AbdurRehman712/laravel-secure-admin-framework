<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RegisterModuleRolePermissions extends Command
{
    protected $signature = 'permissions:register-module-roles';
    protected $description = 'Register permissions for ModuleRoleResource';

    public function handle()
    {
        $this->info('🔐 Registering Module Role permissions...');
        
        $permissions = [
            'view_any_module_role',
            'view_module_role', 
            'create_module_role',
            'update_module_role',
            'delete_module_role',
            'delete_any_module_role'
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'admin');
            $this->info("   ✅ Created/found permission: {$permission}");
        }

        // Assign these permissions to Super Admin role
        $superAdminRole = Role::where('name', 'Super Admin')->where('guard_name', 'admin')->first();
        
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($permissions);
            $this->info("   ✅ Assigned permissions to Super Admin role");
        } else {
            $this->warn("   ⚠️  Super Admin role not found - permissions not assigned");
        }

        $this->info('✅ Module Role permissions registered successfully!');
        
        return 0;
    }
}

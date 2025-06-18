<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ModulePermissionService;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Modules\Core\app\Models\Admin;

class SetupModulePermissions extends Command
{
    protected $signature = 'permissions:setup 
                            {--demo : Create demo roles with module permissions}
                            {--guard=admin : Specify the guard}';

    protected $description = 'Setup module permissions and create demo roles';

    public function handle()
    {
        $guard = $this->option('guard');
        $demo = $this->option('demo');

        $this->info('🛡️  Setting up Module Permission System...');
        $this->newLine();

        // Step 1: Register all module permissions
        $this->info('Step 1: Registering module permissions...');
        ModulePermissionService::registerAllPermissions();
        
        $permissionCount = Permission::where('guard_name', $guard)->count();
        $this->line("✓ Registered permissions for {$guard} guard: <comment>{$permissionCount}</comment>");

        // Step 2: Show discovered modules and permissions
        $this->info('Step 2: Discovered modules and resources...');
        $modules = ModulePermissionService::getModulesWithPermissions();
        
        foreach ($modules as $moduleName => $permissions) {
            $this->line("📦 <info>{$moduleName}</info> module:");
            $resourceGroups = [];
            foreach ($permissions as $permission) {
                $resourceGroups[$permission['resource']][] = $permission['action'];
            }
            
            foreach ($resourceGroups as $resource => $actions) {
                $resourceName = str_replace('Resource', '', $resource);
                $actionsList = implode(', ', array_unique($actions));
                $this->line("   - {$resourceName}: {$actionsList}");
            }
        }

        if ($demo) {
            $this->newLine();
            $this->info('Step 3: Creating demo roles...');
            $this->createDemoRoles($guard, $modules);
        }

        $this->newLine();
        $this->info('✅ Module permission system setup complete!');
        $this->line('');
        $this->line('📋 <comment>Next Steps:</comment>');
        $this->line('1. Visit /admin to see the "Module Roles" resource');
        $this->line('2. Create roles and assign module-specific permissions');
        $this->line('3. Assign roles to admin users');
        $this->line('4. Test access control in your resources');
    }

    private function createDemoRoles(string $guard, array $modules): void
    {
        // Create Module Manager role - can manage all module resources
        $moduleManager = Role::firstOrCreate([
            'name' => 'Module Manager',
            'guard_name' => $guard
        ]);

        $allPermissions = [];
        foreach ($modules as $moduleName => $permissions) {
            foreach ($permissions as $permission) {
                $allPermissions[] = $permission['name'];
            }
        }

        $moduleManager->syncPermissions($allPermissions);
        $this->line("✓ Created <info>Module Manager</info> role with all module permissions");

        // Create Core Admin role - only Core module permissions
        if (isset($modules['Core'])) {
            $coreAdmin = Role::firstOrCreate([
                'name' => 'Core Admin',
                'guard_name' => $guard
            ]);

            $corePermissions = array_map(fn($p) => $p['name'], $modules['Core']);
            $coreAdmin->syncPermissions($corePermissions);
            $this->line("✓ Created <info>Core Admin</info> role with Core module permissions");
        }

        // Create User Manager role - only PublicUser module permissions
        if (isset($modules['PublicUser'])) {
            $userManager = Role::firstOrCreate([
                'name' => 'User Manager',
                'guard_name' => $guard
            ]);

            $userPermissions = array_map(fn($p) => $p['name'], $modules['PublicUser']);
            $userManager->syncPermissions($userPermissions);
            $this->line("✓ Created <info>User Manager</info> role with PublicUser module permissions");
        }

        // Assign Module Manager role to Super Admin
        if ($guard === 'admin') {
            $superAdmin = Admin::where('email', 'admin@admin.com')->first();
            if ($superAdmin) {
                $superAdmin->assignRole('Module Manager');
                $this->line("✓ Assigned Module Manager role to Super Admin");
            }
        }
    }
}

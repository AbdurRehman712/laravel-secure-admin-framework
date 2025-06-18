<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ModulePermissionService;

class RegisterModulePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:register 
                            {--module= : Register permissions for a specific module}
                            {--guard=admin : Specify the guard (admin or web)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register module permissions automatically';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $module = $this->option('module');
        $guard = $this->option('guard');

        $this->info('🛡️  Registering Module Permissions...');
        $this->newLine();

        if ($module) {
            $this->registerSingleModule($module, $guard);
        } else {
            $this->registerAllModules($guard);
        }

        $this->newLine();
        $this->info('✅ Module permissions registered successfully!');
    }

    private function registerAllModules(string $guard): void
    {
        $modules = ModulePermissionService::getModulesWithPermissions();

        if (empty($modules)) {
            $this->warn('No modules found with Filament resources.');
            return;
        }

        foreach ($modules as $moduleName => $permissions) {
            $this->registerModulePermissions($moduleName, $permissions, $guard);
        }
    }

    private function registerSingleModule(string $moduleName, string $guard): void
    {
        $permissions = ModulePermissionService::getModulePermissions($moduleName);

        if (empty($permissions)) {
            $this->warn("No permissions found for module: {$moduleName}");
            return;
        }

        $this->registerModulePermissions($moduleName, $permissions, $guard);
    }

    private function registerModulePermissions(string $moduleName, array $permissions, string $guard): void
    {
        $this->line("📦 Processing module: <info>{$moduleName}</info>");

        ModulePermissionService::registerModulePermissions($moduleName, $permissions, $guard);

        $count = count($permissions);
        $this->line("   ✓ Registered {$count} permissions for guard: <comment>{$guard}</comment>");

        // Show permission details
        if ($this->getOutput()->isVerbose()) {
            foreach ($permissions as $permission) {
                $this->line("     - {$permission['name']} ({$permission['display_name']})");
            }
        }
    }
}

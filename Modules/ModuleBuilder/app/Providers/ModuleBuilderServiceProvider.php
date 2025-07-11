<?php

namespace Modules\ModuleBuilder\app\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;

class ModuleBuilderServiceProvider extends ServiceProvider
{
    protected string $name = 'ModuleBuilder';
    protected string $nameLower = 'modulebuilder';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerViews();
        $this->registerFilamentAssets();

        // Only create permissions if database is ready
        if ($this->isDatabaseReady()) {
            $this->createModuleEditorPermission();
        }
    }

    /**
     * Check if the database is ready for permission operations.
     */
    private function isDatabaseReady(): bool
    {
        try {
            // Check if we're running migrations
            if (app()->runningInConsole() &&
                (in_array('migrate', $_SERVER['argv'] ?? []) ||
                 in_array('migrate:fresh', $_SERVER['argv'] ?? []) ||
                 in_array('migrate:reset', $_SERVER['argv'] ?? []))) {
                return false;
            }

            // Check if permissions table exists
            return \Schema::hasTable('permissions') && \Schema::hasTable('roles');
        } catch (\Exception $e) {
            return false;
        }
    }

    private function createModuleEditorPermission(): void
    {
        if (class_exists(\Spatie\Permission\Models\Permission::class)) {
            try {
                $permission = \Spatie\Permission\Models\Permission::firstOrCreate([
                    'name' => 'view_module_editor',
                    'guard_name' => 'admin'
                ]);

                // Assign to Super Admin role if it exists
                $superAdminRole = \Spatie\Permission\Models\Role::where('name', 'Super Admin')
                    ->where('guard_name', 'admin')
                    ->first();

                if ($superAdminRole && !$superAdminRole->hasPermissionTo('view_module_editor')) {
                    $superAdminRole->givePermissionTo('view_module_editor');
                }
            } catch (\Exception $e) {
                // Silently fail if database is not ready or permissions table doesn't exist
            }
        }
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        // Simple Module Builder doesn't need additional providers
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $sourcePath = module_path($this->name, 'resources/views');
        $this->loadViewsFrom([$sourcePath], $this->nameLower);
    }

    /**
     * Register Filament assets.
     */
    protected function registerFilamentAssets(): void
    {
        // Register assets if they exist
        $cssPath = module_path($this->name, 'resources/css/modulebuilder.css');
        $jsPath = module_path($this->name, 'resources/js/modulebuilder.js');

        $assets = [];

        if (file_exists($cssPath)) {
            $assets[] = Css::make('modulebuilder-styles', $cssPath);
        }

        if (file_exists($jsPath)) {
            $assets[] = Js::make('modulebuilder-scripts', $jsPath);
        }

        if (!empty($assets)) {
            FilamentAsset::register($assets);
        }
    }
}
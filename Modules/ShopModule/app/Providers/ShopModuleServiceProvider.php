<?php

namespace Modules\ShopModule\app\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ModulePermissionService;

class ShopModuleServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'ShopModule';
    protected string $moduleNameLower = 'shopmodule';

    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'database/migrations'));
        $this->registerPermissions();
    }    public function register(): void
    {
        // Register RouteServiceProvider if it exists (for API modules)
        if (class_exists("Modules\\{$this->moduleName}\\app\\Providers\\RouteServiceProvider")) {
            $this->app->register("Modules\\{$this->moduleName}\\app\\Providers\\RouteServiceProvider");
        }
    }    protected function registerConfig(): void
    {
        $configPath = module_path($this->moduleName, 'config/config.php');
        
        if (file_exists($configPath)) {
            $this->publishes([
                $configPath => config_path($this->moduleNameLower . '.php'),
            ], 'config');
            
            $this->mergeConfigFrom($configPath, $this->moduleNameLower);
        }
    }

    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'resources/lang'), $this->moduleNameLower);
        }
    }    protected function registerPermissions(): void
    {
        // Register all permissions for this module
        ModulePermissionService::registerAllPermissions();
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }
}

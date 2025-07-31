<?php

namespace Modules\CmsEditor\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;

class CmsEditorServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path('CmsEditor', 'database/migrations'));
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path('CmsEditor', 'config/config.php') => config_path('cmseditor.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path('CmsEditor', 'config/config.php'), 'cmseditor'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/cmseditor');

        $sourcePath = module_path('CmsEditor', 'resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], 'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/cmseditor';
        }, \Config::get('view.paths')), [$sourcePath]), 'cmseditor');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/cmseditor');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'cmseditor');
        } else {
            $this->loadTranslationsFrom(module_path('CmsEditor', 'resources/lang'), 'cmseditor');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}

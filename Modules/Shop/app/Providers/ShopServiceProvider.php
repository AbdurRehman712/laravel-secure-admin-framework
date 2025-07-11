<?php

namespace Modules\Shop\app\Providers;

use Illuminate\Support\ServiceProvider;

class ShopServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path('Shop', 'database/migrations'));
    }

    public function register(): void
    {
        //
    }
}
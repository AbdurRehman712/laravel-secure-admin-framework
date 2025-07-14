<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    Modules\ModuleBuilder\app\Providers\ModuleBuilderServiceProvider::class,
    // Generated module providers will be automatically added here by the module builder
    Modules\Shop\app\Providers\ShopServiceProvider::class,
];

<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    Modules\ModuleBuilder\app\Providers\ModuleBuilderServiceProvider::class,
    Modules\Shop\app\Providers\ShopServiceProvider::class,


    Modules\Ecommerce\app\Providers\EcommerceServiceProvider::class,
    Modules\Blog\app\Providers\BlogServiceProvider::class,
    Modules\Library\app\Providers\LibraryServiceProvider::class,
];

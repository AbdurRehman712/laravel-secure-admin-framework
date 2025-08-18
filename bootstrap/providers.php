<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    // Core module providers - temporarily disabled to fix route conflicts
    // Modules\Core\app\Providers\CoreServiceProvider::class,
    // Modules\PublicUser\app\Providers\PublicUserServiceProvider::class,
    // Modules\ERDDesigner\app\Providers\ERDDesignerServiceProvider::class,
    // Register functional modules before CmsEditor so their routes load before the catch-all
    
    Modules\CmsEditor\Providers\CmsEditorServiceProvider::class,

    // Modules\NewERDProject\app\Providers\NewERDProjectServiceProvider::class,
];

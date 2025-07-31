<?php

namespace Modules\CmsEditor\app\Filament\Resources\ThemeResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\CmsEditor\app\Filament\Resources\ThemeResource;
use Modules\CmsEditor\app\Services\ThemeManager;

class CreateTheme extends CreateRecord
{
    protected static string $resource = ThemeResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Create a new Theme model instance to satisfy Filament's type requirements
        $themeModel = new \Modules\CmsEditor\app\Models\Theme();
        $themeModel->fill($data);
        
        // Log the theme creation attempt
        \Illuminate\Support\Facades\Log::info('Theme creation attempt', $data);
        
        // In a real implementation, you would save to filesystem/DB here
        // $themeManager = app(ThemeManager::class);
        // $themeManager->createTheme($data);
        
        return $themeModel;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

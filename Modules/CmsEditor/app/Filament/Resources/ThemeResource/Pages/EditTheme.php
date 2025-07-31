<?php

namespace Modules\CmsEditor\app\Filament\Resources\ThemeResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\CmsEditor\app\Filament\Resources\ThemeResource;
use Modules\CmsEditor\app\Services\ThemeManager;
use Modules\CmsEditor\app\Models\FileTheme;

class EditTheme extends EditRecord
{
    protected static string $resource = ThemeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function resolveRecord($key): FileTheme
    {
        // Load theme data from file system using the theme code
        $themeManager = app(ThemeManager::class);
        $themeData = $themeManager->getTheme($key);

        if (!$themeData) {
            abort(404, "Theme '{$key}' not found");
        }

        // Create a FileTheme instance with the theme data
        return new FileTheme($themeData);
    }



    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        // In a real implementation, you would update the theme here
        // $themeManager = app(ThemeManager::class);
        // $themeManager->updateTheme($record->code, $data);
        
        // Log the update attempt
        \Illuminate\Support\Facades\Log::info('Theme update attempt', [
            'theme' => $record->code ?? 'unknown',
            'data' => $data
        ]);
        
        // Update the model
        $record->fill($data);
        
        return $record;
    }
}

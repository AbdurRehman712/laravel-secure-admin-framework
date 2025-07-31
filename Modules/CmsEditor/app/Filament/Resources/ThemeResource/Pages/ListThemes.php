<?php

namespace Modules\CmsEditor\app\Filament\Resources\ThemeResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Modules\CmsEditor\app\Filament\Resources\ThemeResource;
use Modules\CmsEditor\app\Services\ThemeManager;
use Modules\CmsEditor\app\Models\FileTheme;
use Illuminate\Database\Eloquent\Collection;

class ListThemes extends ListRecords
{
    protected static string $resource = ThemeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        // Return a dummy query since we override getTableRecords
        return \Modules\CmsEditor\app\Models\Theme::query();
    }

    public function getTableRecords(): Collection
    {
        $themeManager = app(ThemeManager::class);
        $themes = $themeManager->getAllThemes();

        // Convert array data to FileTheme model instances
        return FileTheme::fromThemeData($themes);
    }
}

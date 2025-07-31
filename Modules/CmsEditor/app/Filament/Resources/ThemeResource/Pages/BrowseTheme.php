<?php

namespace Modules\CmsEditor\app\Filament\Resources\ThemeResource\Pages;

use Filament\Resources\Pages\Page;
use Modules\CmsEditor\app\Filament\Resources\ThemeResource;
use Modules\CmsEditor\app\Services\ThemeManager;
use Modules\CmsEditor\app\Models\FileTheme;

class BrowseTheme extends Page
{
    protected static string $resource = ThemeResource::class;
    
    protected string $view = 'cmseditor::filament.resources.theme-resource.pages.browse-theme';
    
    public $theme;
    public $currentPath;
    public $files = [];
    
    public function mount(): void
    {
        // Get the theme code directly from the route parameter
        $themeCode = request()->route('record');

        // Validate that the theme exists
        $themeManager = app(ThemeManager::class);
        $themeData = $themeManager->getTheme($themeCode);

        if (!$themeData) {
            abort(404, "Theme '{$themeCode}' not found");
        }

        $this->theme = $themeCode;
        $this->currentPath = '';
        $this->loadFiles();
    }
    
    protected function loadFiles(): void
    {
        $themeManager = app(ThemeManager::class);
        $this->files = $themeManager->getThemeStructure($this->theme);
    }
    
    public function navigateTo(string $path): void
    {
        $this->currentPath = $path;
        $this->loadFiles();
    }

    public function editFile(string $fileName): void
    {
        // Build the full file path
        $filePath = $this->currentPath ? $this->currentPath . '/' . $fileName : $fileName;

        // Redirect to the file editor
        $this->redirect(static::getResource()::getUrl('edit-file', [
            'theme' => $this->theme,
            'file' => urlencode($filePath)
        ]));
    }

    public function deleteItem(string $itemName, string $itemType): void
    {
        $themeManager = app(ThemeManager::class);
        $itemPath = $this->currentPath ? $this->currentPath . '/' . $itemName : $itemName;

        try {
            $success = $themeManager->deleteFile($this->theme, $itemPath);

            if ($success) {
                $this->loadFiles(); // Refresh the file list
                \Filament\Notifications\Notification::make()
                    ->title('Success')
                    ->body("$itemType '$itemName' deleted successfully.")
                    ->success()
                    ->send();
            } else {
                \Filament\Notifications\Notification::make()
                    ->title('Error')
                    ->body("Failed to delete $itemType '$itemName'.")
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('Error')
                ->body("Error deleting $itemType: " . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getTitle(): string
    {
        return 'Browse Theme: ' . $this->theme;
    }
}

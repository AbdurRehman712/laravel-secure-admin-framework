<?php

namespace Modules\ModuleBuilder\Filament\Resources\ModuleProjectResource\Pages;

use Modules\ModuleBuilder\Filament\Resources\ModuleProjectResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditModuleProject extends EditRecord
{
    protected static string $resource = ModuleProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('runMigrations')
                ->label('Run Migrations')
                ->icon('heroicon-o-arrow-up-on-square-stack')
                ->color('info')
                ->action(function () {
                    $moduleName = str_replace(' ', '', $this->record->name);
                    $output = \Artisan::call('module:migrate', ['module' => $moduleName]);
                    Notification::make()
                        ->title('Migrations executed!')
                        ->body('Migrations for the module were run via Artisan.')
                        ->success()
                        ->send();
                    $this->redirect(static::getUrl(['record' => $this->record]));
                }),
            Actions\Action::make('build')
                ->label('Build Module')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('success')
                ->visible(fn () => $this->record->status !== 'building')
                ->action(function () {
                    $generator = new \Modules\ModuleBuilder\Services\ModuleGeneratorService($this->record);
                    $result = $generator->generateModule();
                    
                    if ($result['success']) {
                        Notification::make()
                            ->title('Module built successfully!')
                            ->body("Generated {$this->record->name} with " . count($result['files']) . " files.")
                            ->success()
                            ->send();
                            
                        // Refresh the page to show updated status
                        $this->redirect(static::getUrl(['record' => $this->record]));
                    } else {
                        Notification::make()
                            ->title('Build failed')
                            ->body($result['message'])
                            ->danger()
                            ->send();
                    }
                }),                Actions\Action::make('import')
                    ->label('Import Module')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('warning')
                    ->form([
                        Forms\Components\FileUpload::make('zip_file')
                            ->label('Module Zip File')
                            ->acceptedFileTypes(['application/zip'])
                            ->required()
                            ->helperText('Upload a module zip file exported from Module Builder'),
                        
                        Forms\Components\TextInput::make('target_name')
                            ->label('Module Name (Optional)')
                            ->placeholder('Leave empty to use original name')
                            ->helperText('Override the module name if needed'),
                    ])
                    ->action(function (array $data) {
                        $zipPath = $data['zip_file'];
                        $targetName = $data['target_name'] ?: null;
                        
                        $result = \Modules\ModuleBuilder\Services\ModuleGeneratorService::importModule($zipPath, $targetName);
                        
                        if ($result['success']) {
                            Notification::make()
                                ->title('Module imported successfully!')
                                ->body($result['message'])
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Import failed')
                                ->body($result['message'])
                                ->danger()
                                ->send();
                        }
                    }),
                
                Actions\Action::make('export')
                    ->label('Export Module')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->visible(fn () => $this->record->status === 'built')
                    ->action(function () {
                        $generator = new \Modules\ModuleBuilder\Services\ModuleGeneratorService($this->record);
                    $zipPath = $generator->exportModule();
                    
                    return response()->download($zipPath, "{$this->record->slug}.zip");
                }),
            Actions\Action::make('artisanEnable')
                ->label('Enable')
                ->icon('heroicon-o-play')
                ->color('primary')
                ->visible(fn () => !$this->record->enabled)
                ->action(function () {
                    $moduleName = str_replace(' ', '', $this->record->name);
                    \Artisan::call('module:enable', ['module' => $moduleName]);
                    // Sync state after enabling
                    $statusesPath = base_path('modules_statuses.json');
                    $statuses = file_exists($statusesPath) ? json_decode(file_get_contents($statusesPath), true) : [];
                    $statuses[$moduleName] = true;
                    file_put_contents($statusesPath, json_encode($statuses, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                    Notification::make()
                        ->title('Module enabled!')
                        ->body('The module was enabled and state synced.')
                        ->success()
                        ->send();
                    $this->redirect(static::getUrl(['record' => $this->record]));
                }),
            Actions\Action::make('artisanDisable')
                ->label('Disable')
                ->icon('heroicon-o-pause')
                ->color('danger')
                ->visible(fn () => $this->record->enabled)
                ->action(function () {
                    $moduleName = str_replace(' ', '', $this->record->name);
                    \Artisan::call('module:disable', ['module' => $moduleName]);
                    // Sync state after disabling
                    $statusesPath = base_path('modules_statuses.json');
                    $statuses = file_exists($statusesPath) ? json_decode(file_get_contents($statusesPath), true) : [];
                    $statuses[$moduleName] = false;
                    file_put_contents($statusesPath, json_encode($statuses, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                    Notification::make()
                        ->title('Module disabled!')
                        ->body('The module was disabled and state synced.')
                        ->success()
                        ->send();
                    $this->redirect(static::getUrl(['record' => $this->record]));
                }),
            Actions\DeleteAction::make()
                ->before(function () {
                    // Clean up generated module files when deleting project
                    $modulePath = base_path("Modules/{$this->record->name}");
                    if (is_dir($modulePath)) {
                        \Illuminate\Support\Facades\File::deleteDirectory($modulePath);
                    }
                }),
        ];
    }
}

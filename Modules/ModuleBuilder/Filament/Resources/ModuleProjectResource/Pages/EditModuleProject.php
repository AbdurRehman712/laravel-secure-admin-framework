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

<?php

namespace Modules\ModuleBuilder\Filament\Resources\ModuleTableResource\Pages;

use Modules\ModuleBuilder\Filament\Resources\ModuleTableResource;
use Modules\ModuleBuilder\Services\MigrationService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action as NotificationAction;

class EditModuleTable extends EditRecord
{
    protected static string $resource = ModuleTableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generate_migration')
                ->label('Generate Migration')
                ->icon('heroicon-o-document-text')
                ->color('warning')
                ->action(function () {
                    $migrationService = new MigrationService();
                    
                    try {
                        $migrationFile = $migrationService->generateFieldMigration($this->record);
                        
                        Notification::make()
                            ->title('Migration Generated')
                            ->body('Migration file created: ' . basename($migrationFile))
                            ->success()
                            ->send();
                            
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Migration Failed')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->requiresConfirmation()
                ->modalHeading('Generate Migration for Field Changes')
                ->modalDescription('This will create a new migration file for any field changes made to this table.')
                ->modalSubmitActionLabel('Generate Migration'),
                
            Actions\Action::make('run_migration')
                ->label('Run Pending Migrations')
                ->icon('heroicon-o-play')
                ->color('success')
                ->action(function () {
                    try {
                        \Artisan::call('migrate', [
                            '--path' => "Modules/{$this->record->moduleProject->name}/database/migrations",
                            '--force' => true
                        ]);
                        
                        Notification::make()
                            ->title('Migrations Executed')
                            ->body('All pending migrations have been run successfully.')
                            ->success()
                            ->send();
                            
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Migration Failed')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->requiresConfirmation()
                ->modalHeading('Run Pending Migrations')
                ->modalDescription('This will execute all pending migrations for this module.')
                ->modalSubmitActionLabel('Run Migrations'),
                
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // Auto-generate migration when fields are modified
        if ($this->hasFieldChanges()) {
            $migrationService = new MigrationService();
            
            try {
                $migrationFile = $migrationService->generateFieldMigration($this->record);
                
                Notification::make()
                    ->title('Auto-Migration Generated')
                    ->body('A migration file has been automatically created for your field changes: ' . basename($migrationFile) . '. Use the "Run Pending Migrations" button to apply changes.')
                    ->warning()
                    ->persistent()
                    ->send();
                    
            } catch (\Exception $e) {
                Notification::make()
                    ->title('Auto-Migration Failed')
                    ->body('Could not generate migration: ' . $e->getMessage())
                    ->danger()
                    ->send();
            }
        }
    }
    
    /**
     * Check if fields have been modified
     */
    private function hasFieldChanges(): bool
    {
        // Simple check - in a real implementation, you'd compare the original 
        // field definitions with the new ones
        return $this->record->fields()->exists();
    }
}

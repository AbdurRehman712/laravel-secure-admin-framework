<?php

namespace Modules\ModuleBuilder\Filament\Resources\UnifiedModuleBuilderResource\Pages;

use Modules\ModuleBuilder\Filament\Resources\UnifiedModuleBuilderResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Modules\ModuleBuilder\Services\ModuleGeneratorService;

class CreateUnifiedModuleBuilder extends CreateRecord
{
    protected static string $resource = UnifiedModuleBuilderResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Create & Generate Module')
            ->icon('heroicon-o-rocket-launch');
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        
        // Auto-generate the module after creation
        try {
            $generator = new ModuleGeneratorService($record);
            $result = $generator->generateModule();
            
            Notification::make()
                ->title('Module Created & Generated Successfully!')
                ->body("Module '{$record->name}' has been created and all components generated.")
                ->success()
                ->duration(8000)
                ->send();
                
        } catch (\Exception $e) {
            Notification::make()
                ->title('Module Created but Generation Failed')
                ->body("Module record created but generation failed: {$e->getMessage()}")
                ->warning()
                ->duration(8000)
                ->send();
        }
    }
}

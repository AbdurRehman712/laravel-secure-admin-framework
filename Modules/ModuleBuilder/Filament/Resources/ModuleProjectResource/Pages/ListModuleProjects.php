<?php

namespace Modules\ModuleBuilder\Filament\Resources\ModuleProjectResource\Pages;

use Modules\ModuleBuilder\Filament\Resources\ModuleProjectResource;
use Modules\ModuleBuilder\Models\ModuleProject;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListModuleProjects extends ListRecords
{
    protected static string $resource = ModuleProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Module')
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }
}

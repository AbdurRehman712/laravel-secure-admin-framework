<?php

namespace Modules\ModuleBuilder\Filament\Resources\ModuleFieldResource\Pages;

use Modules\ModuleBuilder\Filament\Resources\ModuleFieldResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListModuleFields extends ListRecords
{
    protected static string $resource = ModuleFieldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

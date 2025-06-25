<?php

namespace Modules\ModuleBuilder\Filament\Resources\ModuleTableResource\Pages;

use Modules\ModuleBuilder\Filament\Resources\ModuleTableResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListModuleTables extends ListRecords
{
    protected static string $resource = ModuleTableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

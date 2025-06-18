<?php

namespace App\Filament\Resources\ModuleRoleResource\Pages;

use App\Filament\Resources\ModuleRoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListModuleRoles extends ListRecords
{
    protected static string $resource = ModuleRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

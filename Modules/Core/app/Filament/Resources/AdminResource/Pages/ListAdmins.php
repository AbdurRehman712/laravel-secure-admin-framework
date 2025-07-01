<?php

namespace Modules\Core\app\Filament\Resources\AdminResource\Pages;

use Modules\Core\app\Filament\Resources\AdminResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdmins extends ListRecords
{
    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }
}

<?php

namespace Modules\PublicUser\app\Filament\Resources\UserResource\Pages;

use Modules\PublicUser\app\Filament\Resources\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }
}

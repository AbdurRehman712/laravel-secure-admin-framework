<?php

namespace Modules\BlogModule\Filament\Resources\TestItemResource\Pages;

use Modules\BlogModule\Filament\Resources\TestItemResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListTestItem extends ListRecords
{
    protected static string $resource = TestItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

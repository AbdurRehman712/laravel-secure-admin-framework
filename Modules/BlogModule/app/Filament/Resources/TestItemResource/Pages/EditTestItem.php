<?php

namespace Modules\BlogModule\Filament\Resources\TestItemResource\Pages;

use Modules\BlogModule\Filament\Resources\TestItemResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditTestItem extends EditRecord
{
    protected static string $resource = TestItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

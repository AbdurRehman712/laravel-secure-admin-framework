<?php

namespace Modules\ShopModule\Filament\Resources\CategoryResource\Pages;

use Modules\ShopModule\Filament\Resources\CategoryResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListCategory extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

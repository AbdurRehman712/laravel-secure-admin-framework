<?php

namespace Modules\ShopModule\Filament\Resources\ProductResource\Pages;

use Modules\ShopModule\Filament\Resources\ProductResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListProduct extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

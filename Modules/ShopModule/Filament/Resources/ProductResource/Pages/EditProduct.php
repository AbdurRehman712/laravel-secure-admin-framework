<?php

namespace Modules\ShopModule\Filament\Resources\ProductResource\Pages;

use Modules\ShopModule\Filament\Resources\ProductResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

<?php

namespace Modules\ShopModule\Filament\Resources\CategoryResource\Pages;

use Modules\ShopModule\Filament\Resources\CategoryResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

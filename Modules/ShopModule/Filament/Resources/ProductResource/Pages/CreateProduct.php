<?php

namespace Modules\ShopModule\Filament\Resources\ProductResource\Pages;

use Modules\ShopModule\Filament\Resources\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
}

<?php

namespace Modules\Shop\app\Filament\Resources\ProductResource\Pages;

use Modules\Shop\app\Filament\Resources\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
}
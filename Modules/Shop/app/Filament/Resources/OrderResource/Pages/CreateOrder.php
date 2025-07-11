<?php

namespace Modules\Shop\app\Filament\Resources\OrderResource\Pages;

use Modules\Shop\app\Filament\Resources\OrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
}
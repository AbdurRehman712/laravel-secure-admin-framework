<?php

namespace Modules\BlogModule\Filament\Resources\TestItemResource\Pages;

use Modules\BlogModule\Filament\Resources\TestItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTestItem extends CreateRecord
{
    protected static string $resource = TestItemResource::class;
}

<?php

namespace Modules\Core\app\Filament\Resources\AdminResource\Pages;

use Modules\Core\app\Filament\Resources\AdminResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAdmin extends CreateRecord
{
    protected static string $resource = AdminResource::class;
}

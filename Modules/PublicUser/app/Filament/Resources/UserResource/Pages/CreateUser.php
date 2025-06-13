<?php

namespace Modules\PublicUser\app\Filament\Resources\UserResource\Pages;

use Modules\PublicUser\app\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}

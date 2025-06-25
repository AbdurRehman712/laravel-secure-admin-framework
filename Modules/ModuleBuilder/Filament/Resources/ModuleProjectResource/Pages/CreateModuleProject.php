<?php

namespace Modules\ModuleBuilder\Filament\Resources\ModuleProjectResource\Pages;

use Modules\ModuleBuilder\Filament\Resources\ModuleProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateModuleProject extends CreateRecord
{
    protected static string $resource = ModuleProjectResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Module project created successfully! Now you can add tables and fields.';
    }
}

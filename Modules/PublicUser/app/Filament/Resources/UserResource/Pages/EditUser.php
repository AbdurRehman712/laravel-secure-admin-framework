<?php

namespace Modules\PublicUser\app\Filament\Resources\UserResource\Pages;

use Modules\PublicUser\app\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\Models\Role;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
    
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['roles'] = $this->record->roles->pluck('id')->toArray();
        return $data;
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $roles = $data['roles'] ?? [];
        unset($data['roles']);
        
        return $data;
    }
    
    protected function afterSave(): void
    {
        $roles = $this->data['roles'] ?? [];
        
        if (!empty($roles)) {
            $this->record->syncRoles($roles);
        }
    }
}

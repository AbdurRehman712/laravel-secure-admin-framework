<?php

namespace Modules\PublicUser\app\Filament\Resources\UserResource\Pages;

use Modules\PublicUser\app\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Role;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $roles = $data['roles'] ?? [];
        unset($data['roles']);
        
        return $data;
    }
    
    protected function afterCreate(): void
    {
        $roles = $this->data['roles'] ?? [];
        
        if (!empty($roles)) {
            $this->record->syncRoles($roles);
        }
    }
}

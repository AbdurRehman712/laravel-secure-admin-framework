<?php

namespace Modules\Core\app\Filament\Resources\AdminResource\Pages;

use Modules\Core\app\Filament\Resources\AdminResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Role;

class CreateAdmin extends CreateRecord
{
    protected static string $resource = AdminResource::class;
    
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
            $roleModels = Role::whereIn('id', $roles)->get();
            $this->record->syncRoles($roleModels);
        }
    }
}

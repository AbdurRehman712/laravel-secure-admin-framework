<?php

namespace Modules\Core\app\Filament\Resources\AdminResource\Pages;

use Modules\Core\app\Filament\Resources\AdminResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\Models\Role;

class EditAdmin extends EditRecord
{
    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
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
            $roleModels = Role::whereIn('id', $roles)->get();
            $this->record->syncRoles($roleModels);
        }
    }
}

<?php

namespace App\Filament\Resources\ModuleRoleResource\Pages;

use App\Filament\Resources\ModuleRoleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class EditModuleRole extends EditRecord
{
    protected static string $resource = ModuleRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $rolePermissions = $this->record->permissions()->pluck('name')->toArray();
        $modules = \App\Services\ModulePermissionService::getModulesWithPermissions();

        foreach ($modules as $moduleName => $permissions) {
            $modulePermissionNames = collect($permissions)->pluck('name')->toArray();
            $selected = array_values(array_intersect($rolePermissions, $modulePermissionNames));
            $data["{$moduleName}_permissions"] = $selected; // always an array, even if empty
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $allPermissions = [];
        $modules = \App\Services\ModulePermissionService::getModulesWithPermissions();

        foreach ($modules as $moduleName => $permissions) {
            // A checkbox list can return false if nothing is selected.
            // We need to ensure we have an array to merge.
            $modulePerms = $data["{$moduleName}_permissions"];
            if (is_array($modulePerms)) {
                $allPermissions = array_merge($allPermissions, $modulePerms);
            }
        }

        // Update the role's name and guard from the form data.
        $record->update(Arr::only($data, ['name', 'guard_name']));

        // Sync all collected permissions.
        $record->syncPermissions($allPermissions);

        return $record;
    }
}

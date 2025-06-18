<?php

namespace App\Filament\Resources\ModuleRoleResource\Pages;

use App\Filament\Resources\ModuleRoleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Services\ModulePermissionService;
use Illuminate\Database\Eloquent\Model;

class CreateModuleRole extends CreateRecord
{
    protected static string $resource = ModuleRoleResource::class;    protected function beforeCreate(): void
    {
        // Ensure all module permissions are registered before creating roles
        ModulePermissionService::registerAllPermissions();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Don't try to save module permission fields as regular role fields
        $modules = \App\Services\ModulePermissionService::getModulesWithPermissions();
        
        foreach ($modules as $moduleName => $modulePermissions) {
            unset($data["{$moduleName}_permissions"]);
        }
        
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Create the role
        $record = static::getModel()::create([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'],
        ]);

        // Collect permissions from all module fields in the form state
        $allPermissions = [];
        $modules = \App\Services\ModulePermissionService::getModulesWithPermissions();
        $formData = $this->form->getState();
        
        foreach ($modules as $moduleName => $permissions) {
            $modulePermissions = $formData["{$moduleName}_permissions"] ?? [];
            $allPermissions = array_merge($allPermissions, $modulePermissions);
        }

        // Assign permissions
        if (!empty($allPermissions)) {
            $record->syncPermissions($allPermissions);
        }

        return $record;
    }
}

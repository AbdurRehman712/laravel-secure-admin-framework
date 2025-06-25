<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class ModulePermissionService
{
    /**
     * Default CRUD permissions for resources
     */
    const DEFAULT_PERMISSIONS = [
        'view_any',
        'view',
        'create',
        'update',
        'delete',
        'delete_any',
        'force_delete',
        'force_delete_any',
        'restore',
        'restore_any',
        'replicate',
    ];    /**
     * Get all modules with their permissions
     */
    public static function getModulesWithPermissions(): array
    {
        $modules = [];
        $modulesPath = base_path('Modules');
        
        if (File::exists($modulesPath)) {
            $moduleDirs = File::directories($modulesPath);
            
            foreach ($moduleDirs as $moduleDir) {
                $moduleName = basename($moduleDir);
                $modulePermissions = self::getModulePermissions($moduleName);
                
                // Only include modules that have permissions
                if (!empty($modulePermissions)) {
                    $modules[$moduleName] = $modulePermissions;
                }
            }
        }
        
        // Also include app-level resources (like ModuleRoleResource)
        $appPermissions = self::getAppPermissions();
        if (!empty($appPermissions)) {
            $modules['System'] = $appPermissions;
        }

        return $modules;
    }

    /**
     * Get permissions for a specific module
     */
    public static function getModulePermissions(string $moduleName): array
    {
        $permissions = [];
        
        // Check both possible resource paths
        $resourcePaths = [
            base_path("Modules/{$moduleName}/app/Filament/Resources"),
            base_path("Modules/{$moduleName}/Filament/Resources")
        ];
        
        foreach ($resourcePaths as $resourcesPath) {
            if (!File::exists($resourcesPath)) {
                continue;
            }

            $resourceFiles = File::glob($resourcesPath . '/*Resource.php');
            
            foreach ($resourceFiles as $resourceFile) {
                $resourceName = basename($resourceFile, '.php');
                $resourceKey = Str::snake(str_replace('Resource', '', $resourceName));
                
                foreach (self::DEFAULT_PERMISSIONS as $action) {
                    $permissions[] = [
                        'name' => "{$action}_{$resourceKey}",
                        'display_name' => ucwords(str_replace('_', ' ', "{$action} {$resourceKey}")),
                        'resource' => $resourceName,
                        'action' => $action,
                    ];
                }
            }
        }

        return $permissions;
    }

    /**
     * Get permissions for app-level resources (non-module resources)
     */
    public static function getAppPermissions(): array
    {
        $permissions = [];
        $resourcesPath = app_path('Filament/Resources');
        
        if (!File::exists($resourcesPath)) {
            return $permissions;
        }

        $resourceFiles = File::glob($resourcesPath . '/*Resource.php');
        
        foreach ($resourceFiles as $resourceFile) {
            $resourceName = basename($resourceFile, '.php');
            $resourceKey = Str::snake(str_replace('Resource', '', $resourceName));
            
            foreach (self::DEFAULT_PERMISSIONS as $action) {
                $permissions[] = [
                    'name' => "{$action}_{$resourceKey}",
                    'display_name' => ucwords(str_replace('_', ' ', "{$action} {$resourceKey}")),
                    'resource' => $resourceName,
                    'action' => $action,
                ];
            }
        }

        return $permissions;
    }

    /**
     * Register all permissions for all modules
     */
    public static function registerAllPermissions(): void
    {
        $modules = self::getModulesWithPermissions();
        
        foreach ($modules as $moduleName => $permissions) {
            self::registerModulePermissions($moduleName, $permissions);
        }
    }

    /**
     * Register permissions for a specific module
     */
    public static function registerModulePermissions(string $moduleName, array $permissions, string $guard = 'admin'): void
    {
        foreach ($permissions as $permissionData) {
            Permission::findOrCreate($permissionData['name'], $guard);
        }
    }

    /**
     * Get permissions grouped by modules for role assignment
     */
    public static function getPermissionsGroupedByModule(string $guard = 'admin'): array
    {
        $modules = self::getModulesWithPermissions();
        $grouped = [];
        
        foreach ($modules as $moduleName => $permissions) {
            $grouped[$moduleName] = [];
            
            foreach ($permissions as $permissionData) {
                $permission = Permission::where('name', $permissionData['name'])
                    ->where('guard_name', $guard)
                    ->first();
                    
                if ($permission) {
                    $grouped[$moduleName][] = [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'display_name' => $permissionData['display_name'],
                        'resource' => $permissionData['resource'],
                        'action' => $permissionData['action'],
                    ];
                }
            }
        }

        return $grouped;
    }

    /**
     * Assign module permissions to a role
     */
    public static function assignModulePermissionsToRole(Role $role, string $moduleName, array $permissions): void
    {
        $modulePermissions = collect($permissions)->filter(function ($permission) use ($moduleName) {
            return Str::contains($permission, Str::snake($moduleName));
        });

        $role->syncPermissions($role->permissions->merge(
            Permission::whereIn('name', $modulePermissions->toArray())
                ->where('guard_name', $role->guard_name)
                ->get()
        ));
    }

    /**
     * Get role permissions grouped by modules
     */
    public static function getRolePermissionsByModule(Role $role): array
    {
        $allModulePermissions = self::getPermissionsGroupedByModule($role->guard_name);
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        
        $result = [];
        
        foreach ($allModulePermissions as $moduleName => $permissions) {
            $result[$moduleName] = [];
            
            foreach ($permissions as $permission) {
                $result[$moduleName][] = array_merge($permission, [
                    'assigned' => in_array($permission['name'], $rolePermissions)
                ]);
            }
        }

        return $result;
    }
}

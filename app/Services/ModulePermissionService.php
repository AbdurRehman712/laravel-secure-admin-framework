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
                $modules[$moduleName] = self::getModulePermissions($moduleName);
            }
        }
        
        // Also include app-level resources (like ModuleRoleResource)
        $appPermissions = self::getAppPermissions();
        if (!empty($appPermissions)) {
            $modules['System'] = $appPermissions;
        }

        // Include AI Platform permissions
        $aiPlatformPermissions = self::getAiPlatformPermissions();
        if (!empty($aiPlatformPermissions)) {
            $modules['AI Platform'] = $aiPlatformPermissions;
        }

        return $modules;
    }

    /**
     * Get permissions for a specific module
     */
    public static function getModulePermissions(string $moduleName): array
    {
        $permissions = [];

        // Handle special module permissions (like ModuleBuilder)
        $specialPermissions = self::getSpecialModulePermissions($moduleName);
        if (!empty($specialPermissions)) {
            $permissions = array_merge($permissions, $specialPermissions);
        }

        // Handle regular Filament resources
        $resourcesPath = base_path("Modules/{$moduleName}/app/Filament/Resources");

        if (File::exists($resourcesPath)) {
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
     * Get special permissions for modules that don't follow standard resource patterns
     */
    private static function getSpecialModulePermissions(string $moduleName): array
    {
        $specialPermissions = [];

        // ModuleBuilder special permissions - these will be auto-created by AdminSeeder
        if ($moduleName === 'ModuleBuilder') {
            $specialPermissions = [
                [
                    'name' => 'view_module_editor',
                    'display_name' => 'View Module Editor',
                    'resource' => 'ModuleEditor',
                    'action' => 'view',
                ],
                [
                    'name' => 'create_modules',
                    'display_name' => 'Create Modules',
                    'resource' => 'ModuleBuilder',
                    'action' => 'create',
                ],
                [
                    'name' => 'generate_modules',
                    'display_name' => 'Generate Modules',
                    'resource' => 'ModuleBuilder',
                    'action' => 'generate',
                ],
                [
                    'name' => 'manage_module_builder',
                    'display_name' => 'Manage Module Builder',
                    'resource' => 'ModuleBuilder',
                    'action' => 'manage',
                ],
            ];
        }

        // ERDDesigner special permissions
        if ($moduleName === 'ERDDesigner') {
            $specialPermissions = [
                [
                    'name' => 'access_erd_designer',
                    'display_name' => 'Access ERD Designer',
                    'resource' => 'ERDDesigner',
                    'action' => 'access',
                ],
                [
                    'name' => 'view_erd_projects',
                    'display_name' => 'View ERD Projects',
                    'resource' => 'ERDProject',
                    'action' => 'view',
                ],
                [
                    'name' => 'create_erd_projects',
                    'display_name' => 'Create ERD Projects',
                    'resource' => 'ERDProject',
                    'action' => 'create',
                ],
                [
                    'name' => 'edit_erd_projects',
                    'display_name' => 'Edit ERD Projects',
                    'resource' => 'ERDProject',
                    'action' => 'edit',
                ],
                [
                    'name' => 'delete_erd_projects',
                    'display_name' => 'Delete ERD Projects',
                    'resource' => 'ERDProject',
                    'action' => 'delete',
                ],
                [
                    'name' => 'export_erd_projects',
                    'display_name' => 'Export ERD Projects',
                    'resource' => 'ERDProject',
                    'action' => 'export',
                ],
                [
                    'name' => 'import_erd_projects',
                    'display_name' => 'Import ERD Projects',
                    'resource' => 'ERDProject',
                    'action' => 'import',
                ],
                [
                    'name' => 'manage_erd_tables',
                    'display_name' => 'Manage ERD Tables',
                    'resource' => 'ERDTable',
                    'action' => 'manage',
                ],
                [
                    'name' => 'manage_erd_fields',
                    'display_name' => 'Manage ERD Fields',
                    'resource' => 'ERDField',
                    'action' => 'manage',
                ],
                [
                    'name' => 'manage_erd_relationships',
                    'display_name' => 'Manage ERD Relationships',
                    'resource' => 'ERDRelationship',
                    'action' => 'manage',
                ],
                [
                    'name' => 'export_sql_from_erd',
                    'display_name' => 'Export SQL from ERD',
                    'resource' => 'ERDDesigner',
                    'action' => 'export_sql',
                ],
                [
                    'name' => 'import_sql_to_erd',
                    'display_name' => 'Import SQL to ERD',
                    'resource' => 'ERDDesigner',
                    'action' => 'import_sql',
                ],
                [
                    'name' => 'integrate_erd_module_builder',
                    'display_name' => 'Integrate ERD with Module Builder',
                    'resource' => 'ERDDesigner',
                    'action' => 'integrate',
                ],
            ];
        }

        return $specialPermissions;
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
     * Get AI Platform specific permissions (Simplified 3-Role Structure)
     */
    public static function getAiPlatformPermissions(): array
    {
        return [
            // Core AI Platform Access
            [
                'name' => 'access_ai_platform',
                'display_name' => 'Access AI Development Platform',
                'resource' => 'AiPlatform',
                'action' => 'access',
            ],
            [
                'name' => 'view_application_templates',
                'display_name' => 'View Application Templates',
                'resource' => 'ApplicationTemplates',
                'action' => 'view',
            ],
            [
                'name' => 'create_projects_from_templates',
                'display_name' => 'Create Projects from Templates',
                'resource' => 'ApplicationTemplates',
                'action' => 'create_projects',
            ],
            [
                'name' => 'generate_modules_from_templates',
                'display_name' => 'Generate Modules from Templates',
                'resource' => 'ApplicationTemplates',
                'action' => 'generate_modules',
            ],

            // Project Management (Simplified)
            [
                'name' => 'access_project_workspace',
                'display_name' => 'Access Project Workspace',
                'resource' => 'ProjectWorkspace',
                'action' => 'access',
            ],
            [
                'name' => 'manage_project_teams',
                'display_name' => 'Manage Project Teams (3 Roles)',
                'resource' => 'ProjectTeams',
                'action' => 'manage',
            ],
            [
                'name' => 'approve_workspace_content',
                'display_name' => 'Approve Workspace Content',
                'resource' => 'WorkspaceContent',
                'action' => 'approve',
            ],

            // Workspace permissions
            [
                'name' => 'access_project_workspace',
                'display_name' => 'Access Project Workspace',
                'resource' => 'ProjectWorkspace',
                'action' => 'access',
            ],
            [
                'name' => 'create_workspace_content',
                'display_name' => 'Create Workspace Content',
                'resource' => 'ProjectWorkspace',
                'action' => 'create_content',
            ],
            [
                'name' => 'edit_workspace_content',
                'display_name' => 'Edit Workspace Content',
                'resource' => 'ProjectWorkspace',
                'action' => 'edit_content',
            ],
            [
                'name' => 'delete_workspace_content',
                'display_name' => 'Delete Workspace Content',
                'resource' => 'ProjectWorkspace',
                'action' => 'delete_content',
            ],
            [
                'name' => 'approve_workspace_content',
                'display_name' => 'Approve Workspace Content',
                'resource' => 'ProjectWorkspace',
                'action' => 'approve_content',
            ],

            // Simplified 3-Role Workspace Permissions
            [
                'name' => 'access_product_owner_workspace',
                'display_name' => 'Product Owner: User Stories & Requirements',
                'resource' => 'ProductOwnerWorkspace',
                'action' => 'access',
            ],
            [
                'name' => 'access_database_backend_workspace',
                'display_name' => 'Database/Backend Dev: Schema & APIs',
                'resource' => 'DatabaseBackendWorkspace',
                'action' => 'access',
            ],
            [
                'name' => 'access_project_manager_workspace',
                'display_name' => 'Project Manager: Planning & Deployment',
                'resource' => 'ProjectManagerWorkspace',
                'action' => 'access',
            ],

            // Module generation permissions
            [
                'name' => 'generate_modules',
                'display_name' => 'Generate Modules',
                'resource' => 'ModuleGenerator',
                'action' => 'generate',
            ],
            [
                'name' => 'install_modules',
                'display_name' => 'Install Modules',
                'resource' => 'ModuleGenerator',
                'action' => 'install',
            ],
            [
                'name' => 'manage_generated_modules',
                'display_name' => 'Manage Generated Modules',
                'resource' => 'ModuleGenerator',
                'action' => 'manage',
            ],

            // AI Platform dashboard
            [
                'name' => 'view_ai_platform_dashboard',
                'display_name' => 'View AI Platform Dashboard',
                'resource' => 'AiPlatformDashboard',
                'action' => 'view',
            ],
        ];
    }

    /**
     * Register all permissions for all modules
     */
    public static function registerAllPermissions(): void
    {
        // Check if database is ready before attempting to register permissions
        if (!self::isDatabaseReady()) {
            return;
        }

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
        // Check if database is ready before attempting to register permissions
        if (!self::isDatabaseReady()) {
            return;
        }

        try {
            foreach ($permissions as $permissionData) {
                Permission::findOrCreate($permissionData['name'], $guard);
            }
        } catch (\Exception $e) {
            // Silently fail during installation
            if (app()->environment('local')) {
                \Log::info("Permission registration failed for module {$moduleName}: " . $e->getMessage());
            }
        }
    }

    /**
     * Check if the database is ready for permission operations.
     */
    private static function isDatabaseReady(): bool
    {
        try {
            // Check if we're running migrations
            if (app()->runningInConsole() &&
                (in_array('migrate', $_SERVER['argv'] ?? []) ||
                 in_array('migrate:fresh', $_SERVER['argv'] ?? []) ||
                 in_array('migrate:reset', $_SERVER['argv'] ?? []))) {
                return false;
            }

            // Check if permissions table exists
            return \Schema::hasTable('permissions') && \Schema::hasTable('roles');
        } catch (\Exception $e) {
            return false;
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

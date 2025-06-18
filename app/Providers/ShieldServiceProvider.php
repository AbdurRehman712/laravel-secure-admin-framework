<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Gate;

class ShieldServiceProvider extends ServiceProvider
{
    /**
     * List of resources and their permissions
     */
    protected array $resources = [
        'admin' => [
            'view_admin',
            'view_any_admin',
            'create_admin',
            'update_admin',
            'delete_admin',
            'delete_any_admin',
            'force_delete_admin',
            'force_delete_any_admin',
            'restore_admin',
            'restore_any_admin',
            'replicate_admin',
        ],
        'user' => [
            'view_user',
            'view_any_user',
            'create_user',
            'update_user',
            'delete_user',
            'delete_any_user',
            'force_delete_user',
            'force_delete_any_user',
            'restore_user',
            'restore_any_user',
            'replicate_user',
        ],
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerResourcePermissions();
        $this->registerGates();
    }

    /**
     * Register resource permissions for both admin and web guards.
     */
    protected function registerResourcePermissions(): void
    {
        foreach ($this->resources as $resource => $permissions) {
            foreach ($permissions as $permission) {
                // Create permission for admin guard
                Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => 'admin',
                ]);

                // Create permission for web guard (for public users)
                Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => 'web',
                ]);
            }
        }
    }

    /**
     * Register gates for resource permissions.
     */
    protected function registerGates(): void
    {
        Gate::before(function ($user, $ability) {
            // Super admin can do everything
            if ($user->hasRole('super-admin', $user->guard_name ?? 'admin')) {
                return true;
            }

            return null; // Continue with normal permission checking
        });
    }    /**
     * Get all resource permissions for a specific guard.
     */
    public static function getResourcePermissions(string $guard = 'admin'): array
    {
        $instance = app(static::class);
        $permissions = [];
        
        foreach ($instance->resources as $resource => $resourcePermissions) {
            $permissions = array_merge($permissions, $resourcePermissions);
        }

        return $permissions;
    }

    /**
     * Get permissions for a specific resource.
     */
    public static function getPermissionsForResource(string $resource): array
    {
        $instance = app(static::class);
        return $instance->resources[$resource] ?? [];
    }

    /**
     * Generate permissions for a new resource.
     */
    public static function generatePermissionsForResource(string $resource, string $guard = 'admin'): array
    {
        $permissions = [
            "view_{$resource}",
            "view_any_{$resource}",
            "create_{$resource}",
            "update_{$resource}",
            "delete_{$resource}",
            "delete_any_{$resource}",
            "force_delete_{$resource}",
            "force_delete_any_{$resource}",
            "restore_{$resource}",
            "restore_any_{$resource}",
            "replicate_{$resource}",
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => $guard,
            ]);
        }

        return $permissions;
    }

    /**
     * Assign all permissions for a resource to a role.
     */
    public static function assignResourcePermissionsToRole(string $resource, string $roleName, string $guard = 'admin'): void
    {
        $role = Role::firstOrCreate([
            'name' => $roleName,
            'guard_name' => $guard,
        ]);

        $permissions = static::getPermissionsForResource($resource);
        
        foreach ($permissions as $permission) {
            $permissionModel = Permission::where('name', $permission)
                ->where('guard_name', $guard)
                ->first();
            
            if ($permissionModel) {
                $role->givePermissionTo($permissionModel);
            }
        }
    }
}

<?php

namespace App\Filament\Concerns;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasModulePermissions
{
    /**
     * Get the permission prefix for this resource.
     */
    public static function getPermissionPrefix(): string
    {
        return Str::snake(str_replace('Resource', '', class_basename(static::class)));
    }

    /**
     * Get all permissions for this resource.
     */
    public static function getResourcePermissions(): array
    {
        $prefix = static::getPermissionPrefix();
        
        return [
            "view_any_{$prefix}",
            "view_{$prefix}",
            "create_{$prefix}",
            "update_{$prefix}",
            "delete_{$prefix}",
            "delete_any_{$prefix}",
            "force_delete_{$prefix}",
            "force_delete_any_{$prefix}",
            "restore_{$prefix}",
            "restore_any_{$prefix}",
            "replicate_{$prefix}",
        ];
    }

    /**
     * Check if the user can access this resource.
     */
    public static function canAccess(): bool
    {
        $user = Filament::auth()->user();
        
        if (!$user) {
            return false;
        }

        $prefix = static::getPermissionPrefix();
        
        return $user->can("view_any_{$prefix}") || $user->can("view_{$prefix}");
    }

    /**
     * Check if the user can view any records.
     */
    public static function canViewAny(): bool
    {
        $user = Filament::auth()->user();
        
        if (!$user) {
            return false;
        }

        $prefix = static::getPermissionPrefix();
        
        return $user->can("view_any_{$prefix}");
    }

    /**
     * Check if the user can view the record.
     */
    public static function canView(Model $record): bool
    {
        $user = Filament::auth()->user();
        
        if (!$user) {
            return false;
        }

        $prefix = static::getPermissionPrefix();
        
        return $user->can("view_{$prefix}") || $user->can("view_any_{$prefix}");
    }

    /**
     * Check if the user can create records.
     */
    public static function canCreate(): bool
    {
        $user = Filament::auth()->user();
        
        if (!$user) {
            return false;
        }

        $prefix = static::getPermissionPrefix();
        
        return $user->can("create_{$prefix}");
    }

    /**
     * Check if the user can edit the record.
     */
    public static function canEdit(Model $record): bool
    {
        $user = Filament::auth()->user();
        
        if (!$user) {
            return false;
        }

        $prefix = static::getPermissionPrefix();
        
        return $user->can("update_{$prefix}");
    }

    /**
     * Check if the user can delete the record.
     */
    public static function canDelete(Model $record): bool
    {
        $user = Filament::auth()->user();
        
        if (!$user) {
            return false;
        }

        $prefix = static::getPermissionPrefix();
        
        return $user->can("delete_{$prefix}");
    }

    /**
     * Check if the user can delete any records.
     */
    public static function canDeleteAny(): bool
    {
        $user = Filament::auth()->user();
        
        if (!$user) {
            return false;
        }

        $prefix = static::getPermissionPrefix();
        
        return $user->can("delete_any_{$prefix}");
    }

    /**
     * Check if the user can force delete the record.
     */
    public static function canForceDelete(Model $record): bool
    {
        $user = Filament::auth()->user();
        
        if (!$user) {
            return false;
        }

        $prefix = static::getPermissionPrefix();
        
        return $user->can("force_delete_{$prefix}");
    }

    /**
     * Check if the user can restore the record.
     */
    public static function canRestore(Model $record): bool
    {
        $user = Filament::auth()->user();
        
        if (!$user) {
            return false;
        }

        $prefix = static::getPermissionPrefix();
        
        return $user->can("restore_{$prefix}");
    }

    /**
     * Check if the user can replicate the record.
     */
    public static function canReplicate(Model $record): bool
    {
        $user = Filament::auth()->user();
        
        if (!$user) {
            return false;
        }

        $prefix = static::getPermissionPrefix();
        
        return $user->can("replicate_{$prefix}");
    }
}

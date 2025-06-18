<?php

namespace App\Filament\Concerns;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;

trait HasShield
{
    /**
     * Get the permission prefix for this resource.
     */
    public static function getPermissionPrefix(): string
    {
        return str(static::getModel())
            ->afterLast('\\')
            ->lower()
            ->toString();
    }

    /**
     * Get all permissions for this resource.
     */
    public static function getResourcePermissions(): array
    {
        $prefix = static::getPermissionPrefix();
        
        return [
            "view_{$prefix}",
            "view_any_{$prefix}",
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
     * Check if the user can perform the given action.
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
     * Check if the user can force delete any records.
     */
    public static function canForceDeleteAny(): bool
    {
        $user = Filament::auth()->user();
        
        if (!$user) {
            return false;
        }

        $prefix = static::getPermissionPrefix();
        
        return $user->can("force_delete_any_{$prefix}");
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
     * Check if the user can restore any records.
     */
    public static function canRestoreAny(): bool
    {
        $user = Filament::auth()->user();
        
        if (!$user) {
            return false;
        }

        $prefix = static::getPermissionPrefix();
        
        return $user->can("restore_any_{$prefix}");
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

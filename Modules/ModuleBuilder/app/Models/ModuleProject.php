<?php

namespace Modules\ModuleBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModuleProject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'namespace',
        'description',
        'author_name',
        'author_email',
        'homepage',
        'version',
        'icon',
        'status',
        'enabled',
        'has_api',
        'has_web_routes',
        'has_admin_panel',
        'has_frontend',
        'has_permissions',
        'has_middleware',
        'has_commands',
        'has_events',
        'has_jobs',
        'has_mail',
        'has_notifications',
        'export_path',
        'settings'
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'has_api' => 'boolean',
        'has_web_routes' => 'boolean',
        'has_admin_panel' => 'boolean',
        'has_frontend' => 'boolean',
        'has_permissions' => 'boolean',
        'has_middleware' => 'boolean',
        'has_commands' => 'boolean',
        'has_events' => 'boolean',
        'has_jobs' => 'boolean',
        'has_mail' => 'boolean',
        'has_notifications' => 'boolean',
        'settings' => 'array',
        'deleted_at' => 'datetime'
    ];

    protected $attributes = [
        'version' => '1.0.0',
        'status' => 'draft',
        'enabled' => false,
        'has_api' => false,
        'has_web_routes' => false,
        'has_admin_panel' => true,
        'has_frontend' => false,
        'has_permissions' => true,
        'has_middleware' => false,
        'has_commands' => false,
        'has_events' => false,
        'has_jobs' => false,
        'has_mail' => false,
        'has_notifications' => false,
    ];

    public function tables(): HasMany
    {
        return $this->hasMany(ModuleTable::class, 'project_id');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(ModulePermission::class, 'project_id');
    }

    public function components(): HasMany
    {
        return $this->hasMany(ModuleComponent::class, 'project_id');
    }

    public function allFields(): HasManyThrough
    {
        return $this->hasManyThrough(ModuleField::class, ModuleTable::class, 'project_id', 'table_id');
    }

    public function getModulePathAttribute(): string
    {
        return base_path("Modules/{$this->name}");
    }

    public function getNamespaceAttribute($value): string
    {
        return $value ?? "Modules\\{$this->name}";
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'secondary',
            'building' => 'warning',
            'built' => 'success',
            'error' => 'danger',
            default => 'secondary'
        };
    }

    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}

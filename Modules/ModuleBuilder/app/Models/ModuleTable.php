<?php

namespace Modules\ModuleBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ModuleTable extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'display_name',
        'model_name',
        'controller_name',
        'migration_name',
        'resource_name',
        'is_pivot',
        'has_timestamps',
        'has_soft_deletes',
        'has_uuid',
        'description',
        'icon',
        'sort_order',
        'settings'
    ];

    protected $casts = [
        'is_pivot' => 'boolean',
        'has_timestamps' => 'boolean',
        'has_soft_deletes' => 'boolean',
        'has_uuid' => 'boolean',
        'settings' => 'array'
    ];

    protected $attributes = [
        'has_timestamps' => true,
        'has_soft_deletes' => false,
        'has_uuid' => false,
        'is_pivot' => false,
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ModuleProject::class, 'project_id');
    }

    // Alias for Filament resource compatibility
    public function moduleProject(): BelongsTo
    {
        return $this->belongsTo(ModuleProject::class, 'project_id');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(ModuleField::class, 'table_id')->orderBy('sort_order');
    }

    public function relationships(): HasMany
    {
        return $this->hasMany(ModuleRelationship::class, 'from_table_id');
    }

    public function inverseRelationships(): HasMany
    {
        return $this->hasMany(ModuleRelationship::class, 'to_table_id');
    }

    // Auto-generate names based on table name
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = Str::snake($value);
        
        if (empty($this->attributes['display_name'])) {
            $this->attributes['display_name'] = Str::title(str_replace('_', ' ', $value));
        }
        
        if (empty($this->attributes['model_name'])) {
            $this->attributes['model_name'] = Str::studly(Str::singular($value));
        }
        
        if (empty($this->attributes['controller_name'])) {
            $this->attributes['controller_name'] = Str::studly($value) . 'Controller';
        }
        
        if (empty($this->attributes['migration_name'])) {
            $this->attributes['migration_name'] = 'create_' . Str::snake($value) . '_table';
        }
        
        if (empty($this->attributes['resource_name'])) {
            $this->attributes['resource_name'] = Str::studly(Str::singular($value)) . 'Resource';
        }
    }

    public function getTableNameAttribute(): string
    {
        return $this->name;
    }

    public function getSingularNameAttribute(): string
    {
        return Str::singular($this->name);
    }

    public function getPluralNameAttribute(): string
    {
        return Str::plural($this->name);
    }

    public function getStudlyNameAttribute(): string
    {
        return Str::studly($this->name);
    }

    public function getStudlySingularNameAttribute(): string
    {
        return Str::studly(Str::singular($this->name));
    }

    public function getKebabNameAttribute(): string
    {
        return Str::kebab($this->name);
    }

    public function getCamelNameAttribute(): string
    {
        return Str::camel($this->name);
    }

    public function getCamelSingularNameAttribute(): string
    {
        return Str::camel(Str::singular($this->name));
    }
}

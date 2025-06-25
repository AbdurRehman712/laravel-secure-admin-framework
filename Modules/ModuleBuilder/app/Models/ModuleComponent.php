<?php

namespace Modules\ModuleBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'type',
        'name',
        'class_name',
        'description',
        'namespace',
        'file_path',
        'template',
        'settings'
    ];

    protected $casts = [
        'settings' => 'array'
    ];

    // Component types
    public const TYPES = [
        'command' => 'Artisan Command',
        'event' => 'Event',
        'listener' => 'Event Listener',
        'job' => 'Job',
        'mail' => 'Mailable',
        'notification' => 'Notification',
        'middleware' => 'Middleware',
        'request' => 'Form Request',
        'rule' => 'Validation Rule',
        'policy' => 'Policy',
        'observer' => 'Model Observer',
        'scope' => 'Query Scope',
        'cast' => 'Custom Cast',
        'channel' => 'Broadcast Channel',
        'exception' => 'Exception',
        'facade' => 'Facade',
        'provider' => 'Service Provider',
        'seeder' => 'Database Seeder',
        'factory' => 'Model Factory',
        'test' => 'Test Case'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ModuleProject::class, 'project_id');
    }

    public function getFullNamespaceAttribute(): string
    {
        $projectNamespace = $this->project->namespace;
        return $this->namespace ? "{$projectNamespace}\\{$this->namespace}" : $projectNamespace;
    }

    public function getFullClassNameAttribute(): string
    {
        return $this->full_namespace . '\\' . $this->class_name;
    }

    public function getFilePathAttribute($value): string
    {
        if ($value) {
            return $value;
        }

        // Auto-generate file path based on type and name
        $basePath = match ($this->type) {
            'command' => 'app/Console/Commands',
            'event' => 'app/Events',
            'listener' => 'app/Listeners',
            'job' => 'app/Jobs',
            'mail' => 'app/Mail',
            'notification' => 'app/Notifications',
            'middleware' => 'app/Http/Middleware',
            'request' => 'app/Http/Requests',
            'rule' => 'app/Rules',
            'policy' => 'app/Policies',
            'observer' => 'app/Observers',
            'cast' => 'app/Casts',
            'channel' => 'app/Broadcasting',
            'exception' => 'app/Exceptions',
            'facade' => 'app/Facades',
            'provider' => 'app/Providers',
            'seeder' => 'database/seeders',
            'factory' => 'database/factories',
            'test' => 'tests/Feature',
            default => 'app'
        };

        return $basePath . '/' . $this->class_name . '.php';
    }

    public function getTemplateAttribute($value): string
    {
        if ($value) {
            return $value;
        }

        // Return default template based on type
        return $this->type . '.stub';
    }
}

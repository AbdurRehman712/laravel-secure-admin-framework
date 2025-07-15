<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'module_name',
        'module_slug',
        'description',
        'generated_by',
        'generation_data',
        'file_structure',
        'status',
        'version',
        'ai_generated',
    ];

    protected $casts = [
        'generation_data' => 'array',
        'file_structure' => 'array',
        'ai_generated' => 'boolean',
    ];

    /**
     * Module statuses
     */
    const STATUS_GENERATED = 'generated';
    const STATUS_INSTALLED = 'installed';
    const STATUS_ACTIVE = 'active';
    const STATUS_DISABLED = 'disabled';
    const STATUS_ERROR = 'error';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_GENERATED => 'Generated',
            self::STATUS_INSTALLED => 'Installed',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_DISABLED => 'Disabled',
            self::STATUS_ERROR => 'Error',
        ];
    }

    /**
     * Get the project this module belongs to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the admin who generated this module
     */
    public function generator(): BelongsTo
    {
        return $this->belongsTo(\Modules\Core\app\Models\Admin::class, 'generated_by');
    }

    /**
     * Check if module exists in filesystem
     */
    public function existsInFilesystem(): bool
    {
        return is_dir(base_path("Modules/{$this->module_name}"));
    }

    /**
     * Get module path
     */
    public function getModulePath(): string
    {
        return base_path("Modules/{$this->module_name}");
    }

    /**
     * Get module.json content
     */
    public function getModuleJson(): ?array
    {
        $path = $this->getModulePath() . '/module.json';
        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true);
        }
        return null;
    }

    /**
     * Install the module
     */
    public function install(): bool
    {
        try {
            if (!$this->existsInFilesystem()) {
                return false;
            }

            // Run migrations if they exist
            $migrationsPath = $this->getModulePath() . '/database/migrations';
            if (is_dir($migrationsPath)) {
                \Artisan::call('migrate', [
                    '--path' => "Modules/{$this->module_name}/database/migrations"
                ]);
            }

            // Register permissions
            $this->registerPermissions();

            $this->update(['status' => self::STATUS_INSTALLED]);
            return true;
        } catch (\Exception $e) {
            $this->update(['status' => self::STATUS_ERROR]);
            \Log::error("Failed to install module {$this->module_name}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Activate the module
     */
    public function activate(): bool
    {
        try {
            if ($this->status !== self::STATUS_INSTALLED) {
                $this->install();
            }

            // Clear Filament cache
            \Artisan::call('filament:clear-cached-components');

            $this->update(['status' => self::STATUS_ACTIVE]);
            return true;
        } catch (\Exception $e) {
            $this->update(['status' => self::STATUS_ERROR]);
            return false;
        }
    }

    /**
     * Deactivate the module
     */
    public function deactivate(): bool
    {
        try {
            $this->update(['status' => self::STATUS_DISABLED]);
            
            // Clear Filament cache
            \Artisan::call('filament:clear-cached-components');
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Register module permissions
     */
    private function registerPermissions(): void
    {
        if (class_exists(\App\Services\ModulePermissionService::class)) {
            $permissions = \App\Services\ModulePermissionService::getModulePermissions($this->module_name);
            \App\Services\ModulePermissionService::registerModulePermissions(
                $this->module_name, 
                $permissions, 
                'admin'
            );
        }
    }

    /**
     * Get generation summary
     */
    public function getGenerationSummary(): array
    {
        $data = $this->generation_data ?? [];
        
        return [
            'tables_count' => count($data['tables'] ?? []),
            'models_count' => count($data['models'] ?? []),
            'resources_count' => count($data['resources'] ?? []),
            'ai_generated' => $this->ai_generated,
            'generated_at' => $this->created_at,
            'generator' => $this->generator?->name,
        ];
    }

    /**
     * Export module data
     */
    public function exportData(): array
    {
        return [
            'module' => $this->toArray(),
            'file_structure' => $this->file_structure,
            'generation_data' => $this->generation_data,
            'module_json' => $this->getModuleJson(),
            'exists_in_filesystem' => $this->existsInFilesystem(),
        ];
    }

    /**
     * Scope for active modules
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for AI generated modules
     */
    public function scopeAiGenerated($query)
    {
        return $query->where('ai_generated', true);
    }
}

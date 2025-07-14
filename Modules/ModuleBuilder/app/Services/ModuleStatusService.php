<?php

namespace Modules\ModuleBuilder\app\Services;

use Illuminate\Support\Facades\File;

class ModuleStatusService
{
    private string $statusFile;

    public function __construct()
    {
        $this->statusFile = base_path('modules_statuses.json');
    }

    /**
     * Get all module statuses
     */
    public function getAllStatuses(): array
    {
        if (!File::exists($this->statusFile)) {
            return [];
        }

        return json_decode(File::get($this->statusFile), true) ?? [];
    }

    /**
     * Check if a module is enabled
     */
    public function isEnabled(string $moduleName): bool
    {
        $statuses = $this->getAllStatuses();
        return $statuses[$moduleName] ?? false;
    }

    /**
     * Enable a module
     */
    public function enable(string $moduleName): void
    {
        $this->setStatus($moduleName, true);
    }

    /**
     * Disable a module
     */
    public function disable(string $moduleName): void
    {
        $this->setStatus($moduleName, false);
    }

    /**
     * Toggle module status
     */
    public function toggle(string $moduleName): bool
    {
        $newStatus = !$this->isEnabled($moduleName);
        $this->setStatus($moduleName, $newStatus);
        return $newStatus;
    }

    /**
     * Set module status
     */
    private function setStatus(string $moduleName, bool $enabled): void
    {
        $statuses = $this->getAllStatuses();
        $statuses[$moduleName] = $enabled;
        
        File::put($this->statusFile, json_encode($statuses, JSON_PRETTY_PRINT));
    }

    /**
     * Remove module from status file
     */
    public function remove(string $moduleName): void
    {
        $statuses = $this->getAllStatuses();
        unset($statuses[$moduleName]);
        
        File::put($this->statusFile, json_encode($statuses, JSON_PRETTY_PRINT));
    }

    /**
     * Get enabled modules only
     */
    public function getEnabledModules(): array
    {
        return array_keys(array_filter($this->getAllStatuses()));
    }

    /**
     * Get disabled modules only
     */
    public function getDisabledModules(): array
    {
        return array_keys(array_filter($this->getAllStatuses(), fn($status) => !$status));
    }

    /**
     * Check if module exists in filesystem
     */
    public function moduleExists(string $moduleName): bool
    {
        return File::exists(base_path("Modules/{$moduleName}/module.json"));
    }

    /**
     * Sync status file with actual modules in filesystem
     */
    public function syncWithFilesystem(): array
    {
        $statuses = $this->getAllStatuses();
        $modulesPath = base_path('Modules');
        $changes = ['added' => [], 'removed' => []];

        if (!File::exists($modulesPath)) {
            return $changes;
        }

        // Get all module directories
        $directories = File::directories($modulesPath);
        $existingModules = [];

        foreach ($directories as $directory) {
            $moduleName = basename($directory);
            if (File::exists($directory . '/module.json')) {
                $existingModules[] = $moduleName;
                
                // Add new modules as disabled by default
                if (!isset($statuses[$moduleName])) {
                    $statuses[$moduleName] = false;
                    $changes['added'][] = $moduleName;
                }
            }
        }

        // Remove modules that no longer exist
        foreach ($statuses as $moduleName => $status) {
            if (!in_array($moduleName, $existingModules)) {
                unset($statuses[$moduleName]);
                $changes['removed'][] = $moduleName;
            }
        }

        File::put($this->statusFile, json_encode($statuses, JSON_PRETTY_PRINT));
        
        return $changes;
    }
}

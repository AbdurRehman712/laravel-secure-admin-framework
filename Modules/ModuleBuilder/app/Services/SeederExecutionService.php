<?php

namespace Modules\ModuleBuilder\app\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SeederExecutionService
{
    /**
     * Execute all seeders for a module in the correct order
     */
    public function executeModuleSeeders(string $moduleName): array
    {
        $results = [];
        $seedersPath = base_path("Modules/{$moduleName}/database/seeders");
        
        if (!File::exists($seedersPath)) {
            throw new \Exception("Seeders directory not found for module {$moduleName}");
        }

        // Try to run the main module seeder first
        $mainSeederClass = "Modules\\{$moduleName}\\database\\seeders\\{$moduleName}DatabaseSeeder";
        if (class_exists($mainSeederClass)) {
            $results[] = $this->executeSingleSeeder($mainSeederClass);
        } else {
            // Get all seeder files and execute them in dependency order
            $seederFiles = File::glob($seedersPath . '/*Seeder.php');
            $orderedSeeders = $this->orderSeedersByDependencies($moduleName, $seederFiles);
            
            foreach ($orderedSeeders as $seederClass) {
                $results[] = $this->executeSingleSeeder($seederClass);
            }
        }

        return $results;
    }

    /**
     * Execute a single seeder
     */
    public function executeSingleSeeder(string $seederClass): array
    {
        try {
            $startTime = microtime(true);
            
            // Get record count before seeding
            $modelClass = $this->getModelClassFromSeeder($seederClass);
            $beforeCount = $modelClass ? $modelClass::count() : 0;
            
            Artisan::call('db:seed', ['--class' => $seederClass]);
            
            // Get record count after seeding
            $afterCount = $modelClass ? $modelClass::count() : 0;
            $recordsCreated = $afterCount - $beforeCount;
            
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            return [
                'seeder' => $seederClass,
                'status' => 'success',
                'records_created' => $recordsCreated,
                'execution_time_ms' => $executionTime,
                'message' => "Successfully created {$recordsCreated} records"
            ];
            
        } catch (\Exception $e) {
            return [
                'seeder' => $seederClass,
                'status' => 'error',
                'records_created' => 0,
                'execution_time_ms' => 0,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Execute factory for a specific model
     */
    public function executeModelFactory(string $moduleName, string $modelName, int $count = 10): array
    {
        try {
            $modelClass = "Modules\\{$moduleName}\\app\\Models\\{$modelName}";
            
            if (!class_exists($modelClass)) {
                throw new \Exception("Model class {$modelClass} not found");
            }

            $startTime = microtime(true);
            $beforeCount = $modelClass::count();
            
            // Create records using factory
            $modelClass::factory()->count($count)->create();
            
            $afterCount = $modelClass::count();
            $recordsCreated = $afterCount - $beforeCount;
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            return [
                'model' => $modelName,
                'status' => 'success',
                'records_created' => $recordsCreated,
                'execution_time_ms' => $executionTime,
                'message' => "Successfully created {$recordsCreated} {$modelName} records"
            ];
            
        } catch (\Exception $e) {
            return [
                'model' => $modelName,
                'status' => 'error',
                'records_created' => 0,
                'execution_time_ms' => 0,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Order seeders by their dependencies (models with foreign keys should be seeded after their dependencies)
     */
    private function orderSeedersByDependencies(string $moduleName, array $seederFiles): array
    {
        $seeders = [];
        $dependencies = [];
        
        foreach ($seederFiles as $seederFile) {
            $seederName = pathinfo($seederFile, PATHINFO_FILENAME);
            $seederClass = "Modules\\{$moduleName}\\database\\seeders\\{$seederName}";
            
            if (class_exists($seederClass)) {
                $modelName = str_replace('Seeder', '', $seederName);
                $seeders[$modelName] = $seederClass;
                $dependencies[$modelName] = $this->getModelDependencies($moduleName, $modelName);
            }
        }

        // Simple topological sort
        $ordered = [];
        $visited = [];
        
        foreach ($seeders as $modelName => $seederClass) {
            $this->visitSeeder($modelName, $seeders, $dependencies, $visited, $ordered);
        }
        
        return array_reverse($ordered);
    }

    /**
     * Get model dependencies (foreign key relationships)
     */
    private function getModelDependencies(string $moduleName, string $modelName): array
    {
        $dependencies = [];
        $modelClass = "Modules\\{$moduleName}\\app\\Models\\{$modelName}";
        
        if (!class_exists($modelClass)) {
            return $dependencies;
        }

        try {
            // Get table name and check for foreign key columns
            $model = new $modelClass();
            $table = $model->getTable();
            $columns = DB::getSchemaBuilder()->getColumnListing($table);
            
            foreach ($columns as $column) {
                if (Str::endsWith($column, '_id') && $column !== 'id') {
                    $relatedModel = Str::studly(str_replace('_id', '', $column));
                    $dependencies[] = $relatedModel;
                }
            }
        } catch (\Exception $e) {
            // If we can't determine dependencies, return empty array
        }
        
        return $dependencies;
    }

    /**
     * Recursive function for topological sort
     */
    private function visitSeeder(string $modelName, array $seeders, array $dependencies, array &$visited, array &$ordered): void
    {
        if (isset($visited[$modelName])) {
            return;
        }
        
        $visited[$modelName] = true;
        
        foreach ($dependencies[$modelName] ?? [] as $dependency) {
            if (isset($seeders[$dependency])) {
                $this->visitSeeder($dependency, $seeders, $dependencies, $visited, $ordered);
            }
        }
        
        if (isset($seeders[$modelName])) {
            $ordered[] = $seeders[$modelName];
        }
    }

    /**
     * Get model class from seeder class name
     */
    private function getModelClassFromSeeder(string $seederClass): ?string
    {
        // Extract module and model name from seeder class
        if (preg_match('/Modules\\\\(.+?)\\\\database\\\\seeders\\\\(.+?)Seeder/', $seederClass, $matches)) {
            $moduleName = $matches[1];
            $modelName = $matches[2];
            $modelClass = "Modules\\{$moduleName}\\app\\Models\\{$modelName}";
            
            return class_exists($modelClass) ? $modelClass : null;
        }
        
        return null;
    }

    /**
     * Check if a module has seeders
     */
    public function hasModuleSeeders(string $moduleName): bool
    {
        $seedersPath = base_path("Modules/{$moduleName}/database/seeders");
        if (!File::exists($seedersPath)) {
            return false;
        }
        
        $seederFiles = File::glob($seedersPath . '/*Seeder.php');
        return count($seederFiles) > 0;
    }

    /**
     * Check if a module has factories
     */
    public function hasModuleFactories(string $moduleName): bool
    {
        $factoriesPath = base_path("Modules/{$moduleName}/database/factories");
        if (!File::exists($factoriesPath)) {
            return false;
        }
        
        $factoryFiles = File::glob($factoriesPath . '/*Factory.php');
        return count($factoryFiles) > 0;
    }

    /**
     * Get all models for a module that have factories
     */
    public function getModelsWithFactories(string $moduleName): array
    {
        $models = [];
        $factoriesPath = base_path("Modules/{$moduleName}/database/factories");
        
        if (File::exists($factoriesPath)) {
            $factoryFiles = File::glob($factoriesPath . '/*Factory.php');
            foreach ($factoryFiles as $factoryFile) {
                $factoryName = pathinfo($factoryFile, PATHINFO_FILENAME);
                $modelName = str_replace('Factory', '', $factoryName);
                $models[] = $modelName;
            }
        }
        
        return $models;
    }
}

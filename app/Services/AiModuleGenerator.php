<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectModule;
use App\Models\ProjectWorkspaceContent;
use Modules\ModuleBuilder\app\Services\EnhancedModuleGenerator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class AiModuleGenerator
{
    protected Project $project;
    protected array $workspaceData;

    public function __construct(Project $project)
    {
        $this->project = $project;
        $this->loadWorkspaceData();
    }

    /**
     * Load all workspace content for the project
     */
    protected function loadWorkspaceData(): void
    {
        $this->workspaceData = $this->project->workspaceContent()
            ->where('status', 'approved')
            ->get()
            ->groupBy('role')
            ->toArray();
    }

    /**
     * Generate modules based on AI workspace content
     */
    public function generateModules(): array
    {
        $generatedModules = [];

        // Extract database schema from database admin content
        $databaseSchemas = $this->extractDatabaseSchemas();
        
        // Extract user stories from product owner content
        $userStories = $this->extractUserStories();

        // Generate modules for each table/entity
        foreach ($databaseSchemas as $schema) {
            foreach ($schema['tables'] as $table) {
                $moduleName = $this->generateModuleName($table['name']);
                
                // Skip if module already exists
                if ($this->moduleExists($moduleName)) {
                    continue;
                }

                $moduleData = $this->prepareModuleData($table, $userStories);
                $module = $this->generateModule($moduleName, $moduleData);
                
                if ($module) {
                    $generatedModules[] = $module;
                }
            }
        }

        return $generatedModules;
    }

    /**
     * Extract database schemas from workspace content
     */
    protected function extractDatabaseSchemas(): array
    {
        $schemas = [];
        
        if (isset($this->workspaceData['database_admin'])) {
            foreach ($this->workspaceData['database_admin'] as $content) {
                if ($content['content_type'] === 'database_schema' && isset($content['parsed_data']['tables'])) {
                    $schemas[] = $content['parsed_data'];
                }
            }
        }

        return $schemas;
    }

    /**
     * Extract user stories from workspace content
     */
    protected function extractUserStories(): array
    {
        $stories = [];
        
        if (isset($this->workspaceData['product_owner'])) {
            foreach ($this->workspaceData['product_owner'] as $content) {
                if ($content['content_type'] === 'user_stories' && isset($content['parsed_data']['stories'])) {
                    $stories = array_merge($stories, $content['parsed_data']['stories']);
                }
            }
        }

        return $stories;
    }

    /**
     * Generate module name from table name
     */
    protected function generateModuleName(string $tableName): string
    {
        // Convert table name to module name (e.g., 'products' -> 'Product')
        $singular = Str::singular($tableName);
        return Str::studly($singular);
    }

    /**
     * Check if module already exists
     */
    protected function moduleExists(string $moduleName): bool
    {
        return $this->project->generatedModules()
            ->where('module_name', $moduleName)
            ->exists();
    }

    /**
     * Prepare module data for generation
     */
    protected function prepareModuleData(array $table, array $userStories): array
    {
        $tableName = $table['name'];
        $modelName = Str::studly(Str::singular($tableName));
        
        // Convert database fields to module builder format
        $fields = [];
        foreach ($table['fields'] as $field) {
            if ($field['name'] === 'id' || in_array($field['name'], ['created_at', 'updated_at'])) {
                continue; // Skip auto-generated fields
            }

            $fieldType = $this->mapDatabaseTypeToModuleType($field['type']);
            $fields[] = [
                'name' => $field['name'],
                'type' => $fieldType,
                'required' => !($field['nullable'] ?? false),
                'length' => $field['length'] ?? null,
                'default' => $field['default'] ?? null,
            ];
        }

        // Extract relationships
        $relationships = [];
        if (isset($table['relationships'])) {
            foreach ($table['relationships'] as $relation) {
                $relationships[] = [
                    'type' => $relation['type'],
                    'related_model' => Str::studly(Str::singular($relation['related_table'])),
                    'foreign_key' => $relation['foreign_key'],
                ];
            }
        }

        // Find related user stories
        $relatedStories = $this->findRelatedUserStories($tableName, $userStories);

        return [
            'module_name' => Str::studly(Str::singular($tableName)),
            'description' => "Generated module for {$tableName} management",
            'tables' => [
                [
                    'name' => $tableName,
                    'model_name' => $modelName,
                    'fields' => $fields,
                    'relationships' => $relationships,
                ]
            ],
            'user_stories' => $relatedStories,
            'ai_generated' => true,
        ];
    }

    /**
     * Map database field types to module builder types
     */
    protected function mapDatabaseTypeToModuleType(string $dbType): string
    {
        return match (strtolower($dbType)) {
            'varchar', 'char' => 'string',
            'text', 'longtext' => 'text',
            'int', 'integer', 'bigint' => 'integer',
            'decimal', 'float', 'double' => 'decimal',
            'boolean', 'bool' => 'boolean',
            'date' => 'date',
            'datetime', 'timestamp' => 'datetime',
            'json' => 'json',
            'enum' => 'enum',
            default => 'string',
        };
    }

    /**
     * Find user stories related to a table/entity
     */
    protected function findRelatedUserStories(string $tableName, array $userStories): array
    {
        $relatedStories = [];
        $entityName = Str::singular($tableName);
        
        foreach ($userStories as $story) {
            $storyText = strtolower($story['title'] . ' ' . $story['description']);
            
            // Check if story mentions the entity
            if (str_contains($storyText, $entityName) || 
                str_contains($storyText, $tableName) ||
                str_contains($storyText, Str::plural($entityName))) {
                $relatedStories[] = $story;
            }
        }

        return $relatedStories;
    }

    /**
     * Generate the actual module
     */
    protected function generateModule(string $moduleName, array $moduleData): ?ProjectModule
    {
        try {
            // Use the existing Enhanced Module Generator
            $generator = new EnhancedModuleGenerator($moduleName, $moduleData);
            $generator->generate();

            // Record the generated module
            $projectModule = ProjectModule::create([
                'project_id' => $this->project->id,
                'module_name' => $moduleName,
                'module_slug' => Str::slug($moduleName),
                'description' => $moduleData['description'],
                'generated_by' => Auth::guard('admin')->id(),
                'generation_data' => $moduleData,
                'file_structure' => $this->getGeneratedFileStructure($moduleName),
                'status' => 'generated',
                'ai_generated' => true,
            ]);

            return $projectModule;

        } catch (\Exception $e) {
            \Log::error("Failed to generate module {$moduleName}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get the file structure of the generated module
     */
    protected function getGeneratedFileStructure(string $moduleName): array
    {
        $modulePath = base_path("Modules/{$moduleName}");
        
        if (!is_dir($modulePath)) {
            return [];
        }

        return [
            'models' => $this->getFilesInDirectory("{$modulePath}/app/Models"),
            'resources' => $this->getFilesInDirectory("{$modulePath}/app/Filament/Resources"),
            'migrations' => $this->getFilesInDirectory("{$modulePath}/database/migrations"),
            'factories' => $this->getFilesInDirectory("{$modulePath}/database/factories"),
            'seeders' => $this->getFilesInDirectory("{$modulePath}/database/seeders"),
        ];
    }

    /**
     * Get files in a directory
     */
    protected function getFilesInDirectory(string $path): array
    {
        if (!is_dir($path)) {
            return [];
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * Generate all modules and install them
     */
    public function generateAndInstallModules(): array
    {
        $modules = $this->generateModules();
        $results = [];

        foreach ($modules as $module) {
            $installed = $module->install();
            $activated = $installed ? $module->activate() : false;
            
            $results[] = [
                'module' => $module,
                'installed' => $installed,
                'activated' => $activated,
            ];
        }

        return $results;
    }

    /**
     * Get generation summary
     */
    public function getGenerationSummary(): array
    {
        $schemas = $this->extractDatabaseSchemas();
        $stories = $this->extractUserStories();
        
        $totalTables = 0;
        foreach ($schemas as $schema) {
            $totalTables += count($schema['tables'] ?? []);
        }

        return [
            'database_schemas' => count($schemas),
            'total_tables' => $totalTables,
            'user_stories' => count($stories),
            'existing_modules' => $this->project->generatedModules()->count(),
            'workspace_content_items' => $this->project->workspaceContent()->count(),
        ];
    }
}

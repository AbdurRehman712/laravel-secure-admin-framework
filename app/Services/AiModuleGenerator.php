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

        // Group tables by module name and generate one module per group
        $moduleGroups = [];
        foreach ($databaseSchemas as $schema) {
            foreach ($schema['tables'] as $table) {
                $moduleName = $this->generateModuleName($table['name']);

                if (!isset($moduleGroups[$moduleName])) {
                    $moduleGroups[$moduleName] = [];
                }
                $moduleGroups[$moduleName][] = $table;
            }
        }

        // Generate one module for each group
        foreach ($moduleGroups as $moduleName => $tables) {
            // Skip if module already exists
            if ($this->moduleExists($moduleName)) {
                continue;
            }

            // Combine all tables into one module
            $moduleData = $this->prepareModuleDataForMultipleTables($tables, $userStories);
            $module = $this->generateModule($moduleName, $moduleData);

            if ($module) {
                $generatedModules[] = $module;
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
        
        if (isset($this->workspaceData['database_backend_developer'])) {
            foreach ($this->workspaceData['database_backend_developer'] as $content) {
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
     * Generate module name from table name - Groups similar functionality into single modules
     */
    protected function generateModuleName(string $tableName): string
    {
        // Group similar functionality into single modules based on project context
        $projectName = $this->project->name;

        // Extract the main project type from the project name and group all tables into one module
        if (Str::contains($projectName, ['E-commerce', 'Ecommerce', 'Shop', 'Store'])) {
            return 'Ecommerce';
        } elseif (Str::contains($projectName, ['Task', 'Project Management', 'Todo', 'Kanban'])) {
            return 'TaskManagement';
        } elseif (Str::contains($projectName, ['Blog', 'CMS', 'Content', 'Article', 'Post'])) {
            return 'BlogPlatform';
        } elseif (Str::contains($projectName, ['CRM', 'Customer', 'Lead', 'Sales'])) {
            return 'CRM';
        } elseif (Str::contains($projectName, ['Inventory', 'Stock', 'Warehouse'])) {
            return 'Inventory';
        } elseif (Str::contains($projectName, ['Learning', 'Education', 'Course', 'LMS'])) {
            return 'LearningManagement';
        } elseif (Str::contains($projectName, ['Event', 'Booking', 'Reservation'])) {
            return 'EventManagement';
        } elseif (Str::contains($projectName, ['HR', 'Human Resource', 'Employee', 'Payroll'])) {
            return 'HumanResource';
        } else {
            // Fallback: Use project name as module name (cleaned up)
            $cleanName = preg_replace('/[^a-zA-Z0-9]/', '', $projectName);
            return Str::studly($cleanName) ?: 'CustomModule';
        }
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
            $fieldData = [
                'name' => $field['name'],
                'type' => $fieldType,
                'required' => !($field['nullable'] ?? false),
                'length' => $field['length'] ?? null,
                'default' => $field['default'] ?? null,
            ];

            // Handle ENUM fields specially
            if ($fieldType === 'enum' && !empty($field['length'])) {
                // Convert enum values from length to enum_options
                $enumValues = str_replace(["'", '"'], '', $field['length']);
                $fieldData['enum_options'] = str_replace(', ', "\n", $enumValues);
                $fieldData['length'] = null;
            }

            $fields[] = $fieldData;
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
     * Prepare module data for multiple tables (grouped functionality)
     */
    protected function prepareModuleDataForMultipleTables(array $tables, array $userStories): array
    {
        $allTables = [];
        $allRelatedStories = [];

        // Process each table
        foreach ($tables as $table) {
            $tableName = $table['name'];
            $modelName = Str::studly(Str::singular($tableName));

            // Convert database fields to module builder format
            $fields = [];
            foreach ($table['fields'] as $field) {
                if ($field['name'] === 'id' || in_array($field['name'], ['created_at', 'updated_at'])) {
                    continue; // Skip auto-generated fields
                }

                $fieldType = $this->mapDatabaseTypeToModuleType($field['type']);
                $fieldData = [
                    'name' => $field['name'],
                    'type' => $fieldType,
                    'required' => !($field['nullable'] ?? false),
                    'length' => $field['length'] ?? null,
                    'default' => $field['default'] ?? null,
                ];

                // Handle ENUM fields specially
                if ($fieldType === 'enum' && !empty($field['length'])) {
                    // Convert enum values from length to enum_options
                    $enumValues = str_replace(["'", '"'], '', $field['length']);
                    $fieldData['enum_options'] = str_replace(', ', "\n", $enumValues);
                    $fieldData['length'] = null;
                }

                $fields[] = $fieldData;
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

            $allTables[] = [
                'name' => $modelName,
                'table_name' => $tableName,
                'fields' => $fields,
                'relationships' => $relationships,
            ];

            // Collect related user stories
            $relatedStories = $this->findRelatedUserStories($tableName, $userStories);
            $allRelatedStories = array_merge($allRelatedStories, $relatedStories);
        }

        // Remove duplicate stories
        $allRelatedStories = array_unique($allRelatedStories, SORT_REGULAR);

        // Generate module name based on project context
        $projectName = $this->project->name;
        $moduleDescription = "Generated module for {$projectName} - includes " .
                           implode(', ', array_column($allTables, 'name')) . " management";

        return [
            'module_name' => $this->generateModuleName($tables[0]['name']), // Use first table for naming
            'description' => $moduleDescription,
            'models' => $allTables,
            'user_stories' => $allRelatedStories,
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
                'generated_by' => Auth::guard('admin')->id() ?? 1, // Fallback to admin ID 1
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

    /**
     * Generate modules from application template
     */
    public function generateFromTemplate(array $template): array
    {
        $generatedModules = [];

        if (!isset($template['modules']) || empty($template['modules'])) {
            throw new \Exception('Template does not contain module definitions.');
        }

        // Use the existing Enhanced Module Builder demo data for e-commerce
        if (isset($template['name']) && str_contains(strtolower($template['name']), 'commerce')) {
            return $this->generateEcommerceModules();
        }

        // For other templates, generate based on module names
        foreach ($template['modules'] as $moduleName) {
            if ($this->moduleExists($moduleName)) {
                continue;
            }

            $moduleData = $this->prepareTemplateModuleData($moduleName, $template);
            $module = $this->generateModule($moduleName, $moduleData);

            if ($module) {
                $generatedModules[] = $module;
            }
        }

        return $generatedModules;
    }

    /**
     * Generate e-commerce modules using existing demo data
     */
    protected function generateEcommerceModules(): array
    {
        $generatedModules = [];

        // Use the existing shop demo data from Enhanced Module Builder
        $shopModuleData = [
            'module_name' => 'Shop',
            'description' => 'Complete e-commerce shop with products, categories, and orders',
            'tables' => [
                [
                    'name' => 'shop_categories',
                    'model_name' => 'Category',
                    'fields' => [
                        ['name' => 'name', 'type' => 'string', 'required' => true, 'length' => 255],
                        ['name' => 'slug', 'type' => 'slug', 'required' => true, 'length' => 255],
                        ['name' => 'description', 'type' => 'text', 'required' => false],
                        ['name' => 'image', 'type' => 'image', 'required' => false],
                        ['name' => 'active', 'type' => 'boolean', 'required' => false],
                        ['name' => 'sort_order', 'type' => 'integer', 'required' => false],
                    ],
                    'relationships' => [
                        ['type' => 'hasMany', 'related_model' => 'Product', 'foreign_key' => 'category_id'],
                    ]
                ],
                [
                    'name' => 'shop_products',
                    'model_name' => 'Product',
                    'fields' => [
                        ['name' => 'category_id', 'type' => 'foreignId', 'required' => true],
                        ['name' => 'name', 'type' => 'string', 'required' => true, 'length' => 255],
                        ['name' => 'slug', 'type' => 'slug', 'required' => true, 'length' => 255],
                        ['name' => 'description', 'type' => 'rich_text', 'required' => false],
                        ['name' => 'short_description', 'type' => 'text', 'required' => false],
                        ['name' => 'sku', 'type' => 'string', 'required' => true, 'length' => 100],
                        ['name' => 'price', 'type' => 'decimal', 'required' => true],
                        ['name' => 'sale_price', 'type' => 'decimal', 'required' => false],
                        ['name' => 'stock_quantity', 'type' => 'integer', 'required' => false],
                        ['name' => 'manage_stock', 'type' => 'boolean', 'required' => false],
                        ['name' => 'status', 'type' => 'enum', 'required' => true, 'options' => ['active', 'inactive', 'draft']],
                        ['name' => 'featured', 'type' => 'boolean', 'required' => false],
                        ['name' => 'images', 'type' => 'json', 'required' => false],
                        ['name' => 'meta_title', 'type' => 'string', 'required' => false],
                        ['name' => 'meta_description', 'type' => 'text', 'required' => false],
                    ],
                    'relationships' => [
                        ['type' => 'belongsTo', 'related_model' => 'Category', 'foreign_key' => 'category_id'],
                        ['type' => 'hasMany', 'related_model' => 'OrderItem', 'foreign_key' => 'product_id'],
                    ]
                ]
            ],
            'ai_generated' => true,
        ];

        // Generate the Shop module
        $module = $this->generateModule('Shop', $shopModuleData);
        if ($module) {
            $generatedModules[] = $module;
        }

        return $generatedModules;
    }

    /**
     * Prepare module data for template-based generation
     */
    protected function prepareTemplateModuleData(string $moduleName, array $template): array
    {
        // Basic module structure for template-based modules
        return [
            'module_name' => $moduleName,
            'description' => "Generated {$moduleName} module from {$template['name']} template",
            'tables' => [
                [
                    'name' => strtolower($moduleName),
                    'model_name' => $moduleName,
                    'fields' => [
                        ['name' => 'name', 'type' => 'string', 'required' => true, 'length' => 255],
                        ['name' => 'description', 'type' => 'text', 'required' => false],
                        ['name' => 'status', 'type' => 'enum', 'required' => true, 'options' => ['active', 'inactive']],
                    ],
                    'relationships' => []
                ]
            ],
            'ai_generated' => true,
            'template_source' => $template['name'],
        ];
    }
}

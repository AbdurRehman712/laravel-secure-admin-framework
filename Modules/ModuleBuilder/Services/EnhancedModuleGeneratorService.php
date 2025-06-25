<?php

namespace Modules\ModuleBuilder\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Modules\ModuleBuilder\Models\ModuleProject;

class EnhancedModuleGeneratorService
{
    protected ModuleProject $project;
    protected string $modulePath;
    protected array $generatedFiles = [];

    public function __construct(ModuleProject $project)
    {
        $this->project = $project;
        $this->modulePath = base_path("Modules/{$project->name}");
    }

    public function generateModule(): array
    {
        $this->createDirectoryStructure();
        $this->generateModuleJson();
        $this->generateServiceProvider();
        $this->generateModels();
        $this->generateMigrations();
        $this->generateFilamentResources();
        $this->generateApiControllers();
        $this->generateRoutes();
        $this->generateSeeders();
        $this->updateComposerAutoload();
        $this->registerModuleInCache();

        return [
            'success' => true,
            'message' => "Module {$this->project->name} generated successfully",
            'files' => $this->generatedFiles,
            'path' => $this->modulePath
        ];
    }

    protected function createDirectoryStructure(): void
    {
        $directories = [
            'app/Models',
            'app/Http/Controllers/Api',
            'app/Http/Controllers/Web',
            'app/Filament/Resources',
            'app/Providers',
            'database/migrations',
            'database/seeders',
            'database/factories',
            'routes',
            'config',
            'resources/views',
            'resources/lang',
            'tests/Feature',
            'tests/Unit',
        ];

        foreach ($directories as $dir) {
            $fullPath = $this->modulePath . '/' . $dir;
            if (!File::exists($fullPath)) {
                File::makeDirectory($fullPath, 0755, true);
            }
        }
    }

    protected function generateModuleJson(): void
    {
        $config = [
            'name' => $this->project->name,
            'alias' => $this->project->alias,
            'description' => $this->project->description,
            'keywords' => [],
            'priority' => 0,
            'providers' => [
                "{$this->project->namespace}\\app\\Providers\\{$this->project->name}ServiceProvider"
            ],
            'files' => []
        ];

        $filePath = $this->modulePath . '/module.json';
        File::put($filePath, json_encode($config, JSON_PRETTY_PRINT));
        $this->generatedFiles[] = $filePath;
    }

    protected function generateServiceProvider(): void
    {
        $stub = $this->getStub('service-provider');
        $content = str_replace([
            '{{namespace}}',
            '{{moduleName}}',
            '{{moduleNameLower}}',
        ], [
            $this->project->namespace,
            $this->project->name,
            Str::lower($this->project->name),
        ], $stub);

        $filePath = $this->modulePath . "/app/Providers/{$this->project->name}ServiceProvider.php";
        File::put($filePath, $content);
        $this->generatedFiles[] = $filePath;
    }

    protected function generateModels(): void
    {
        $tables = $this->project->tables ?? [];
        
        foreach ($tables as $table) {
            $this->generateModel($table);
        }
    }

    protected function generateModel(array $table): void
    {
        $modelName = $table['model_name'];
        $tableName = $table['name'];
        
        $stub = $this->getStub('model');
        
        // Build fillable array
        $fillable = [];
        $casts = [];
        $relationships = '';
        
        foreach ($table['fields'] ?? [] as $field) {
            if (!in_array($field['name'], ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                $fillable[] = "'{$field['name']}'";
                
                // Add casts for specific field types
                if ($field['type'] === 'boolean') {
                    $casts[] = "'{$field['name']}' => 'boolean'";
                } elseif ($field['type'] === 'decimal') {
                    $casts[] = "'{$field['name']}' => 'decimal:2'";
                } elseif (in_array($field['type'], ['date', 'datetime'])) {
                    $casts[] = "'{$field['name']}' => '{$field['type']}'";
                }
            }
        }

        // Generate relationships
        $relationships = $this->generateModelRelationships($modelName);

        $traits = ['HasFactory'];
        if ($table['has_soft_deletes'] ?? false) {
            $traits[] = 'SoftDeletes';
        }

        $content = str_replace([
            '{{namespace}}',
            '{{modelName}}',
            '{{tableName}}',
            '{{traits}}',
            '{{fillable}}',
            '{{casts}}',
            '{{relationships}}',
        ], [
            $this->project->namespace,
            $modelName,
            $tableName,
            implode(', ', $traits),
            implode(",\n        ", $fillable),
            implode(",\n        ", $casts),
            $relationships,
        ], $stub);

        $filePath = $this->modulePath . "/app/Models/{$modelName}.php";
        File::put($filePath, $content);
        $this->generatedFiles[] = $filePath;
    }

    protected function generateMigrations(): void
    {
        $tables = $this->project->tables ?? [];
        
        foreach ($tables as $table) {
            $this->generateMigration($table);
        }
    }

    protected function generateMigration(array $table): void
    {
        $tableName = $table['name'];
        $timestamp = now()->format('Y_m_d_His');
        $fileName = "{$timestamp}_create_{$tableName}_table.php";
        
        $stub = $this->getStub('migration');
        
        // Build table schema (excluding id and timestamps as they're in the stub)
        $schema = "";
        
        foreach ($table['fields'] ?? [] as $field) {
            $line = $this->buildMigrationFieldLine($field);
            $schema .= "            {$line}\n";
        }
        
        // Add soft deletes if needed
        if ($table['has_soft_deletes'] ?? false) {
            $schema .= "            \$table->softDeletes();\n";
        }
        
        // Add any indexes
        if (isset($table['indexes'])) {
            foreach ($table['indexes'] as $index) {
                $schema .= "            \$table->index({$index});\n";
            }
        }

        $content = str_replace([
            '{{tableName}}',
            '{{schema}}',
        ], [
            $tableName,
            $schema,
        ], $stub);

        $filePath = $this->modulePath . "/database/migrations/{$fileName}";
        File::put($filePath, $content);
        $this->generatedFiles[] = $filePath;
    }

    protected function buildMigrationFieldLine(array $field): string
    {
        $name = $field['name'];
        $type = $field['type'];
        $line = "\$table->{$type}('{$name}'";
        
        if ($type === 'string' && isset($field['length'])) {
            $line .= ", {$field['length']}";
        }
        
        $line .= ')';
        
        if ($field['nullable'] ?? false) {
            $line .= '->nullable()';
        }
        
        if ($field['unique'] ?? false) {
            $line .= '->unique()';
        }
        
        if (isset($field['default'])) {
            $default = is_string($field['default']) ? "'{$field['default']}'" : $field['default'];
            $line .= "->default({$default})";
        }
        
        if ($type === 'foreignId' && isset($field['foreign_table'])) {
            $onDelete = $field['on_delete'] ?? 'cascade';
            $line .= "->constrained('{$field['foreign_table']}')->onDelete('{$onDelete}')";
        }
        
        $line .= ';';
        
        return $line;
    }

    protected function generateFilamentResources(): void
    {
        $resources = $this->project->resources ?? [];
        
        foreach ($resources as $resource) {
            $this->generateFilamentResource($resource);
        }
    }

    protected function generateFilamentResource(array $resource): void
    {
        $resourceName = $resource['resource_name'];
        $modelName = $resource['model'];
        
        // Find the model table to get fields
        $table = collect($this->project->tables ?? [])->firstWhere('model_name', $modelName);
        
        if (!$table) {
            return;
        }

        $stub = $this->getStub('filament-resource');
        
        // Generate form fields
        $formFields = $this->generateFilamentFormFields($table['fields'] ?? []);
        
        // Generate table columns
        $tableColumns = $this->generateFilamentTableColumns($table['fields'] ?? []);

        $content = str_replace([
            '{{namespace}}',
            '{{resourceName}}',
            '{{modelName}}',
            '{{navigationIcon}}',
            '{{formFields}}',
            '{{tableColumns}}',
        ], [
            $this->project->namespace,
            $resourceName,
            $modelName,
            $resource['navigation_icon'] ?? 'heroicon-o-rectangle-stack',
            $formFields,
            $tableColumns,
        ], $stub);

        $resourcePath = $this->modulePath . "/app/Filament/Resources";
        File::makeDirectory($resourcePath, 0755, true);
        
        $filePath = "{$resourcePath}/{$resourceName}.php";
        File::put($filePath, $content);
        $this->generatedFiles[] = $filePath;

        // Generate resource pages
        $this->generateFilamentResourcePages($resourceName);
    }

    protected function generateApiControllers(): void
    {
        if (!($this->project->has_api ?? true)) {
            return;
        }

        $endpoints = $this->project->api_endpoints ?? [];
        
        foreach ($endpoints as $endpoint) {
            $this->generateApiController($endpoint);
        }
    }

    protected function generateRoutes(): void
    {
        if ($this->project->has_api ?? true) {
            $this->generateApiRoutes();
        }
        
        if ($this->project->has_web ?? false) {
            $this->generateWebRoutes();
        }
    }

    protected function getStub(string $name): string
    {
        $stubPath = __DIR__ . "/../Stubs/{$name}.stub";
        
        if (!File::exists($stubPath)) {
            throw new \Exception("Stub file not found: {$stubPath}");
        }
        
        return File::get($stubPath);
    }

    protected function generateModelRelationships(string $modelName): string
    {
        $relationships = collect($this->project->relationships ?? [])
            ->where('from_model', $modelName)
            ->map(function ($rel) {
                return $this->buildRelationshipMethod($rel);
            })
            ->implode("\n\n    ");

        return $relationships ? "\n    " . $relationships : '';
    }

    protected function buildRelationshipMethod(array $relationship): string
    {
        $methodName = $relationship['method_name'];
        $type = $relationship['relationship_type'];
        $toModel = $relationship['to_model'];
        
        $method = "/**\n     * Get the {$methodName}.\n     */\n    ";
        $method .= "public function {$methodName}(): \\Illuminate\\Database\\Eloquent\\Relations\\" . Str::studly($type) . "\n    ";
        $method .= "{\n        ";
        $method .= "return \$this->{$type}({$this->project->namespace}\\app\\Models\\{$toModel}::class";
        
        if (isset($relationship['foreign_key'])) {
            $method .= ", '{$relationship['foreign_key']}'";
        }
        
        $method .= ");\n    }";
        
        return $method;
    }

    protected function generateFilamentFormFields(array $fields): string
    {
        $formFields = [];
        
        foreach ($fields as $field) {
            $formFields[] = $this->buildFilamentFormField($field);
        }
        
        return implode(",\n                ", $formFields);
    }

    protected function buildFilamentFormField(array $field): string
    {
        $name = $field['name'];
        $type = $field['type'];
        
        switch ($type) {
            case 'string':
                $component = "Forms\\Components\\TextInput::make('{$name}')";
                if (!($field['nullable'] ?? false)) {
                    $component .= "\n                    ->required()";
                }
                if (isset($field['length'])) {
                    $component .= "\n                    ->maxLength({$field['length']})";
                }
                break;
                
            case 'text':
                $component = "Forms\\Components\\Textarea::make('{$name}')";
                if (!($field['nullable'] ?? false)) {
                    $component .= "\n                    ->required()";
                }
                break;
                
            case 'boolean':
                $component = "Forms\\Components\\Toggle::make('{$name}')";
                if (isset($field['default'])) {
                    $default = $field['default'] ? 'true' : 'false';
                    $component .= "\n                    ->default({$default})";
                }
                break;
                
            case 'foreignId':
                $foreignTable = $field['foreign_table'] ?? 'related';
                $component = "Forms\\Components\\Select::make('{$name}')\n";
                $component .= "                    ->relationship('" . Str::singular($foreignTable) . "', 'name')\n";
                $component .= "                    ->searchable()\n";
                $component .= "                    ->preload()";
                break;
                
            default:
                $component = "Forms\\Components\\TextInput::make('{$name}')";
                if (!($field['nullable'] ?? false)) {
                    $component .= "\n                    ->required()";
                }
                break;
        }
        
        return $component;
    }

    protected function generateFilamentTableColumns(array $fields): string
    {
        $columns = [];
        
        foreach ($fields as $field) {
            if (in_array($field['name'], ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }
            
            $columns[] = $this->buildFilamentTableColumn($field);
        }
        
        // Add standard columns
        $columns[] = "Tables\\Columns\\TextColumn::make('created_at')\n                    ->dateTime()\n                    ->sortable()\n                    ->toggleable(isToggledHiddenByDefault: true)";
        
        return implode(",\n                ", $columns);
    }

    protected function buildFilamentTableColumn(array $field): string
    {
        $name = $field['name'];
        $type = $field['type'];
        
        switch ($type) {
            case 'boolean':
                return "Tables\\Columns\\IconColumn::make('{$name}')\n                    ->boolean()";
                
            case 'foreignId':
                $foreignTable = $field['foreign_table'] ?? 'related';
                $relationName = Str::singular($foreignTable);
                return "Tables\\Columns\\TextColumn::make('{$relationName}.name')\n                    ->sortable()";
                
            default:
                $column = "Tables\\Columns\\TextColumn::make('{$name}')\n                    ->searchable()";
                if ($type === 'text') {
                    $column .= "\n                    ->limit(50)";
                }
                return $column;
        }
    }

    protected function generateFilamentResourcePages(string $resourceName): void
    {
        $pages = ['List', 'Create', 'Edit'];
        
        foreach ($pages as $page) {
            $this->generateFilamentResourcePage($resourceName, $page);
        }
    }

    protected function generateFilamentResourcePage(string $resourceName, string $pageType): void
    {
        $stub = $this->getStub("filament-resource-{$pageType}");
        
        $content = str_replace([
            '{{namespace}}',
            '{{resourceName}}',
            '{{pageType}}',
        ], [
            $this->project->namespace,
            $resourceName,
            $pageType,
        ], $stub);

        $pagesPath = $this->modulePath . "/app/Filament/Resources/{$resourceName}/Pages";
        File::makeDirectory($pagesPath, 0755, true);
        
        $filePath = "{$pagesPath}/{$pageType}{$resourceName}.php";
        File::put($filePath, $content);
        $this->generatedFiles[] = $filePath;
    }

    protected function generateApiController(array $endpoint): void
    {
        $controllerName = $endpoint['controller_name'];
        $modelName = $endpoint['model'];
        
        $stub = $this->getStub('api-controller');
        
        $content = str_replace([
            '{{namespace}}',
            '{{controllerName}}',
            '{{modelName}}',
        ], [
            $this->project->namespace,
            $controllerName,
            $modelName,
        ], $stub);

        $filePath = $this->modulePath . "/app/Http/Controllers/Api/{$controllerName}.php";
        File::put($filePath, $content);
        $this->generatedFiles[] = $filePath;
    }

    protected function generateApiRoutes(): void
    {
        $endpoints = $this->project->api_endpoints ?? [];
        $routes = "<?php\n\nuse Illuminate\Support\Facades\Route;\n\n";
        
        foreach ($endpoints as $endpoint) {
            $controller = $endpoint['controller_name'];
            $prefix = $endpoint['route_prefix'] ?? 'api/v1';
            $routeName = Str::kebab(str_replace('Controller', '', $controller));
            
            $routes .= "Route::apiResource('{$routeName}', App\\Http\\Controllers\\Api\\{$controller}::class);\n";
        }

        $filePath = $this->modulePath . "/routes/api.php";
        File::put($filePath, $routes);
        $this->generatedFiles[] = $filePath;
    }

    protected function generateWebRoutes(): void
    {
        $content = "<?php\n\nuse Illuminate\Support\Facades\Route;\n\n// Web routes for {$this->project->name}\n";
        
        $filePath = $this->modulePath . "/routes/web.php";
        File::put($filePath, $content);
        $this->generatedFiles[] = $filePath;
    }

    protected function generateSeeders(): void
    {
        if (!($this->project->generate_seeders ?? true)) {
            return;
        }

        $tables = $this->project->tables ?? [];
        
        foreach ($tables as $table) {
            $this->generateSeeder($table);
        }
    }

    protected function generateSeeder(array $table): void
    {
        $modelName = $table['model_name'];
        $seederName = "{$modelName}Seeder";
        
        $stub = $this->getStub('seeder');
        
        $content = str_replace([
            '{{namespace}}',
            '{{seederName}}',
            '{{modelName}}',
        ], [
            $this->project->namespace,
            $seederName,
            $modelName,
        ], $stub);

        $filePath = $this->modulePath . "/database/seeders/{$seederName}.php";
        File::put($filePath, $content);
        $this->generatedFiles[] = $filePath;
    }

    protected function updateComposerAutoload(): void
    {
        $composerPath = base_path('composer.json');
        $composer = json_decode(File::get($composerPath), true);
        
        $namespace = str_replace('\\', '\\\\', $this->project->namespace) . '\\\\';
        $path = "Modules/{$this->project->name}/app/";
        
        if (!isset($composer['autoload']['psr-4'][$namespace])) {
            $composer['autoload']['psr-4'][$namespace] = $path;
            File::put($composerPath, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
    }

    protected function registerModuleInCache(): void
    {
        if (!($this->project->register_module ?? true)) {
            return;
        }

        try {
            Artisan::call('optimize:clear');
        } catch (\Exception $e) {
            // Ignore cache clearing errors
        }
    }
}

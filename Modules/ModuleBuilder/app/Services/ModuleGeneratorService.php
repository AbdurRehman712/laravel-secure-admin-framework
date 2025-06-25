<?php

namespace Modules\ModuleBuilder\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Modules\ModuleBuilder\Models\ModuleProject;
use ZipArchive;

class ModuleGeneratorService
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
        try {
            $this->project->update(['status' => 'building']);
            
            $this->createModuleStructure();
            $this->generateMigrations();
            $this->generateModels();
            $this->generateFilamentResources();
              if ($this->project->has_api) {
                $this->generateApiControllers();
                $this->generateApiRoutes();
                $this->generateRouteServiceProvider();
            }
            
            $this->generateWebRoutes();
              $this->generatePermissions();
            $this->updateComposerAutoload();
            $this->registerFilamentResources();
            
            // Enable the module if the enabled flag is set
            if ($this->project->enabled) {
                $this->enableModule();
            }
            
            // Clear all caches to ensure the module is properly loaded
            $this->clearCaches();
            
            $this->project->update(['status' => 'built']);
            
            return [
                'success' => true,
                'message' => 'Module generated successfully',
                'files' => $this->generatedFiles
            ];
            
        } catch (\Exception $e) {
            $this->project->update(['status' => 'error']);
            
            return [
                'success' => false,
                'message' => 'Error generating module: ' . $e->getMessage(),
                'files' => []
            ];
        }
    }

    protected function createModuleStructure(): void
    {        $directories = [
            'Models',
            'app/Http/Controllers',
            'app/Http/Controllers/Api',
            'Filament/Resources',
            'app/Providers',
            'database/migrations',
            'database/seeders',
            'routes',
            'config',
            'resources/views',
        ];

        foreach ($directories as $dir) {
            File::ensureDirectoryExists("{$this->modulePath}/{$dir}");
        }

        // Create module.json
        $this->createModuleJson();
        
        // Create service provider
        $this->createServiceProvider();
        
        $this->generatedFiles[] = 'Module structure created';
    }

    protected function createModuleJson(): void
    {
        $config = [
            'name' => $this->project->name,
            'alias' => Str::lower($this->project->name),
            'description' => $this->project->description,
            'keywords' => [],
            'priority' => 0,
            'active' => $this->project->enabled, // Set active status based on enabled flag
            'providers' => [
                "Modules\\{$this->project->name}\\app\\Providers\\{$this->project->name}ServiceProvider"
            ],
            'files' => []
        ];

        File::put(
            "{$this->modulePath}/module.json",
            json_encode($config, JSON_PRETTY_PRINT)
        );
        
        $this->generatedFiles[] = 'module.json';
    }

    protected function createServiceProvider(): void
    {
        $stub = $this->getStub('service-provider');
        $content = str_replace([
            '{{MODULE_NAME}}',
            '{{MODULE_LOWER}}',
        ], [
            $this->project->name,
            Str::lower($this->project->name),
        ], $stub);

        File::put(
            "{$this->modulePath}/app/Providers/{$this->project->name}ServiceProvider.php",
            $content
        );
        
        $this->generatedFiles[] = "app/Providers/{$this->project->name}ServiceProvider.php";
    }

    protected function generateMigrations(): void
    {
        foreach ($this->project->tables as $table) {
            $this->generateTableMigration($table);
        }
    }

    protected function generateTableMigration($table): void
    {
        $timestamp = now()->addSeconds(count($this->generatedFiles))->format('Y_m_d_His');
        
        // Use just the table name without namespace
        $tableName = $table->name;
        
        $className = 'Create' . Str::studly($tableName) . 'Table';
        $fileName = "{$timestamp}_create_{$tableName}_table.php";

        $stub = $this->getStub('migration');
        $fields = $this->generateMigrationFields($table);
        
        $content = str_replace([
            '{{CLASS_NAME}}',
            '{{TABLE_NAME}}',
            '{{FIELDS}}',
        ], [
            $className,
            $tableName,
            $fields,
        ], $stub);

        File::put(
            "{$this->modulePath}/database/migrations/{$fileName}",
            $content
        );
        
        $this->generatedFiles[] = "database/migrations/{$fileName}";
    }

    protected function generateMigrationFields($table): string
    {
        $fields = [];
        
        // Add ID field
        $fields[] = '$table->id();';
        
        // Add custom fields
        foreach ($table->fields as $field) {
            $fieldLine = $this->generateMigrationField($field);
            if ($fieldLine) {
                $fields[] = $fieldLine;
            }
        }
        
        // Add timestamps if enabled
        if ($table->has_timestamps) {
            $fields[] = '$table->timestamps();';
        }
        
        // Add soft deletes if enabled
        if ($table->has_soft_deletes) {
            $fields[] = '$table->softDeletes();';
        }

        return implode("\n            ", $fields);
    }

    protected function generateMigrationField($field): string
    {
        // Use database_type if available, fallback to type
        $databaseType = $field->database_type ?? $field->type;
        
        // Map database types to Laravel Schema builder methods
        $columnType = $this->mapDatabaseTypeToSchemaMethod($databaseType);
        
        if ($columnType === 'enum') {
            // Handle enum fields with proper values
            if (empty($field->enum_values)) {
                throw new \Exception("Enum field '{$field->name}' must have 'enum_values' defined.");
            }
            
            $enumValues = $field->enum_values;
            if (is_string($enumValues)) {
                // Try to decode JSON, fallback to comma split
                $decoded = json_decode($enumValues, true);
                if (is_array($decoded)) {
                    $enumValues = $decoded;
                } else {
                    $enumValues = array_map('trim', explode(',', $enumValues));
                }
            }
            
            if (!is_array($enumValues) || empty($enumValues)) {
                throw new \Exception("Enum field '{$field->name}' has invalid 'enum_values'.");
            }
            
            $enumValuesPhp = '[' . implode(", ", array_map(function($v) { return var_export($v, true); }, $enumValues)) . ']';
            $line = "\$table->enum('{$field->name}', {$enumValuesPhp})";
        } else {
            $line = "\$table->{$columnType}('{$field->name}'";
            
            // Add length for string fields
            if (in_array($columnType, ['string', 'char']) && $field->length) {
                $line .= ", {$field->length}";
            }
            
            // Add precision and scale for decimal fields
            if ($columnType === 'decimal' && $field->precision) {
                $scale = $field->scale ?? 2;
                $line .= ", {$field->precision}, {$scale}";
            }
            
            $line .= ')';
        }
        
        // Add modifiers
        if ($field->nullable) {
            $line .= '->nullable()';
        }
        
        if ($field->unique) {
            $line .= '->unique()';
        }
        
        if ($field->default_value) {
            if ($field->type === 'boolean') {
                // Boolean fields should not have quotes around default values
                $default = $field->default_value === 'true' || $field->default_value === true ? 'true' : 'false';
            } else {
                $default = is_numeric($field->default_value) ? $field->default_value : "'{$field->default_value}'";
            }
            $line .= "->default({$default})";
        }
        
        $line .= ';';
        
        return $line;
    }

    protected function generateModels(): void
    {
        foreach ($this->project->tables as $table) {
            $this->generateModel($table);
        }
    }

    protected function generateModel($table): void
    {
        // Use the stored model name from the table record
        $modelName = $table->model_name;
        $stub = $this->getStub('model');
        
        // Table name should not include namespace - only the actual table name
        $tableName = $table->name;
        
        $fillable = $table->fields->pluck('name')->toArray();
        $casts = $this->generateModelCasts($table);
        $relationships = $this->generateModelRelationships($table);
        
        $content = str_replace([
            '{{MODULE_NAME}}',
            '{{MODEL_NAME}}',
            '{{TABLE_NAME}}',
            '{{FILLABLE}}',
            '{{CASTS}}',
            '{{SOFT_DELETES}}',
            '{{RELATIONSHIPS}}',
        ], [
            $this->project->name,
            $modelName,
            $tableName,
            $this->formatArrayForCode($fillable),
            $casts,
            $table->has_soft_deletes ? 'use SoftDeletes;' : '',
            $relationships,
        ], $stub);

        File::put(
            "{$this->modulePath}/Models/{$modelName}.php",
            $content
        );
        
        $this->generatedFiles[] = "Models/{$modelName}.php";
    }

    protected function generateModelCasts($table): string
    {
        $casts = [];
        
        foreach ($table->fields as $field) {
            $cast = match($field->type) {
                'boolean' => 'boolean',
                'date' => 'date',
                'datetime' => 'datetime',
                'json' => 'array',
                'integer' => 'integer',
                'decimal' => 'decimal:2',
                default => null
            };
            
            if ($cast) {
                $casts[] = "'{$field->name}' => '{$cast}'";
            }
        }
        
        if ($table->has_soft_deletes) {
            $casts[] = "'deleted_at' => 'datetime'";
        }
        
        return empty($casts) ? '' : 
            "protected \$casts = [\n        " . implode(",\n        ", $casts) . "\n    ];";
    }

    protected function generateModelRelationships($table): string
    {
        $relationships = [];
        
        // Load relationships with their related tables
        $table->load('relationships.toTable', 'relationships.fromTable');
        
        foreach ($table->relationships as $relationship) {
            $methodName = Str::camel($relationship->name);
            $relatedModel = $this->getRelatedModelClass($relationship);
            
            if (empty($relatedModel)) {
                continue; // Skip if no related model found
            }
            
            $method = match($relationship->type) {
                'hasOne' => "return \$this->hasOne({$relatedModel}::class);",
                'hasMany' => "return \$this->hasMany({$relatedModel}::class);",
                'belongsTo' => "return \$this->belongsTo({$relatedModel}::class);",
                'belongsToMany' => "return \$this->belongsToMany({$relatedModel}::class);",
                default => "return \$this->{$relationship->type}({$relatedModel}::class);"
            };
            
            $relationships[] = "public function {$methodName}()\n    {\n        {$method}\n    }";
        }
        
        return implode("\n\n    ", $relationships);
    }

    protected function generateFilamentResources(): void
    {
        // Generate resources for defined tables
        foreach ($this->project->tables as $table) {
            $this->generateFilamentResource($table);
        }
        
        // If no tables are defined but admin panel is enabled, generate a default resource
        if ($this->project->tables->isEmpty() && $this->project->has_admin_panel) {
            $this->generateDefaultFilamentResource();
        }
    }
    
    protected function generateDefaultFilamentResource(): void
    {
        $moduleName = $this->project->name;
        $modelName = Str::singular($moduleName);
        $resourceName = $modelName . 'Resource';
        
        // Create a default model if it doesn't exist
        $this->generateDefaultModel($modelName);
        
        // Generate the Filament resource
        $stub = $this->getStub('filament-resource');
        $formFields = $this->generateDefaultFormFields();
        $tableColumns = $this->generateDefaultTableColumns();
        
        $content = str_replace([
            '{{namespace}}',
            '{{resourceName}}',
            '{{modelName}}',
            '{{navigationIcon}}',
            '{{formFields}}',
            '{{tableColumns}}',
        ], [
            "Modules\\{$this->project->name}",
            $resourceName,
            $modelName,
            'heroicon-o-rectangle-stack',
            $formFields,
            $tableColumns,
        ], $stub);

        File::ensureDirectoryExists("{$this->modulePath}/Filament/Resources");
        File::put(
            "{$this->modulePath}/Filament/Resources/{$resourceName}.php",
            $content
        );
        
        $this->generatedFiles[] = "Filament/Resources/{$resourceName}.php";
        
        // Generate resource pages
        $this->generateFilamentPages(null, $modelName, $resourceName);
    }
    
    protected function generateDefaultModel(string $modelName): void
    {
        $stub = $this->getStub('model');
        
        $content = str_replace([
            '{{MODULE_NAME}}',
            '{{MODEL_NAME}}',
            '{{TABLE_NAME}}',
            '{{FILLABLE}}',
            '{{CASTS}}',
            '{{SOFT_DELETES}}',
            '{{RELATIONSHIPS}}',
        ], [
            $this->project->name,
            $modelName,
            Str::snake(Str::plural($modelName)),
            $this->formatArrayForCode(['name', 'description']),
            "protected \$casts = [\n        'created_at' => 'datetime',\n        'updated_at' => 'datetime',\n    ];",
            '',
            '',
        ], $stub);

        File::put(
            "{$this->modulePath}/Models/{$modelName}.php",
            $content
        );
        
        $this->generatedFiles[] = "Models/{$modelName}.php";
        
        // Generate default migration
        $this->generateDefaultMigration($modelName);
    }
    
    protected function generateDefaultMigration(string $modelName): void
    {
        $timestamp = now()->format('Y_m_d_His');
        $tableName = Str::snake(Str::plural($modelName));
        $className = 'Create' . Str::studly($tableName) . 'Table';
        $fileName = "{$timestamp}_create_{$tableName}_table.php";

        $stub = $this->getStub('migration');
        $fields = "\$table->id();\n            \$table->string('name');\n            \$table->text('description')->nullable();\n            \$table->timestamps();";
        
        $content = str_replace([
            '{{CLASS_NAME}}',
            '{{TABLE_NAME}}',
            '{{FIELDS}}',
        ], [
            $className,
            $tableName,
            $fields,
        ], $stub);

        File::put(
            "{$this->modulePath}/database/migrations/{$fileName}",
            $content
        );
        
        $this->generatedFiles[] = "database/migrations/{$fileName}";
    }
    
    protected function generateDefaultFormFields(): string
    {
        return "Forms\\Components\\TextInput::make('name')\n                    ->required()\n                    ->maxLength(255),\n                Forms\\Components\\Textarea::make('description')\n                    ->maxLength(65535)\n                    ->columnSpanFull(),";
    }
    
    protected function generateDefaultTableColumns(): string
    {
        return "Tables\\Columns\\TextColumn::make('name')\n                    ->searchable(),\n                Tables\\Columns\\TextColumn::make('description')\n                    ->limit(50)\n                    ->searchable(),\n                Tables\\Columns\\TextColumn::make('created_at')\n                    ->dateTime()\n                    ->sortable()\n                    ->toggleable(isToggledHiddenByDefault: true),\n                Tables\\Columns\\TextColumn::make('updated_at')\n                    ->dateTime()\n                    ->sortable()\n                    ->toggleable(isToggledHiddenByDefault: true),";
    }

    protected function generateFilamentResource($table): void
    {
        // Use the stored model name from the table record
        $modelName = $table->model_name;
        $resourceName = $modelName . 'Resource';
        $modelLower = Str::snake($table->name);
        
        $stub = $this->getStub('filament-resource');
        $formFields = $this->generateFilamentFormFields($table);
        $tableColumns = $this->generateFilamentTableColumns($table);
        
        // Generate permission prefix that matches ModulePermissionService logic
        $permissionPrefix = Str::snake(str_replace('Resource', '', $resourceName));
        
        $content = str_replace([
            '{{MODULE_NAME}}',
            '{{RESOURCE_NAME}}',
            '{{MODEL_NAME}}',
            '{{MODEL_CLASS}}',
            '{{MODEL_LOWER}}',
            '{{PERMISSION_PREFIX}}',
            '{{FORM_FIELDS}}',
            '{{TABLE_COLUMNS}}',
        ], [
            $this->project->name,
            $resourceName,
            $modelName,
            "\\Modules\\{$this->project->name}\\Models\\{$modelName}",
            $modelLower,
            $permissionPrefix,
            $formFields,
            $tableColumns,
        ], $stub);

        File::put(
            "{$this->modulePath}/Filament/Resources/{$resourceName}.php",
            $content
        );        
        $this->generatedFiles[] = "Filament/Resources/{$resourceName}.php";
        
        // Generate Filament Pages
        $this->generateFilamentPages($table, $modelName, $resourceName);
    }

    protected function generateFilamentPages($table, $modelName, $resourceName): void
    {
        $pages = ['List', 'Create', 'Edit'];
        
        foreach ($pages as $pageType) {
            $this->generateFilamentPage($table, $modelName, $resourceName, $pageType);
        }
    }

    protected function generateFilamentPage($table, $modelName, $resourceName, $pageType): void
    {
        $pageName = $pageType . $modelName;
        $basePageClass = match($pageType) {
            'List' => 'ListRecords',
            'Create' => 'CreateRecord', 
            'Edit' => 'EditRecord',
            default => 'Page'
        };
        
        $stub = $this->getStub("filament-page-{$pageType}");
        if (!$stub) {
            // Generate basic page if stub doesn't exist
            $stub = $this->getBasicPageStub($pageType, $basePageClass);
        }
        
        $content = str_replace([
            '{{MODULE_NAME}}',
            '{{RESOURCE_NAME}}',
            '{{MODEL_NAME}}',
            '{{PAGE_NAME}}',
            '{{BASE_PAGE_CLASS}}',
        ], [
            $this->project->name,
            $resourceName,
            $modelName,
            $pageName,
            $basePageClass,
        ], $stub);

        $pagesDir = "{$this->modulePath}/Filament/Resources/{$resourceName}/Pages";
        File::ensureDirectoryExists($pagesDir);
        
        File::put(
            "{$pagesDir}/{$pageName}.php",
            $content
        );
        
        $this->generatedFiles[] = "Filament/Resources/{$resourceName}/Pages/{$pageName}.php";
    }

    protected function getBasicPageStub($pageType, $basePageClass): string
    {
        return "<?php

namespace Modules\\{{MODULE_NAME}}\\app\\Filament\\Resources\\{{RESOURCE_NAME}}\\Pages;

use Modules\\{{MODULE_NAME}}\\app\\Filament\\Resources\\{{RESOURCE_NAME}};
use Filament\\Resources\\Pages\\{$basePageClass};

class {{PAGE_NAME}} extends {$basePageClass}
{
    protected static string \$resource = {{RESOURCE_NAME}}::class;
}";
    }

    protected function generateFilamentFormFields($table): string
    {
        $fields = [];
        
        foreach ($table->fields as $field) {
            $component = $field->form_component ?: $this->getDefaultFormComponent($field->type);
            $fieldCode = $this->generateFormFieldCode($field, $component);
            $fields[] = $fieldCode;
        }
        
        return implode(",\n                ", $fields);
    }

    protected function generateFormFieldCode($field, $component): string
    {
        // Check if this is a foreign key field (ends with _id) AND has a defined relationship
        if (str_ends_with($field->name, '_id') && 
            in_array($field->type, ['integer', 'unsignedBigInteger', 'bigint']) && 
            $this->hasRelationshipForField($field)) {
            
            $relationName = str_replace('_id', '', $field->name);
            $relationModel = ucfirst(Str::camel($relationName));
            
            $code = "Forms\\Components\\Select::make('{$field->name}')";
            if ($field->label) {
                $code .= "\n                    ->label('{$field->label}')";
            } else {
                $code .= "\n                    ->label('" . ucfirst($relationName) . "')";
            }
            $code .= "\n                    ->relationship('{$relationName}', 'name')";
            $code .= "\n                    ->searchable()";
            $code .= "\n                    ->preload()";
            if ($field->nullable) {
                $code .= "\n                    ->nullable()";
            } else {
                $code .= "\n                    ->required()";
            }
            
            return $code;
        }
        
        $code = "Forms\\Components\\{$component}::make('{$field->name}')";
        
        if ($field->display_name) {
            $code .= "\n                    ->label('{$field->display_name}')";
        }
        
        if (!$field->nullable) {
            $code .= "\n                    ->required()";
        }
        
        // Add specific configurations based on field type
        if ($component === 'TextInput') {
            if (in_array($field->type, ['string', 'varchar'])) {
                $code .= "\n                    ->maxLength(255)";
            }
            if ($field->type === 'decimal' || str_contains($field->name, 'price')) {
                $code .= "\n                    ->numeric()";
                if (str_contains($field->name, 'price')) {
                    $code .= "\n                    ->prefix('$')";
                }
            }
        }
        
        if ($component === 'Textarea') {
            $code .= "\n                    ->columnSpanFull()";
        }
        
        if ($component === 'Toggle' && $field->type === 'boolean') {
            $code .= "\n                    ->default(false)";
        }
        
        if ($field->validation_rules) {
            $rules = is_array($field->validation_rules) 
                ? implode('|', $field->validation_rules)
                : $field->validation_rules;
            $code .= "\n                    ->rules('{$rules}')";
        }
        
        return $code;
    }

    protected function generateFilamentTableColumns($table): string
    {
        $columns = [];
        
        // Get fields that are marked for table display
        $tableFields = $table->fields->where('table_column', true);
        
        // If no fields are specifically marked for table display, use common displayable fields as fallback
        if ($tableFields->isEmpty()) {
            $tableFields = $table->fields->whereNotIn('type', ['text', 'longText'])->take(5);
        }
        
        foreach ($tableFields as $field) {
            $columnType = $this->getFilamentColumnType($field);
            
            // Handle foreign key relationships only if they actually exist
            if (str_ends_with($field->name, '_id') && 
                in_array($field->type, ['integer', 'unsignedBigInteger', 'bigint']) && 
                $this->hasRelationshipForField($field)) {
                
                $relationName = str_replace('_id', '', $field->name);
                $relationModel = ucfirst(Str::camel($relationName));
                $columnCode = "Tables\\Columns\\TextColumn::make('{$relationName}.name')";
                if ($field->label) {
                    $columnCode .= "\n                    ->label('{$field->label}')";
                } else {
                    $columnCode .= "\n                    ->label('" . ucfirst($relationName) . "')";
                }
                $columnCode .= "\n                    ->searchable()";
                $columnCode .= "\n                    ->sortable()";
            } else {
                $columnCode = "Tables\\Columns\\{$columnType}::make('{$field->name}')";
                
                if ($field->label) {
                    $columnCode .= "\n                    ->label('{$field->label}')";
                }
                
                if ($field->table_searchable || in_array($field->name, ['name', 'title', 'email'])) {
                    $columnCode .= "\n                    ->searchable()";
                }
                
                if ($field->table_sortable || in_array($field->name, ['name', 'title', 'created_at', 'updated_at'])) {
                    $columnCode .= "\n                    ->sortable()";
                }
            }
            
            // Add specific formatting based on field type
            if ($field->type === 'boolean') {
                $columnCode = "Tables\\Columns\\IconColumn::make('{$field->name}')\n                    ->boolean()";
            } elseif ($field->type === 'decimal' && str_contains($field->name, 'price')) {
                $columnCode .= "\n                    ->money('USD')";
            } elseif (in_array($field->name, ['created_at', 'updated_at', 'published_at'])) {
                $columnCode .= "\n                    ->dateTime()";
                if ($field->name !== 'created_at') {
                    $columnCode .= "\n                    ->toggleable(isToggledHiddenByDefault: true)";
                }
            }
            
            $columns[] = $columnCode;
        }
        
        // Ensure we always have at least one column
        if (empty($columns)) {
            $columns[] = "Tables\\Columns\\TextColumn::make('id')\n                    ->sortable()";
        }
        
        return implode(",\n                ", $columns);
    }
    
    protected function getFilamentColumnType($field): string
    {
        return match ($field->type) {
            'boolean' => 'IconColumn',
            'date', 'datetime', 'timestamp' => 'TextColumn',
            default => 'TextColumn'
        };
    }

    protected function generateApiControllers(): void
    {
        foreach ($this->project->tables as $table) {
            $this->generateApiController($table);
        }
    }

    protected function generateApiController($table): void
    {
        // Use the stored model name from the table record
        $modelName = $table->model_name;
        $controllerName = $modelName . 'Controller';
        
        $stub = $this->getStub('api-controller');
        
        $content = str_replace([
            '{{MODULE_NAME}}',
            '{{CONTROLLER_NAME}}',
            '{{MODEL_NAME}}',
            '{{MODEL_CLASS}}',
            '{{MODEL_VARIABLE}}',
        ], [
            $this->project->name,
            $controllerName,
            $modelName,
            "\\Modules\\{$this->project->name}\\Models\\{$modelName}",
            Str::camel($modelName),
        ], $stub);

        File::put(
            "{$this->modulePath}/app/Http/Controllers/Api/{$controllerName}.php",
            $content
        );
        
        $this->generatedFiles[] = "app/Http/Controllers/Api/{$controllerName}.php";
    }    protected function generateApiRoutes(): void
    {
        $routes = [];
        $imports = [];
        
        foreach ($this->project->tables as $table) {
            // Use the stored model name from the table record  
            $modelName = $table->model_name;
            
            // Generate clean route name based on table name (not including namespace prefix)
            $routeName = Str::kebab(Str::plural($table->name));
            
            $controllerName = $modelName . 'Controller';
            
            $routes[] = "    Route::apiResource('{$routeName}', {$controllerName}::class);";
            $imports[] = "use Modules\\{$this->project->name}\\app\\Http\\Controllers\\Api\\{$controllerName};";
        }
        
        $stub = $this->getStub('api-routes');
        
        // Use the API prefix from the project, fallback to kebab case module name
        $apiPrefix = $this->project->api_prefix ?: Str::kebab($this->project->name);
        // Remove 'api/' prefix if it exists since it's already in the template
        $apiPrefix = str_replace('api/', '', $apiPrefix);
        
        $content = str_replace([
            '{{MODULE_NAME}}',
            '{{MODULE_LOWER}}',
            '{{ROUTES}}',
            '{{CONTROLLER_IMPORTS}}',
        ], [
            $this->project->name,
            $apiPrefix,
            implode("\n", $routes),
            implode("\n", $imports),
        ], $stub);

        File::put(
            "{$this->modulePath}/routes/api.php",
            $content
        );
        
        $this->generatedFiles[] = "routes/api.php";
    }    protected function generateWebRoutes(): void
    {
        $content = "<?php

use Illuminate\Support\Facades\Route;
use Modules\\{$this->project->name}\\app\\Http\\Controllers\\{$this->project->name}Controller;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your module. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the \"web\" middleware group. Now create something great!
|
*/

Route::group([], function () {
    // Add your web routes here
});
";

        File::put(
            "{$this->modulePath}/routes/web.php",
            $content
        );
        
        $this->generatedFiles[] = "routes/web.php";
    }

    protected function generatePermissions(): void
    {
        // Get the permissions for this module by discovering its resources
        $permissions = \App\Services\ModulePermissionService::getModulePermissions($this->project->name);
        
        // Register the permissions in the database
        \App\Services\ModulePermissionService::registerModulePermissions(
            $this->project->name,
            $permissions,
            'admin'
        );
        
        $this->generatedFiles[] = 'Permissions registered: ' . count($permissions) . ' permissions created';
    }

    protected function updateComposerAutoload(): void
    {
        $composerPath = base_path('composer.json');
        $composer = json_decode(File::get($composerPath), true);
        
        // Add PSR-4 autoload mapping for this module
        $moduleNamespace = "Modules\\{$this->project->name}\\";
        $modulePath = "Modules/{$this->project->name}/app/";
        
        if (!isset($composer['autoload']['psr-4'][$moduleNamespace])) {
            $composer['autoload']['psr-4'][$moduleNamespace] = $modulePath;
            
            File::put($composerPath, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->generatedFiles[] = 'Updated composer.json with module autoload mapping';
        }
        
        // Trigger composer dump-autoload
        exec('composer dump-autoload', $output, $return);
        
        $this->generatedFiles[] = 'Composer autoload updated';
    }

    protected function getDefaultFormComponent(string $type): string
    {
        return match($type) {
            'string' => 'TextInput',
            'text' => 'Textarea',
            'integer' => 'TextInput',
            'boolean' => 'Toggle',
            'date' => 'DatePicker',
            'datetime' => 'DateTimePicker',
            'json' => 'KeyValue',
            'file' => 'FileUpload',
            default => 'TextInput'
        };
    }

    private function enableModule()
    {
        $moduleName = $this->project->name;
        
        try {
            // Use artisan command to enable the module
            Artisan::call('module:enable', ['module' => $moduleName]);
            
            // Also clear module cache to ensure it's recognized
            Artisan::call('module:cache');
            
            // Update the project status to enabled
            $this->project->update(['enabled' => true]);
            
        } catch (\Exception $e) {
            // If artisan command fails, try manual cache update
            $this->updateModuleCache($moduleName);
        }
    }
    
    private function updateModuleCache(string $moduleName)
    {
        $cachePath = base_path('bootstrap/cache/modules.php');
        
        // Read existing cache or create new one
        $modules = [];
        if (File::exists($cachePath)) {
            $modules = include $cachePath;
        }
        
        // Add the new module to cache
        $modules[$moduleName] = [
            'name' => $moduleName,
            'alias' => Str::lower($moduleName),
            'description' => $this->project->description,
            'enabled' => true,
            'providers' => [
                "Modules\\{$moduleName}\\Providers\\{$moduleName}ServiceProvider"
            ],
            'path' => "Modules/{$moduleName}"
        ];
        
        // Write cache file
        $content = "<?php\n\nreturn " . var_export($modules, true) . ";\n";
        File::put($cachePath, $content);
        
        // Update project status
        $this->project->update(['enabled' => true]);
    }

    protected function formatArrayForCode(array $items): string
    {
        if (empty($items)) {
            return '';
        }
        
        $formatted = array_map(fn($item) => "'{$item}'", $items);
        return "\n        " . implode(",\n        ", $formatted) . "\n    ";
    }

    protected function getRelatedModelClass($relationship): string
    {
        // Get the related table model name
        $relatedTable = $relationship->toTable;
        if (!$relatedTable) {
            return '';
        }
        
        return $relatedTable->model_name;
    }

    protected function getStub(string $name): string
    {
        $stubPath = __DIR__ . "/../Stubs/{$name}.stub";
        
        if (!File::exists($stubPath)) {
            return ''; // Return empty string if stub doesn't exist
        }
        
        return File::get($stubPath);
    }

    public function exportModule(): string
    {
        $zipPath = storage_path("modules/{$this->project->slug}.zip");
        
        // Ensure directory exists
        File::ensureDirectoryExists(dirname($zipPath));
        
        $zip = new ZipArchive();
        
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $this->addDirectoryToZip($zip, $this->modulePath);
            
            // Add metadata
            $metadata = [
                'name' => $this->project->name,
                'version' => '1.0.0',
                'description' => $this->project->description,
                'generated_at' => now()->toISOString(),
                'laravel_version' => app()->version(),
                'filament_version' => '4.x',
                'has_api' => $this->project->has_api,
            ];
            
            $zip->addFromString('module-metadata.json', json_encode($metadata, JSON_PRETTY_PRINT));
            $zip->close();
        }
        
        return $zipPath;
    }

    protected function addDirectoryToZip(ZipArchive $zip, string $path, string $base = ''): void
    {
        $files = File::allFiles($path);
        
        foreach ($files as $file) {
            $relativePath = $base . str_replace($path, '', $file->getPathname());
            $zip->addFile($file->getPathname(), ltrim($relativePath, '/\\'));
        }
    }

    protected function registerFilamentResources(): void
    {
        try {
            $adminPanelProviderPath = app_path('Providers/Filament/AdminPanelProvider.php');
            
            if (!File::exists($adminPanelProviderPath)) {
                return;
            }
            
            $content = File::get($adminPanelProviderPath);
            
            // Check if the module is already registered
            $discoverResourcesLine = "->discoverResources(in: base_path('Modules/{$this->project->name}/Filament/Resources'), for: 'Modules\\{$this->project->name}\\Filament\\Resources')";
            
            if (strpos($content, $discoverResourcesLine) !== false) {
                // Already registered
                return;
            }
            
            // Find the last discoverResources line and add our module after it
            $pattern = '/->discoverResources\(in: base_path\(\'Modules\/[^\']+\/app\/Filament\/Resources\'\), for: \'[^\']+\'\)/';
            preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);
            
            if (!empty($matches[0])) {
                $lastMatch = end($matches[0]);
                $insertPosition = $lastMatch[1] + strlen($lastMatch[0]);
                
                $newLine = "\n            " . $discoverResourcesLine;
                $content = substr_replace($content, $newLine, $insertPosition, 0);
                
                File::put($adminPanelProviderPath, $content);
                $this->generatedFiles[] = 'AdminPanelProvider updated with Filament resource discovery';
            }
            
        } catch (\Exception $e) {
            
        } catch (\Exception $e) {
            // Log error but don't fail the build
            \Log::warning("Failed to auto-register Filament resources: " . $e->getMessage());
        }
    }

    protected function generateRouteServiceProvider(): void
    {
        $stub = $this->getStub('route-service-provider');
        $content = str_replace([
            '{{MODULE_NAME}}',
        ], [
            $this->project->name,
        ], $stub);

        File::put(
            "{$this->modulePath}/app/Providers/RouteServiceProvider.php",
            $content
        );
        
        $this->generatedFiles[] = "app/Providers/RouteServiceProvider.php";
    }
    
    public static function importModule(string $zipPath, string $targetName = null): array
    {
        try {
            if (!File::exists($zipPath)) {
                return [
                    'success' => false,
                    'message' => 'Zip file not found',
                ];
            }

            $zip = new ZipArchive();
            if ($zip->open($zipPath) !== TRUE) {
                return [
                    'success' => false,
                    'message' => 'Failed to open zip file',
                ];
            }

            // Extract to temporary directory
            $tempDir = sys_get_temp_dir() . '/module_import_' . uniqid();
            $zip->extractTo($tempDir);
            $zip->close();

            // Read metadata
            $metadataPath = $tempDir . '/module-metadata.json';
            if (!File::exists($metadataPath)) {
                File::deleteDirectory($tempDir);
                return [
                    'success' => false,
                    'message' => 'Invalid module zip - missing metadata',
                ];
            }

            $metadata = json_decode(File::get($metadataPath), true);
            $moduleName = $targetName ?: $metadata['name'];

            // Check if module already exists
            if (ModuleProject::where('name', $moduleName)->exists()) {
                File::deleteDirectory($tempDir);
                return [
                    'success' => false,
                    'message' => "Module '{$moduleName}' already exists",
                ];
            }

            // Create module project record
            $project = ModuleProject::create([
                'name' => $moduleName,
                'slug' => Str::slug($moduleName),
                'description' => $metadata['description'] ?? 'Imported module',
                'version' => $metadata['version'] ?? '1.0.0',
                'has_api' => $metadata['has_api'] ?? false,
                'status' => 'draft',
                'enabled' => false,
            ]);

            // Copy module files
            $moduleTargetPath = base_path("Modules/{$moduleName}");
            File::ensureDirectoryExists($moduleTargetPath);
            File::copyDirectory($tempDir, $moduleTargetPath);

            // Clean up temp directory
            File::deleteDirectory($tempDir);

            return [
                'success' => true,
                'message' => "Module '{$moduleName}' imported successfully",
                'project' => $project,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ];
        }
    }
    
    private function clearCaches()
    {
        try {
            // Clear various Laravel caches
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            
            // Clear module specific caches if they exist
            if (array_key_exists('module:cache', Artisan::all())) {
                Artisan::call('module:cache');
            }
            
        } catch (\Exception $e) {
            // Silent fail if cache clearing fails
        }
    }
    
    protected function hasRelationshipForField($field): bool
    {
        // Get the table that contains this field
        $table = $field->moduleTable;
        
        if (!$table) {
            return false;
        }
        
        // Check if there's a relationship defined for this field
        $relationName = str_replace('_id', '', $field->name);
        
        return $table->relationships()
            ->where(function($query) use ($relationName, $field) {
                $query->where('name', $relationName)
                      ->orWhere('foreign_key', $field->name);
            })
            ->exists();
    }
    
    /**
     * Map database types to Laravel Schema builder method names
     */
    protected function mapDatabaseTypeToSchemaMethod(string $databaseType): string
    {
        $mapping = [
            'varchar' => 'string',
            'char' => 'char',
            'text' => 'text',
            'longtext' => 'longText',
            'mediumtext' => 'mediumText',
            'tinytext' => 'text',
            'int' => 'integer',
            'integer' => 'integer',
            'bigint' => 'bigInteger',
            'tinyint' => 'tinyInteger',
            'smallint' => 'smallInteger',
            'mediumint' => 'mediumInteger',
            'unsignedbiginteger' => 'unsignedBigInteger',
            'unsignedinteger' => 'unsignedInteger',
            'decimal' => 'decimal',
            'double' => 'double',
            'float' => 'float',
            'boolean' => 'boolean',
            'bool' => 'boolean',
            'date' => 'date',
            'datetime' => 'dateTime',
            'timestamp' => 'timestamp',
            'time' => 'time',
            'year' => 'year',
            'enum' => 'enum',
            'set' => 'set',
            'json' => 'json',
            'jsonb' => 'jsonb',
            'binary' => 'binary',
            'uuid' => 'uuid',
            'ipaddress' => 'ipAddress',
            'macaddress' => 'macAddress',
        ];

        $lowerType = strtolower($databaseType);
        return $mapping[$lowerType] ?? $databaseType;
    }
}

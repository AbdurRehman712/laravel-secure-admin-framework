<?php

namespace Modules\ModuleBuilder\app\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;
use Modules\ModuleBuilder\app\Services\ModuleStatusService;

class EnhancedModuleGenerator
{
    private string $moduleName;
    private array $data;
    private string $modulePath;

    public function __construct(string $moduleName, array $data)
    {
        $this->moduleName = Str::studly($moduleName);
        $this->data = $data;
        $this->modulePath = base_path("Modules/{$this->moduleName}");
    }

    public function generate(): void
    {
        // 1. Create module structure
        $this->createModuleStructure();
        
        // 2. Generate models with relationships
        $this->generateModels();
        
        // 3. Generate migrations with relationships
        $this->generateMigrations();
        
        // 4. Generate enhanced Filament resources
        $this->generateFilamentResources();
        
        // 5. Generate additional features
        $this->generateAdditionalFeatures();
        
        // 6. Register module and run migrations
        $this->registerAndMigrate();
    }

    private function createModuleStructure(): void
    {
        $directories = [
            'app/Models',
            'app/Filament/Resources',
            'app/Http/Controllers',
            'app/Providers',
            'database/migrations',
            'database/factories',
            'database/seeders',
            'routes',
            'tests/Feature',
            'tests/Unit',
        ];

        foreach ($directories as $dir) {
            File::ensureDirectoryExists("{$this->modulePath}/{$dir}");
        }

        // Create module.json
        $this->generateModuleJson();
        
        // Create service provider
        $this->generateServiceProvider();
    }

    private function generateModuleJson(): void
    {
        $moduleJson = [
            'name' => $this->moduleName,
            'alias' => Str::lower($this->moduleName),
            'description' => $this->data['description'] ?? "Generated {$this->moduleName} module",
            'keywords' => [],
            'priority' => 0,
            'providers' => [
                "Modules\\{$this->moduleName}\\app\\Providers\\{$this->moduleName}ServiceProvider"
            ],
            'files' => []
        ];

        File::put(
            "{$this->modulePath}/module.json",
            json_encode($moduleJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    private function generateServiceProvider(): void
    {
        $content = "<?php

namespace Modules\\{$this->moduleName}\\app\\Providers;

use Illuminate\\Support\\ServiceProvider;

class {$this->moduleName}ServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        \$this->loadMigrationsFrom(module_path('{$this->moduleName}', 'database/migrations'));
    }

    public function register(): void
    {
        //
    }
}";

        File::put(
            "{$this->modulePath}/app/Providers/{$this->moduleName}ServiceProvider.php",
            $content
        );
    }

    private function generateModels(): void
    {
        foreach ($this->data['models'] ?? [] as $modelData) {
            $this->generateModel($modelData);
        }
    }

    private function generateModel(array $modelData): void
    {
        $modelName = Str::studly($modelData['name']);
        $tableName = $modelData['table_name'];
        
        // Generate fillable fields
        $fillableFields = [];
        foreach ($modelData['fields'] ?? [] as $field) {
            if ($field['name'] !== 'id' && !Str::endsWith($field['name'], ['_at'])) {
                $fillableFields[] = "'{$field['name']}'";
            }
        }

        // Add foreign keys from relationships
        foreach ($this->data['relationships'] ?? [] as $relationship) {
            if ($relationship['from_model'] === $modelData['name'] && $relationship['type'] === 'belongsTo') {
                $foreignKey = $relationship['foreign_key'] ?? Str::snake($relationship['to_model']) . '_id';
                if (!in_array("'{$foreignKey}'", $fillableFields)) {
                    $fillableFields[] = "'{$foreignKey}'";
                }
            }
        }

        $fillable = implode(', ', $fillableFields);

        // Generate relationships
        $relationships = $this->generateModelRelationships($modelName);

        // Generate casts for special field types
        $casts = $this->generateModelCasts($modelData['fields'] ?? []);

        $content = "<?php

namespace Modules\\{$this->moduleName}\\app\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Database\\Eloquent\\SoftDeletes;

class {$modelName} extends Model
{
    use HasFactory;

    protected \$table = '{$tableName}';
    
    protected \$fillable = [{$fillable}];

    {$casts}

    protected static function newFactory()
    {
        return \\Modules\\{$this->moduleName}\\database\\factories\\{$modelName}Factory::new();
    }

    {$relationships}
}";

        File::put(
            "{$this->modulePath}/app/Models/{$modelName}.php",
            $content
        );
    }

    private function generateModelRelationships(string $modelName): string
    {
        $relationships = '';
        
        foreach ($this->data['relationships'] ?? [] as $relationship) {
            if ($relationship['from_model'] === $modelName) {
                $relationships .= $this->generateRelationshipMethod($relationship);
            }
        }
        
        return $relationships;
    }

    private function generateRelationshipMethod(array $relationship): string
    {
        $type = $relationship['type'];
        $toModel = $relationship['to_model'];
        $methodName = $relationship['relationship_name'] ?? Str::camel(
            $type === 'hasMany' ? Str::plural($toModel) : $toModel
        );
        
        $relatedModel = Str::contains($toModel, '::') 
            ? str_replace('::', '\\app\\Models\\', $toModel)
            : "\\Modules\\{$this->moduleName}\\app\\Models\\{$toModel}";

        switch ($type) {
            case 'belongsTo':
                $foreignKey = $relationship['foreign_key'] ?? Str::snake($toModel) . '_id';
                return "
    public function {$methodName}()
    {
        return \$this->belongsTo({$relatedModel}::class, '{$foreignKey}');
    }";

            case 'hasMany':
                $foreignKey = $relationship['foreign_key'] ?? Str::snake($relationship['from_model']) . '_id';
                return "
    public function {$methodName}()
    {
        return \$this->hasMany({$relatedModel}::class, '{$foreignKey}');
    }";

            case 'hasOne':
                $foreignKey = $relationship['foreign_key'] ?? Str::snake($relationship['from_model']) . '_id';
                return "
    public function {$methodName}()
    {
        return \$this->hasOne({$relatedModel}::class, '{$foreignKey}');
    }";

            case 'belongsToMany':
                $pivotTable = $relationship['pivot_table'] ?? $this->generatePivotTableName(
                    $relationship['from_model'], 
                    $toModel
                );
                return "
    public function {$methodName}()
    {
        return \$this->belongsToMany({$relatedModel}::class, '{$pivotTable}');
    }";

            default:
                return '';
        }
    }

    private function generatePivotTableName(string $model1, string $model2): string
    {
        $tables = [Str::snake($model1), Str::snake($model2)];
        sort($tables);
        return implode('_', $tables);
    }

    private function generateModelCasts(array $fields): string
    {
        $casts = [];
        
        foreach ($fields as $field) {
            switch ($field['type']) {
                case 'boolean':
                    $casts[] = "'{$field['name']}' => 'boolean'";
                    break;
                case 'json':
                    $casts[] = "'{$field['name']}' => 'array'";
                    break;
                case 'date':
                    $casts[] = "'{$field['name']}' => 'date'";
                    break;
                case 'datetime':
                case 'timestamp':
                    $casts[] = "'{$field['name']}' => 'datetime'";
                    break;
                case 'decimal':
                    $casts[] = "'{$field['name']}' => 'decimal:2'";
                    break;
            }
        }
        
        if (empty($casts)) {
            return '';
        }
        
        return "protected \$casts = [\n        " . implode(",\n        ", $casts) . "\n    ];";
    }

    private function generateMigrations(): void
    {
        foreach ($this->data['models'] ?? [] as $modelData) {
            $this->generateMigration($modelData);
        }
        
        // Generate pivot table migrations for many-to-many relationships
        $this->generatePivotMigrations();
    }

    private function generateMigration(array $modelData): void
    {
        $tableName = $modelData['table_name'];
        $timestamp = now()->addSeconds(count($this->data['models'] ?? []))->format('Y_m_d_His');
        $className = "Create" . Str::studly($tableName) . "Table";
        
        // Generate fields
        $fieldsCode = '';
        foreach ($modelData['fields'] ?? [] as $field) {
            // Convert slug type to string for migration
            if ($field['type'] === 'slug') {
                $field['type'] = 'string';
            }
            $fieldsCode .= $this->generateMigrationField($field);
        }
        
        // Add foreign keys for belongsTo relationships
        $foreignKeys = $this->generateForeignKeys($modelData['name']);
        
        $content = "<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$tableName}', function (Blueprint \$table) {
            \$table->id();
{$fieldsCode}{$foreignKeys}
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$tableName}');
    }
};";

        File::put(
            "{$this->modulePath}/database/migrations/{$timestamp}_create_{$tableName}_table.php",
            $content
        );
    }

    private function generateMigrationField(array $field): string
    {
        $name = $field['name'];
        $type = $field['type'];
        $required = $field['required'] ?? false;
        $default = $field['default'] ?? null;
        $length = $field['length'] ?? null;

        $fieldCode = "            ";

        switch ($type) {
            case 'string':
                $fieldCode .= $length ? "\$table->string('{$name}', {$length})" : "\$table->string('{$name}')";
                break;
            case 'text':
                $fieldCode .= "\$table->text('{$name}')";
                break;
            case 'integer':
                $fieldCode .= "\$table->integer('{$name}')";
                break;
            case 'decimal':
                $fieldCode .= "\$table->decimal('{$name}', 10, 2)";
                break;
            case 'boolean':
                $fieldCode .= "\$table->boolean('{$name}')";
                break;
            case 'date':
                $fieldCode .= "\$table->date('{$name}')";
                break;
            case 'datetime':
                $fieldCode .= "\$table->dateTime('{$name}')";
                break;
            case 'timestamp':
                $fieldCode .= "\$table->timestamp('{$name}')";
                break;
            case 'json':
                $fieldCode .= "\$table->json('{$name}')";
                break;
            case 'enum':
                $options = explode("\n", $field['enum_options'] ?? '');
                $options = array_map('trim', $options);
                $options = array_filter($options);
                $enumValues = "'" . implode("', '", $options) . "'";
                $fieldCode .= "\$table->enum('{$name}', [{$enumValues}])";
                break;
            case 'file':
            case 'image':
                $fieldCode .= "\$table->string('{$name}')";
                break;
            case 'rich_text':
                $fieldCode .= "\$table->longText('{$name}')";
                break;
            case 'email':
                $fieldCode .= "\$table->string('{$name}')";
                break;
            case 'url':
                $fieldCode .= "\$table->string('{$name}')";
                break;
            case 'password':
                $fieldCode .= "\$table->string('{$name}')";
                break;
            default:
                $fieldCode .= "\$table->string('{$name}')";
        }

        if (!$required) {
            $fieldCode .= "->nullable()";
        }

        if ($default !== null) {
            if ($type === 'boolean') {
                $fieldCode .= "->default(" . ($default === true || $default === 'true' ? 'true' : 'false') . ")";
            } elseif (is_numeric($default)) {
                $fieldCode .= "->default({$default})";
            } else {
                $fieldCode .= "->default('{$default}')";
            }
        }

        $fieldCode .= ";\n";

        return $fieldCode;
    }

    private function generateForeignKeys(string $modelName): string
    {
        $foreignKeys = '';

        foreach ($this->data['relationships'] ?? [] as $relationship) {
            if ($relationship['from_model'] === $modelName && $relationship['type'] === 'belongsTo') {
                $foreignKey = $relationship['foreign_key'] ?? Str::snake($relationship['to_model']) . '_id';

                // Find the related model's table name
                $relatedTableName = null;
                foreach ($this->data['models'] ?? [] as $model) {
                    if ($model['name'] === $relationship['to_model']) {
                        $relatedTableName = $model['table_name'];
                        break;
                    }
                }

                if ($relatedTableName) {
                    $foreignKeys .= "            \$table->foreignId('{$foreignKey}')->constrained('{$relatedTableName}');\n";
                } else {
                    $foreignKeys .= "            \$table->foreignId('{$foreignKey}')->constrained();\n";
                }
            }
        }

        return $foreignKeys;
    }

    private function generatePivotMigrations(): void
    {
        $pivotTables = [];

        foreach ($this->data['relationships'] ?? [] as $relationship) {
            if ($relationship['type'] === 'belongsToMany') {
                $pivotTable = $relationship['pivot_table'] ?? $this->generatePivotTableName(
                    $relationship['from_model'],
                    $relationship['to_model']
                );

                if (!in_array($pivotTable, $pivotTables)) {
                    $this->generatePivotMigration($relationship, $pivotTable);
                    $pivotTables[] = $pivotTable;
                }
            }
        }
    }

    private function generatePivotMigration(array $relationship, string $pivotTable): void
    {
        $timestamp = now()->addMinutes(10)->format('Y_m_d_His');
        $className = "Create" . Str::studly($pivotTable) . "Table";

        $fromKey = Str::snake($relationship['from_model']) . '_id';
        $toKey = Str::snake($relationship['to_model']) . '_id';

        $content = "<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$pivotTable}', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('{$fromKey}')->constrained();
            \$table->foreignId('{$toKey}')->constrained();
            \$table->timestamps();

            \$table->unique(['{$fromKey}', '{$toKey}']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$pivotTable}');
    }
};";

        File::put(
            "{$this->modulePath}/database/migrations/{$timestamp}_create_{$pivotTable}_table.php",
            $content
        );
    }

    private function generateFilamentResources(): void
    {
        foreach ($this->data['models'] ?? [] as $modelData) {
            $this->generateFilamentResource($modelData);
        }
    }

    private function generateFilamentResource(array $modelData): void
    {
        $modelName = Str::studly($modelData['name']);
        $resourceName = $modelName . 'Resource';
        $pluralName = Str::plural($modelName);

        // Generate basic resource structure
        $content = "<?php

namespace Modules\\{$this->moduleName}\\app\\Filament\\Resources;

use Modules\\{$this->moduleName}\\app\\Models\\{$modelName};
use Modules\\{$this->moduleName}\\app\\Filament\\Resources\\{$resourceName}\\Pages;
use Filament\\Resources\\Resource;
use Filament\\Schemas\\Schema;
use Filament\\Tables\\Table;
use Filament\\Tables\\Columns\\TextColumn;
use Filament\\Tables\\Columns\\BooleanColumn;
use Filament\\Actions\\Action;
use Filament\\Actions\\BulkAction;
use Filament\\Actions\\ActionGroup;
use Filament\\Actions\\BulkActionGroup;
use Filament\\Actions\\EditAction;
use Filament\\Actions\\DeleteAction;
use Filament\\Actions\\ViewAction;
use Filament\\Forms\\Components\\TextInput;
use Filament\\Forms\\Components\\Textarea;
use Filament\\Forms\\Components\\Select;
use Filament\\Forms\\Components\\Toggle;
use Filament\\Forms\\Components\\DateTimePicker;
use Filament\\Forms\\Components\\FileUpload;
use Filament\\Forms\\Components\\RichEditor;


class {$resourceName} extends Resource
{
    protected static ?string \$model = {$modelName}::class;

    protected static \\BackedEnum|string|null \$navigationIcon = 'heroicon-o-rectangle-stack';

    protected static \\UnitEnum|string|null \$navigationGroup = '{$this->moduleName}';

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_" . Str::snake($modelName) . "') ?? false;
    }

    public static function canView(\$record): bool
    {
        return auth()->user()?->can('view_" . Str::snake($modelName) . "') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_" . Str::snake($modelName) . "') ?? false;
    }

    public static function canEdit(\$record): bool
    {
        return auth()->user()?->can('update_" . Str::snake($modelName) . "') ?? false;
    }

    public static function canDelete(\$record): bool
    {
        return auth()->user()?->can('delete_" . Str::snake($modelName) . "') ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function form(Schema \$schema): Schema
    {
        return \$schema->schema([
            " . $this->generateFormFields($modelData) . "
        ]);
    }

    public static function table(Table \$table): Table
    {
        return \$table
            ->columns([
                TextColumn::make('id')->sortable()->toggleable(),
                " . $this->generateTableColumns($modelData) . "
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('delete')
                        ->requiresConfirmation()
                        ->action(fn (\\Illuminate\\Database\\Eloquent\\Collection \$records) => \$records->each->delete()),
                    BulkAction::make('export')
                        ->action(function (\\Illuminate\\Database\\Eloquent\\Collection \$records) {
                            // Simple CSV export functionality
                            \$filename = '" . Str::snake($modelName) . "_export_' . now()->format('Y_m_d_H_i_s') . '.csv';
                            \$headers = [
                                'Content-Type' => 'text/csv',
                                'Content-Disposition' => 'attachment; filename=\"' . \$filename . '\"',
                            ];

                            \$callback = function() use (\$records) {
                                \$file = fopen('php://output', 'w');

                                // Add CSV headers
                                if (\$records->isNotEmpty()) {
                                    \$firstRecord = \$records->first();
                                    \$headers = [];
                                    foreach (\$firstRecord->toArray() as \$key => \$value) {
                                        \$headers[] = \$key;
                                    }
                                    fputcsv(\$file, \$headers);
                                }

                                // Add data rows
                                foreach (\$records as \$record) {
                                    \$row = [];
                                    foreach (\$record->toArray() as \$key => \$value) {
                                        // Handle array/object values (like relationships)
                                        if (is_array(\$value) || is_object(\$value)) {
                                            \$row[] = is_array(\$value) ? implode(', ', \$value) : (string) \$value;
                                        } else {
                                            \$row[] = \$value;
                                        }
                                    }
                                    fputcsv(\$file, \$row);
                                }

                                fclose(\$file);
                            };

                            return response()->stream(\$callback, 200, \$headers);
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\\List{$pluralName}::route('/'),
            'create' => Pages\\Create{$modelName}::route('/create'),
            'edit' => Pages\\Edit{$modelName}::route('/{record}/edit'),
        ];
    }
}";

        File::put(
            "{$this->modulePath}/app/Filament/Resources/{$resourceName}.php",
            $content
        );

        // Generate resource pages
        $this->generateResourcePages($modelName, $resourceName);
    }

    private function generateTableColumns(array $modelData): string
    {
        $columns = [];

        foreach ($modelData['fields'] as $field) {
            $fieldName = $field['name'];
            $fieldType = $field['type'];

            switch ($fieldType) {
                case 'boolean':
                    $columns[] = "BooleanColumn::make('{$fieldName}')->toggleable()";
                    break;
                case 'decimal':
                    $columns[] = "TextColumn::make('{$fieldName}')->money('USD')->sortable()->toggleable()";
                    break;
                case 'email':
                    $columns[] = "TextColumn::make('{$fieldName}')->searchable()->sortable()->toggleable()";
                    break;
                case 'enum':
                    $columns[] = "TextColumn::make('{$fieldName}')->badge()->toggleable()";
                    break;
                case 'date':
                case 'datetime':
                    $columns[] = "TextColumn::make('{$fieldName}')->dateTime()->sortable()->toggleable()";
                    break;
                default:
                    if (in_array($fieldName, ['name', 'title', 'slug', 'sku', 'email'])) {
                        $columns[] = "TextColumn::make('{$fieldName}')->searchable()->sortable()->toggleable()";
                    } else {
                        $columns[] = "TextColumn::make('{$fieldName}')->sortable()->toggleable()";
                    }
                    break;
            }
        }

        // Add relationships
        foreach ($this->data['relationships'] ?? [] as $relationship) {
            if ($relationship['from_model'] === $modelData['name'] && $relationship['type'] === 'belongsTo') {
                $relationshipName = $relationship['relationship_name'] ?? Str::camel($relationship['to_model']);
                $columns[] = "TextColumn::make('{$relationshipName}.name')->label('" . Str::title($relationship['to_model']) . "')->toggleable()";
            }
        }

        // Add timestamps
        $columns[] = "TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)";
        $columns[] = "TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)";

        return implode(",\n                ", $columns);
    }

    private function generateFormFields(array $modelData): string
    {
        $fields = [];

        foreach ($modelData['fields'] as $field) {
            $fieldName = $field['name'];
            $fieldType = $field['type'];
            $required = $field['required'] ?? false;
            $length = $field['length'] ?? null;
            $enumOptions = $field['enum_options'] ?? null;

            switch ($fieldType) {
                case 'string':
                    $component = "TextInput::make('{$fieldName}')";
                    if ($required) $component .= "->required()";
                    if ($length) $component .= "->maxLength({$length})";
                    $fields[] = $component;
                    break;

                case 'slug':
                    // Find the name field to use as source for auto-slug
                    $nameField = null;
                    foreach ($modelData['fields'] as $field) {
                        if (in_array($field['name'], ['name', 'title'])) {
                            $nameField = $field['name'];
                            break;
                        }
                    }

                    $component = "TextInput::make('{$fieldName}')";
                    if ($required) $component .= "->required()";
                    if ($length) $component .= "->maxLength({$length})";

                    if ($nameField) {
                        $component .= "->helperText('Auto-generated from {$nameField}, but you can edit it')";
                        $component .= "->placeholder('Will be auto-generated')";
                    }

                    $fields[] = $component;
                    break;

                case 'text':
                    $component = "Textarea::make('{$fieldName}')";
                    if ($required) $component .= "->required()";
                    $component .= "->rows(3)";
                    $fields[] = $component;
                    break;

                case 'rich_text':
                    $component = "RichEditor::make('{$fieldName}')";
                    if ($required) $component .= "->required()";
                    $fields[] = $component;
                    break;

                case 'integer':
                    $component = "TextInput::make('{$fieldName}')->numeric()";
                    if ($required) $component .= "->required()";
                    $fields[] = $component;
                    break;

                case 'decimal':
                    $component = "TextInput::make('{$fieldName}')->numeric()->step(0.01)";
                    if ($required) $component .= "->required()";
                    $fields[] = $component;
                    break;

                case 'boolean':
                    $component = "Toggle::make('{$fieldName}')";
                    $fields[] = $component;
                    break;

                case 'date':
                    $component = "DateTimePicker::make('{$fieldName}')->date()";
                    if ($required) $component .= "->required()";
                    $fields[] = $component;
                    break;

                case 'datetime':
                    $component = "DateTimePicker::make('{$fieldName}')";
                    if ($required) $component .= "->required()";
                    $fields[] = $component;
                    break;

                case 'email':
                    $component = "TextInput::make('{$fieldName}')->email()";
                    if ($required) $component .= "->required()";
                    $fields[] = $component;
                    break;

                case 'url':
                    $component = "TextInput::make('{$fieldName}')->url()";
                    if ($required) $component .= "->required()";
                    $fields[] = $component;
                    break;

                case 'password':
                    $component = "TextInput::make('{$fieldName}')->password()";
                    if ($required) $component .= "->required()";
                    $fields[] = $component;
                    break;

                case 'enum':
                    if ($enumOptions) {
                        $options = array_map('trim', explode("\n", $enumOptions));
                        $optionsArray = [];
                        foreach ($options as $option) {
                            $optionsArray[] = "'{$option}' => '" . ucfirst($option) . "'";
                        }
                        $optionsString = implode(', ', $optionsArray);
                        $component = "Select::make('{$fieldName}')->options([{$optionsString}])";
                        if ($required) $component .= "->required()";
                        $fields[] = $component;
                    }
                    break;

                case 'file':
                case 'image':
                    $component = "FileUpload::make('{$fieldName}')";
                    if ($fieldType === 'image') {
                        $component .= "->image()";
                    }
                    $fields[] = $component;
                    break;
            }
        }

        // Add relationship selects
        foreach ($this->data['relationships'] ?? [] as $relationship) {
            if ($relationship['from_model'] === $modelData['name'] && $relationship['type'] === 'belongsTo') {
                $relationshipName = $relationship['relationship_name'] ?? Str::camel($relationship['to_model']);
                $foreignKey = $relationship['foreign_key'] ?? Str::snake($relationship['to_model']) . '_id';
                $relatedModel = $relationship['to_model'];

                $component = "Select::make('{$foreignKey}')->label('" . Str::title($relatedModel) . "')->options(\\Modules\\{$this->moduleName}\\app\\Models\\{$relatedModel}::all()->pluck('name', 'id')->toArray())->required()";
                $fields[] = $component;
            }
        }

        return implode(",\n            ", $fields);
    }

    private function generateResourcePages(string $modelName, string $resourceName): void
    {
        $pluralName = Str::plural($modelName);
        $pagesPath = "{$this->modulePath}/app/Filament/Resources/{$resourceName}";
        File::ensureDirectoryExists("{$pagesPath}/Pages");

        // List page
        $listContent = "<?php

namespace Modules\\{$this->moduleName}\\app\\Filament\\Resources\\{$resourceName}\\Pages;

use Modules\\{$this->moduleName}\\app\\Filament\\Resources\\{$resourceName};
use Filament\\Actions\\Action;
use Filament\\Actions\\CreateAction;
use Filament\\Resources\\Pages\\ListRecords;
use Filament\\Notifications\\Notification;
use Illuminate\\Support\\Facades\\Artisan;

class List{$pluralName} extends ListRecords
{
    protected static string \$resource = {$resourceName}::class;

    protected function getHeaderActions(): array
    {
        \$actions = [
            CreateAction::make()
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];

        // Add seeder action if seeder exists
        if (class_exists('Modules\\{$this->moduleName}\\database\\seeders\\{$modelName}Seeder')) {
            \$actions[] = Action::make('run_seeder')
                ->label('Run Seeder')
                ->icon('heroicon-o-play')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Run {$modelName} Seeder')
                ->modalDescription('This will create sample {$modelName} records. Are you sure?')
                ->action(function () {
                    try {
                        Artisan::call('db:seed', ['--class' => 'Modules\\{$this->moduleName}\\database\\seeders\\{$modelName}Seeder']);

                        Notification::make()
                            ->title('Seeder executed successfully!')
                            ->body('Sample {$modelName} records have been created.')
                            ->success()
                            ->send();
                    } catch (\\Exception \$e) {
                        Notification::make()
                            ->title('Seeder execution failed')
                            ->body(\$e->getMessage())
                            ->danger()
                            ->send();
                    }
                });
        }

        // Add factory action if factory exists
        if (class_exists('Modules\\{$this->moduleName}\\database\\factories\\{$modelName}Factory')) {
            \$actions[] = Action::make('create_test_data')
                ->label('Create Test Data')
                ->icon('heroicon-o-beaker')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Create Test {$modelName} Data')
                ->modalDescription('This will create 10 test {$modelName} records using the factory. Are you sure?')
                ->action(function () {
                    try {
                        \\Modules\\{$this->moduleName}\\app\\Models\\{$modelName}::factory()->count(10)->create();

                        Notification::make()
                            ->title('Test data created successfully!')
                            ->body('10 test {$modelName} records have been created.')
                            ->success()
                            ->send();
                    } catch (\\Exception \$e) {
                        Notification::make()
                            ->title('Test data creation failed')
                            ->body(\$e->getMessage())
                            ->danger()
                            ->send();
                    }
                });
        }

        return \$actions;
    }
}";
        File::put("{$pagesPath}/Pages/List{$pluralName}.php", $listContent);

        // Create page
        $createContent = "<?php

namespace Modules\\{$this->moduleName}\\app\\Filament\\Resources\\{$resourceName}\\Pages;

use Modules\\{$this->moduleName}\\app\\Filament\\Resources\\{$resourceName};
use Filament\\Resources\\Pages\\CreateRecord;

class Create{$modelName} extends CreateRecord
{
    protected static string \$resource = {$resourceName}::class;
}";
        File::put("{$pagesPath}/Pages/Create{$modelName}.php", $createContent);

        // Edit page
        $editContent = "<?php

namespace Modules\\{$this->moduleName}\\app\\Filament\\Resources\\{$resourceName}\\Pages;

use Modules\\{$this->moduleName}\\app\\Filament\\Resources\\{$resourceName};
use Filament\\Actions\\DeleteAction;
use Filament\\Resources\\Pages\\EditRecord;

class Edit{$modelName} extends EditRecord
{
    protected static string \$resource = {$resourceName}::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}";
        File::put("{$pagesPath}/Pages/Edit{$modelName}.php", $editContent);
    }

    private function generateAdditionalFeatures(): void
    {
        // Generate factories, seeders, tests based on options
        if ($this->data['generate_factory'] ?? true) {
            $this->generateFactories();
        }

        if ($this->data['generate_seeder'] ?? true) {
            $this->generateSeeders();
        }
    }

    private function generateFactories(): void
    {
        foreach ($this->data['models'] ?? [] as $modelData) {
            $modelName = Str::studly($modelData['name']);

            $content = "<?php

namespace Modules\\{$this->moduleName}\\database\\factories;

use Modules\\{$this->moduleName}\\app\\Models\\{$modelName};
use Illuminate\\Database\\Eloquent\\Factories\\Factory;

class {$modelName}Factory extends Factory
{
    protected \$model = {$modelName}::class;

    public function definition(): array
    {
        return [
            " . $this->generateFactoryFields($modelData) . "
        ];
    }
}";

            File::put(
                "{$this->modulePath}/database/factories/{$modelName}Factory.php",
                $content
            );
        }
    }

    private function generateFactoryFields(array $modelData): string
    {
        $fields = [];

        // Process all fields including required ones
        foreach ($modelData['fields'] as $field) {
            $fieldName = $field['name'];
            $fieldType = $field['type'];
            $required = $field['required'] ?? false;
            $enumOptions = $field['enum_options'] ?? null;

            // Skip auto-generated fields
            if (in_array($fieldName, ['id', 'created_at', 'updated_at'])) {
                continue;
            }

            switch ($fieldType) {
                case 'string':
                    if ($fieldName === 'name') {
                        $fields[] = "'{$fieldName}' => \$this->faker->name()";
                    } elseif ($fieldName === 'title') {
                        $fields[] = "'{$fieldName}' => \$this->faker->sentence(3)";
                    } elseif ($fieldName === 'slug') {
                        $fields[] = "'{$fieldName}' => \$this->faker->slug()";
                    } elseif ($fieldName === 'sku') {
                        $fields[] = "'{$fieldName}' => \$this->faker->unique()->regexify('[A-Z]{3}[0-9]{3}')";
                    } elseif (str_contains($fieldName, 'number')) {
                        $fields[] = "'{$fieldName}' => \$this->faker->unique()->numerify('ORD-####')";
                    } else {
                        $fields[] = "'{$fieldName}' => \$this->faker->words(2, true)";
                    }
                    break;

                case 'slug':
                    $fields[] = "'{$fieldName}' => \$this->faker->slug()";
                    break;

                case 'text':
                    if ($fieldName === 'description') {
                        $fields[] = "'{$fieldName}' => \$this->faker->paragraph()";
                    } elseif ($fieldName === 'content') {
                        $fields[] = "'{$fieldName}' => \$this->faker->paragraphs(3, true)";
                    } else {
                        $fields[] = "'{$fieldName}' => \$this->faker->text()";
                    }
                    break;

                case 'rich_text':
                    $fields[] = "'{$fieldName}' => \$this->faker->paragraphs(3, true)";
                    break;

                case 'integer':
                    if (str_contains($fieldName, 'stock')) {
                        $fields[] = "'{$fieldName}' => \$this->faker->numberBetween(0, 100)";
                    } else {
                        $fields[] = "'{$fieldName}' => \$this->faker->numberBetween(1, 1000)";
                    }
                    break;

                case 'decimal':
                    if (str_contains($fieldName, 'price') || str_contains($fieldName, 'total')) {
                        $fields[] = "'{$fieldName}' => \$this->faker->randomFloat(2, 10, 1000)";
                    } else {
                        $fields[] = "'{$fieldName}' => \$this->faker->randomFloat(2, 1, 100)";
                    }
                    break;

                case 'boolean':
                    $fields[] = "'{$fieldName}' => \$this->faker->boolean(80)"; // 80% true
                    break;

                case 'date':
                    $fields[] = "'{$fieldName}' => \$this->faker->date()";
                    break;

                case 'datetime':
                    if (str_contains($fieldName, 'published')) {
                        $fields[] = "'{$fieldName}' => \$this->faker->dateTimeBetween('-1 year', 'now')";
                    } else {
                        $fields[] = "'{$fieldName}' => \$this->faker->dateTime()";
                    }
                    break;

                case 'email':
                    $fields[] = "'{$fieldName}' => \$this->faker->unique()->safeEmail()";
                    break;

                case 'url':
                    $fields[] = "'{$fieldName}' => \$this->faker->url()";
                    break;

                case 'enum':
                    if ($enumOptions) {
                        $options = array_map('trim', explode("\n", $enumOptions));
                        $optionsString = "'" . implode("', '", $options) . "'";
                        $fields[] = "'{$fieldName}' => \$this->faker->randomElement([{$optionsString}])";
                    } else {
                        $fields[] = "'{$fieldName}' => 'active'"; // Default enum value
                    }
                    break;

                case 'file':
                case 'image':
                    $fields[] = "'{$fieldName}' => \$this->faker->imageUrl(640, 480, 'business', true)";
                    break;

                case 'password':
                    $fields[] = "'{$fieldName}' => bcrypt('password')";
                    break;

                default:
                    // Handle any unspecified field types
                    if ($required) {
                        $fields[] = "'{$fieldName}' => \$this->faker->words(2, true)";
                    }
                    break;
            }
        }

        // Add foreign key relationships
        foreach ($this->data['relationships'] ?? [] as $relationship) {
            if ($relationship['from_model'] === $modelData['name'] && $relationship['type'] === 'belongsTo') {
                $foreignKey = $relationship['foreign_key'] ?? Str::snake($relationship['to_model']) . '_id';
                $relatedModel = $relationship['to_model'];

                // Check if the related model exists in this module
                $relatedModelExists = false;
                foreach ($this->data['models'] ?? [] as $model) {
                    if ($model['name'] === $relatedModel) {
                        $relatedModelExists = true;
                        break;
                    }
                }

                if ($relatedModelExists) {
                    $fields[] = "'{$foreignKey}' => \\Modules\\{$this->moduleName}\\app\\Models\\{$relatedModel}::factory()";
                } else {
                    // For external models, just use a random ID (1-10)
                    $fields[] = "'{$foreignKey}' => \$this->faker->numberBetween(1, 10)";
                }
            }
        }

        return implode(",\n            ", $fields);
    }

    private function generateSeeders(): void
    {
        foreach ($this->data['models'] ?? [] as $modelData) {
            $modelName = Str::studly($modelData['name']);

            $content = "<?php

namespace Modules\\{$this->moduleName}\\database\\seeders;

use Illuminate\\Database\\Seeder;
use Modules\\{$this->moduleName}\\app\\Models\\{$modelName};

class {$modelName}Seeder extends Seeder
{
    public function run(): void
    {
        // Create records with proper relationships
        {$modelName}::factory()
            ->count(10)
            ->create();
    }
}";

            File::put(
                "{$this->modulePath}/database/seeders/{$modelName}Seeder.php",
                $content
            );
        }

        // Generate main module seeder
        $this->generateMainModuleSeeder();
    }

    private function generateMainModuleSeeder(): void
    {
        $seederCalls = [];
        foreach ($this->data['models'] ?? [] as $modelData) {
            $modelName = Str::studly($modelData['name']);
            $seederCalls[] = "        \$this->call({$modelName}Seeder::class);";
        }

        $seederCallsString = implode("\n", $seederCalls);

        $content = "<?php

namespace Modules\\{$this->moduleName}\\database\\seeders;

use Illuminate\\Database\\Seeder;

class {$this->moduleName}DatabaseSeeder extends Seeder
{
    public function run(): void
    {
{$seederCallsString}
    }
}";

        File::put(
            "{$this->modulePath}/database/seeders/{$this->moduleName}DatabaseSeeder.php",
            $content
        );
    }

    private function registerAndMigrate(): void
    {
        // Register service provider
        $this->registerServiceProvider();

        // Run migrations
        Artisan::call('migrate', ['--path' => "Modules/{$this->moduleName}/database/migrations"]);

        // Register permissions
        Artisan::call('permissions:register', ['--module' => $this->moduleName]);

        // Auto-enable module after generation
        $this->enableModule();
    }

    private function enableModule(): void
    {
        $statusService = new ModuleStatusService();
        $statusService->enable($this->moduleName);
    }

    private function registerServiceProvider(): void
    {
        $providersFile = base_path('bootstrap/providers.php');
        $content = File::get($providersFile);

        $providerClass = "Modules\\{$this->moduleName}\\app\\Providers\\{$this->moduleName}ServiceProvider::class,";

        if (!Str::contains($content, $providerClass)) {
            $content = str_replace(
                '];',
                "    {$providerClass}\n];",
                $content
            );

            File::put($providersFile, $content);
        }
    }
}

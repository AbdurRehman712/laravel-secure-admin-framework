<?php

namespace Modules\ModuleBuilder\app\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class ModuleEditorService
{
    private string $moduleName;
    private array $data;
    private string $modulePath;

    public function __construct(string $moduleName, array $data)
    {
        $this->moduleName = $moduleName;
        $this->data = $data;
        $this->modulePath = base_path("Modules/{$moduleName}");
    }

    public function updateModule(): void
    {
        // Add fields to existing tables
        $this->addFieldsToExistingTables();

        // Generate new models
        $this->generateNewModels();

        // Generate new migrations
        $this->generateNewMigrations();

        // Generate new Filament resources
        $this->generateNewFilamentResources();

        // Update existing models with new relationships
        $this->updateExistingModels();

        // Generate new factories and seeders
        $this->generateNewFactoriesAndSeeders();

        // Register service provider if not already registered
        $this->registerServiceProvider();

        // Run migrations
        $this->runMigrations();

        // Register new permissions
        $this->registerPermissions();
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

    private function addFieldsToExistingTables(): void
    {
        foreach ($this->data['existing_tables'] ?? [] as $tableData) {
            if (empty($tableData['table_name']) || empty($tableData['fields'])) {
                continue;
            }

            $tableName = $tableData['table_name'];
            $fields = $tableData['fields'];

            // Generate migration for adding fields
            $this->generateAddFieldsMigration($tableName, $fields);

            // Update existing model with new fillable fields
            $this->updateModelFillableFields($tableName, $fields);

            // Update existing Filament resource
            $this->updateFilamentResource($tableName, $fields);
        }
    }

    private function generateAddFieldsMigration(string $tableName, array $fields): void
    {
        $timestamp = now()->format('Y_m_d_His');
        $className = 'Add' . Str::studly(implode('And', array_column($fields, 'name'))) . 'To' . Str::studly($tableName) . 'Table';

        // Generate fields code
        $fieldsCode = '';
        foreach ($fields as $field) {
            $fieldsCode .= $this->generateMigrationField($field);
        }

        $content = "<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('{$tableName}', function (Blueprint \$table) {
{$fieldsCode}
        });
    }

    public function down(): void
    {
        Schema::table('{$tableName}', function (Blueprint \$table) {
            \$table->dropColumn(['" . implode("', '", array_column($fields, 'name')) . "']);
        });
    }
};";

        File::put(
            "{$this->modulePath}/database/migrations/{$timestamp}_{$className}.php",
            $content
        );
    }

    private function updateModelFillableFields(string $tableName, array $fields): void
    {
        // Find the model by table name
        $modelName = $this->getModelNameFromTable($tableName);
        if (!$modelName) return;

        $modelPath = "{$this->modulePath}/app/Models/{$modelName}.php";
        if (!File::exists($modelPath)) return;

        $content = File::get($modelPath);

        // Extract current fillable fields
        preg_match('/protected \$fillable = \[(.*?)\];/s', $content, $matches);
        if (!$matches) return;

        $currentFillable = $matches[1];
        $newFields = array_column($fields, 'name');

        // Add new fields to fillable
        foreach ($newFields as $fieldName) {
            if (strpos($currentFillable, "'{$fieldName}'") === false) {
                $currentFillable .= ", '{$fieldName}'";
            }
        }

        // Update the fillable array
        $newFillable = "protected \$fillable = [{$currentFillable}];";
        $content = preg_replace('/protected \$fillable = \[.*?\];/s', $newFillable, $content);

        // Add new casts if needed
        $newCasts = [];
        foreach ($fields as $field) {
            switch ($field['type']) {
                case 'boolean':
                    $newCasts[] = "'{$field['name']}' => 'boolean'";
                    break;
                case 'decimal':
                    $newCasts[] = "'{$field['name']}' => 'decimal:2'";
                    break;
                case 'date':
                    $newCasts[] = "'{$field['name']}' => 'date'";
                    break;
                case 'datetime':
                case 'timestamp':
                    $newCasts[] = "'{$field['name']}' => 'datetime'";
                    break;
                case 'json':
                    $newCasts[] = "'{$field['name']}' => 'array'";
                    break;
            }
        }

        if (!empty($newCasts)) {
            // Find existing casts and add new ones
            if (preg_match('/protected \$casts = \[(.*?)\];/s', $content, $castMatches)) {
                $currentCasts = trim($castMatches[1]);
                if (!empty($currentCasts)) {
                    $currentCasts .= ",\n        ";
                }
                $currentCasts .= implode(",\n        ", $newCasts);
                $newCastsString = "protected \$casts = [\n        {$currentCasts}\n    ];";
                $content = preg_replace('/protected \$casts = \[.*?\];/s', $newCastsString, $content);
            }
        }

        File::put($modelPath, $content);
    }

    private function getModelNameFromTable(string $tableName): ?string
    {
        // Remove module prefix and convert to model name
        $moduleLower = strtolower($this->moduleName);
        $tableWithoutPrefix = str_replace($moduleLower . '_', '', $tableName);
        return Str::studly(Str::singular($tableWithoutPrefix));
    }

    private function updateFilamentResource(string $tableName, array $fields): void
    {
        $modelName = $this->getModelNameFromTable($tableName);
        if (!$modelName) return;

        $resourcePath = "{$this->modulePath}/app/Filament/Resources/{$modelName}Resource.php";
        if (!File::exists($resourcePath)) return;

        $content = File::get($resourcePath);

        // Generate new form fields
        $newFormFields = $this->generateFormFields(['fields' => $fields]);

        // Generate new table columns
        $newTableColumns = $this->generateTableColumns(['fields' => $fields]);

        // More careful regex to add fields before the closing bracket
        // Look for the schema array and add new fields before the closing bracket
        if (preg_match('/return \$schema->schema\(\[\s*(.*?)\s*\]\);/s', $content, $matches)) {
            $currentFields = trim($matches[1]);

            // Remove any trailing comma and whitespace
            $currentFields = rtrim($currentFields, ",\n\r\t ");

            if (!empty($currentFields)) {
                $currentFields .= ",\n            ";
            }
            $currentFields .= $newFormFields;

            $newFormSchema = "return \$schema->schema([\n            {$currentFields}\n        ]);";
            $content = preg_replace('/return \$schema->schema\(\[\s*.*?\s*\]\);/s', $newFormSchema, $content);
        }

        // More careful regex for table columns
        if (preg_match('/->columns\(\[\s*(.*?)\s*\]\)/s', $content, $matches)) {
            $currentColumns = trim($matches[1]);

            // Remove any trailing comma and whitespace
            $currentColumns = rtrim($currentColumns, ",\n\r\t ");

            if (!empty($currentColumns)) {
                $currentColumns .= ",\n                ";
            }
            $currentColumns .= $newTableColumns;

            $newTableColumnsString = "->columns([\n                {$currentColumns}\n            ])";
            $content = preg_replace('/->columns\(\[\s*.*?\s*\]\)/s', $newTableColumnsString, $content);
        }

        File::put($resourcePath, $content);
    }

    private function generateNewModels(): void
    {
        foreach ($this->data['new_tables'] ?? [] as $tableData) {
            if (empty($tableData['name'])) continue;
            
            $modelName = Str::studly($tableData['name']);
            $tableName = $tableData['table_name'];
            
            // Generate fillable fields
            $fillableFields = [];
            foreach ($tableData['fields'] as $field) {
                $fillableFields[] = "'{$field['name']}'";
            }
            $fillableString = implode(', ', $fillableFields);
            
            // Generate casts
            $casts = [];
            foreach ($tableData['fields'] as $field) {
                switch ($field['type']) {
                    case 'boolean':
                        $casts[] = "'{$field['name']}' => 'boolean'";
                        break;
                    case 'decimal':
                        $casts[] = "'{$field['name']}' => 'decimal:2'";
                        break;
                    case 'date':
                        $casts[] = "'{$field['name']}' => 'date'";
                        break;
                    case 'datetime':
                    case 'timestamp':
                        $casts[] = "'{$field['name']}' => 'datetime'";
                        break;
                    case 'json':
                        $casts[] = "'{$field['name']}' => 'array'";
                        break;
                }
            }
            $castsString = implode(",\n        ", $casts);
            
            $content = "<?php

namespace Modules\\{$this->moduleName}\\app\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;

class {$modelName} extends Model
{
    use HasFactory;

    protected \$table = '{$tableName}';
    
    protected \$fillable = [{$fillableString}];
    
    protected \$casts = [
        {$castsString}
    ];

    protected static function newFactory()
    {
        return \\Modules\\{$this->moduleName}\\database\\factories\\{$modelName}Factory::new();
    }

    // Relationships will be added here
}";

            File::put(
                "{$this->modulePath}/app/Models/{$modelName}.php",
                $content
            );
        }
    }

    private function generateNewMigrations(): void
    {
        foreach ($this->data['new_tables'] ?? [] as $tableData) {
            if (empty($tableData['name'])) continue;
            
            $tableName = $tableData['table_name'];
            $timestamp = now()->addSeconds(count($this->data['new_tables'] ?? []))->format('Y_m_d_His');
            
            // Generate fields
            $fieldsCode = '';
            foreach ($tableData['fields'] as $field) {
                $fieldsCode .= $this->generateMigrationField($field);
            }
            
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
{$fieldsCode}
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
    }

    private function generateMigrationField(array $field): string
    {
        $fieldName = $field['name'];
        $fieldType = $field['type'];
        $required = $field['required'] ?? false;
        $length = $field['length'] ?? null;
        $default = $field['default'] ?? null;

        $fieldCode = "            \$table->";

        switch ($fieldType) {
            case 'string':
            case 'slug':
                $fieldCode .= $length ? "string('{$fieldName}', {$length})" : "string('{$fieldName}')";
                break;
            case 'text':
                $fieldCode .= "text('{$fieldName}')";
                break;
            case 'rich_text':
                $fieldCode .= "longText('{$fieldName}')";
                break;
            case 'integer':
                $fieldCode .= "integer('{$fieldName}')";
                break;
            case 'decimal':
                $fieldCode .= "decimal('{$fieldName}', 10, 2)";
                break;
            case 'boolean':
                $fieldCode .= "boolean('{$fieldName}')";
                break;
            case 'date':
                $fieldCode .= "date('{$fieldName}')";
                break;
            case 'datetime':
                $fieldCode .= "dateTime('{$fieldName}')";
                break;
            case 'timestamp':
                $fieldCode .= "timestamp('{$fieldName}')";
                break;
            case 'json':
                $fieldCode .= "json('{$fieldName}')";
                break;
            case 'enum':
                $enumOptions = $field['enum_options'] ?? '';
                if ($enumOptions) {
                    $options = array_map('trim', explode("\n", $enumOptions));
                    $optionsString = "'" . implode("', '", $options) . "'";
                    $fieldCode .= "enum('{$fieldName}', [{$optionsString}])";
                } else {
                    $fieldCode .= "string('{$fieldName}')";
                }
                break;
            case 'file':
            case 'image':
                $fieldCode .= "string('{$fieldName}')";
                break;
            case 'email':
            case 'url':
            case 'password':
                $fieldCode .= "string('{$fieldName}')";
                break;
            default:
                $fieldCode .= "string('{$fieldName}')";
                break;
        }

        if (!$required) {
            $fieldCode .= "->nullable()";
        }

        if ($default !== null) {
            if ($fieldType === 'boolean') {
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

    private function generateNewFilamentResources(): void
    {
        foreach ($this->data['new_tables'] ?? [] as $tableData) {
            if (empty($tableData['name'])) continue;
            
            $modelName = Str::studly($tableData['name']);
            $resourceName = $modelName . 'Resource';
            $pluralName = Str::plural($modelName);
            
            // Use the same enhanced generator logic
            $generator = new EnhancedModuleGenerator($this->moduleName, [
                'models' => [$tableData],
                'relationships' => []
            ]);
            
            // Generate the resource using the existing method
            $this->generateSingleFilamentResource($tableData);
        }
    }

    private function generateSingleFilamentResource(array $modelData): void
    {
        $modelName = Str::studly($modelData['name']);
        $resourceName = $modelName . 'Resource';
        $pluralName = Str::plural($modelName);
        
        // Generate form fields
        $formFields = $this->generateFormFields($modelData);
        
        // Generate table columns
        $tableColumns = $this->generateTableColumns($modelData);
        
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

    public static function form(Schema \$schema): Schema
    {
        return \$schema->schema([
            {$formFields}
        ]);
    }

    public static function table(Table \$table): Table
    {
        return \$table
            ->columns([
                TextColumn::make('id')->sortable()->toggleable(),
                {$tableColumns}
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
                            \$filename = '{$modelName}_export_' . now()->format('Y_m_d_H_i_s') . '.csv';
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
                    $component = "TextInput::make('{$fieldName}')";
                    if ($required) $component .= "->required()";
                    if ($length) $component .= "->maxLength({$length})";
                    $component .= "->helperText('Auto-generated from name, but you can edit it')";
                    $component .= "->placeholder('Will be auto-generated')";
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
        
        return implode(",\n            ", $fields);
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
        
        // Add timestamps
        $columns[] = "TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)";
        $columns[] = "TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)";

        return implode(",\n                ", $columns);
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
use Filament\\Actions\\CreateAction;
use Filament\\Resources\\Pages\\ListRecords;

class List{$pluralName} extends ListRecords
{
    protected static string \$resource = {$resourceName}::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
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

    private function updateExistingModels(): void
    {
        // Add new relationships to existing models
        foreach ($this->data['new_relationships'] ?? [] as $relationship) {
            $this->addRelationshipToModel($relationship);
        }
    }

    private function addRelationshipToModel(array $relationship): void
    {
        $fromModel = $relationship['from_model'];
        $toModel = $relationship['to_model'];
        $type = $relationship['type'];
        $relationshipName = $relationship['relationship_name'] ?? Str::camel($toModel);
        $foreignKey = $relationship['foreign_key'] ?? Str::snake($toModel) . '_id';
        
        $modelPath = "{$this->modulePath}/app/Models/{$fromModel}.php";
        
        if (File::exists($modelPath)) {
            $content = File::get($modelPath);
            
            // Generate relationship method
            $relationshipMethod = $this->generateRelationshipMethod($relationship);
            
            // Add the relationship method before the closing brace
            $content = str_replace(
                "\n}",
                "\n{$relationshipMethod}\n}",
                $content
            );
            
            File::put($modelPath, $content);
        }
    }

    private function generateRelationshipMethod(array $relationship): string
    {
        $toModel = $relationship['to_model'];
        $type = $relationship['type'];
        $relationshipName = $relationship['relationship_name'] ?? Str::camel($toModel);
        $foreignKey = $relationship['foreign_key'] ?? Str::snake($toModel) . '_id';
        
        switch ($type) {
            case 'belongsTo':
                return "
    public function {$relationshipName}()
    {
        return \$this->belongsTo(\\Modules\\{$this->moduleName}\\app\\Models\\{$toModel}::class, '{$foreignKey}');
    }";
                
            case 'hasMany':
                return "
    public function {$relationshipName}()
    {
        return \$this->hasMany(\\Modules\\{$this->moduleName}\\app\\Models\\{$toModel}::class, '{$foreignKey}');
    }";
                
            case 'hasOne':
                return "
    public function {$relationshipName}()
    {
        return \$this->hasOne(\\Modules\\{$this->moduleName}\\app\\Models\\{$toModel}::class, '{$foreignKey}');
    }";
                
            case 'belongsToMany':
                $pivotTable = $relationship['pivot_table'] ?? $this->generatePivotTableName($relationship['from_model'], $toModel);
                return "
    public function {$relationshipName}()
    {
        return \$this->belongsToMany(\\Modules\\{$this->moduleName}\\app\\Models\\{$toModel}::class, '{$pivotTable}');
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

    private function generateNewFactoriesAndSeeders(): void
    {
        foreach ($this->data['new_tables'] ?? [] as $tableData) {
            if (empty($tableData['name'])) continue;
            
            $modelName = Str::studly($tableData['name']);
            
            // Generate factory
            $this->generateFactory($modelName, $tableData);
            
            // Generate seeder
            $this->generateSeeder($modelName);
        }
    }

    private function generateFactory(string $modelName, array $tableData): void
    {
        $factoryFields = [];
        
        foreach ($tableData['fields'] as $field) {
            $fieldName = $field['name'];
            $fieldType = $field['type'];
            
            switch ($fieldType) {
                case 'string':
                    if ($fieldName === 'name') {
                        $factoryFields[] = "'{$fieldName}' => \$this->faker->name()";
                    } elseif ($fieldName === 'title') {
                        $factoryFields[] = "'{$fieldName}' => \$this->faker->sentence(3)";
                    } else {
                        $factoryFields[] = "'{$fieldName}' => \$this->faker->words(2, true)";
                    }
                    break;

                case 'slug':
                    $factoryFields[] = "'{$fieldName}' => \$this->faker->slug()";
                    break;
                case 'text':
                    $factoryFields[] = "'{$fieldName}' => \$this->faker->paragraph()";
                    break;
                case 'rich_text':
                    $factoryFields[] = "'{$fieldName}' => \$this->faker->paragraphs(3, true)";
                    break;
                case 'integer':
                    $factoryFields[] = "'{$fieldName}' => \$this->faker->numberBetween(1, 100)";
                    break;
                case 'decimal':
                    $factoryFields[] = "'{$fieldName}' => \$this->faker->randomFloat(2, 10, 1000)";
                    break;
                case 'boolean':
                    $factoryFields[] = "'{$fieldName}' => \$this->faker->boolean(80)";
                    break;
                case 'date':
                    $factoryFields[] = "'{$fieldName}' => \$this->faker->date()";
                    break;
                case 'datetime':
                    $factoryFields[] = "'{$fieldName}' => \$this->faker->dateTime()";
                    break;
                case 'email':
                    $factoryFields[] = "'{$fieldName}' => \$this->faker->unique()->safeEmail()";
                    break;
                case 'enum':
                    $enumOptions = $field['enum_options'] ?? '';
                    if ($enumOptions) {
                        $options = array_map('trim', explode("\n", $enumOptions));
                        $optionsString = "'" . implode("', '", $options) . "'";
                        $factoryFields[] = "'{$fieldName}' => \$this->faker->randomElement([{$optionsString}])";
                    }
                    break;
            }
        }
        
        $fieldsString = implode(",\n            ", $factoryFields);
        
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
            {$fieldsString}
        ];
    }
}";

        File::put(
            "{$this->modulePath}/database/factories/{$modelName}Factory.php",
            $content
        );
    }

    private function generateSeeder(string $modelName): void
    {
        $content = "<?php

namespace Modules\\{$this->moduleName}\\database\\seeders;

use Illuminate\\Database\\Seeder;
use Modules\\{$this->moduleName}\\app\\Models\\{$modelName};

class {$modelName}Seeder extends Seeder
{
    public function run(): void
    {
        {$modelName}::factory()->count(10)->create();
    }
}";

        File::put(
            "{$this->modulePath}/database/seeders/{$modelName}Seeder.php",
            $content
        );
    }

    private function runMigrations(): void
    {
        Artisan::call('migrate', ['--path' => "Modules/{$this->moduleName}/database/migrations"]);
    }

    private function registerPermissions(): void
    {
        Artisan::call('permissions:register', ['--module' => $this->moduleName]);
    }
}

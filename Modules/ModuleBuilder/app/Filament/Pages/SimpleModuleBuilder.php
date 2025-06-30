<?php

namespace Modules\ModuleBuilder\app\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SimpleModuleBuilder extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Module Builder';
    protected static ?string $title = 'Simple Module Builder';
    protected static \UnitEnum|string|null $navigationGroup = 'Development Tools';
    protected string $view = 'modulebuilder::simple-module-builder';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_simple_module_builder') ?? false;
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'module_name' => '',
            'description' => '',
            'tables' => [
                [
                    'name' => '',
                    'fields' => [
                        ['name' => 'name', 'type' => 'string', 'required' => true],
                    ]
                ]
            ]
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('module_name')
                    ->label('Module Name')
                    ->required()
                    ->placeholder('e.g., Blog, Shop, Users')
                    ->helperText('This will create a complete module with CRUD operations'),

                Textarea::make('description')
                    ->label('Description')
                    ->placeholder('Brief description of what this module does')
                    ->rows(2),

                Repeater::make('tables')
                    ->label('Tables')
                    ->schema([
                        TextInput::make('name')
                            ->label('Table Name')
                            ->required()
                            ->placeholder('e.g., Posts, Products, Categories'),

                        Repeater::make('fields')
                            ->label('Fields')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Field Name')
                                    ->required(),

                                Select::make('type')
                                    ->label('Field Type')
                                    ->options([
                                        'string' => 'Text (String)',
                                        'text' => 'Long Text',
                                        'integer' => 'Number',
                                        'boolean' => 'Yes/No',
                                        'date' => 'Date',
                                        'datetime' => 'Date & Time',
                                    ])
                                    ->required(),

                                Toggle::make('required')
                                    ->label('Required')
                                    ->default(false),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->addActionLabel('Add Field'),
                    ])
                    ->defaultItems(1)
                    ->addActionLabel('Add Table'),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate')
                ->label('Generate Module')
                ->icon('heroicon-o-rocket-launch')
                ->color('success')
                ->size('lg')
                ->action('generateModule'),
        ];
    }

    public function generateModule(): void
    {
        $data = $this->form->getState();
        
        try {
            $moduleName = Str::studly($data['module_name']);
            $moduleSlug = Str::slug($data['module_name']);
            
            // 1. Create module directory structure
            $this->createModuleStructure($moduleName);
            
            // 2. Generate models and migrations
            foreach ($data['tables'] as $table) {
                $this->generateTable($moduleName, $table);
            }
            
            // 3. Generate service provider
            $this->generateServiceProvider($moduleName);
            
            // 4. Register module automatically
            $this->registerModule($moduleName);
            
            // 5. Run migrations
            $this->runMigrations($moduleName);
            
            Notification::make()
                ->title('Module Generated Successfully!')
                ->body("Module '{$moduleName}' has been created and is ready to use. Check the sidebar for your new module.")
                ->success()
                ->persistent()
                ->send();
                
            // Clear form
            $this->form->fill([
                'module_name' => '',
                'description' => '',
                'tables' => [['name' => '', 'fields' => [['name' => 'name', 'type' => 'string', 'required' => true]]]]
            ]);
            
        } catch (\Exception $e) {
            Notification::make()
                ->title('Generation Failed')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    private function createModuleStructure(string $moduleName): void
    {
        $basePath = base_path("Modules/{$moduleName}");
        
        $directories = [
            'app/Models',
            'app/Filament/Resources',
            'app/Providers',
            'database/migrations',
            'routes',
        ];
        
        foreach ($directories as $dir) {
            File::makeDirectory("{$basePath}/{$dir}", 0755, true, true);
        }
        
        // Create module.json
        $moduleConfig = [
            'name' => $moduleName,
            'alias' => strtolower($moduleName),
            'description' => $this->data['description'] ?? "Generated {$moduleName} module",
            'keywords' => [],
            'priority' => 0,
            'providers' => ["Modules\\{$moduleName}\\app\\Providers\\{$moduleName}ServiceProvider"],
            'files' => []
        ];
        
        File::put("{$basePath}/module.json", json_encode($moduleConfig, JSON_PRETTY_PRINT));
    }

    private function generateTable(string $moduleName, array $tableData): void
    {
        $tableName = Str::plural(Str::snake($tableData['name']));
        $modelName = Str::studly(Str::singular($tableData['name']));
        
        // Generate migration
        $this->generateMigration($moduleName, $tableName, $tableData['fields']);
        
        // Generate model
        $this->generateModel($moduleName, $modelName, $tableData['fields']);
        
        // Generate Filament resource
        $this->generateFilamentResource($moduleName, $modelName, $tableName, $tableData['fields']);
    }

    private function generateMigration(string $moduleName, string $tableName, array $fields): void
    {
        $timestamp = now()->format('Y_m_d_His');
        $className = "Create" . Str::studly($tableName) . "Table";
        
        $fieldsCode = '';
        foreach ($fields as $field) {
            $type = $this->getMigrationFieldType($field['type']);
            $nullable = !($field['required'] ?? false) ? '->nullable()' : '';
            $fieldsCode .= "            \$table->{$type}('{$field['name']}'){$nullable};\n";
        }
        
        $migrationContent = "<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$tableName}', function (Blueprint \$table) {
            \$table->id();
{$fieldsCode}            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$tableName}');
    }
};";

        $migrationPath = base_path("Modules/{$moduleName}/database/migrations/{$timestamp}_create_{$tableName}_table.php");
        File::put($migrationPath, $migrationContent);
    }

    private function generateModel(string $moduleName, string $modelName, array $fields): void
    {
        $fillable = collect($fields)->pluck('name')->map(fn($name) => "'{$name}'")->join(', ');
        
        $modelContent = "<?php

namespace Modules\\{$moduleName}\\app\\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class {$modelName} extends Model
{
    use HasFactory;

    protected \$fillable = [{$fillable}];
}";

        $modelPath = base_path("Modules/{$moduleName}/app/Models/{$modelName}.php");
        File::put($modelPath, $modelContent);
    }

    private function generateFilamentResource(string $moduleName, string $modelName, string $tableName, array $fields): void
    {
        $resourceName = $modelName . 'Resource';
        $pluralName = Str::plural($modelName);
        
        // Generate form fields
        $formFields = '';
        foreach ($fields as $field) {
            $component = $this->getFilamentComponent($field['type']);
            $required = ($field['required'] ?? false) ? '->required()' : '';
            $formFields .= "                {$component}::make('{$field['name']}'){$required},\n";
        }
        
        // Generate table columns
        $tableColumns = '';
        foreach ($fields as $field) {
            $tableColumns .= "                TextColumn::make('{$field['name']}')->searchable()->sortable(),\n";
        }
        
        $resourceContent = "<?php

namespace Modules\\{$moduleName}\\app\\Filament\\Resources;

use Modules\\{$moduleName}\\app\\Models\\{$modelName};
use Modules\\{$moduleName}\\app\\Filament\\Resources\\{$resourceName}\\Pages;
use Filament\\Forms\\Components\\TextInput;
use Filament\\Forms\\Components\\Textarea;
use Filament\\Forms\\Components\\Toggle;
use Filament\\Forms\\Components\\DatePicker;
use Filament\\Forms\\Components\\DateTimePicker;
use Filament\\Resources\\Resource;
use Filament\\Schemas\\Schema;
use Filament\\Tables\\Table;
use Filament\\Tables\\Columns\\TextColumn;
use Filament\\Tables\\Columns\\BooleanColumn;

class {$resourceName} extends Resource
{
    protected static ?string \$model = {$modelName}::class;
    protected static \\BackedEnum|string|null \$navigationIcon = 'heroicon-o-rectangle-stack';
    protected static \\UnitEnum|string|null \$navigationGroup = '{$moduleName}';

    public static function form(Schema \$schema): Schema
    {
        return \$schema->schema([
{$formFields}        ]);
    }

    public static function table(Table \$table): Table
    {
        return \$table->columns([
{$tableColumns}        ]);
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

        $resourcePath = base_path("Modules/{$moduleName}/app/Filament/Resources/{$resourceName}.php");
        File::put($resourcePath, $resourceContent);
        
        // Generate resource pages
        $this->generateResourcePages($moduleName, $modelName, $resourceName);
    }

    private function getMigrationFieldType(string $type): string
    {
        return match($type) {
            'string' => 'string',
            'text' => 'text',
            'integer' => 'integer',
            'boolean' => 'boolean',
            'date' => 'date',
            'datetime' => 'timestamp',
            default => 'string'
        };
    }

    private function getFilamentComponent(string $type): string
    {
        return match($type) {
            'string' => 'TextInput',
            'text' => 'Textarea',
            'integer' => 'TextInput',
            'boolean' => 'Toggle',
            'date' => 'DatePicker',
            'datetime' => 'DateTimePicker',
            default => 'TextInput'
        };
    }

    private function generateResourcePages(string $moduleName, string $modelName, string $resourceName): void
    {
        $pluralName = Str::plural($modelName);
        $pagesPath = base_path("Modules/{$moduleName}/app/Filament/Resources/{$resourceName}");
        File::makeDirectory("{$pagesPath}/Pages", 0755, true, true);

        // List page
        $listContent = "<?php

namespace Modules\\{$moduleName}\\app\\Filament\\Resources\\{$resourceName}\\Pages;

use Modules\\{$moduleName}\\app\\Filament\\Resources\\{$resourceName};
use Filament\\Actions;
use Filament\\Resources\\Pages\\ListRecords;

class List{$pluralName} extends ListRecords
{
    protected static string \$resource = {$resourceName}::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\\CreateAction::make(),
        ];
    }
}";
        File::put("{$pagesPath}/Pages/List{$pluralName}.php", $listContent);

        // Create page
        $createContent = "<?php

namespace Modules\\{$moduleName}\\app\\Filament\\Resources\\{$resourceName}\\Pages;

use Modules\\{$moduleName}\\app\\Filament\\Resources\\{$resourceName};
use Filament\\Resources\\Pages\\CreateRecord;

class Create{$modelName} extends CreateRecord
{
    protected static string \$resource = {$resourceName}::class;
}";
        File::put("{$pagesPath}/Pages/Create{$modelName}.php", $createContent);

        // Edit page
        $editContent = "<?php

namespace Modules\\{$moduleName}\\app\\Filament\\Resources\\{$resourceName}\\Pages;

use Modules\\{$moduleName}\\app\\Filament\\Resources\\{$resourceName};
use Filament\\Actions;
use Filament\\Resources\\Pages\\EditRecord;

class Edit{$modelName} extends EditRecord
{
    protected static string \$resource = {$resourceName}::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\\DeleteAction::make(),
        ];
    }
}";
        File::put("{$pagesPath}/Pages/Edit{$modelName}.php", $editContent);
    }

    private function generateServiceProvider(string $moduleName): void
    {
        $serviceProviderContent = "<?php

namespace Modules\\{$moduleName}\\app\\Providers;

use Illuminate\\Support\\ServiceProvider;

class {$moduleName}ServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        \$this->loadMigrationsFrom(module_path('{$moduleName}', 'database/migrations'));
    }

    public function register(): void
    {
        //
    }
}";

        $providerPath = base_path("Modules/{$moduleName}/app/Providers/{$moduleName}ServiceProvider.php");
        File::put($providerPath, $serviceProviderContent);
    }

    private function registerModule(string $moduleName): void
    {
        // Auto-register in AdminPanelProvider
        $adminProviderPath = app_path('Providers/Filament/AdminPanelProvider.php');
        $content = File::get($adminProviderPath);

        $newDiscovery = "->discoverResources(in: base_path('Modules/{$moduleName}/app/Filament/Resources'), for: 'Modules\\{$moduleName}\\app\\Filament\\Resources')";

        if (!str_contains($content, $newDiscovery)) {
            $content = str_replace(
                '->discoverPages(in: app_path(\'Filament/Pages\'), for: \'App\Filament\Pages\')',
                "->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')\n            {$newDiscovery}",
                $content
            );
            File::put($adminProviderPath, $content);
        }

        // Auto-register service provider
        $providersPath = base_path('bootstrap/providers.php');
        $providersContent = File::get($providersPath);

        $newProvider = "Modules\\{$moduleName}\\app\\Providers\\{$moduleName}ServiceProvider::class,";

        if (!str_contains($providersContent, $newProvider)) {
            $providersContent = str_replace(
                '];',
                "    {$newProvider}\n];",
                $providersContent
            );
            File::put($providersPath, $providersContent);
        }
    }

    private function runMigrations(string $moduleName): void
    {
        \Artisan::call('migrate', ['--path' => "Modules/{$moduleName}/database/migrations"]);
        \Artisan::call('optimize:clear');
    }
}

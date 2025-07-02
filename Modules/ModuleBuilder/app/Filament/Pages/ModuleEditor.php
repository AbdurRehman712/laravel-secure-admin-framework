<?php

namespace Modules\ModuleBuilder\app\Filament\Pages;

use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Modules\ModuleBuilder\app\Services\ModuleEditorService;
use Modules\ModuleBuilder\app\Services\SeederExecutionService;

class ModuleEditor extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-pencil-square';
    
    protected static ?string $navigationLabel = 'Module Editor';
    
    protected static ?string $title = 'Module Editor';
    
    protected string $view = 'modulebuilder::module-editor';
    
    protected static \UnitEnum|string|null $navigationGroup = 'Development Tools';
    
    protected static ?int $navigationSort = 3;

    public ?array $data = [];
    
    public ?string $selectedModule = null;
    
    public ?array $moduleData = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_module_editor') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'selected_module' => '',
            'existing_tables' => [],
            'new_tables' => [],
            'new_relationships' => [],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('selected_module')
                ->label('Select Module to Edit')
                ->options($this->getAvailableModules())
                ->live()
                ->afterStateUpdated(function ($state, callable $set) {
                    $this->selectedModule = $state;
                    $this->loadModuleData();

                    // Reset the form sections when module changes
                    if ($state) {
                        $set('existing_tables', []);
                        $set('new_tables', []);
                        $set('new_relationships', []);
                    }
                })
                ->placeholder('Choose a module to edit'),

            $this->getExistingTablesRepeater()
                ->visible(fn (callable $get) => !empty($get('selected_module'))),

            $this->getNewTablesRepeater()
                ->visible(fn (callable $get) => !empty($get('selected_module'))),

            $this->getNewRelationshipsRepeater()
                ->visible(fn (callable $get) => !empty($get('selected_module'))),
        ])
        ->statePath('data');
    }



    private function getAvailableModules(): array
    {
        $modules = [];
        $modulesPath = base_path('Modules');
        
        if (File::exists($modulesPath)) {
            $directories = File::directories($modulesPath);
            
            foreach ($directories as $directory) {
                $moduleName = basename($directory);
                
                // Skip system modules
                if (in_array($moduleName, ['Core', 'PublicUser', 'ModuleBuilder'])) {
                    continue;
                }
                
                // Check if it has the module structure
                if (File::exists($directory . '/module.json')) {
                    $modules[$moduleName] = $moduleName;
                }
            }
        }
        
        return $modules;
    }

    private function loadModuleData(): void
    {
        if (!$this->selectedModule) {
            $this->moduleData = null;
            return;
        }
        
        $modulePath = base_path("Modules/{$this->selectedModule}");
        $moduleJsonPath = $modulePath . '/module.json';
        
        if (File::exists($moduleJsonPath)) {
            $this->moduleData = json_decode(File::get($moduleJsonPath), true);
        }
    }

    private function getExistingTablesRepeater(): Repeater
    {
        return Repeater::make('existing_tables')
            ->label('Add Fields to Existing Tables')
            ->schema([
                Select::make('table_name')
                    ->label('Select Existing Table')
                    ->options(function (callable $get) {
                        if (!$this->selectedModule) {
                            return [];
                        }

                        $models = $this->getExistingModels();
                        $options = [];

                        foreach ($models as $modelName) {
                            $tableName = $this->getTableNameFromModel($modelName);
                            $options[$tableName] = "{$modelName} ({$tableName})";
                        }

                        return $options;
                    })
                    ->required()
                    ->live(),

                $this->getFieldsRepeater()
                    ->label('New Fields to Add')
                    ->visible(fn (callable $get) => !empty($get('table_name'))),
            ])
            ->itemLabel(fn (array $state): ?string =>
                ($state['table_name'] ?? 'Select Table') . ' - ' .
                (count($state['fields'] ?? []) . ' new fields')
            )
            ->addActionLabel('Add Fields to Existing Table')
            ->reorderableWithButtons()
            ->collapsible()
            ->defaultItems(0);
    }

    private function getNewTablesRepeater(): Repeater
    {
        return Repeater::make('new_tables')
            ->label('Add New Tables')
            ->schema([
                TextInput::make('name')
                    ->label('Model Name')
                    ->required()
                    ->placeholder('e.g., Review, Comment, Tag')
                    ->live(debounce: 500)
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        // Only auto-generate table name if it's empty
                        $currentTableName = $get('table_name');
                        if (empty($currentTableName)) {
                            $set('table_name', Str::plural(Str::snake($state)));
                        }
                    }),

                TextInput::make('table_name')
                    ->label('Table Name')
                    ->required()
                    ->placeholder('Auto-generated from model name')
                    ->helperText('Edit to customize the table name'),
                
                Textarea::make('description')
                    ->label('Model Description')
                    ->placeholder('What this model represents')
                    ->rows(2),
                
                $this->getFieldsRepeater(),
            ])
            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
            ->addActionLabel('Add New Table')
            ->reorderableWithButtons()
            ->collapsible()
            ->defaultItems(0);
    }

    private function getFieldsRepeater(): Repeater
    {
        return Repeater::make('fields')
            ->label('Fields')
            ->schema([
                TextInput::make('name')
                    ->label('Field Name')
                    ->required()
                    ->placeholder('e.g., title, rating, content'),
                
                Select::make('type')
                    ->label('Field Type')
                    ->required()
                    ->options([
                        'string' => 'String (Text)',
                        'slug' => 'Slug (Auto-generated)',
                        'text' => 'Text (Long Text)',
                        'integer' => 'Integer (Number)',
                        'decimal' => 'Decimal (Money/Float)',
                        'boolean' => 'Boolean (Yes/No)',
                        'date' => 'Date',
                        'datetime' => 'Date & Time',
                        'timestamp' => 'Timestamp',
                        'json' => 'JSON',
                        'enum' => 'Enum (Select Options)',
                        'file' => 'File Upload',
                        'image' => 'Image Upload',
                        'rich_text' => 'Rich Text Editor',
                        'email' => 'Email',
                        'url' => 'URL',
                        'password' => 'Password',
                    ])
                    ->live(),
                
                Toggle::make('required')
                    ->label('Required')
                    ->default(false),
                
                TextInput::make('length')
                    ->label('Max Length')
                    ->numeric()
                    ->visible(fn ($get) => in_array($get('type'), ['string', 'text']))
                    ->placeholder('255'),
                
                TextInput::make('default')
                    ->label('Default Value')
                    ->placeholder('Default value for this field'),
                
                Textarea::make('enum_options')
                    ->label('Enum Options (one per line)')
                    ->visible(fn ($get) => $get('type') === 'enum')
                    ->placeholder("active\ninactive\npending")
                    ->rows(3),
                
                TextInput::make('validation')
                    ->label('Validation Rules')
                    ->placeholder('e.g., min:3|max:255|unique:table,column')
                    ->helperText('Laravel validation rules'),
            ])
            ->itemLabel(fn (array $state): ?string => 
                ($state['name'] ?? 'Field') . ' (' . ($state['type'] ?? 'unknown') . ')'
            )
            ->addActionLabel('Add Field')
            ->reorderableWithButtons()
            ->collapsible()
            ->defaultItems(1);
    }

    private function getNewRelationshipsRepeater(): Repeater
    {
        return Repeater::make('new_relationships')
            ->label('Add New Relationships')
            ->schema([
                Select::make('from_model')
                    ->label('From Model')
                    ->required()
                    ->options(function (callable $get) {
                        $options = [];
                        
                        // Add existing models
                        if ($this->selectedModule) {
                            $options = array_merge($options, $this->getExistingModels());
                        }
                        
                        // Add new models being created
                        $newTables = $get('../../new_tables') ?? [];
                        foreach ($newTables as $table) {
                            if (!empty($table['name'])) {
                                $options[$table['name']] = $table['name'];
                            }
                        }
                        
                        return $options;
                    })
                    ->live(),
                
                Select::make('to_model')
                    ->label('To Model')
                    ->required()
                    ->options(function (callable $get) {
                        $options = [];
                        
                        // Add existing models from current module
                        if ($this->selectedModule) {
                            $options = array_merge($options, $this->getExistingModels());
                        }
                        
                        // Add new models being created
                        $newTables = $get('../../new_tables') ?? [];
                        foreach ($newTables as $table) {
                            if (!empty($table['name'])) {
                                $options[$table['name']] = $table['name'];
                            }
                        }
                        
                        // Add models from other modules
                        $options = array_merge($options, $this->getExistingModules());
                        
                        return $options;
                    }),
                
                Select::make('type')
                    ->label('Relationship Type')
                    ->required()
                    ->options([
                        'belongsTo' => 'Belongs To (Many to One)',
                        'hasMany' => 'Has Many (One to Many)',
                        'hasOne' => 'Has One (One to One)',
                        'belongsToMany' => 'Belongs To Many (Many to Many)',
                    ])
                    ->live(),
                
                TextInput::make('foreign_key')
                    ->label('Foreign Key')
                    ->placeholder('Auto-generated if empty')
                    ->helperText('e.g., user_id, category_id'),
                
                TextInput::make('relationship_name')
                    ->label('Relationship Method Name')
                    ->placeholder('Auto-generated if empty')
                    ->helperText('Method name in the model'),
            ])
            ->itemLabel(fn (array $state): ?string => 
                ($state['from_model'] ?? 'Model') . ' → ' . ($state['to_model'] ?? 'Model') . 
                ' (' . ($state['type'] ?? 'relationship') . ')'
            )
            ->addActionLabel('Add Relationship')
            ->reorderableWithButtons()
            ->collapsible()
            ->defaultItems(0);
    }

    private function getExistingModels(): array
    {
        if (!$this->selectedModule) {
            return [];
        }
        
        $models = [];
        $modelsPath = base_path("Modules/{$this->selectedModule}/app/Models");
        
        if (File::exists($modelsPath)) {
            $files = File::files($modelsPath);
            
            foreach ($files as $file) {
                if ($file->getExtension() === 'php') {
                    $modelName = $file->getFilenameWithoutExtension();
                    $models[$modelName] = $modelName;
                }
            }
        }
        
        return $models;
    }

    private function getExistingModules(): array
    {
        // This would return models from other modules
        // For now, return empty array
        return [];
    }

    private function getTableNameFromModel(string $modelName): string
    {
        if (!$this->selectedModule) {
            return '';
        }

        try {
            $modelClass = "\\Modules\\{$this->selectedModule}\\app\\Models\\{$modelName}";
            if (class_exists($modelClass)) {
                $model = new $modelClass();
                return $model->getTable();
            }
        } catch (\Exception $e) {
            // Fallback to convention
        }

        // Fallback to naming convention
        $moduleLower = strtolower($this->selectedModule);
        return $moduleLower . '_' . Str::snake(Str::plural($modelName));
    }

    public function updateModule(): void
    {
        $data = $this->form->getState();

        try {
            $editorService = new ModuleEditorService($this->selectedModule, $data);
            $editorService->updateModule();

            Notification::make()
                ->title('Module Updated Successfully!')
                ->body("Added new tables and relationships to {$this->selectedModule} module.")
                ->success()
                ->persistent()
                ->send();

            // Clear form
            $this->clearForm();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Update Failed')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }

    public function clearForm(): void
    {
        $this->form->fill([
            'selected_module' => '',
            'existing_tables' => [],
            'new_tables' => [],
            'new_relationships' => [],
        ]);
        $this->selectedModule = null;
        $this->moduleData = null;
    }

    public function hasModuleSeeders(): bool
    {
        if (!$this->selectedModule) {
            return false;
        }

        $seedersPath = base_path("Modules/{$this->selectedModule}/database/seeders");
        if (!File::exists($seedersPath)) {
            return false;
        }

        $seederFiles = File::glob($seedersPath . '/*Seeder.php');
        return count($seederFiles) > 0;
    }

    public function hasModuleFactories(): bool
    {
        if (!$this->selectedModule) {
            return false;
        }

        $factoriesPath = base_path("Modules/{$this->selectedModule}/database/factories");
        if (!File::exists($factoriesPath)) {
            return false;
        }

        $factoryFiles = File::glob($factoriesPath . '/*Factory.php');
        return count($factoryFiles) > 0;
    }

    public function runModuleSeeders(): void
    {
        if (!$this->selectedModule) {
            return;
        }

        try {
            $seederService = new SeederExecutionService();
            $results = $seederService->executeModuleSeeders($this->selectedModule);

            $successCount = count(array_filter($results, fn($r) => $r['status'] === 'success'));
            $totalRecords = array_sum(array_column($results, 'records_created'));

            if ($successCount > 0) {
                Notification::make()
                    ->title('Seeders Executed Successfully!')
                    ->body("Executed {$successCount} seeders and created {$totalRecords} records for '{$this->selectedModule}' module.")
                    ->success()
                    ->send();
            } else {
                throw new \Exception('No seeders were executed successfully');
            }

        } catch (\Exception $e) {
            Notification::make()
                ->title('Seeder Execution Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function createTestData(): void
    {
        if (!$this->selectedModule) {
            return;
        }

        try {
            $seederService = new SeederExecutionService();
            $models = $seederService->getModelsWithFactories($this->selectedModule);
            $totalRecords = 0;

            foreach ($models as $modelName) {
                $result = $seederService->executeModelFactory($this->selectedModule, $modelName, 10);
                if ($result['status'] === 'success') {
                    $totalRecords += $result['records_created'];
                }
            }

            Notification::make()
                ->title('Test Data Created Successfully!')
                ->body("Created {$totalRecords} test records for '{$this->selectedModule}' module.")
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Test Data Creation Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}

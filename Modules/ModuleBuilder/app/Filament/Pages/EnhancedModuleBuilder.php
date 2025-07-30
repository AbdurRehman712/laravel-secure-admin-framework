<?php

namespace Modules\ModuleBuilder\app\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;

use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Models\Project;
use App\Services\AiModuleGenerator;
use App\Services\ExcelSchemaImporter;
use Filament\Forms\Components\FileUpload;
use Livewire\WithFileUploads;

class EnhancedModuleBuilder extends Page
{
    use WithFileUploads;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Enhanced Module Builder';
    protected static ?string $title = 'Enhanced Module Builder';
    protected static \UnitEnum|string|null $navigationGroup = 'Development Tools';
    protected string $view = 'modulebuilder::enhanced-module-builder';
    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage_module_builder') ?? false;
    }

    public ?array $data = [];
    public $showProjectSelection = false;
    public $availableProjectsForSelection = [];
    public $showExcelImport = false;
    public $excelFile = null;
    public $showCSVDownload = false;

    public function mount(): void
    {
        $this->form->fill([
            'module_name' => '',
            'description' => '',
            'models' => [
                [
                    'name' => '',
                    'table_name' => '',
                    'description' => '',
                    'fields' => [
                        ['name' => 'name', 'type' => 'string', 'required' => true],
                    ]
                ]
            ],
            'relationships' => [],
            'generate_factory' => true,
            'generate_seeder' => true,
            'generate_api' => false,
            'generate_tests' => false,
            'enable_global_search' => true,
            'enable_bulk_actions' => true,
            'enable_filters' => true,
            'enable_exports' => false,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->schema([
            TextInput::make('module_name')
                ->label('Module Name')
                ->required()
                ->placeholder('e.g., Blog, Shop, CRM')
                ->live(debounce: 500)
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    // Only auto-generate slug if it's empty or matches the previous auto-generated value
                    $currentSlug = $get('module_slug');
                    if (empty($currentSlug) || $currentSlug === Str::slug($get('module_name'))) {
                        $set('module_slug', Str::slug($state));
                    }
                })
                ->columnSpan(1),

            TextInput::make('module_slug')
                ->label('Module Slug')
                ->required()
                ->placeholder('Auto-generated from module name')
                ->helperText('Edit to customize the module slug')
                ->columnSpan(1),

            Textarea::make('description')
                ->label('Description')
                ->placeholder('Brief description of what this module does')
                ->rows(2)
                ->columnSpan('full'),

            $this->getModelsRepeater()
                ->columnSpan('full'),

            $this->getRelationshipsRepeater()
                ->columnSpan('full'),

            Toggle::make('generate_factory')
                ->label('Generate Model Factories')
                ->default(true)
                ->helperText('For testing and seeding'),

            Toggle::make('generate_seeder')
                ->label('Generate Database Seeders')
                ->default(true)
                ->helperText('Sample data for development'),

            Toggle::make('enable_global_search')
                ->label('Enable Global Search')
                ->default(true)
                ->helperText('Searchable in Filament global search'),

            Toggle::make('enable_bulk_actions')
                ->label('Enable Bulk Actions')
                ->default(true)
                ->helperText('Delete, export, etc.'),
        ])
        ->statePath('data');
    }



    private function getModelsRepeater(): Repeater
    {
        return Repeater::make('models')
            ->label('Models & Tables')
            ->columns(2)
            ->schema([
                TextInput::make('name')
                    ->label('Model Name')
                    ->required()
                    ->placeholder('e.g., Product, Category, Order')
                    ->live(debounce: 500)
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        // Only auto-generate table name if it's empty or if user hasn't manually edited it
                        $currentTableName = $get('table_name');
                        if (empty($currentTableName)) {
                            $set('table_name', Str::plural(Str::snake($state)));
                        }
                    })
                    ->columnSpan(1),

                TextInput::make('table_name')
                    ->label('Table Name')
                    ->required()
                    ->placeholder('Auto-generated from model name')
                    ->helperText('Edit to customize the table name')
                    ->columnSpan(1),

                Textarea::make('description')
                    ->label('Model Description')
                    ->placeholder('What this model represents')
                    ->rows(2)
                    ->columnSpan('full'),

                $this->getFieldsRepeater()
                    ->columnSpan('full'),
            ])
            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
            ->addActionLabel('Add Model')
            ->reorderableWithButtons()
            ->collapsible()
            ->defaultItems(1);
    }

    private function getFieldsRepeater(): Repeater
    {
        return Repeater::make('fields')
            ->label('Fields')
            ->schema([
                TextInput::make('name')
                    ->label('Field Name')
                    ->required()
                    ->placeholder('e.g., title, price, description'),

                Select::make('type')
                    ->label('Field Type')
                    ->required()
                    ->options([
                        'string' => 'String',
                        'slug' => 'Slug (Auto-generated)',
                        'text' => 'Text',
                        'integer' => 'Integer',
                        'decimal' => 'Decimal',
                        'boolean' => 'Boolean',
                        'date' => 'Date',
                        'datetime' => 'DateTime',
                        'timestamp' => 'Timestamp',
                        'json' => 'JSON',
                        'enum' => 'Enum',
                        'file' => 'File',
                        'image' => 'Image',
                        'rich_text' => 'Rich Text',
                        'email' => 'Email',
                        'url' => 'URL',
                        'password' => 'Password',
                    ])
                    ->live(),

                Select::make('required')
                    ->label('Required')
                    ->options([
                        true => 'Yes',
                        false => 'No',
                    ])
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
                    ->label('Enum Options')
                    ->visible(fn ($get) => $get('type') === 'enum')
                    ->placeholder("active\ninactive\npending")
                    ->rows(2),

                TextInput::make('validation')
                    ->label('Validation')
                    ->placeholder('e.g., min:3|max:255'),
            ])
            ->table([
                TableColumn::make('name'),
                TableColumn::make('type'),
                TableColumn::make('required'),
                TableColumn::make('length'),
                TableColumn::make('default'),
            ])
            ->addActionLabel('Add Field')
            ->reorderableWithButtons()
            ->defaultItems(1);
    }

    private function getRelationshipsRepeater(): Repeater
    {
        return Repeater::make('relationships')
            ->label('Model Relationships')
            ->schema([
                Select::make('from_model')
                    ->label('From Model')
                    ->required()
                    ->options(function (callable $get) {
                        $models = $get('../../models') ?? [];
                        return collect($models)->pluck('name', 'name')->toArray();
                    })
                    ->live(),

                Select::make('to_model')
                    ->label('To Model')
                    ->required()
                    ->options(function (callable $get) {
                        $models = $get('../../models') ?? [];
                        $existingModules = $this->getExistingModules();
                        $allModels = collect($models)->pluck('name', 'name')->toArray();
                        return array_merge($allModels, $existingModules);
                    }),

                Select::make('type')
                    ->label('Relationship Type')
                    ->required()
                    ->options([
                        'belongsTo' => 'belongsTo',
                        'hasMany' => 'hasMany',
                        'hasOne' => 'hasOne',
                        'belongsToMany' => 'belongsToMany',
                    ])
                    ->live(),

                TextInput::make('foreign_key')
                    ->label('Foreign Key')
                    ->placeholder('Auto-generated if empty'),

                TextInput::make('relationship_name')
                    ->placeholder('Method name'),

                TextInput::make('local_key')
                    ->label('Local Key')
                    ->placeholder('id (default)')
                    ->visible(fn ($get) => in_array($get('type'), ['hasMany', 'hasOne'])),

                TextInput::make('pivot_table')
                    ->label('Pivot Table')
                    ->visible(fn ($get) => $get('type') === 'belongsToMany')
                    ->placeholder('Auto-generated if empty'),
            ])
            ->table([
                TableColumn::make('from_model'),
                TableColumn::make('to_model'),
                TableColumn::make('type'),
                TableColumn::make('foreign_key'),
                TableColumn::make('relationship_name'),
            ])
            ->addActionLabel('Add Relationship')
            ->reorderableWithButtons();
    }



    private function getExistingModules(): array
    {
        $modules = [];
        $modulesPath = base_path('Modules');

        if (File::exists($modulesPath)) {
            $moduleDirs = File::directories($modulesPath);

            foreach ($moduleDirs as $moduleDir) {
                $moduleName = basename($moduleDir);
                $modelsPath = $moduleDir . '/app/Models';

                if (File::exists($modelsPath)) {
                    $modelFiles = File::glob($modelsPath . '/*.php');
                    foreach ($modelFiles as $modelFile) {
                        $modelName = basename($modelFile, '.php');
                        $modules["Modules\\{$moduleName}\\app\\Models\\{$modelName}"] = "{$moduleName}::{$modelName}";
                    }
                }
            }
        }

        return $modules;
    }

    public function generateModule(): void
    {
        $data = $this->form->getState();

        try {
            $moduleName = Str::studly($data['module_name']);

            // Enhanced generation logic will go here
            $this->generateEnhancedModule($data);

            Notification::make()
                ->title('Enhanced Module Generated Successfully!')
                ->body("Module '{$moduleName}' has been created with all advanced features.")
                ->success()
                ->persistent()
                ->send();

            // Clear form
            $this->clearForm();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Generation Failed')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    private function generateEnhancedModule(array $data): void
    {
        $moduleName = Str::studly($data['module_name']);

        // Use the Enhanced Module Generator service
        $generator = new \Modules\ModuleBuilder\app\Services\EnhancedModuleGenerator($moduleName, $data);
        $generator->generate();

        // Clear Filament cache
        \Illuminate\Support\Facades\Artisan::call('filament:clear-cached-components');
    }

    public function fillDemoData(): void
    {
        $this->form->fill([
            'module_name' => 'Shop',
            'module_description' => 'Complete e-commerce shop with products, categories, and orders',
            'models' => [
                [
                    'name' => 'Category',
                    'table_name' => 'shop_categories',
                    'description' => 'Product categories for organization',
                    'fields' => [
                        ['name' => 'name', 'type' => 'string', 'required' => true, 'length' => 255],
                        ['name' => 'slug', 'type' => 'slug', 'required' => true, 'length' => 255],
                        ['name' => 'description', 'type' => 'text', 'required' => false],
                        ['name' => 'image', 'type' => 'image', 'required' => false],
                        ['name' => 'active', 'type' => 'boolean', 'required' => false],
                        ['name' => 'sort_order', 'type' => 'integer', 'required' => false],
                    ]
                ],
                [
                    'name' => 'Product',
                    'table_name' => 'shop_products',
                    'description' => 'Shop products with full e-commerce features',
                    'fields' => [
                        ['name' => 'name', 'type' => 'string', 'required' => true, 'length' => 255],
                        ['name' => 'slug', 'type' => 'slug', 'required' => true, 'length' => 255],
                        ['name' => 'description', 'type' => 'rich_text', 'required' => false],
                        ['name' => 'short_description', 'type' => 'text', 'required' => false],
                        ['name' => 'sku', 'type' => 'string', 'required' => true, 'length' => 100],
                        ['name' => 'price', 'type' => 'decimal', 'required' => true],
                        ['name' => 'sale_price', 'type' => 'decimal', 'required' => false],
                        ['name' => 'stock_quantity', 'type' => 'integer', 'required' => false],
                        ['name' => 'weight', 'type' => 'decimal', 'required' => false],
                        ['name' => 'dimensions', 'type' => 'string', 'required' => false, 'length' => 100],
                        ['name' => 'featured_image', 'type' => 'image', 'required' => false],
                        ['name' => 'gallery', 'type' => 'file', 'required' => false],
                        ['name' => 'status', 'type' => 'enum', 'required' => true, 'enum_options' => "draft\npublished\narchived"],
                        ['name' => 'featured', 'type' => 'boolean', 'required' => false],
                        ['name' => 'meta_title', 'type' => 'string', 'required' => false, 'length' => 255],
                        ['name' => 'meta_description', 'type' => 'text', 'required' => false],
                    ]
                ],
                [
                    'name' => 'Order',
                    'table_name' => 'shop_orders',
                    'description' => 'Customer orders and order management',
                    'fields' => [
                        ['name' => 'order_number', 'type' => 'string', 'required' => true, 'length' => 50],
                        ['name' => 'customer_name', 'type' => 'string', 'required' => true, 'length' => 255],
                        ['name' => 'customer_email', 'type' => 'email', 'required' => true, 'length' => 255],
                        ['name' => 'customer_phone', 'type' => 'string', 'required' => false, 'length' => 20],
                        ['name' => 'billing_address', 'type' => 'json', 'required' => true],
                        ['name' => 'shipping_address', 'type' => 'json', 'required' => false],
                        ['name' => 'subtotal', 'type' => 'decimal', 'required' => true],
                        ['name' => 'tax_amount', 'type' => 'decimal', 'required' => false],
                        ['name' => 'shipping_amount', 'type' => 'decimal', 'required' => false],
                        ['name' => 'total_amount', 'type' => 'decimal', 'required' => true],
                        ['name' => 'status', 'type' => 'enum', 'required' => true, 'enum_options' => "pending\nprocessing\nshipped\ndelivered\ncancelled\nrefunded"],
                        ['name' => 'payment_status', 'type' => 'enum', 'required' => true, 'enum_options' => "pending\npaid\nfailed\nrefunded"],
                        ['name' => 'payment_method', 'type' => 'string', 'required' => false, 'length' => 50],
                        ['name' => 'notes', 'type' => 'text', 'required' => false],
                        ['name' => 'shipped_at', 'type' => 'datetime', 'required' => false],
                    ]
                ]
            ],
            'relationships' => [
                [
                    'from_model' => 'Product',
                    'to_model' => 'Category',
                    'type' => 'belongsTo',
                    'foreign_key' => 'category_id',
                    'relationship_name' => 'category'
                ],
                [
                    'from_model' => 'Category',
                    'to_model' => 'Product',
                    'type' => 'hasMany',
                    'foreign_key' => 'category_id',
                    'relationship_name' => 'products'
                ]
            ],
            'generate_factory' => true,
            'generate_seeder' => true,
            'enable_global_search' => true,
            'enable_bulk_actions' => true,
        ]);

        Notification::make()
            ->title('Demo Data Filled!')
            ->body('Shop module configuration has been loaded with products, categories, and orders.')
            ->success()
            ->send();
    }

    public function hasProjectsWithSchemas(): bool
    {
        $projects = Project::all();

        foreach ($projects as $project) {
            try {
                $generator = new AiModuleGenerator($project);
                $reflection = new \ReflectionClass($generator);
                $method = $reflection->getMethod('extractDatabaseSchemas');
                $method->setAccessible(true);
                $schemas = $method->invoke($generator);

                if (!empty($schemas)) {
                    return true;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return false;
    }

    public function getAvailableProjects(): array
    {
        $availableProjects = [];
        $projects = Project::all();

        foreach ($projects as $project) {
            try {
                $generator = new AiModuleGenerator($project);
                $reflection = new \ReflectionClass($generator);
                $method = $reflection->getMethod('extractDatabaseSchemas');
                $method->setAccessible(true);
                $schemas = $method->invoke($generator);

                if (!empty($schemas)) {
                    $tableCount = count($schemas[0]['tables'] ?? []);
                    $availableProjects[$project->id] = "{$project->name} ({$tableCount} tables)";
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $availableProjects;
    }

    public function selectProjectAndFill(): void
    {
        $availableProjects = $this->getAvailableProjects();

        if (empty($availableProjects)) {
            Notification::make()
                ->title('No Projects Available')
                ->body('No projects with AI-generated database schemas found.')
                ->warning()
                ->send();
            return;
        }

        // If only one project, auto-fill from it
        if (count($availableProjects) === 1) {
            $projectId = array_key_first($availableProjects);
            $this->fillFromProject($projectId);
            return;
        }

        // Show project selection modal
        $this->availableProjectsForSelection = $availableProjects;
        $this->showProjectSelection = true;
    }

    public function fillFromSelectedProject(int $projectId): void
    {
        $this->fillFromProject($projectId);
        $this->showProjectSelection = false;
        $this->availableProjectsForSelection = [];
    }

    public function closeProjectSelection(): void
    {
        $this->showProjectSelection = false;
        $this->availableProjectsForSelection = [];
    }

    public function fillFromProject(int $projectId = null): void
    {
        try {
            // If no project ID provided, get the first available project
            $project = $projectId ? Project::find($projectId) : Project::first();

            if (!$project) {
                Notification::make()
                    ->title('No Project Found')
                    ->body('Please create a project first with AI-generated database schemas.')
                    ->warning()
                    ->send();
                return;
            }

            $generator = new AiModuleGenerator($project);
            $reflection = new \ReflectionClass($generator);

            // Extract database schemas
            $method = $reflection->getMethod('extractDatabaseSchemas');
            $method->setAccessible(true);
            $schemas = $method->invoke($generator);

            if (empty($schemas)) {
                Notification::make()
                    ->title('No Database Schemas Found')
                    ->body('The selected project does not have any AI-generated database schemas.')
                    ->warning()
                    ->send();
                return;
            }

            // Prepare module data
            $method2 = $reflection->getMethod('prepareModuleDataForMultipleTables');
            $method2->setAccessible(true);
            $moduleData = $method2->invoke($generator, $schemas[0]['tables'], []);

            // Convert to form format
            $formData = $this->convertModuleDataToFormFormat($moduleData, $project);

            $this->form->fill($formData);

            Notification::make()
                ->title('Project Data Loaded!')
                ->body("Module configuration has been loaded from '{$project->name}' project with " . count($moduleData['models']) . " models.")
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error Loading Project Data')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    private function convertModuleDataToFormFormat(array $moduleData, Project $project): array
    {
        $models = [];
        $relationships = [];

        foreach ($moduleData['models'] as $model) {
            $models[] = [
                'name' => $model['name'],
                'table_name' => $model['table_name'],
                'description' => "Generated from {$project->name} project",
                'fields' => $model['fields']
            ];

            // Convert relationships
            foreach ($model['relationships'] as $relationship) {
                $relationships[] = [
                    'from_model' => $model['name'],
                    'to_model' => $relationship['related_model'],
                    'type' => $relationship['type'],
                    'foreign_key' => $relationship['foreign_key'],
                    'relationship_name' => Str::camel($relationship['related_model'])
                ];
            }
        }

        return [
            'module_name' => $moduleData['module_name'],
            'module_description' => $moduleData['description'],
            'models' => $models,
            'relationships' => $relationships,
            'generate_factory' => true,
            'generate_seeder' => true,
            'enable_global_search' => true,
            'enable_bulk_actions' => true,
        ];
    }

    public function clearForm(): void
    {
        $this->form->fill([
            'module_name' => '',
            'module_description' => '',
            'models' => [],
            'relationships' => [],
            'generate_factory' => true,
            'generate_seeder' => true,
            'enable_global_search' => true,
            'enable_bulk_actions' => true,
        ]);
    }

    public function downloadExcelTemplate()
    {
        $importer = new ExcelSchemaImporter();
        $filename = $importer->generateSampleExcel();
        $filepath = storage_path('app/public/' . $filename);

        if (!file_exists($filepath)) {
            Notification::make()
                ->title('Error')
                ->body('Template file could not be generated.')
                ->danger()
                ->send();
            return;
        }

        // Return file download response
        return response()->download($filepath, 'database_schema_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function downloadCSVTemplates()
    {
        $importer = new ExcelSchemaImporter();
        $files = $importer->generateSampleCSV();

        // Create a ZIP file containing all CSV templates
        $zipFilename = 'database_schema_csv_templates_' . date('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/public/demo/' . $zipFilename);

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
            foreach ($files as $type => $relativePath) {
                $fullPath = storage_path('app/public/' . $relativePath);
                if (file_exists($fullPath)) {
                    $zip->addFile($fullPath, basename($fullPath));
                }
            }
            $zip->close();

            if (file_exists($zipPath)) {
                return response()->download($zipPath, 'database_schema_csv_templates.zip', [
                    'Content-Type' => 'application/zip',
                ]);
            }
        }

        Notification::make()
            ->title('Error')
            ->body('CSV templates could not be generated.')
            ->danger()
            ->send();
    }

    public function loadFromERD()
    {
        // Get available ERD projects
        $erdProjects = \Modules\ERDDesigner\app\Models\ErdProject::where('created_by', auth()->id())
            ->orderBy('updated_at', 'desc')
            ->get();

        if ($erdProjects->isEmpty()) {
            Notification::make()
                ->title('No ERD Projects Found')
                ->body('Please create an ERD project first using the ERD Designer.')
                ->warning()
                ->send();
            return;
        }

        // For now, load the most recent project
        $erdProject = $erdProjects->first();

        $this->importFromERD($erdProject);
    }

    public function importFromERD(\Modules\ERDDesigner\app\Models\ErdProject $erdProject)
    {
        try {
            // Load ERD project data
            $tables = $erdProject->tables()->with(['fields' => function($query) {
                $query->orderBy('order');
            }])->get();

            $relationships = $erdProject->relationships()->with([
                'sourceTable', 'targetTable', 'sourceField', 'targetField'
            ])->get();

            // Convert ERD data to Module Builder format
            $models = [];
            $moduleRelationships = [];

            foreach ($tables as $table) {
                $formattedFields = [];

                foreach ($table->fields as $field) {
                    // Skip auto-generated fields
                    if (in_array($field->name, ['id', 'created_at', 'updated_at'])) {
                        continue;
                    }

                    $formattedFields[] = [
                        'name' => $field->name,
                        'type' => $this->mapSqlToLaravelType($field->type),
                        'required' => !$field->is_nullable,
                        'length' => $field->length,
                        'default' => $field->default_value,
                        'display_name' => $field->display_name ?: ucfirst($field->name),
                        'form_type' => $field->form_type,
                        'validation_rules' => $field->validation_rules,
                    ];
                }

                $models[] = [
                    'name' => Str::studly(Str::singular($table->name)),
                    'table_name' => $table->name,
                    'description' => $table->description ?: "Manage {$table->display_name}",
                    'fields' => $formattedFields,
                    'seeder_data' => []
                ];
            }

            // Convert relationships
            foreach ($relationships as $relationship) {
                if ($relationship->sourceTable && $relationship->targetTable) {
                    $moduleRelationships[] = [
                        'from_model' => Str::studly(Str::singular($relationship->sourceTable->name)),
                        'to_model' => Str::studly(Str::singular($relationship->targetTable->name)),
                        'type' => $this->mapErdToLaravelRelationType($relationship->type),
                        'foreign_key' => $relationship->sourceField ? $relationship->sourceField->name : null,
                        'relationship_name' => Str::camel(Str::singular($relationship->targetTable->name))
                    ];
                }
            }

            // Fill the form with imported data
            $this->form->fill([
                'module_name' => Str::studly($erdProject->name),
                'description' => $erdProject->description ?: 'Module imported from ERD Designer',
                'models' => $models,
                'relationships' => $moduleRelationships
            ]);

            Notification::make()
                ->title('ERD Import Successful')
                ->body("Imported {$erdProject->name} with " . count($models) . ' tables and ' . count($moduleRelationships) . ' relationships.')
                ->success()
                ->duration(5000)
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('ERD Import Failed')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    private function mapSqlToLaravelType(string $sqlType): string
    {
        $typeMap = [
            'varchar' => 'string',
            'char' => 'string',
            'text' => 'text',
            'mediumtext' => 'text',
            'longtext' => 'text',
            'tinytext' => 'text',
            'int' => 'integer',
            'bigint' => 'bigInteger',
            'tinyint' => 'boolean',
            'smallint' => 'integer',
            'mediumint' => 'integer',
            'decimal' => 'decimal',
            'float' => 'float',
            'double' => 'double',
            'date' => 'date',
            'datetime' => 'datetime',
            'timestamp' => 'timestamp',
            'time' => 'time',
            'year' => 'integer',
            'enum' => 'enum',
            'set' => 'string',
            'json' => 'json',
            'boolean' => 'boolean'
        ];

        return $typeMap[$sqlType] ?? 'string';
    }

    private function mapErdToLaravelRelationType(string $erdType): string
    {
        $typeMap = [
            'one_to_one' => 'hasOne',
            'one_to_many' => 'hasMany',
            'many_to_one' => 'belongsTo',
            'many_to_many' => 'belongsToMany'
        ];

        return $typeMap[$erdType] ?? 'belongsTo';
    }

    public function showCSVDownloadModal(): void
    {
        $this->showCSVDownload = true;
    }

    public function hideCSVDownloadModal(): void
    {
        $this->showCSVDownload = false;
    }

    public function showExcelImportModal(): void
    {
        $this->showExcelImport = true;
    }

    public function hideExcelImportModal(): void
    {
        $this->showExcelImport = false;
        $this->excelFile = null;
    }

    public function importFromExcel(): void
    {
        if (!$this->excelFile) {
            Notification::make()
                ->title('No File Selected')
                ->body('Please select an Excel file to import.')
                ->warning()
                ->send();
            return;
        }

        try {
            $importer = new ExcelSchemaImporter();

            // Handle Livewire file upload
            if (is_object($this->excelFile) && method_exists($this->excelFile, 'getRealPath')) {
                // This is a Livewire UploadedFile object
                $filePath = $this->excelFile->getRealPath();
            } else {
                // Handle string paths (for demo files or direct paths)
                $possiblePaths = [
                    storage_path('app/public/' . $this->excelFile),
                    storage_path('app/livewire-tmp/' . $this->excelFile),
                    storage_path('app/public/demo/' . $this->excelFile),
                    $this->excelFile // Direct path
                ];

                $filePath = null;
                foreach ($possiblePaths as $path) {
                    if (file_exists($path)) {
                        $filePath = $path;
                        break;
                    }
                }
            }

            if (!$filePath || !file_exists($filePath)) {
                $debugInfo = [
                    'excelFile_type' => gettype($this->excelFile),
                    'excelFile_value' => is_object($this->excelFile) ? get_class($this->excelFile) : $this->excelFile,
                    'checked_paths' => isset($possiblePaths) ? $possiblePaths : ['N/A'],
                    'final_path' => $filePath ?? 'null'
                ];

                throw new \Exception('File not found. Debug info: ' . json_encode($debugInfo, JSON_PRETTY_PRINT));
            }

            // Use the new parseFile method that handles both Excel and CSV
            $data = $importer->parseFile($filePath);

            if (empty($data['tables']) || empty($data['fields'])) {
                throw new \Exception('Invalid Excel format. Please use the provided template.');
            }

            // Convert parsed data to form format
            $models = [];
            $relationships = [];

            foreach ($data['tables'] as $table) {
                $tableFields = array_filter($data['fields'], function($field) use ($table) {
                    return $field['table_name'] === $table['name'];
                });

                $formattedFields = [];
                foreach ($tableFields as $field) {
                    // Skip timestamp fields that will be auto-added by Laravel
                    if (in_array($field['name'], ['id', 'created_at', 'updated_at'])) {
                        continue;
                    }

                    // Process relationships first
                    if (!empty($field['relationship']) && !empty($field['related_table'])) {
                        $relationships[] = [
                            'from_model' => Str::studly(Str::singular($table['name'])),
                            'to_model' => Str::studly(Str::singular($field['related_table'])),
                            'type' => $field['relationship'],
                            'foreign_key' => $field['name'],
                            'relationship_name' => Str::camel(Str::singular($field['related_table']))
                        ];

                        // Skip adding foreign key fields to the regular fields array
                        // They will be handled by the foreign key generation
                        if ($field['relationship'] === 'belongsTo' && str_ends_with($field['name'], '_id')) {
                            continue;
                        }
                    }

                    $formattedFields[] = [
                        'name' => $field['name'],
                        'type' => $this->mapFieldType($field['type']),
                        'required' => !$field['nullable'], // Convert nullable to required (opposite)
                        'length' => $field['length'] ?: null,
                        'default' => $field['default'] ?: null,
                        'display_name' => $field['display_name'],
                        'form_type' => $field['form_type'],
                        'validation_rules' => $field['validation'] ?: null,
                    ];
                }

                $models[] = [
                    'name' => Str::studly(Str::singular($table['name'])),
                    'table_name' => $table['name'],
                    'description' => $table['description'] ?: "Manage {$table['display_name']}",
                    'fields' => $formattedFields,
                    'seeder_data' => $data['seeder_data'][$table['name']] ?? []
                ];
            }

            // Fill the form with imported data
            $this->form->fill([
                'module_name' => 'ImportedModule',
                'description' => 'Module imported from Excel schema',
                'models' => $models,
                'relationships' => $relationships
            ]);

            $this->hideExcelImportModal();

            Notification::make()
                ->title('Excel Import Successful')
                ->body(count($models) . ' tables imported successfully. Review and customize as needed.')
                ->success()
                ->duration(5000)
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Import Failed')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    private function mapFieldType(string $excelType): string
    {
        return match (strtolower($excelType)) {
            'varchar' => 'string',
            'text' => 'text',
            'integer', 'int' => 'integer',
            'bigint' => 'integer', // Changed from 'bigInteger' to 'integer'
            'decimal', 'float', 'double' => 'decimal',
            'boolean', 'bool' => 'boolean',
            'timestamp' => 'timestamp',
            'datetime' => 'datetime',
            'date' => 'date',
            'json' => 'json',
            'enum' => 'enum',
            'file' => 'file',
            'image' => 'image',
            'email' => 'email',
            'url' => 'url',
            'password' => 'password',
            'rich_text' => 'rich_text',
            default => 'string'
        };
    }
}

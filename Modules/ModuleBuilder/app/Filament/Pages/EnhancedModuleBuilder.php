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

class EnhancedModuleBuilder extends Page
{

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
                ->live()
                ->afterStateUpdated(fn ($state, callable $set) =>
                    $set('module_slug', Str::slug($state))
                )
                ->columnSpan(1),

            TextInput::make('module_slug')
                ->label('Module Slug')
                ->disabled()
                ->dehydrated()
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
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('table_name', Str::plural(Str::snake($state)));
                    })
                    ->columnSpan(1),

                TextInput::make('table_name')
                    ->label('Table Name')
                    ->required()
                    ->disabled()
                    ->dehydrated()
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
                TableColumn::make('name')
                    ->markAsRequired(),
                TableColumn::make('type')
                    ->markAsRequired(),
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
                TableColumn::make('from_model')
                    ->markAsRequired(),
                TableColumn::make('to_model')
                    ->markAsRequired(),
                TableColumn::make('type')
                    ->markAsRequired(),
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
                        ['name' => 'slug', 'type' => 'string', 'required' => true, 'length' => 255],
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
                        ['name' => 'slug', 'type' => 'string', 'required' => true, 'length' => 255],
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
}

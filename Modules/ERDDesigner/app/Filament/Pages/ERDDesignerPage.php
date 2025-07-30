<?php

namespace Modules\ERDDesigner\app\Filament\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Modules\ERDDesigner\app\Models\ErdProject;
use Modules\ERDDesigner\app\Models\ErdTable;
use Modules\ERDDesigner\app\Models\ErdField;
use Modules\ERDDesigner\app\Services\SqlImportService;
use Modules\ERDDesigner\app\Services\SqlExportService;

class ERDDesignerPage extends Page
{
    protected string $view = 'erddesigner::simple-erd-designer';

    protected static ?string $navigationLabel = 'ERD Designer';

    protected static ?string $title = 'Database ERD Designer';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'erd-designer-page';

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-squares-2x2';
    }

    public ?ErdProject $currentProject = null;
    public array $projects = [];
    public bool $showCreateModal = false;
    public bool $showImportModal = false;
    public string $sqlImportContent = '';
    public string $projectName = '';
    public string $projectDescription = '';
    public string $databaseName = '';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('access_erd_designer') ?? false;
    }

    public function mount(): void
    {
        $this->loadProjects();

        // If no projects exist, create a demo project automatically
        if (empty($this->projects)) {
            $this->createDemoProject();
            $this->loadProjects();
        }
    }

    public function loadProjects(): void
    {
        $this->projects = ErdProject::where('created_by', auth()->id())
            ->orderBy('updated_at', 'desc')
            ->get()
            ->toArray();
    }

    public function createNewProject(): void
    {
        if (!auth()->user()->can('create_erd_projects')) {
            Notification::make()
                ->title('Access Denied')
                ->body('You do not have permission to create ERD projects.')
                ->danger()
                ->send();
            return;
        }

        $this->currentProject = ErdProject::create([
            'name' => 'New ERD Project',
            'description' => 'A new database design project',
            'database_name' => 'new_database',
            'database_type' => 'mysql',
            'canvas_data' => [
                'zoom' => 1.0,
                'offset_x' => 0,
                'offset_y' => 0,
            ],
            'settings' => [
                'auto_save' => true,
                'show_grid' => true,
                'snap_to_grid' => true,
            ],
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $this->loadProjects();

        Notification::make()
            ->title('Project Created')
            ->body('New ERD project has been created successfully!')
            ->success()
            ->send();

        // Redirect to the advanced ERD designer
        $this->redirect('/admin/advanced-erd-designer?project=' . $this->currentProject->id);
    }

    public function importSQL(): void
    {
        if (!auth()->user()->can('import_sql_to_erd')) {
            Notification::make()
                ->title('Access Denied')
                ->body('You do not have permission to import SQL.')
                ->danger()
                ->send();
            return;
        }

        // Reset form data
        $this->projectName = '';
        $this->projectDescription = '';
        $this->databaseName = '';
        $this->sqlImportContent = '';
        $this->showImportModal = true;
    }

    public function processImportSQL(): void
    {
        // Validate inputs
        if (empty($this->projectName)) {
            Notification::make()
                ->title('Validation Error')
                ->body('Project name is required.')
                ->danger()
                ->send();
            return;
        }

        if (empty($this->sqlImportContent)) {
            Notification::make()
                ->title('Validation Error')
                ->body('SQL content is required.')
                ->danger()
                ->send();
            return;
        }

        try {
            // Create new project
            $project = ErdProject::create([
                'name' => $this->projectName,
                'description' => $this->projectDescription ?: 'Project created from SQL import',
                'database_name' => $this->databaseName ?: Str::snake($this->projectName) . '_db',
                'database_type' => 'mysql',
                'canvas_data' => [
                    'zoom' => 1.0,
                    'offset_x' => 0,
                    'offset_y' => 0,
                ],
                'settings' => [
                    'auto_save' => true,
                    'show_grid' => true,
                    'snap_to_grid' => true,
                ],
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            // Import SQL into the new project
            $importService = new SqlImportService();
            $importService->importToProject($project, $this->sqlImportContent);

            $this->loadProjects();
            $this->showImportModal = false;

            Notification::make()
                ->title('Project Created Successfully')
                ->body("Project '{$this->projectName}' has been created and SQL imported successfully!")
                ->success()
                ->send();

            // Redirect to the advanced ERD designer to show the imported tables
            $this->redirect('/admin/advanced-erd-designer?project=' . $project->id);

        } catch (\Exception $e) {
            Notification::make()
                ->title('Import Failed')
                ->body('Error creating project from SQL: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function exportSql(): string
    {
        if (!auth()->user()->can('export_sql_from_erd')) {
            Notification::make()
                ->title('Access Denied')
                ->body('You do not have permission to export SQL.')
                ->danger()
                ->send();
            return '';
        }

        if (!$this->currentProject) {
            Notification::make()
                ->title('No Project Selected')
                ->body('Please select a project first.')
                ->warning()
                ->send();
            return '';
        }

        try {
            $exportService = new SqlExportService();
            return $exportService->exportProject($this->currentProject);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Export Failed')
                ->body('Error exporting SQL: ' . $e->getMessage())
                ->danger()
                ->send();
            return '';
        }
    }

    public function loadDemo(): void
    {
        if (!auth()->user()->can('create_erd_projects')) {
            Notification::make()
                ->title('Access Denied')
                ->body('You do not have permission to create ERD projects.')
                ->danger()
                ->send();
            return;
        }

        // Check if demo project already exists
        $demoProject = ErdProject::where('name', 'E-commerce Database')
            ->where('created_by', auth()->id())
            ->first();

        if ($demoProject) {
            Notification::make()
                ->title('Demo Already Loaded')
                ->body('Demo project already exists. Opening existing demo project.')
                ->warning()
                ->send();

            $this->currentProject = $demoProject;
        } else {
            // Create demo project by running the seeder for this user
            $this->createDemoProject();
        }

        $this->loadProjects();

        // Redirect to the advanced ERD designer
        $this->redirect('/admin/advanced-erd-designer?project=' . $this->currentProject->id);
    }

    private function createDemoProject(): void
    {
        // Create demo ERD project
        $this->currentProject = ErdProject::create([
            'name' => 'E-commerce Database',
            'description' => 'Complete e-commerce database schema with products, orders, and users',
            'database_name' => 'ecommerce_db',
            'database_type' => 'mysql',
            'canvas_data' => [
                'zoom' => 1.0,
                'offset_x' => 0,
                'offset_y' => 0,
            ],
            'settings' => [
                'auto_save' => true,
                'show_grid' => true,
                'snap_to_grid' => true,
            ],
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $this->createDemoTables();

        Notification::make()
            ->title('Demo Project Created')
            ->body('Demo e-commerce database project has been created with sample tables!')
            ->success()
            ->send();
    }

    private function createDemoTables(): void
    {
        if (!$this->currentProject) return;

        // Create Users table
        $usersTable = ErdTable::create([
            'project_id' => $this->currentProject->id,
            'name' => 'users',
            'display_name' => 'Users',
            'description' => 'System users and customers',
            'position_x' => 50,
            'position_y' => 50,
            'width' => 280,
            'height' => 250,
            'color' => '#3b82f6',
        ]);

        $this->createFieldsForTable($usersTable, [
            ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_auto_increment' => true, 'is_nullable' => false],
            ['name' => 'name', 'type' => 'varchar', 'length' => 255, 'is_nullable' => false],
            ['name' => 'email', 'type' => 'varchar', 'length' => 255, 'is_nullable' => false],
            ['name' => 'created_at', 'type' => 'timestamp', 'is_nullable' => true],
            ['name' => 'updated_at', 'type' => 'timestamp', 'is_nullable' => true],
        ]);

        // Create Categories table
        $categoriesTable = ErdTable::create([
            'project_id' => $this->currentProject->id,
            'name' => 'categories',
            'display_name' => 'Categories',
            'description' => 'Product categories',
            'position_x' => 50,
            'position_y' => 350,
            'width' => 280,
            'height' => 250,
            'color' => '#10b981',
        ]);

        $this->createFieldsForTable($categoriesTable, [
            ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_auto_increment' => true, 'is_nullable' => false],
            ['name' => 'name', 'type' => 'varchar', 'length' => 255, 'is_nullable' => false],
            ['name' => 'slug', 'type' => 'varchar', 'length' => 255, 'is_nullable' => false],
            ['name' => 'description', 'type' => 'text', 'is_nullable' => true],
            ['name' => 'image', 'type' => 'varchar', 'length' => 255, 'is_nullable' => true],
            ['name' => 'is_active', 'type' => 'tinyint', 'length' => 1, 'is_nullable' => false],
            ['name' => 'created_at', 'type' => 'timestamp', 'is_nullable' => true],
            ['name' => 'updated_at', 'type' => 'timestamp', 'is_nullable' => true],
        ]);

        // Create Products table
        $productsTable = ErdTable::create([
            'project_id' => $this->currentProject->id,
            'name' => 'products',
            'display_name' => 'Products',
            'description' => 'Product catalog',
            'position_x' => 380,
            'position_y' => 350,
            'width' => 280,
            'height' => 250,
            'color' => '#f59e0b',
        ]);

        $this->createFieldsForTable($productsTable, [
            ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_auto_increment' => true, 'is_nullable' => false],
            ['name' => 'category_id', 'type' => 'bigint', 'is_foreign_key' => true, 'is_nullable' => false],
            ['name' => 'name', 'type' => 'varchar', 'length' => 255, 'is_nullable' => false],
            ['name' => 'created_at', 'type' => 'timestamp', 'is_nullable' => true],
            ['name' => 'updated_at', 'type' => 'timestamp', 'is_nullable' => true],
        ]);

        // Create Orders table
        $ordersTable = ErdTable::create([
            'project_id' => $this->currentProject->id,
            'name' => 'orders',
            'display_name' => 'Orders',
            'description' => 'Customer orders',
            'position_x' => 380,
            'position_y' => 50,
            'width' => 280,
            'height' => 250,
            'color' => '#8b5cf6',
        ]);

        $this->createFieldsForTable($ordersTable, [
            ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_auto_increment' => true, 'is_nullable' => false],
            ['name' => 'user_id', 'type' => 'bigint', 'is_foreign_key' => true, 'is_nullable' => false],
            ['name' => 'order_number', 'type' => 'varchar', 'length' => 100, 'is_nullable' => false],
            ['name' => 'status', 'type' => 'enum', 'is_nullable' => false],
            ['name' => 'total_amount', 'type' => 'decimal', 'is_nullable' => false],
            ['name' => 'created_at', 'type' => 'timestamp', 'is_nullable' => true],
            ['name' => 'updated_at', 'type' => 'timestamp', 'is_nullable' => true],
        ]);

        // Create Order Items table
        $orderItemsTable = ErdTable::create([
            'project_id' => $this->currentProject->id,
            'name' => 'order_items',
            'display_name' => 'Order Items',
            'description' => 'Items in each order',
            'position_x' => 710,
            'position_y' => 200,
            'width' => 280,
            'height' => 250,
            'color' => '#ef4444',
        ]);

        $this->createFieldsForTable($orderItemsTable, [
            ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_auto_increment' => true, 'is_nullable' => false],
            ['name' => 'order_id', 'type' => 'bigint', 'is_foreign_key' => true, 'is_nullable' => false],
            ['name' => 'product_id', 'type' => 'bigint', 'is_foreign_key' => true, 'is_nullable' => false],
            ['name' => 'quantity', 'type' => 'int', 'is_nullable' => false],
            ['name' => 'unit_price', 'type' => 'decimal', 'is_nullable' => false],
            ['name' => 'total_price', 'type' => 'decimal', 'is_nullable' => false],
            ['name' => 'created_at', 'type' => 'timestamp', 'is_nullable' => true],
            ['name' => 'updated_at', 'type' => 'timestamp', 'is_nullable' => true],
        ]);
    }

    private function createFieldsForTable(ErdTable $table, array $fields): void
    {
        foreach ($fields as $index => $fieldData) {
            ErdField::create(array_merge($fieldData, [
                'table_id' => $table->id,
                'order' => $index,
                'form_type' => $this->getFormType($fieldData['type']),
            ]));
        }
    }

    private function getFormType(string $type): string
    {
        $typeMap = [
            'varchar' => 'text',
            'text' => 'textarea',
            'bigint' => 'number',
            'int' => 'number',
            'timestamp' => 'datetime',
        ];

        return $typeMap[$type] ?? 'text';
    }
}

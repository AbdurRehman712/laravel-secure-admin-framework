<?php

namespace Modules\ERDDesigner\app\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Livewire\Attributes\On;
use Modules\ERDDesigner\app\Models\ErdProject;
use Modules\ERDDesigner\app\Models\ErdTable;
use Modules\ERDDesigner\app\Models\ErdField;
use Modules\ERDDesigner\app\Models\ErdRelationship;
use Modules\ERDDesigner\app\Services\SqlImportService;
use Modules\ERDDesigner\app\Services\SqlExportService;

class ERDDesigner extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationLabel = 'ERD Designer (Legacy)';
    protected static ?string $title = 'Database ERD Designer (Legacy)';
    protected string $view = 'erddesigner::erd-designer';
    protected static ?int $navigationSort = 3;
    protected static bool $shouldRegisterNavigation = false; // Hide this legacy version

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-squares-2x2';
    }

    public ?ErdProject $currentProject = null;
    public array $canvasData = [];
    public array $tables = [];
    public array $relationships = [];
    public bool $showProjectModal = false;
    public bool $showTableModal = false;
    public bool $showImportModal = false;
    public bool $showExportModal = false;
    public ?ErdTable $selectedTable = null;
    public array $tableForm = [];
    public array $fieldForms = [];

    // Canvas settings
    public float $canvasZoom = 1.0;
    public int $canvasOffsetX = 0;
    public int $canvasOffsetY = 0;
    public bool $isDragging = false;
    public bool $isConnecting = false;
    public ?int $connectionSourceTable = null;

    public function mount(): void
    {
        $this->loadProjects();
        $this->initializeCanvas();
    }

    public function loadProjects(): void
    {
        // Load user's projects or create a default one
        $this->currentProject = ErdProject::where('created_by', auth()->id())
            ->latest()
            ->first();

        if (!$this->currentProject) {
            $this->createNewProject();
        } else {
            $this->loadProjectData();
        }
    }

    public function createNewProject(): void
    {
        $this->currentProject = ErdProject::create([
            'name' => 'New ERD Project',
            'description' => 'Database design project',
            'database_type' => 'mysql',
            'canvas_data' => [
                'zoom' => 1.0,
                'offset_x' => 0,
                'offset_y' => 0,
                'tables' => [],
                'relationships' => []
            ],
            'settings' => [
                'auto_save' => true,
                'show_grid' => true,
                'snap_to_grid' => true
            ],
            'created_by' => auth()->id(),
            'updated_by' => auth()->id()
        ]);

        $this->loadProjectData();
    }

    public function loadProjectData(): void
    {
        if (!$this->currentProject) return;

        $this->canvasData = $this->currentProject->canvas_data ?? [];
        $this->canvasZoom = $this->canvasData['zoom'] ?? 1.0;
        $this->canvasOffsetX = $this->canvasData['offset_x'] ?? 0;
        $this->canvasOffsetY = $this->canvasData['offset_y'] ?? 0;

        // Load tables with fields
        $this->tables = $this->currentProject->tables()
            ->with(['fields' => function($query) {
                $query->orderBy('order');
            }])
            ->get()
            ->map(function($table) {
                return [
                    'id' => $table->id,
                    'name' => $table->name,
                    'display_name' => $table->display_name,
                    'position_x' => $table->position_x,
                    'position_y' => $table->position_y,
                    'width' => $table->width,
                    'height' => $table->height,
                    'color' => $table->color,
                    'fields' => $table->fields->map(function($field) {
                        return [
                            'id' => $field->id,
                            'name' => $field->name,
                            'type' => $field->type,
                            'length' => $field->length,
                            'is_nullable' => $field->is_nullable,
                            'is_primary' => $field->is_primary,
                            'is_foreign_key' => $field->is_foreign_key,
                            'default_value' => $field->default_value
                        ];
                    })->toArray()
                ];
            })->toArray();

        // Load relationships
        $this->relationships = $this->currentProject->relationships()
            ->with(['sourceTable', 'targetTable', 'sourceField', 'targetField'])
            ->get()
            ->map(function($rel) {
                return [
                    'id' => $rel->id,
                    'type' => $rel->type,
                    'source_table_id' => $rel->source_table_id,
                    'target_table_id' => $rel->target_table_id,
                    'source_field_id' => $rel->source_field_id,
                    'target_field_id' => $rel->target_field_id,
                    'cardinality_source' => $rel->cardinality_source,
                    'cardinality_target' => $rel->cardinality_target
                ];
            })->toArray();
    }

    public function initializeCanvas(): void
    {
        $this->dispatch('initializeCanvas', [
            'tables' => $this->tables,
            'relationships' => $this->relationships,
            'zoom' => $this->canvasZoom,
            'offsetX' => $this->canvasOffsetX,
            'offsetY' => $this->canvasOffsetY
        ]);
    }

    #[On('table-moved')]
    public function updateTablePosition(int $tableId, int $x, int $y): void
    {
        $table = ErdTable::find($tableId);
        if ($table && $table->project_id === $this->currentProject->id) {
            $table->update([
                'position_x' => $x,
                'position_y' => $y
            ]);

            $this->saveCanvasState();
        }
    }

    #[On('canvas-updated')]
    public function updateCanvasState(float $zoom, int $offsetX, int $offsetY): void
    {
        $this->canvasZoom = $zoom;
        $this->canvasOffsetX = $offsetX;
        $this->canvasOffsetY = $offsetY;

        $this->saveCanvasState();
    }

    public function saveCanvasState(): void
    {
        if (!$this->currentProject) return;

        $this->currentProject->update([
            'canvas_data' => [
                'zoom' => $this->canvasZoom,
                'offset_x' => $this->canvasOffsetX,
                'offset_y' => $this->canvasOffsetY,
                'tables' => $this->tables,
                'relationships' => $this->relationships
            ],
            'updated_by' => auth()->id()
        ]);
    }

    public function addTable(): void
    {
        $this->selectedTable = null;
        $this->tableForm = [
            'name' => '',
            'display_name' => '',
            'description' => '',
            'position_x' => 100,
            'position_y' => 100,
            'color' => '#ffffff'
        ];
        $this->fieldForms = [];
        $this->showTableModal = true;
    }

    public function editTable(int $tableId): void
    {
        $this->selectedTable = ErdTable::with('fields')->find($tableId);
        
        if ($this->selectedTable) {
            $this->tableForm = [
                'name' => $this->selectedTable->name,
                'display_name' => $this->selectedTable->display_name,
                'description' => $this->selectedTable->description,
                'position_x' => $this->selectedTable->position_x,
                'position_y' => $this->selectedTable->position_y,
                'color' => $this->selectedTable->color
            ];

            $this->fieldForms = $this->selectedTable->fields->map(function($field) {
                return [
                    'id' => $field->id,
                    'name' => $field->name,
                    'type' => $field->type,
                    'length' => $field->length,
                    'is_nullable' => $field->is_nullable,
                    'is_primary' => $field->is_primary,
                    'is_unique' => $field->is_unique,
                    'is_auto_increment' => $field->is_auto_increment,
                    'default_value' => $field->default_value,
                    'comment' => $field->comment
                ];
            })->toArray();

            $this->showTableModal = true;
        }
    }

    public function saveTable(): void
    {
        if ($this->selectedTable) {
            // Update existing table
            $this->selectedTable->update($this->tableForm);
            $table = $this->selectedTable;
        } else {
            // Create new table
            $table = ErdTable::create(array_merge($this->tableForm, [
                'project_id' => $this->currentProject->id
            ]));
        }

        // Save fields
        $this->saveTableFields($table);

        $this->loadProjectData();
        $this->showTableModal = false;
        $this->dispatch('refreshCanvas');

        Notification::make()
            ->title('Table saved successfully')
            ->success()
            ->send();
    }

    private function saveTableFields(ErdTable $table): void
    {
        // Delete existing fields if updating
        if ($this->selectedTable) {
            $table->fields()->delete();
        }

        foreach ($this->fieldForms as $index => $fieldData) {
            ErdField::create(array_merge($fieldData, [
                'table_id' => $table->id,
                'order' => $index
            ]));
        }
    }

    public function addField(): void
    {
        $this->fieldForms[] = [
            'name' => '',
            'type' => 'varchar',
            'length' => 255,
            'is_nullable' => true,
            'is_primary' => false,
            'is_unique' => false,
            'is_auto_increment' => false,
            'default_value' => '',
            'comment' => ''
        ];
    }

    public function removeField(int $index): void
    {
        unset($this->fieldForms[$index]);
        $this->fieldForms = array_values($this->fieldForms);
    }

    public function deleteTable(int $tableId): void
    {
        $table = ErdTable::find($tableId);
        if ($table && $table->project_id === $this->currentProject->id) {
            $table->delete();
            $this->loadProjectData();
            $this->dispatch('refreshCanvas');

            Notification::make()
                ->title('Table deleted successfully')
                ->success()
                ->send();
        }
    }

    public function exportSql(): string
    {
        if (!$this->currentProject) {
            return '';
        }

        $exportService = new SqlExportService();
        return $exportService->exportProject($this->currentProject);
    }

    public function importSql(string $sql): void
    {
        if (!$this->currentProject) {
            return;
        }

        $importService = new SqlImportService();
        $importService->importToProject($this->currentProject, $sql);
        
        $this->loadProjectData();
        $this->dispatch('refreshCanvas');

        Notification::make()
            ->title('SQL imported successfully')
            ->success()
            ->send();
    }

    public function getTitle(): string
    {
        return $this->currentProject ?
            "ERD Designer - {$this->currentProject->name}" :
            'ERD Designer';
    }

    public function loadFromModuleBuilder(array $moduleData): void
    {
        // Integration with Module Builder
        if (!$this->currentProject) {
            $this->createNewProject();
        }

        // Clear existing data
        $this->currentProject->tables()->delete();
        $this->currentProject->relationships()->delete();

        // Import module data
        foreach ($moduleData['models'] ?? [] as $modelData) {
            $table = ErdTable::create([
                'project_id' => $this->currentProject->id,
                'name' => $modelData['table_name'],
                'display_name' => $modelData['name'],
                'description' => $modelData['description'] ?? '',
                'position_x' => rand(100, 800),
                'position_y' => rand(100, 600)
            ]);

            foreach ($modelData['fields'] ?? [] as $index => $fieldData) {
                ErdField::create([
                    'table_id' => $table->id,
                    'name' => $fieldData['name'],
                    'type' => $this->mapLaravelToSqlType($fieldData['type']),
                    'length' => $fieldData['length'],
                    'is_nullable' => !($fieldData['required'] ?? false),
                    'is_primary' => $fieldData['name'] === 'id',
                    'default_value' => $fieldData['default'],
                    'order' => $index
                ]);
            }
        }

        // Import relationships
        foreach ($moduleData['relationships'] ?? [] as $relData) {
            $sourceTable = ErdTable::where('project_id', $this->currentProject->id)
                ->where('display_name', $relData['from_model'])
                ->first();

            $targetTable = ErdTable::where('project_id', $this->currentProject->id)
                ->where('display_name', $relData['to_model'])
                ->first();

            if ($sourceTable && $targetTable) {
                ErdRelationship::create([
                    'project_id' => $this->currentProject->id,
                    'type' => $this->mapLaravelToErdRelationType($relData['type']),
                    'source_table_id' => $sourceTable->id,
                    'target_table_id' => $targetTable->id,
                    'name' => $relData['relationship_name'] ?? null
                ]);
            }
        }

        $this->loadProjectData();
        $this->dispatch('refreshCanvas');

        Notification::make()
            ->title('Module data loaded successfully')
            ->success()
            ->send();
    }

    private function mapLaravelToSqlType(string $laravelType): string
    {
        $typeMap = [
            'string' => 'varchar',
            'text' => 'text',
            'integer' => 'int',
            'bigInteger' => 'bigint',
            'decimal' => 'decimal',
            'boolean' => 'tinyint',
            'date' => 'date',
            'datetime' => 'datetime',
            'timestamp' => 'timestamp',
            'json' => 'json'
        ];

        return $typeMap[$laravelType] ?? 'varchar';
    }

    private function mapLaravelToErdRelationType(string $laravelType): string
    {
        $typeMap = [
            'hasOne' => 'one_to_one',
            'hasMany' => 'one_to_many',
            'belongsTo' => 'many_to_one',
            'belongsToMany' => 'many_to_many'
        ];

        return $typeMap[$laravelType] ?? 'many_to_one';
    }
}

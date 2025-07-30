<?php

namespace Modules\ERDDesigner\app\Filament\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Modules\ERDDesigner\app\Models\ErdProject;
use Modules\ERDDesigner\app\Models\ErdTable;
use Modules\ERDDesigner\app\Models\ErdField;
use Modules\ERDDesigner\app\Models\ErdRelationship;
use Modules\ERDDesigner\app\Services\SqlExportService;
use Modules\ERDDesigner\app\Services\SqlImportService;

class AdvancedERDDesigner extends Page
{
    protected string $view = 'erddesigner::advanced-erd-designer-visual';

    protected static ?string $navigationLabel = null; // Hide from navigation

    protected static ?string $title = 'Advanced ERD Designer';

    protected static bool $shouldRegisterNavigation = false; // Don't show in sidebar

    protected static ?string $slug = 'advanced-erd-designer';

    public ?ErdProject $project = null;
    public array $tables = [];
    public array $relationships = [];
    public bool $showTableModal = false;
    public bool $showImportModal = false;
    public bool $showExportModal = false;
    public bool $showProjectSettingsModal = false;
    public array $tableForm = [];
    public array $projectSettingsForm = [];
    public ?int $editingTableId = null;
    public string $sqlImport = '';
    public bool $skipDemoCreation = false; // Flag to prevent auto demo creation

    public array $fieldForm = [];
    public ?int $editingFieldId = null;
    public bool $showFieldModal = false;
    public string $sqlExport = '';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('access_erd_designer') ?? false;
    }

    public function mount(): void
    {
        // Get project ID from URL parameter
        $project = request()->get('project');

        if ($project) {
            // Super admins can access all projects, regular users only their own
            $query = ErdProject::where('id', $project);

            if (!auth()->user()->hasRole('Super Admin')) {
                $query->where('created_by', auth()->id());
            }

            $this->project = $query->first();

            if (!$this->project) {
                // Check if project exists but belongs to different user
                $projectExists = ErdProject::where('id', $project)->exists();

                if ($projectExists) {
                    Notification::make()
                        ->title('Access Denied')
                        ->body("ERD project #{$project} exists but you don't have access to it.")
                        ->danger()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Project Not Found')
                        ->body("ERD project #{$project} was not found. Please check the project ID.")
                        ->danger()
                        ->send();
                }

                $this->redirect('/admin/erd-designer-page');
                return;
            }

            $this->loadProjectData();

            // Debug logging
            \Log::info('Mount: Project loaded', [
                'project_id' => $this->project->id,
                'tables_in_memory' => count($this->tables),
                'tables_in_db' => $this->project->tables()->count(),
                'skip_demo' => $this->skipDemoCreation
            ]);

            // Don't automatically create demo data for new projects
            // Demo data should only be created when "Load Demo" button is clicked
            \Log::info('Project loaded - demo data creation is manual only', [
                'project_id' => $this->project->id,
                'table_count' => $this->project->tables()->count()
            ]);

            // Debug: Log the loaded data
            \Log::info('ERD Designer loaded', [
                'project_id' => $this->project->id,
                'tables_count' => count($this->tables),
                'relationships_count' => count($this->relationships)
            ]);
        } else {
            Notification::make()
                ->title('No Project Specified')
                ->body('Please select a project to open in the advanced ERD designer.')
                ->warning()
                ->send();

            $this->redirect('/admin/erd-designer-page');
        }
    }

    public function loadProjectData(): void
    {
        if (!$this->project) return;

        // Load tables with fields
        $this->tables = $this->project->tables()
            ->with(['fields' => function($query) {
                $query->orderBy('order');
            }])
            ->get()
            ->map(function($table) {
                return [
                    'id' => $table->id,
                    'name' => $table->name,
                    'display_name' => $table->display_name,
                    'description' => $table->description,
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
                            'is_auto_increment' => $field->is_auto_increment ?? false,
                            'default_value' => $field->default_value,
                            'enum_options' => $field->enum_options,
                            'precision' => $field->precision,
                            'scale' => $field->scale
                        ];
                    })->toArray()
                ];
            })->toArray();

        // Load relationships
        $relationships = $this->project->relationships()
            ->with(['sourceTable', 'targetTable', 'sourceField', 'targetField'])
            ->get();
            
        \Log::info('ERD Relationships loaded:', [
            'project_id' => $this->project->id,
            'relationships_count' => $relationships->count(),
            'relationships_data' => $relationships->toArray()
        ]);
        
        $this->relationships = $relationships
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

        // Debug: Log table positions being loaded
        \Log::info('Table positions loaded from database:', [
            'positions' => collect($this->tables)->map(function($table) {
                return [
                    'name' => $table['name'],
                    'position_x' => $table['position_x'],
                    'position_y' => $table['position_y']
                ];
            })->toArray()
        ]);

        // Dispatch event to refresh frontend data
        $this->dispatch('dataUpdated', [
            'tables' => $this->tables,
            'relationships' => $this->relationships
        ]);
    }

    public function addTable(): void
    {
        // Allow access for authenticated users - remove strict permission check for now
        if (!auth()->check()) {
            Notification::make()
                ->title('Access Denied')
                ->body('You must be logged in to manage ERD tables.')
                ->danger()
                ->send();
            return;
        }

        if (!$this->project) {
            Notification::make()
                ->title('No Project')
                ->body('No project is currently loaded.')
                ->warning()
                ->send();
            return;
        }

        // Initialize table form with default values
        $tableCount = count($this->tables);
        $this->tableForm = [
            'name' => 'table_' . ($tableCount + 1),
            'display_name' => 'New Table ' . ($tableCount + 1),
            'description' => '',
            'color' => '#3b82f6',
            'fields' => [
                [
                    'name' => 'id',
                    'type' => 'bigint',
                    'length' => null,
                    'is_nullable' => false,
                    'is_primary' => true,
                    'is_foreign_key' => false,
                    'is_auto_increment' => true,
                    'default_value' => '',
                    'validation' => '',
                    'enum_options' => ''
                ]
            ]
        ];

        // Ensure color is never empty
        if (empty($this->tableForm['color'])) {
            $this->tableForm['color'] = '#3b82f6';
        }

        $this->showTableModal = true;
    }

    public function editTable(int $tableId): void
    {
        if (!$this->project) return;

        $table = ErdTable::with(['fields' => function($query) {
            $query->orderBy('order', 'asc');
        }])->find($tableId);
        if (!$table || $table->project_id !== $this->project->id) {
            Notification::make()
                ->title('Table Not Found')
                ->body('The table you are trying to edit was not found.')
                ->danger()
                ->send();
            return;
        }

        $this->editingTableId = $tableId;

        // Populate form with existing table data
        $this->tableForm = [
            'name' => $table->name,
            'display_name' => $table->display_name,
            'description' => $table->description ?? '',
            'color' => !empty($table->color) ? $table->color : '#3b82f6',
            'fields' => $table->fields->sortBy('order')->map(function($field) {
                return [
                    'id' => $field->id,
                    'name' => $field->name,
                    'type' => $field->type,
                    'length' => $field->length,
                    'is_nullable' => $field->is_nullable,
                    'is_primary' => $field->is_primary,
                    'is_foreign_key' => $field->is_foreign_key,
                    'is_auto_increment' => $field->is_auto_increment,
                    'default_value' => $field->default_value ?? '',
                    'validation' => '',
                    'enum_options' => ''
                ];
            })->values()->toArray()
        ];

        // Ensure color is never empty
        if (empty($this->tableForm['color'])) {
            $this->tableForm['color'] = '#3b82f6';
        }

        // Debug: Log the loaded data
        \Log::info('Edit Table Data Loaded', [
            'table_id' => $tableId,
            'table_name' => $table->name,
            'fields_count' => $table->fields->count(),
            'form_fields_count' => count($this->tableForm['fields']),
            'fields_data' => $this->tableForm['fields']
        ]);

        $this->showTableModal = true;

        // Force refresh the component to ensure data is properly bound
        $this->dispatch('refresh');
    }

    public function refreshTableForm(): void
    {
        // Method to manually refresh the table form if needed
        if ($this->editingTableId) {
            $this->editTable($this->editingTableId);
        }
    }

    public function refreshPositions(): void
    {
        // Force reload all table positions from database
        $this->loadProjectData();

        Notification::make()
            ->title('Positions Refreshed')
            ->body('All table positions have been reloaded from the database.')
            ->success()
            ->send();
    }

    public function removeFieldFromForm(int $index): void
    {
        if (isset($this->tableForm['fields'][$index])) {
            array_splice($this->tableForm['fields'], $index, 1);
        }
    }

    public function addFieldToForm(): void
    {
        if (!isset($this->tableForm['fields'])) {
            $this->tableForm['fields'] = [];
        }

        $this->tableForm['fields'][] = [
            'id' => null,
            'name' => '',
            'type' => 'varchar',
            'length' => 255,
            'is_nullable' => true,
            'is_primary' => false,
            'is_foreign_key' => false,
            'is_auto_increment' => false,
            'default_value' => '',
            'validation' => '',
            'enum_options' => ''
        ];
    }

    public function editField(int $fieldId): void
    {
        $field = ErdField::find($fieldId);
        if ($field && $field->table->project_id === $this->project->id) {
            // Set up field form for editing
            $this->fieldForm = [
                'id' => $field->id,
                'table_id' => $field->table_id,
                'name' => $field->name,
                'type' => $field->type,
                'length' => $field->length,
                'is_nullable' => $field->is_nullable,
                'is_primary' => $field->is_primary,
                'is_foreign_key' => $field->is_foreign_key,
                'is_auto_increment' => $field->is_auto_increment,
                'default_value' => $field->default_value,
                'validation' => $field->validation ?? '',
                'enum_options' => $field->enum_options ?? ''
            ];

            $this->editingFieldId = $fieldId;
            $this->showFieldModal = true;
        }
    }

    public function addNewFieldToTable(int $tableId): void
    {
        $table = ErdTable::find($tableId);
        if ($table && $table->project_id === $this->project->id) {
            // Set up field form for new field
            $this->fieldForm = [
                'id' => null,
                'table_id' => $tableId,
                'name' => '',
                'type' => 'varchar',
                'length' => 255,
                'is_nullable' => true,
                'is_primary' => false,
                'is_foreign_key' => false,
                'is_auto_increment' => false,
                'default_value' => '',
                'validation' => '',
                'enum_options' => ''
            ];

            $this->editingFieldId = null;
            $this->showFieldModal = true;
        }
    }

    public function saveField(): void
    {
        if ($this->editingFieldId) {
            // Update existing field
            $field = ErdField::find($this->editingFieldId);
            if ($field && $field->table->project_id === $this->project->id) {
                $field->update([
                    'name' => $this->fieldForm['name'],
                    'type' => $this->fieldForm['type'],
                    'length' => $this->fieldForm['length'],
                    'is_nullable' => $this->fieldForm['is_nullable'],
                    'is_primary' => $this->fieldForm['is_primary'],
                    'is_foreign_key' => $this->fieldForm['is_foreign_key'],
                    'is_auto_increment' => $this->fieldForm['is_auto_increment'],
                    'default_value' => $this->fieldForm['default_value'],
                    'validation' => $this->fieldForm['validation'],
                    'enum_options' => $this->fieldForm['enum_options']
                ]);

                Notification::make()
                    ->title('Field Updated')
                    ->body('Field has been updated successfully.')
                    ->success()
                    ->send();
            }
        } else {
            // Create new field
            $table = ErdTable::find($this->fieldForm['table_id']);
            if ($table && $table->project_id === $this->project->id) {
                ErdField::create([
                    'table_id' => $this->fieldForm['table_id'],
                    'name' => $this->fieldForm['name'],
                    'type' => $this->fieldForm['type'],
                    'length' => $this->fieldForm['length'],
                    'is_nullable' => $this->fieldForm['is_nullable'],
                    'is_primary' => $this->fieldForm['is_primary'],
                    'is_foreign_key' => $this->fieldForm['is_foreign_key'],
                    'is_auto_increment' => $this->fieldForm['is_auto_increment'],
                    'default_value' => $this->fieldForm['default_value'],
                    'validation' => $this->fieldForm['validation'],
                    'enum_options' => $this->fieldForm['enum_options'],
                    'order' => $table->fields()->count(),
                    'form_type' => $this->getFormType($this->fieldForm['type'])
                ]);

                Notification::make()
                    ->title('Field Created')
                    ->body('New field has been created successfully.')
                    ->success()
                    ->send();
            }
        }

        $this->closeFieldModal();
        $this->loadProjectData();
    }

    public function closeFieldModal(): void
    {
        $this->fieldForm = [];
        $this->editingFieldId = null;
        $this->showFieldModal = false;
    }

    public function saveTable(): void
    {
        if (!$this->project || empty($this->tableForm)) return;

        // Validate required fields
        if (empty($this->tableForm['name']) || empty($this->tableForm['display_name'])) {
            Notification::make()
                ->title('Validation Error')
                ->body('Table name and display name are required.')
                ->danger()
                ->send();
            return;
        }

        if ($this->editingTableId) {
            // Update existing table
            $this->updateExistingTable();
        } else {
            // Create new table
            $this->createNewTable();
        }
    }

    private function createNewTable(): void
    {
        // Check for unique table name
        $existingTable = collect($this->tables)->firstWhere('name', $this->tableForm['name']);
        if ($existingTable) {
            Notification::make()
                ->title('Validation Error')
                ->body('A table with this name already exists.')
                ->danger()
                ->send();
            return;
        }

        // Calculate position for new table
        $tableCount = count($this->tables);
        $positionX = 100 + ($tableCount * 50);
        $positionY = 100 + ($tableCount * 50);

        // Create the table
        $table = ErdTable::create([
            'project_id' => $this->project->id,
            'name' => $this->tableForm['name'],
            'display_name' => $this->tableForm['display_name'],
            'description' => $this->tableForm['description'] ?? '',
            'position_x' => $positionX,
            'position_y' => $positionY,
            'width' => 280,
            'height' => 200,
            'color' => $this->tableForm['color'] ?? '#3b82f6',
        ]);

        // Create fields
        $this->createTableFields($table->id);

        // Reset form and close modal
        $this->resetTableForm();

        Notification::make()
            ->title('Table Created')
            ->body("Table '{$table->display_name}' has been created successfully with " . count($this->tableForm['fields'] ?? []) . " fields.")
            ->success()
            ->send();
    }

    private function updateExistingTable(): void
    {
        $table = ErdTable::find($this->editingTableId);
        if (!$table || $table->project_id !== $this->project->id) return;

        // Check for unique table name (excluding current table)
        $existingTable = collect($this->tables)
            ->where('id', '!=', $this->editingTableId)
            ->firstWhere('name', $this->tableForm['name']);

        if ($existingTable) {
            Notification::make()
                ->title('Validation Error')
                ->body('A table with this name already exists.')
                ->danger()
                ->send();
            return;
        }

        // Update table basic info
        $table->update([
            'name' => $this->tableForm['name'],
            'display_name' => $this->tableForm['display_name'],
            'description' => $this->tableForm['description'] ?? '',
            'color' => $this->tableForm['color'] ?? '#3b82f6',
        ]);

        // Update fields
        $this->updateTableFields($table->id);

        // Reset form and close modal
        $this->resetTableForm();

        Notification::make()
            ->title('Table Updated')
            ->body("Table '{$table->display_name}' has been updated successfully.")
            ->success()
            ->send();
    }

    private function createTableFields(int $tableId): void
    {
        if (empty($this->tableForm['fields'])) return;

        foreach ($this->tableForm['fields'] as $index => $fieldData) {
            if (empty($fieldData['name'])) continue;

            ErdField::create([
                'table_id' => $tableId,
                'name' => $fieldData['name'],
                'type' => $fieldData['type'] ?? 'varchar',
                'length' => $fieldData['length'] ?: null,
                'is_nullable' => $fieldData['is_nullable'] ?? true,
                'is_primary' => $fieldData['is_primary'] ?? false,
                'is_foreign_key' => $fieldData['is_foreign_key'] ?? false,
                'is_auto_increment' => $fieldData['is_auto_increment'] ?? false,
                'default_value' => $fieldData['default_value'] ?? null,
                'order' => $index,
                'form_type' => $this->getFormType($fieldData['type'] ?? 'varchar'),
            ]);
        }
    }

    private function updateTableFields(int $tableId): void
    {
        if (empty($this->tableForm['fields'])) return;

        // Get existing fields
        $existingFields = ErdField::where('table_id', $tableId)->get()->keyBy('id');
        $processedFieldIds = [];

        foreach ($this->tableForm['fields'] as $index => $fieldData) {
            if (empty($fieldData['name'])) continue;

            $fieldId = $fieldData['id'] ?? null;

            if ($fieldId && $existingFields->has($fieldId)) {
                // Update existing field
                $existingFields[$fieldId]->update([
                    'name' => $fieldData['name'],
                    'type' => $fieldData['type'] ?? 'varchar',
                    'length' => $fieldData['length'] ?: null,
                    'is_nullable' => $fieldData['is_nullable'] ?? true,
                    'is_primary' => $fieldData['is_primary'] ?? false,
                    'is_foreign_key' => $fieldData['is_foreign_key'] ?? false,
                    'is_auto_increment' => $fieldData['is_auto_increment'] ?? false,
                    'default_value' => $fieldData['default_value'] ?? null,
                    'order' => $index,
                    'form_type' => $this->getFormType($fieldData['type'] ?? 'varchar'),
                ]);
                $processedFieldIds[] = $fieldId;
            } else {
                // Create new field
                $newField = ErdField::create([
                    'table_id' => $tableId,
                    'name' => $fieldData['name'],
                    'type' => $fieldData['type'] ?? 'varchar',
                    'length' => $fieldData['length'] ?: null,
                    'is_nullable' => $fieldData['is_nullable'] ?? true,
                    'is_primary' => $fieldData['is_primary'] ?? false,
                    'is_foreign_key' => $fieldData['is_foreign_key'] ?? false,
                    'is_auto_increment' => $fieldData['is_auto_increment'] ?? false,
                    'default_value' => $fieldData['default_value'] ?? null,
                    'order' => $index,
                    'form_type' => $this->getFormType($fieldData['type'] ?? 'varchar'),
                ]);
                $processedFieldIds[] = $newField->id;
            }
        }

        // Delete fields that were removed
        $fieldsToDelete = $existingFields->keys()->diff($processedFieldIds);
        if ($fieldsToDelete->isNotEmpty()) {
            ErdField::whereIn('id', $fieldsToDelete)->delete();
        }
    }

    private function resetTableForm(): void
    {
        $this->tableForm = [];
        $this->editingTableId = null;
        $this->showTableModal = false;
        $this->loadProjectData();
    }

    public function testDataLoad(): void
    {
        if (!$this->project) {
            Notification::make()
                ->title('Debug: No Project')
                ->body('Project is null')
                ->warning()
                ->send();
            return;
        }

        $tableCount = $this->project->tables()->count();
        $relationshipCount = $this->project->relationships()->count();

        Notification::make()
            ->title('Debug: Data Loaded')
            ->body("Project ID: {$this->project->id}, Tables: {$tableCount}, Relations: {$relationshipCount}, Loaded Tables: " . count($this->tables))
            ->info()
            ->send();
    }

    public function createRelationship(array $data): void
    {
        if (!$this->project) return;

        ErdRelationship::create([
            'project_id' => $this->project->id,
            'source_table_id' => $data['source_table_id'],
            'target_table_id' => $data['target_table_id'],
            'source_field_id' => $data['source_field_id'] ?: null,
            'target_field_id' => $data['target_field_id'] ?: null,
            'type' => $data['type'],
            'foreign_key' => $data['foreign_key'] ?? null,
            'local_key' => $data['local_key'] ?? 'id',
            'pivot_table' => $data['pivot_table'] ?? null,
            'relationship_name' => $data['relationship_name'] ?? null,
            'cardinality_source' => $this->getCardinalityFromType($data['type'], 'source'),
            'cardinality_target' => $this->getCardinalityFromType($data['type'], 'target')
        ]);

        $this->loadProjectData();

        Notification::make()
            ->title('Relationship Created')
            ->body("New {$data['type']} relationship has been created successfully.")
            ->success()
            ->send();
    }

    private function getCardinalityFromType(string $type, string $side): string
    {
        $cardinalityMap = [
            'belongsTo' => ['source' => 'exactly_one', 'target' => 'zero_or_many'],
            'hasOne' => ['source' => 'exactly_one', 'target' => 'zero_or_one'],
            'hasMany' => ['source' => 'exactly_one', 'target' => 'zero_or_many'],
            'belongsToMany' => ['source' => 'zero_or_many', 'target' => 'zero_or_many'],
            'hasOneThrough' => ['source' => 'exactly_one', 'target' => 'zero_or_one'],
            'hasManyThrough' => ['source' => 'exactly_one', 'target' => 'zero_or_many'],
            'morphTo' => ['source' => 'exactly_one', 'target' => 'zero_or_many'],
            'morphOne' => ['source' => 'exactly_one', 'target' => 'zero_or_one'],
            'morphMany' => ['source' => 'exactly_one', 'target' => 'zero_or_many'],
            'morphToMany' => ['source' => 'zero_or_many', 'target' => 'zero_or_many']
        ];

        return $cardinalityMap[$type][$side] ?? 'zero_or_many';
    }

    public function updateRelationship(int $relationshipId, array $data): void
    {
        $relationship = ErdRelationship::find($relationshipId);
        if ($relationship && $relationship->project_id === $this->project->id) {
            $relationship->update([
                'source_table_id' => $data['source_table_id'],
                'target_table_id' => $data['target_table_id'],
                'source_field_id' => $data['source_field_id'] ?: null,
                'target_field_id' => $data['target_field_id'] ?: null,
                'type' => $data['type'],
                'cardinality_source' => $data['cardinality_source'] ?? 'exactly_one',
                'cardinality_target' => $data['cardinality_target'] ?? 'zero_or_many'
            ]);

            $this->loadProjectData();

            Notification::make()
                ->title('Relationship Updated')
                ->body('Relationship has been updated successfully.')
                ->success()
                ->send();
        }
    }

    public function deleteRelationship(int $relationshipId): void
    {
        $relationship = ErdRelationship::find($relationshipId);
        if ($relationship && $relationship->project_id === $this->project->id) {
            $relationship->delete();
            $this->loadProjectData();

            Notification::make()
                ->title('Relationship Deleted')
                ->body('Relationship has been deleted successfully.')
                ->success()
                ->send();
        }
    }



    public function exportSQL(): void
    {
        // Super admins can export, or users with specific permission
        if (!auth()->user()->hasRole('Super Admin') && !auth()->user()->can('export_sql_from_erd')) {
            Notification::make()
                ->title('Access Denied')
                ->body('You do not have permission to export SQL.')
                ->danger()
                ->send();
            return;
        }

        if (!$this->project) return;

        $exportService = new SqlExportService();
        $this->sqlExport = $exportService->exportProject($this->project);
        $this->showExportModal = true;
    }

    public function applySmartLayout(): void
    {
        if (!$this->project) return;

        try {
            // Build relationship graph
            $relationships = $this->project->relationships()->with(['sourceTable', 'targetTable'])->get();
            $tables = $this->project->tables()->get();
            
            // Create adjacency list for relationships
            $graph = [];
            foreach ($tables as $table) {
                $graph[$table->name] = [];
            }
            
            foreach ($relationships as $rel) {
                if ($rel->sourceTable && $rel->targetTable) {
                    $graph[$rel->sourceTable->name][] = $rel->targetTable->name;
                    $graph[$rel->targetTable->name][] = $rel->sourceTable->name;
                }
            }
            
            // Separate connected and isolated tables
            $connectedTables = [];
            $isolatedTables = [];
            
            foreach ($tables as $table) {
                if (isset($graph[$table->name]) && count($graph[$table->name]) > 0) {
                    $connectedTables[] = $table;
                } else {
                    $isolatedTables[] = $table;
                }
            }
            
            // Apply smart layout with clear separation
            $this->layoutConnectedTables($connectedTables, $graph);
            $this->layoutIsolatedTables($isolatedTables, count($connectedTables) > 0);
            
            // Reload data for frontend
            $this->loadProjectData();
            
            Notification::make()
                ->title('Smart Layout Applied')
                ->body('Tables have been reorganized with intelligent positioning.')
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            \Log::error('Smart Layout Error: ' . $e->getMessage());
            
            Notification::make()
                ->title('Layout Error')
                ->body('Failed to apply smart layout: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    private function layoutConnectedTables(array $connectedTables, array $graph): void
    {
        if (empty($connectedTables)) return;
        
        // Find most connected table
        $mostConnected = $connectedTables[0];
        $maxConnections = isset($graph[$mostConnected->name]) ? count($graph[$mostConnected->name]) : 0;
        
        foreach ($connectedTables as $table) {
            $connections = isset($graph[$table->name]) ? count($graph[$table->name]) : 0;
            if ($connections > $maxConnections) {
                $mostConnected = $table;
                $maxConnections = $connections;
            }
        }
        
        // Position central table
        $centerX = 200;
        $centerY = 300;
        $mostConnected->update([
            'position_x' => $centerX,
            'position_y' => $centerY
        ]);
        
        // Position other tables in circles around the central one
        $positioned = [$mostConnected->name];
        $queue = [$mostConnected];
        $level = 1;
        
        while (!empty($queue) && count($positioned) < count($connectedTables)) {
            $currentLevel = [];
            $radius = 250 * $level;
            
            foreach ($queue as $currentTable) {
                if (!isset($graph[$currentTable->name])) continue;
                
                $connections = $graph[$currentTable->name];
                $unpositioned = array_filter($connections, function($name) use ($positioned) {
                    return !in_array($name, $positioned);
                });
                
                if (!empty($unpositioned)) {
                    $count = count($unpositioned);
                    $angleStep = (2 * M_PI) / max($count, 1);
                    
                    $i = 0;
                    foreach ($unpositioned as $tableName) {
                        $table = collect($connectedTables)->first(function($t) use ($tableName) {
                            return $t->name === $tableName;
                        });
                        
                        if ($table && !in_array($tableName, $positioned)) {
                            $angle = $i * $angleStep + ($level * 0.5);
                            $x = max(50, min(800, $centerX + $radius * cos($angle)));
                            $y = max(50, min(800, $centerY + $radius * sin($angle)));
                            
                            $table->update([
                                'position_x' => (int)$x,
                                'position_y' => (int)$y
                            ]);
                            
                            $positioned[] = $tableName;
                            $currentLevel[] = $table;
                            $i++;
                        }
                    }
                }
            }
            
            $queue = $currentLevel;
            $level++;
            if ($level > 5) break;
        }
    }
    
    private function layoutIsolatedTables(array $isolatedTables, bool $hasConnectedTables): void
    {
        if (empty($isolatedTables)) return;
        
        // Start isolated tables far to the right with clear separation
        $startX = $hasConnectedTables ? 1000 : 50;
        $startY = 50;
        $spacing = 200;
        
        $currentX = $startX;
        $currentY = $startY;
        
        foreach ($isolatedTables as $index => $table) {
            $table->update([
                'position_x' => $currentX,
                'position_y' => $currentY
            ]);
            
            $currentY += $spacing;
            
            // Move to next column if needed
            if ($currentY > 800) {
                $currentX += $spacing;
                $currentY = $startY;
            }
        }
    }

    public function showProjectSettings(): void
    {
        if (!$this->project) return;
        
        $this->projectSettingsForm = [
            'name' => $this->project->name,
            'description' => $this->project->description,
            'database_name' => $this->project->database_name ?: Str::snake($this->project->name) . '_db',
            'database_type' => $this->project->database_type ?: 'mysql',
        ];
        
        $this->showProjectSettingsModal = true;
    }

    public function updateProjectSettings(): void
    {
        if (!$this->project) return;
        
        try {
            $this->project->update([
                'name' => $this->projectSettingsForm['name'],
                'description' => $this->projectSettingsForm['description'],
                'database_name' => $this->projectSettingsForm['database_name'],
                'database_type' => $this->projectSettingsForm['database_type'],
                'updated_by' => auth()->id(),
            ]);
            
            Notification::make()
                ->title('Project Updated')
                ->body('Project settings have been updated successfully.')
                ->success()
                ->send();
                
            $this->showProjectSettingsModal = false;
            
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Failed to update project settings: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function importSQL(): void
    {
        // Check if user has Super Admin role
        if (!auth()->user()->hasRole('Super Admin')) {
            Notification::make()
                ->title('Access Denied')
                ->body('Only Super Admins can import SQL files.')
                ->danger()
                ->send();
            return;
        }

        $this->showImportModal = true;
    }

    public function processImportSQL(): void
    {
        if (!$this->project || empty($this->sqlImport)) return;

        try {
            $importService = new SqlImportService();
            $importService->importToProject($this->project, $this->sqlImport);
            
            $this->loadProjectData();
            $this->showImportModal = false;
            $this->sqlImport = '';

            // Dispatch browser event to refresh frontend data
            $this->dispatch('dataUpdated', [
                'tables' => $this->tables,
                'relationships' => $this->relationships
            ]);

            Notification::make()
                ->title('SQL Imported')
                ->body('SQL has been successfully imported into your ERD project.')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Import Failed')
                ->body('Error importing SQL: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function exportToModuleBuilder(): void
    {
        // Super admins can integrate, or users with specific permission
        if (!auth()->user()->hasRole('Super Admin') && !auth()->user()->can('integrate_erd_module_builder')) {
            Notification::make()
                ->title('Access Denied')
                ->body('You do not have permission to integrate with Module Builder.')
                ->danger()
                ->send();
            return;
        }

        Notification::make()
            ->title('Integration Available')
            ->body('Use the "Load from ERD Designer" button in the Module Builder to import this project.')
            ->info()
            ->send();

        // Redirect to Module Builder
        $this->redirect('/admin/enhanced-module-builder');
    }

    public function deleteTable(int $tableId): void
    {
        $table = ErdTable::find($tableId);
        if ($table && $table->project_id === $this->project->id) {
            $table->delete();
            $this->loadProjectData();

            Notification::make()
                ->title('Table Deleted')
                ->body('Table has been successfully deleted.')
                ->success()
                ->send();
        }
    }

    public function updateTable(int $tableId, array $data): void
    {
        $table = ErdTable::find($tableId);
        if ($table && $table->project_id === $this->project->id) {
            $table->update($data);
            $this->loadProjectData();
        }
    }

    public function saveTablePosition(int $tableId, float $x, float $y): void
    {
        $table = ErdTable::find($tableId);
        if ($table && $table->project_id === $this->project->id) {
            $table->update([
                'position_x' => $x,
                'position_y' => $y
            ]);

            // Update the local tables array to keep frontend in sync
            foreach ($this->tables as &$localTable) {
                if ($localTable['id'] == $tableId) {
                    $localTable['position_x'] = $x;
                    $localTable['position_y'] = $y;
                    break;
                }
            }

            \Log::info("Table position saved: {$table->name} at ({$x}, {$y})");
        }
    }

    public function updateField(int $fieldId, array $data): void
    {
        $field = ErdField::find($fieldId);
        if ($field && $field->table->project_id === $this->project->id) {
            $field->update($data);
            $this->loadProjectData();
        }
    }

    public function deleteField(int $fieldId): void
    {
        $field = ErdField::find($fieldId);
        if ($field && $field->table->project_id === $this->project->id) {
            $field->delete();
            $this->loadProjectData();

            Notification::make()
                ->title('Field Deleted')
                ->body('Field has been successfully deleted.')
                ->success()
                ->send();
        }
    }

    public function addFieldToTable(int $tableId): void
    {
        $table = ErdTable::find($tableId);
        if ($table && $table->project_id === $this->project->id) {
            ErdField::create([
                'table_id' => $tableId,
                'name' => 'new_field',
                'type' => 'varchar',
                'length' => 255,
                'is_nullable' => true,
                'is_primary' => false,
                'is_foreign_key' => false
            ]);

            $this->loadProjectData();

            Notification::make()
                ->title('Field Added')
                ->body('New field has been added to the table.')
                ->success()
                ->send();
        }
    }

    public function createField(int $tableId, array $data): void
    {
        $table = ErdTable::find($tableId);
        if ($table && $table->project_id === $this->project->id) {
            ErdField::create(array_merge($data, [
                'table_id' => $tableId,
                'order' => $table->fields()->count(),
                'form_type' => $this->getFormType($data['type'])
            ]));

            $this->loadProjectData();

            Notification::make()
                ->title('Field Created')
                ->body('New field has been created successfully.')
                ->success()
                ->send();
        }
    }

    public function createDemoTables(): void
    {
        if (!$this->project) return;

        // Use database transaction to prevent race conditions
        \DB::transaction(function () {
            // Re-check if tables exist within the transaction
            $existingTableCount = $this->project->tables()->count();
            
            if ($existingTableCount > 0) {
                \Log::info('Tables already exist in transaction, aborting demo creation', [
                    'project_id' => $this->project->id,
                    'existing_count' => $existingTableCount
                ]);
                return;
            }

            // Double-check with specific table names
            $existingTableNames = $this->project->tables()->pluck('name')->toArray();
            $demoTableNames = ['users', 'categories', 'products', 'orders', 'order_items'];
            
            $hasExistingDemoTables = !empty(array_intersect($existingTableNames, $demoTableNames));
            
            if ($hasExistingDemoTables) {
                \Log::info('Demo tables already exist in transaction, aborting creation', [
                    'existing_tables' => $existingTableNames,
                    'demo_tables' => $demoTableNames,
                    'intersection' => array_intersect($existingTableNames, $demoTableNames)
                ]);
                return;
            }

            \Log::info('Creating demo tables for project in transaction', [
                'project_id' => $this->project->id,
                'existing_tables' => $existingTableNames
            ]);

            try {
                // Create Users table (top-left) with duplicate protection
                try {
                    $usersTable = ErdTable::create([
                        'project_id' => $this->project->id,
                        'name' => 'users',
                        'display_name' => 'Users',
                        'description' => 'System users and customers',
                        'position_x' => 40,
                        'position_y' => 40,
                        'width' => 220,
                        'height' => 180,
                        'color' => '#3b82f6',
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    if ($e->getCode() == 23000) { // Duplicate entry
                        $usersTable = $this->project->tables()->where('name', 'users')->first();
                        \Log::info('Users table already exists, using existing one');
                    } else {
                        throw $e;
                    }
                }

            if ($usersTable) {
                $this->createFieldsForTable($usersTable, [
                ['name' => 'id', 'type' => 'bigint', 'length' => 20, 'is_primary' => true, 'is_auto_increment' => true, 'is_nullable' => false],
                ['name' => 'name', 'type' => 'varchar', 'length' => 255, 'is_nullable' => false],
                ['name' => 'email', 'type' => 'varchar', 'length' => 255, 'is_nullable' => false],
                ['name' => 'created_at', 'type' => 'timestamp', 'is_nullable' => true],
                ['name' => 'updated_at', 'type' => 'timestamp', 'is_nullable' => true],
            ]);
            }

            // Create Categories table (bottom-left) with duplicate protection
            try {
                $categoriesTable = ErdTable::create([
                    'project_id' => $this->project->id,
                    'name' => 'categories',
                    'display_name' => 'Categories',
                    'description' => 'Product categories',
                    'position_x' => 40,
                    'position_y' => 280,
                    'width' => 220,
                    'height' => 200,
                    'color' => '#10b981',
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->getCode() == 23000) { // Duplicate entry
                    $categoriesTable = $this->project->tables()->where('name', 'categories')->first();
                    \Log::info('Categories table already exists, using existing one');
                } else {
                    throw $e;
                }
            }

            if ($categoriesTable) {
                $this->createFieldsForTable($categoriesTable, [
                ['name' => 'id', 'type' => 'bigint', 'length' => 20, 'is_primary' => true, 'is_auto_increment' => true, 'is_nullable' => false],
                ['name' => 'name', 'type' => 'varchar', 'length' => 255, 'is_nullable' => false],
                ['name' => 'slug', 'type' => 'varchar', 'length' => 255, 'is_nullable' => false],
                ['name' => 'description', 'type' => 'text', 'is_nullable' => true],
                ['name' => 'image', 'type' => 'varchar', 'length' => 255, 'is_nullable' => true],
                ['name' => 'is_active', 'type' => 'tinyint', 'length' => 1, 'is_nullable' => false, 'default_value' => '1'],
                ['name' => 'created_at', 'type' => 'timestamp', 'is_nullable' => true],
                ['name' => 'updated_at', 'type' => 'timestamp', 'is_nullable' => true],
            ]);
            }

            // Create Products table (center) with duplicate protection
            try {
                $productsTable = ErdTable::create([
                    'project_id' => $this->project->id,
                    'name' => 'products',
                    'display_name' => 'Products',
                    'description' => 'Product catalog',
                    'position_x' => 320,
                    'position_y' => 280,
                    'width' => 220,
                    'height' => 180,
                    'color' => '#f59e0b',
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->getCode() == 23000) { // Duplicate entry
                    $productsTable = $this->project->tables()->where('name', 'products')->first();
                    \Log::info('Products table already exists, using existing one');
                } else {
                    throw $e;
                }
            }

            if ($productsTable) {
                $this->createFieldsForTable($productsTable, [
                ['name' => 'id', 'type' => 'bigint', 'length' => 20, 'is_primary' => true, 'is_auto_increment' => true, 'is_nullable' => false],
                ['name' => 'category_id', 'type' => 'bigint', 'length' => 20, 'is_foreign_key' => true, 'is_nullable' => false],
                ['name' => 'name', 'type' => 'varchar', 'length' => 255, 'is_nullable' => false],
                ['name' => 'created_at', 'type' => 'timestamp', 'is_nullable' => true],
                ['name' => 'updated_at', 'type' => 'timestamp', 'is_nullable' => true],
            ]);
            }

            // Create Orders table (top-right) with duplicate protection
            try {
                $ordersTable = ErdTable::create([
                    'project_id' => $this->project->id,
                    'name' => 'orders',
                    'display_name' => 'Orders',
                    'description' => 'Customer orders',
                    'position_x' => 600,
                    'position_y' => 40,
                    'width' => 220,
                    'height' => 200,
                    'color' => '#8b5cf6',
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->getCode() == 23000) { // Duplicate entry
                    $ordersTable = $this->project->tables()->where('name', 'orders')->first();
                    \Log::info('Orders table already exists, using existing one');
                } else {
                    throw $e;
                }
            }

            if ($ordersTable) {
                $this->createFieldsForTable($ordersTable, [
                ['name' => 'id', 'type' => 'bigint', 'length' => 20, 'is_primary' => true, 'is_auto_increment' => true, 'is_nullable' => false],
                ['name' => 'user_id', 'type' => 'bigint', 'length' => 20, 'is_foreign_key' => true, 'is_nullable' => false],
                ['name' => 'order_number', 'type' => 'varchar', 'length' => 100, 'is_nullable' => false],
                ['name' => 'status', 'type' => 'enum', 'enum_options' => "'pending','processing','shipped','delivered','cancelled'", 'is_nullable' => false, 'default_value' => 'pending'],
                ['name' => 'total_amount', 'type' => 'decimal', 'length' => 10, 'scale' => 2, 'is_nullable' => false, 'default_value' => '0.00'],
                ['name' => 'created_at', 'type' => 'timestamp', 'is_nullable' => true],
                ['name' => 'updated_at', 'type' => 'timestamp', 'is_nullable' => true],
            ]);
            }

            // Create Order Items table (bottom-right) with duplicate protection
            try {
                $orderItemsTable = ErdTable::create([
                    'project_id' => $this->project->id,
                    'name' => 'order_items',
                    'display_name' => 'Order Items',
                    'description' => 'Items in each order',
                    'position_x' => 600,
                    'position_y' => 320,
                    'width' => 220,
                    'height' => 200,
                    'color' => '#ef4444',
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->getCode() == 23000) { // Duplicate entry
                    $orderItemsTable = $this->project->tables()->where('name', 'order_items')->first();
                    \Log::info('Order Items table already exists, using existing one');
                } else {
                    throw $e;
                }
            }

            if ($orderItemsTable) {
                $this->createFieldsForTable($orderItemsTable, [
                ['name' => 'id', 'type' => 'bigint', 'length' => 20, 'is_primary' => true, 'is_auto_increment' => true, 'is_nullable' => false],
                ['name' => 'order_id', 'type' => 'bigint', 'length' => 20, 'is_foreign_key' => true, 'is_nullable' => false],
                ['name' => 'product_id', 'type' => 'bigint', 'length' => 20, 'is_foreign_key' => true, 'is_nullable' => false],
                ['name' => 'quantity', 'type' => 'int', 'length' => 11, 'is_nullable' => false, 'default_value' => '1'],
                ['name' => 'unit_price', 'type' => 'decimal', 'length' => 10, 'scale' => 2, 'is_nullable' => false, 'default_value' => '0.00'],
                ['name' => 'total_price', 'type' => 'decimal', 'length' => 10, 'scale' => 2, 'is_nullable' => false, 'default_value' => '0.00'],
                ['name' => 'created_at', 'type' => 'timestamp', 'is_nullable' => true],
                ['name' => 'updated_at', 'type' => 'timestamp', 'is_nullable' => true],
            ]);
            }

            // Create relationships
            $this->createDemoRelationships();
            
            \Log::info('Demo tables created successfully', [
                'project_id' => $this->project->id,
                'tables_created' => 5
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to create demo tables', [
                'project_id' => $this->project->id,
                'error' => $e->getMessage()
            ]);
            
            // Clean up any partially created tables on error
            $this->project->tables()->delete();
            $this->project->relationships()->delete();
            
            throw $e;
        }
        }); // End transaction
    }

    private function createDemoRelationships(): void
    {
        if (!$this->project) return;

        // Get created tables
        $tables = $this->project->tables()->get()->keyBy('name');
        $usersTable = $tables->get('users');
        $categoriesTable = $tables->get('categories');
        $productsTable = $tables->get('products');
        $ordersTable = $tables->get('orders');
        $orderItemsTable = $tables->get('order_items');

        if (!$usersTable || !$categoriesTable || !$productsTable || !$ordersTable || !$orderItemsTable) {
            return; // Tables not found, skip relationship creation
        }

        // Get field mappings
        $getFieldId = function($table, $fieldName) {
            return $table->fields()->where('name', $fieldName)->first()?->id;
        };

        // Create relationships
        try {
            // products.category_id -> categories.id (belongsTo)
            ErdRelationship::create([
                'project_id' => $this->project->id,
                'source_table_id' => $productsTable->id,
                'target_table_id' => $categoriesTable->id,
                'source_field_id' => $getFieldId($productsTable, 'category_id'),
                'target_field_id' => $getFieldId($categoriesTable, 'id'),
                'type' => 'belongsTo',
                'relationship_name' => 'category',
                'foreign_key' => 'category_id',
                'local_key' => 'id',
                'cardinality_source' => 'exactly_one',
                'cardinality_target' => 'zero_or_many'
            ]);

            // orders.user_id -> users.id (belongsTo)
            ErdRelationship::create([
                'project_id' => $this->project->id,
                'source_table_id' => $ordersTable->id,
                'target_table_id' => $usersTable->id,
                'source_field_id' => $getFieldId($ordersTable, 'user_id'),
                'target_field_id' => $getFieldId($usersTable, 'id'),
                'type' => 'belongsTo',
                'relationship_name' => 'user',
                'foreign_key' => 'user_id',
                'local_key' => 'id',
                'cardinality_source' => 'exactly_one',
                'cardinality_target' => 'zero_or_many'
            ]);

            // order_items.order_id -> orders.id (belongsTo)
            ErdRelationship::create([
                'project_id' => $this->project->id,
                'source_table_id' => $orderItemsTable->id,
                'target_table_id' => $ordersTable->id,
                'source_field_id' => $getFieldId($orderItemsTable, 'order_id'),
                'target_field_id' => $getFieldId($ordersTable, 'id'),
                'type' => 'belongsTo',
                'relationship_name' => 'order',
                'foreign_key' => 'order_id',
                'local_key' => 'id',
                'cardinality_source' => 'exactly_one',
                'cardinality_target' => 'zero_or_many'
            ]);

            // order_items.product_id -> products.id (belongsTo)
            ErdRelationship::create([
                'project_id' => $this->project->id,
                'source_table_id' => $orderItemsTable->id,
                'target_table_id' => $productsTable->id,
                'source_field_id' => $getFieldId($orderItemsTable, 'product_id'),
                'target_field_id' => $getFieldId($productsTable, 'id'),
                'type' => 'belongsTo',
                'relationship_name' => 'product',
                'foreign_key' => 'product_id',
                'local_key' => 'id',
                'cardinality_source' => 'exactly_one',
                'cardinality_target' => 'zero_or_many'
            ]);

        } catch (\Exception $e) {
            \Log::warning('Failed to create demo relationships: ' . $e->getMessage());
        }
    }

    private function createFieldsForTable(ErdTable $table, array $fields): void
    {
        foreach ($fields as $index => $fieldData) {
            // Handle decimal types with scale
            if (isset($fieldData['scale']) && $fieldData['type'] === 'decimal') {
                $fieldData['precision'] = $fieldData['length']; // Use length as precision for decimal
                unset($fieldData['length']); // Remove length as it's now precision
            }
            
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
            'decimal' => 'number',
            'tinyint' => 'toggle',
            'enum' => 'select',
        ];

        return $typeMap[$type] ?? 'text';
    }

    public function downloadSQL()
    {
        if (!$this->project) {
            Notification::make()
                ->title('Error')
                ->body('No project selected for export.')
                ->danger()
                ->send();
            return;
        }

        $sql = $this->generateSQL();

        if (empty($sql)) {
            Notification::make()
                ->title('No Data')
                ->body('No tables found to export.')
                ->warning()
                ->send();
            return;
        }

        // Create a downloadable response
        $filename = 'erd_' . Str::slug($this->project->name) . '_' . date('Y-m-d_H-i-s') . '.sql';

        return response()->streamDownload(function () use ($sql) {
            echo $sql;
        }, $filename, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    private function generateSQL(): string
    {
        $sql = "-- =============================================\n";
        $sql .= "-- ERD Project: {$this->project->name}\n";
        if (!empty($this->project->description)) {
            $sql .= "-- Description: {$this->project->description}\n";
        }
        $sql .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Database Type: " . ($this->project->database_type ?? 'mysql') . "\n";
        $sql .= "-- =============================================\n\n";

        // Always include database selection - use project database_name or generate one
        $databaseName = !empty($this->project->database_name) 
            ? $this->project->database_name 
            : Str::snake($this->project->name) . '_db';
            
        $sql .= "-- Create database\n";
        $sql .= "CREATE DATABASE IF NOT EXISTS `{$databaseName}`;\n";
        $sql .= "USE `{$databaseName}`;\n\n";
        
        $sql .= "-- Disable foreign key checks\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        $sql .= "-- =============================================\n";
        $sql .= "-- Table Structure\n";
        $sql .= "-- =============================================\n\n";

        // Generate CREATE TABLE statements
        foreach ($this->tables as $table) {
            $sql .= $this->generateTableSQL($table);
            $sql .= "\n";
        }

        $sql .= "\n-- =============================================\n";
        $sql .= "-- Foreign Key Constraints\n";
        $sql .= "-- =============================================\n\n";
        
        \Log::info('Generating foreign keys for relationships:', [
            'relationships_count' => count($this->relationships),
            'relationships' => $this->relationships
        ]);
        
        // Generate foreign key constraints
        foreach ($this->relationships as $relationship) {
            $foreignKeySQL = $this->generateForeignKeySQL($relationship);
            if (!empty($foreignKeySQL)) {
                $sql .= $foreignKeySQL;
            }
        }

        $sql .= "\n-- Enable foreign key checks\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n\n";

        $sql .= "-- =============================================\n";
        $sql .= "-- Additional Indexes\n";
        $sql .= "-- =============================================\n\n";

        $sql .= "-- =============================================\n";
        $sql .= "-- Sample Data (Optional)\n";
        $sql .= "-- =============================================\n\n";

        return $sql;
    }

    private function generateTableSQL(array $table): string
    {
        $sql = "-- Table: {$table['name']}\n";
        if (!empty($table['description'])) {
            $sql .= "-- Description: {$table['description']}\n";
        }
        $sql .= "DROP TABLE IF EXISTS `{$table['name']}`;\n";
        $sql .= "CREATE TABLE `{$table['name']}` (\n";

        $fields = [];
        $primaryKeys = [];

        foreach ($table['fields'] as $field) {
            $fieldSQL = "  `{$field['name']}` ";

            // Add field type with proper handling
            $fieldSQL .= $this->formatFieldType($field);

            // Add nullable - use null coalescing to handle missing keys
            $isNullable = $field['is_nullable'] ?? true;
            $fieldSQL .= $isNullable ? ' NULL' : ' NOT NULL';

            // Add auto increment - use null coalescing to handle missing keys
            $isAutoIncrement = $field['is_auto_increment'] ?? false;
            if ($isAutoIncrement) {
                $fieldSQL .= ' AUTO_INCREMENT';
            }

            // Add default value - use null coalescing to handle missing keys
            $defaultValue = $field['default_value'] ?? null;
            if (!empty($defaultValue)) {
                // Handle different data types for default values
                if (in_array(strtolower($field['type'] ?? 'varchar'), ['enum'])) {
                    $fieldSQL .= " DEFAULT '{$defaultValue}'";
                } elseif (is_numeric($defaultValue)) {
                    $fieldSQL .= " DEFAULT {$defaultValue}";
                } else {
                    $fieldSQL .= " DEFAULT '{$defaultValue}'";
                }
            }

            $fields[] = $fieldSQL;

            // Track primary keys - use null coalescing to handle missing keys
            $isPrimary = $field['is_primary'] ?? false;
            if ($isPrimary) {
                $primaryKeys[] = "`{$field['name']}`";
            }
        }

        // Add primary key constraint
        if (!empty($primaryKeys)) {
            $fields[] = "  PRIMARY KEY (" . implode(', ', $primaryKeys) . ")";
        }

        $sql .= implode(",\n", $fields);
        $sql .= "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n";

        return $sql;
    }

    private function formatFieldType(array $field): string
    {
        $type = strtoupper($field['type'] ?? 'VARCHAR');

        switch (strtolower($field['type'] ?? 'varchar')) {
            case 'varchar':
            case 'char':
                $length = $field['length'] ?? 255;
                return "{$type}({$length})";

            case 'decimal':
            case 'numeric':
                // Check multiple possible field names for precision and scale
                $precision = $field['length'] ?? $field['precision'] ?? '10,2';
                
                // If precision doesn't contain comma, assume it's just precision and add default scale
                if (strpos($precision, ',') === false) {
                    $precision = $precision . ',2';
                }
                return "{$type}({$precision})";

            case 'enum':
                // Handle different ways ENUM options might be stored
                $enumOptions = $field['enum_options'] ?? $field['options'] ?? $field['length'] ?? "'active','inactive'";
                
                // If enum options are in array format, convert to string
                if (is_array($enumOptions)) {
                    $enumOptions = "'" . implode("','", $enumOptions) . "'";
                }
                
                // Clean up the enum options format
                $enumOptions = str_replace(['[', ']', '"'], '', $enumOptions);
                
                // Ensure enum options are properly formatted with single quotes
                if (!preg_match("/^'.*'$/", $enumOptions)) {
                    // Split by comma and wrap each option in single quotes
                    $options = array_map('trim', explode(',', $enumOptions));
                    $options = array_map(function($opt) {
                        return "'" . trim($opt, "'\"") . "'";
                    }, $options);
                    $enumOptions = implode(',', $options);
                }
                
                return "{$type}({$enumOptions})";

            case 'int':
            case 'integer':
                $length = $field['length'] ?? 11;
                return "{$type}({$length})";

            case 'bigint':
                $length = $field['length'] ?? 20;
                return "{$type}({$length})";

            case 'tinyint':
                $length = $field['length'] ?? 1;
                return "{$type}({$length})";

            case 'float':
            case 'double':
                if (!empty($field['length'])) {
                    return "{$type}({$field['length']})";
                }
                return $type;

            case 'text':
            case 'longtext':
            case 'mediumtext':
            case 'tinytext':
            case 'timestamp':
            case 'datetime':
            case 'date':
            case 'time':
            case 'boolean':
            case 'json':
                // For these types, no length needed
                return $type;

            default:
                // For unknown types, assume varchar with default length
                return "VARCHAR(255)";
        }
    }

    private function generateForeignKeySQL(array $relationship): string
    {
        // Debug log the relationship data
        \Log::info('Generating foreign key SQL for relationship:', $relationship);
        
        if (empty($relationship['source_field_id']) || empty($relationship['target_field_id'])) {
            \Log::warning('Missing field IDs in relationship', [
                'source_field_id' => $relationship['source_field_id'] ?? 'null',
                'target_field_id' => $relationship['target_field_id'] ?? 'null'
            ]);
            return "";
        }

        $sourceTable = collect($this->tables)->firstWhere('id', $relationship['source_table_id']);
        $targetTable = collect($this->tables)->firstWhere('id', $relationship['target_table_id']);

        if (!$sourceTable || !$targetTable) {
            \Log::warning('Missing tables in relationship', [
                'source_table_found' => $sourceTable ? 'yes' : 'no',
                'target_table_found' => $targetTable ? 'yes' : 'no'
            ]);
            return "";
        }

        $sourceField = collect($sourceTable['fields'])->firstWhere('id', $relationship['source_field_id']);
        $targetField = collect($targetTable['fields'])->firstWhere('id', $relationship['target_field_id']);

        if (!$sourceField || !$targetField) {
            \Log::warning('Missing fields in relationship', [
                'source_field_found' => $sourceField ? 'yes' : 'no',
                'target_field_found' => $targetField ? 'yes' : 'no',
                'source_table_fields' => collect($sourceTable['fields'])->pluck('name', 'id')->toArray(),
                'target_table_fields' => collect($targetTable['fields'])->pluck('name', 'id')->toArray()
            ]);
            return "";
        }

        $constraintName = "fk_{$sourceTable['name']}_{$sourceField['name']}";

        $sql = "-- Foreign key: {$sourceTable['name']}.{$sourceField['name']} -> {$targetTable['name']}.{$targetField['name']}\n";
        $sql .= "ALTER TABLE `{$sourceTable['name']}` ADD CONSTRAINT `{$constraintName}` ";
        $sql .= "FOREIGN KEY (`{$sourceField['name']}`) REFERENCES `{$targetTable['name']}` (`{$targetField['name']}`) ";
        $sql .= "ON DELETE CASCADE ON UPDATE CASCADE;\n";

        return $sql;
    }

    public function debugRelationships(): void
    {
        \Log::info('=== DEBUG RELATIONSHIPS ===');
        \Log::info('Project ID: ' . ($this->project ? $this->project->id : 'null'));
        \Log::info('Tables count: ' . count($this->tables));
        \Log::info('Relationships count: ' . count($this->relationships));
        
        \Log::info('Tables data:', $this->tables);
        \Log::info('Relationships data:', $this->relationships);
        
        // Also check database directly
        if ($this->project) {
            $dbRelationships = $this->project->relationships()->get();
            \Log::info('DB Relationships count: ' . $dbRelationships->count());
            \Log::info('DB Relationships:', $dbRelationships->toArray());
        }
        
        Notification::make()
            ->title('Debug Complete')
            ->body('Check the Laravel log for relationship debug info. Tables: ' . count($this->tables) . ', Relationships: ' . count($this->relationships))
            ->info()
            ->send();
    }

    public function deleteProject(): void
    {
        if (!$this->project) {
            Notification::make()
                ->title('No Project')
                ->body('No project is currently loaded.')
                ->warning()
                ->send();
            return;
        }

        // Super admins can delete any project, regular users only their own
        if (!auth()->user()->hasRole('Super Admin') && $this->project->created_by !== auth()->id()) {
            Notification::make()
                ->title('Access Denied')
                ->body('You can only delete projects you created.')
                ->danger()
                ->send();
            return;
        }

        try {
            $projectName = $this->project->name;
            $projectId = $this->project->id;

            // Delete all relationships first
            $this->project->relationships()->delete();
            
            // Delete all tables (this will cascade to fields)
            $this->project->tables()->delete();
            
            // Delete the project itself
            $this->project->delete();

            Notification::make()
                ->title('Project Deleted')
                ->body("Project '{$projectName}' has been deleted successfully.")
                ->success()
                ->send();

            // Redirect to ERD Designer page
            $this->redirect('/admin/erd-designer-page');

        } catch (\Exception $e) {
            Notification::make()
                ->title('Delete Failed')
                ->body('Error deleting project: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function clearProjectData(): void
    {
        if (!$this->project) {
            Notification::make()
                ->title('No Project')
                ->body('No project is currently loaded.')
                ->warning()
                ->send();
            return;
        }

        // Super admins can clear any project, regular users only their own
        if (!auth()->user()->hasRole('Super Admin') && $this->project->created_by !== auth()->id()) {
            Notification::make()
                ->title('Access Denied')
                ->body('You can only clear projects you created.')
                ->danger()
                ->send();
            return;
        }

        try {
            \Log::info('Clearing project data', [
                'project_id' => $this->project->id,
                'tables_before' => $this->project->tables()->count(),
                'relationships_before' => $this->project->relationships()->count()
            ]);

            // Delete all relationships first
            $this->project->relationships()->delete();
            
            // Delete all tables (this will cascade to fields)
            $this->project->tables()->delete();

            // Clear local arrays to prevent any cached data issues
            $this->tables = [];
            $this->relationships = [];

            // Set flag to prevent automatic demo creation
            $this->skipDemoCreation = true;

            // Reload project data to confirm empty state
            $this->loadProjectData();

            // Dispatch browser event to refresh frontend data
            $this->dispatch('dataUpdated', [
                'tables' => $this->tables,
                'relationships' => $this->relationships
            ]);

            \Log::info('Project data cleared successfully', [
                'project_id' => $this->project->id,
                'tables_after' => $this->project->tables()->count(),
                'relationships_after' => $this->project->relationships()->count()
            ]);

            Notification::make()
                ->title('Project Cleared')
                ->body('All tables and relationships have been removed from the project.')
                ->success()
                ->send();

        } catch (\Exception $e) {
            \Log::error('Failed to clear project data', [
                'project_id' => $this->project->id,
                'error' => $e->getMessage()
            ]);

            Notification::make()
                ->title('Clear Failed')
                ->body('Error clearing project: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getTitle(): string
    {
        return $this->project ?
            "ERD Designer - {$this->project->name}" :
            'ERD Designer';
    }
}

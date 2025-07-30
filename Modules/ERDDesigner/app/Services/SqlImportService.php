<?php

namespace Modules\ERDDesigner\app\Services;

use Modules\ERDDesigner\app\Models\ErdProject;
use Modules\ERDDesigner\app\Models\ErdTable;
use Modules\ERDDesigner\app\Models\ErdField;
use Modules\ERDDesigner\app\Models\ErdRelationship;
use Modules\ERDDesigner\app\Models\ErdIndex;

class SqlImportService
{
    private array $parsedTables = [];
    private array $parsedRelationships = [];
    private array $tablePositions = []; // Store calculated positions
    private int $canvasWidth = 2400; // Optimized canvas width
    private int $canvasHeight = 1600; // Optimized canvas height
    private int $tableWidth = 220;
    private int $tableHeight = 180;
    private int $padding = 60; // Reduced padding for more compact layout

    /**
     * Import SQL into ERD project
     */
    public function importToProject(ErdProject $project, string $sql): void
    {
        try {
            \DB::beginTransaction();
            
            // Clear existing data to prevent duplicates
            \Log::info('Clearing existing project data before import', [
                'project_id' => $project->id,
                'existing_tables' => $project->tables()->count(),
                'existing_relationships' => $project->relationships()->count()
            ]);
            
            // Force delete all relationships first (including soft deleted ones)
            $deletedRelationships = $project->relationships()->withTrashed()->forceDelete();
            
            // Force delete all fields and indexes through tables
            foreach ($project->tables()->withTrashed()->get() as $table) {
                $table->fields()->withTrashed()->forceDelete();
                $table->indexes()->withTrashed()->forceDelete();
            }
            
            // Force delete all tables (including soft deleted ones)
            $deletedTables = $project->tables()->withTrashed()->forceDelete();
            
            \Log::info('Cleared project data', [
                'project_id' => $project->id,
                'deleted_tables' => $deletedTables,
                'deleted_relationships' => $deletedRelationships
            ]);

            // Parse SQL
            $this->parseSql($sql);

            // Create tables and fields
            $this->createTables($project);

            // Create relationships
            $this->createRelationships($project);

            // Update project
            $project->update([
                'updated_by' => auth()->id()
            ]);
            
            \DB::commit();
            
            \Log::info('SQL import completed successfully', [
                'project_id' => $project->id,
                'tables_created' => count($this->parsedTables),
                'relationships_created' => count($this->parsedRelationships)
            ]);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('SQL import failed', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Parse SQL string
     */
    private function parseSql(string $sql): void
    {
        // Clean up SQL
        $sql = $this->cleanSql($sql);

        // Parse CREATE TABLE statements
        $this->parseCreateTables($sql);

        // Parse ALTER TABLE statements for foreign keys
        $this->parseAlterTables($sql);
    }

    /**
     * Clean SQL string
     */
    private function cleanSql(string $sql): string
    {
        // Remove comments
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

        // Remove extra whitespace
        $sql = preg_replace('/\s+/', ' ', $sql);

        return trim($sql);
    }

    /**
     * Parse CREATE TABLE statements
     */
    private function parseCreateTables(string $sql): void
    {
        $pattern = '/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?\s*\((.*?)\)(?:\s*ENGINE\s*=\s*(\w+))?(?:\s*DEFAULT\s+CHARSET\s*=\s*(\w+))?(?:\s*COLLATE\s*=\s*(\w+))?(?:\s*COMMENT\s*=\s*[\'"]([^\'"]*)[\'"])?/is';

        preg_match_all($pattern, $sql, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $tableName = $match[1];
            $tableDefinition = $match[2];
            $engine = $match[3] ?? 'InnoDB';
            $charset = $match[4] ?? 'utf8mb4';
            $collation = $match[5] ?? 'utf8mb4_unicode_ci';
            $comment = $match[6] ?? '';

            $this->parsedTables[$tableName] = [
                'name' => $tableName,
                'engine' => $engine,
                'charset' => $charset,
                'collation' => $collation,
                'comment' => $comment,
                'fields' => $this->parseTableFields($tableDefinition),
                'indexes' => $this->parseTableIndexes($tableDefinition)
            ];
        }
        
        // Calculate intelligent positions for all tables
        $this->calculateIntelligentPositions();
    }

    /**
     * Calculate intelligent positions for all tables based on relationships
     */
    private function calculateIntelligentPositions(): void
    {
        $tableNames = array_keys($this->parsedTables);
        $totalTables = count($tableNames);
        
        if ($totalTables === 0) {
            return;
        }

        // Step 1: Create relationship graph
        $relationshipGraph = $this->buildRelationshipGraph();
        
        // Step 2: Find central tables (most connected)
        $centralTables = $this->findCentralTables($relationshipGraph);
        
        // Step 3: Apply intelligent layout algorithm
        $this->applyIntelligentLayout($centralTables, $relationshipGraph);
        
        // Step 4: Apply positions to parsed tables
        foreach ($this->parsedTables as $tableName => &$tableData) {
            $position = $this->tablePositions[$tableName] ?? [
                'x' => 100 + (array_search($tableName, $tableNames) % 5) * ($this->tableWidth + $this->padding),
                'y' => 100 + floor(array_search($tableName, $tableNames) / 5) * ($this->tableHeight + $this->padding)
            ];
            
            $tableData['position_x'] = $position['x'];
            $tableData['position_y'] = $position['y'];
        }
    }

    /**
     * Build relationship graph from parsed relationships
     */
    private function buildRelationshipGraph(): array
    {
        $graph = [];
        
        // Initialize all tables in graph
        foreach (array_keys($this->parsedTables) as $tableName) {
            $graph[$tableName] = [];
        }
        
        // Add relationships
        foreach ($this->parsedRelationships as $rel) {
            $source = $rel['source_table'];
            $target = $rel['target_table'];
            
            if (isset($graph[$source]) && isset($graph[$target])) {
                $graph[$source][] = $target;
                $graph[$target][] = $source; // Bidirectional for positioning
            }
        }
        
        return $graph;
    }

    /**
     * Find central tables (tables with most relationships)
     */
    private function findCentralTables(array $graph): array
    {
        $connectionCounts = [];
        
        foreach ($graph as $table => $connections) {
            $connectionCounts[$table] = count(array_unique($connections));
        }
        
        arsort($connectionCounts);
        
        return array_keys($connectionCounts);
    }

    /**
     * Apply intelligent layout using enhanced space-efficient algorithm
     */
    private function applyIntelligentLayout(array $centralTables, array $graph): void
    {
        $positions = [];
        
        // Step 1: Separate connected and isolated tables
        $connectedTables = [];
        $isolatedTables = [];
        
        foreach ($graph as $table => $connections) {
            if (count($connections) > 0) {
                $connectedTables[] = $table;
            } else {
                $isolatedTables[] = $table;
            }
        }
        
        // Step 2: Calculate optimal layout based on table counts
        $totalTables = count($this->parsedTables);
        $connectedCount = count($connectedTables);
        $isolatedCount = count($isolatedTables);
        
        if ($connectedCount > 0) {
            // Use most of the space for connected tables
            $positions = array_merge($positions, $this->layoutConnectedTablesCompact($connectedTables, $graph));
        }
        
        if ($isolatedCount > 0) {
            // Use remaining space efficiently for isolated tables
            $positions = array_merge($positions, $this->layoutIsolatedTablesCompact($isolatedTables, $connectedCount > 0));
        }
        
        $this->tablePositions = $positions;
    }

    /**
     * Layout connected tables using compact circular positioning
     */
    private function layoutConnectedTablesCompact(array $connectedTables, array $graph): array
    {
        $positions = [];
        
        // Find the most central table
        $connectionCounts = [];
        foreach ($connectedTables as $table) {
            $connectionCounts[$table] = count(array_unique($graph[$table]));
        }
        arsort($connectionCounts);
        $centralTable = array_keys($connectionCounts)[0];
        
        // Position central table in left-center area (not dead center)
        $centerX = $this->canvasWidth * 0.3; // 30% from left instead of 50%
        $centerY = $this->canvasHeight * 0.5;
        $positions[$centralTable] = ['x' => (int)$centerX, 'y' => (int)$centerY];
        
        // Use compact circular layout
        $positioned = [$centralTable];
        $queue = [$centralTable];
        $level = 1;
        $baseRadius = 200; // Reduced radius for compact layout
        
        while (!empty($queue) && count($positioned) < count($connectedTables)) {
            $currentLevelQueue = [];
            $currentRadius = $baseRadius * $level;
            
            foreach ($queue as $currentTable) {
                $unpositionedConnections = array_filter($graph[$currentTable], function($connectedTable) use ($positioned) {
                    return !in_array($connectedTable, $positioned);
                });
                
                if (!empty($unpositionedConnections)) {
                    $connectionCount = count($unpositionedConnections);
                    $angleStep = (2 * M_PI) / max($connectionCount, 3);
                    $startAngle = $level * 0.3; // Smaller offset for compactness
                    
                    foreach ($unpositionedConnections as $index => $connectedTable) {
                        if (!in_array($connectedTable, $positioned)) {
                            $angle = $startAngle + $index * $angleStep;
                            $x = $centerX + $currentRadius * cos($angle);
                            $y = $centerY + $currentRadius * sin($angle);
                            
                            // Keep within left 70% of canvas for connected tables
                            $maxX = $this->canvasWidth * 0.7;
                            $x = max($this->padding, min($maxX - $this->tableWidth, $x));
                            $y = max($this->padding, min($this->canvasHeight - $this->tableHeight - $this->padding, $y));
                            
                            $positions[$connectedTable] = ['x' => (int)$x, 'y' => (int)$y];
                            $positioned[] = $connectedTable;
                            $currentLevelQueue[] = $connectedTable;
                        }
                    }
                }
            }
            
            $queue = array_unique($currentLevelQueue);
            $level++;
            
            // Prevent infinite loops
            if ($level > 5) break;
        }
        
        return $positions;
    }

    /**
     * Layout isolated tables in compact grid using available space
     */
    private function layoutIsolatedTablesCompact(array $isolatedTables, bool $hasConnectedTables): array
    {
        $positions = [];
        
        if (empty($isolatedTables)) {
            return $positions;
        }
        
        // Use right side efficiently
        $startX = $hasConnectedTables ? $this->canvasWidth * 0.72 : $this->padding; // Start at 72% if connected tables exist
        $startY = $this->padding;
        $availableWidth = $this->canvasWidth - $startX - $this->padding;
        $tablesPerRow = max(1, floor($availableWidth / ($this->tableWidth + $this->padding)));
        
        $currentX = $startX;
        $currentY = $startY;
        $tablesInCurrentRow = 0;
        
        foreach ($isolatedTables as $table) {
            $positions[$table] = [
                'x' => (int)$currentX,
                'y' => (int)$currentY
            ];
            
            $tablesInCurrentRow++;
            $currentX += $this->tableWidth + $this->padding;
            
            // Move to next row if needed
            if ($tablesInCurrentRow >= $tablesPerRow || $currentX + $this->tableWidth > $this->canvasWidth - $this->padding) {
                $currentX = $startX;
                $currentY += $this->tableHeight + $this->padding;
                $tablesInCurrentRow = 0;
            }
        }
        
        return $positions;
    }

    /**
     * Parse table fields from CREATE TABLE definition
     */
    private function parseTableFields(string $definition): array
    {
        $fields = [];
        $lines = explode(',', $definition);

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip constraints and indexes
            if (preg_match('/^\s*(PRIMARY\s+KEY|KEY|UNIQUE|INDEX|CONSTRAINT|FOREIGN\s+KEY)/i', $line)) {
                continue;
            }

            // Parse field definition
            if (preg_match('/`?(\w+)`?\s+(\w+)(?:\(([^)]+)\))?\s*(.*)/i', $line, $matches)) {
                $fieldName = $matches[1];
                $fieldType = strtolower($matches[2]);
                $fieldParams = $matches[3] ?? '';
                $fieldOptions = $matches[4] ?? '';

                $field = [
                    'name' => $fieldName,
                    'type' => $fieldType,
                    'length' => null,
                    'precision' => null,
                    'scale' => null,
                    'is_nullable' => !preg_match('/NOT\s+NULL/i', $fieldOptions),
                    'is_primary' => false,
                    'is_unique' => false,
                    'is_auto_increment' => preg_match('/AUTO_INCREMENT/i', $fieldOptions),
                    'is_foreign_key' => false,
                    'default_value' => null,
                    'comment' => ''
                ];

                // Parse field parameters
                if ($fieldParams) {
                    if (in_array($fieldType, ['varchar', 'char', 'varbinary', 'binary'])) {
                        $field['length'] = (int)$fieldParams;
                    } elseif ($fieldType === 'decimal') {
                        $params = explode(',', $fieldParams);
                        $field['precision'] = (int)($params[0] ?? 0);
                        $field['scale'] = (int)($params[1] ?? 0);
                    } elseif (in_array($fieldType, ['float', 'double'])) {
                        $params = explode(',', $fieldParams);
                        $field['precision'] = (int)($params[0] ?? 0);
                        if (isset($params[1])) {
                            $field['scale'] = (int)$params[1];
                        }
                    }
                }

                // Parse default value
                if (preg_match('/DEFAULT\s+([^,\s]+)/i', $fieldOptions, $defaultMatch)) {
                    $defaultValue = trim($defaultMatch[1], '\'"');
                    if (strtolower($defaultValue) !== 'null') {
                        $field['default_value'] = $defaultValue;
                    }
                }

                // Parse comment
                if (preg_match('/COMMENT\s+[\'"]([^\'"]*)[\'"]/', $fieldOptions, $commentMatch)) {
                    $field['comment'] = $commentMatch[1];
                }

                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * Parse table indexes from CREATE TABLE definition
     */
    private function parseTableIndexes(string $definition): array
    {
        $indexes = [];
        $lines = explode(',', $definition);

        foreach ($lines as $line) {
            $line = trim($line);

            // Parse PRIMARY KEY
            if (preg_match('/PRIMARY\s+KEY\s*\(\s*`?([^)]+)`?\s*\)/i', $line, $matches)) {
                $fields = array_map('trim', explode(',', str_replace('`', '', $matches[1])));
                $indexes[] = [
                    'name' => 'PRIMARY',
                    'type' => 'PRIMARY',
                    'fields' => $fields,
                    'is_unique' => true,
                    'is_primary' => true
                ];

                // Mark fields as primary
                foreach ($fields as $fieldName) {
                    $this->markFieldAsPrimary($fieldName);
                }
            }

            // Parse UNIQUE KEY
            elseif (preg_match('/UNIQUE\s+KEY\s+`?(\w+)`?\s*\(\s*`?([^)]+)`?\s*\)/i', $line, $matches)) {
                $indexName = $matches[1];
                $fields = array_map('trim', explode(',', str_replace('`', '', $matches[2])));
                $indexes[] = [
                    'name' => $indexName,
                    'type' => 'UNIQUE',
                    'fields' => $fields,
                    'is_unique' => true,
                    'is_primary' => false
                ];
            }

            // Parse regular KEY/INDEX
            elseif (preg_match('/(?:KEY|INDEX)\s+`?(\w+)`?\s*\(\s*`?([^)]+)`?\s*\)/i', $line, $matches)) {
                $indexName = $matches[1];
                $fields = array_map('trim', explode(',', str_replace('`', '', $matches[2])));
                $indexes[] = [
                    'name' => $indexName,
                    'type' => 'INDEX',
                    'fields' => $fields,
                    'is_unique' => false,
                    'is_primary' => false
                ];
            }
        }

        return $indexes;
    }

    /**
     * Mark field as primary key
     */
    private function markFieldAsPrimary(string $fieldName): void
    {
        foreach ($this->parsedTables as &$table) {
            foreach ($table['fields'] as &$field) {
                if ($field['name'] === $fieldName) {
                    $field['is_primary'] = true;
                    break 2;
                }
            }
        }
    }

    /**
     * Parse ALTER TABLE statements for foreign keys
     */
    private function parseAlterTables(string $sql): void
    {
        $pattern = '/ALTER\s+TABLE\s+`?(\w+)`?\s+ADD\s+(?:CONSTRAINT\s+`?(\w+)`?\s+)?FOREIGN\s+KEY\s*\(\s*`?(\w+)`?\s*\)\s+REFERENCES\s+`?(\w+)`?\s*\(\s*`?(\w+)`?\s*\)(?:\s+ON\s+UPDATE\s+(\w+))?(?:\s+ON\s+DELETE\s+(\w+))?/i';

        preg_match_all($pattern, $sql, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $sourceTable = $match[1];
            $constraintName = $match[2] ?? "fk_{$sourceTable}_{$match[3]}";
            $sourceField = $match[3];
            $targetTable = $match[4];
            $targetField = $match[5];
            $onUpdate = $match[6] ?? 'RESTRICT';
            $onDelete = $match[7] ?? 'RESTRICT';

            $this->parsedRelationships[] = [
                'name' => $constraintName,
                'type' => 'many_to_one',
                'source_table' => $sourceTable,
                'target_table' => $targetTable,
                'source_field' => $sourceField,
                'target_field' => $targetField,
                'on_update' => strtoupper($onUpdate),
                'on_delete' => strtoupper($onDelete)
            ];

            // Mark source field as foreign key
            $this->markFieldAsForeignKey($sourceTable, $sourceField);
        }
    }

    /**
     * Mark field as foreign key
     */
    private function markFieldAsForeignKey(string $tableName, string $fieldName): void
    {
        if (isset($this->parsedTables[$tableName])) {
            foreach ($this->parsedTables[$tableName]['fields'] as &$field) {
                if ($field['name'] === $fieldName) {
                    $field['is_foreign_key'] = true;
                    break;
                }
            }
        }
    }

    /**
     * Create tables in the project
     */
    private function createTables(ErdProject $project): void
    {
        foreach ($this->parsedTables as $tableData) {
            try {
                // Double-check for existing table with same name (including soft deleted)
                $existingTable = ErdTable::withTrashed()
                    ->where('project_id', $project->id)
                    ->where('name', $tableData['name'])
                    ->first();
                
                if ($existingTable) {
                    \Log::warning('Found existing table during import, force deleting it', [
                        'project_id' => $project->id,
                        'table_name' => $tableData['name'],
                        'existing_table_id' => $existingTable->id,
                        'is_soft_deleted' => !is_null($existingTable->deleted_at)
                    ]);
                    $existingTable->forceDelete();
                }

                $table = ErdTable::create([
                    'project_id' => $project->id,
                    'name' => $tableData['name'],
                    'display_name' => ucfirst($tableData['name']),
                    'description' => $tableData['comment'],
                    'position_x' => $tableData['position_x'],
                    'position_y' => $tableData['position_y'],
                    'width' => 200,
                    'height' => 150,
                    'color' => '#ffffff',
                    'engine' => $tableData['engine'],
                    'charset' => $tableData['charset'],
                    'collation' => $tableData['collation'],
                    'comment' => $tableData['comment']
                ]);

                // Create fields
                foreach ($tableData['fields'] as $index => $fieldData) {
                    ErdField::create(array_merge($fieldData, [
                        'table_id' => $table->id,
                        'display_name' => ucfirst($fieldData['name']),
                        'order' => $index,
                        'form_type' => $this->getFormType($fieldData['type']),
                        'validation_rules' => $this->getValidationRules($fieldData)
                    ]));
                }

                // Create indexes
                foreach ($tableData['indexes'] as $indexData) {
                    ErdIndex::create(array_merge($indexData, [
                        'table_id' => $table->id
                    ]));
                }
                
                \Log::info('Created table from SQL import', [
                    'table_name' => $tableData['name'],
                    'table_id' => $table->id,
                    'fields_count' => count($tableData['fields'])
                ]);
                
            } catch (\Exception $e) {
                \Log::error('Failed to create table from SQL import', [
                    'table_name' => $tableData['name'],
                    'error' => $e->getMessage()
                ]);
                throw new \Exception("Failed to create table '{$tableData['name']}': " . $e->getMessage());
            }
        }
    }

    /**
     * Create relationships in the project
     */
    private function createRelationships(ErdProject $project): void
    {
        foreach ($this->parsedRelationships as $relData) {
            $sourceTable = ErdTable::where('project_id', $project->id)
                ->where('name', $relData['source_table'])
                ->first();

            $targetTable = ErdTable::where('project_id', $project->id)
                ->where('name', $relData['target_table'])
                ->first();

            if ($sourceTable && $targetTable) {
                $sourceField = ErdField::where('table_id', $sourceTable->id)
                    ->where('name', $relData['source_field'])
                    ->first();

                $targetField = ErdField::where('table_id', $targetTable->id)
                    ->where('name', $relData['target_field'])
                    ->first();

                if ($sourceField && $targetField) {
                    // Handle empty on_update and on_delete values
                    $onUpdate = !empty($relData['on_update']) ? $relData['on_update'] : 'RESTRICT';
                    $onDelete = !empty($relData['on_delete']) ? $relData['on_delete'] : 'RESTRICT';
                    
                    // Validate enum values
                    $validActions = ['RESTRICT', 'CASCADE', 'SET NULL', 'NO ACTION', 'SET DEFAULT'];
                    if (!in_array($onUpdate, $validActions)) {
                        $onUpdate = 'RESTRICT';
                    }
                    if (!in_array($onDelete, $validActions)) {
                        $onDelete = 'RESTRICT';
                    }

                    ErdRelationship::create([
                        'project_id' => $project->id,
                        'name' => $relData['name'],
                        'type' => $relData['type'],
                        'source_table_id' => $sourceTable->id,
                        'target_table_id' => $targetTable->id,
                        'source_field_id' => $sourceField->id,
                        'target_field_id' => $targetField->id,
                        'on_update' => $onUpdate,
                        'on_delete' => $onDelete,
                        'cardinality_source' => 'zero_or_many',
                        'cardinality_target' => 'exactly_one'
                    ]);
                }
            }
        }
    }

    /**
     * Get appropriate form type for field type
     */
    private function getFormType(string $fieldType): string
    {
        $typeMap = [
            'varchar' => 'text',
            'char' => 'text',
            'text' => 'textarea',
            'mediumtext' => 'textarea',
            'longtext' => 'textarea',
            'int' => 'number',
            'bigint' => 'number',
            'tinyint' => 'number',
            'smallint' => 'number',
            'mediumint' => 'number',
            'decimal' => 'number',
            'float' => 'number',
            'double' => 'number',
            'date' => 'date',
            'datetime' => 'datetime',
            'timestamp' => 'datetime',
            'time' => 'time',
            'year' => 'number',
            'enum' => 'select',
            'set' => 'select',
            'json' => 'json',
            'boolean' => 'toggle'
        ];

        return $typeMap[$fieldType] ?? 'text';
    }

    /**
     * Get validation rules for field
     */
    private function getValidationRules(array $fieldData): string
    {
        $rules = [];

        if (!$fieldData['is_nullable']) {
            $rules[] = 'required';
        }

        if ($fieldData['length'] && in_array($fieldData['type'], ['varchar', 'char'])) {
            $rules[] = "max:{$fieldData['length']}";
        }

        if (in_array($fieldData['type'], ['int', 'bigint', 'tinyint', 'smallint', 'mediumint'])) {
            $rules[] = 'integer';
        }

        if (in_array($fieldData['type'], ['decimal', 'float', 'double'])) {
            $rules[] = 'numeric';
        }

        if ($fieldData['type'] === 'email') {
            $rules[] = 'email';
        }

        if ($fieldData['type'] === 'url') {
            $rules[] = 'url';
        }

        return implode('|', $rules);
    }
}

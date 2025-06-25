<?php

namespace Modules\ModuleBuilder\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Modules\ModuleBuilder\Models\ModuleTable;
use Modules\ModuleBuilder\Models\ModuleField;

class MigrationService
{
    /**
     * Generate a new migration for adding/modifying fields
     */
    public function generateFieldMigration(ModuleTable $table, array $newFields = [], array $modifiedFields = [], array $deletedFields = []): string
    {
        // Check if this is a new table (no migration exists yet)
        $isNewTable = $this->isNewTable($table);
        
        if ($isNewTable) {
            return $this->generateCreateTableMigration($table);
        } else {
            return $this->generateModifyTableMigration($table, $newFields, $modifiedFields, $deletedFields);
        }
    }
    
    /**
     * Check if this is a new table that doesn't have a migration yet
     */
    private function isNewTable(ModuleTable $table): bool
    {
        $migrationPath = base_path("Modules/{$table->moduleProject->name}/database/migrations");
        
        if (!File::exists($migrationPath)) {
            return true;
        }
        
        // Check if there's already a create migration for this table
        $files = File::files($migrationPath);
        foreach ($files as $file) {
            if (str_contains($file->getFilename(), "create_{$table->name}_table")) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Generate migration for creating a new table
     */
    private function generateCreateTableMigration(ModuleTable $table): string
    {
        $migrationName = 'create_' . $table->name . '_table';
        $timestamp = date('Y_m_d_His');
        
        $migrationPath = base_path("Modules/{$table->moduleProject->name}/database/migrations");
        
        if (!File::exists($migrationPath)) {
            File::makeDirectory($migrationPath, 0755, true);
        }
        
        $migrationFile = $migrationPath . '/' . $timestamp . '_' . $migrationName . '.php';
        
        $migrationContent = $this->generateCreateTableContent($table);
        
        File::put($migrationFile, $migrationContent);
        
        return $migrationFile;
    }
    
    /**
     * Generate migration for modifying an existing table
     */
    private function generateModifyTableMigration(ModuleTable $table, array $newFields, array $modifiedFields, array $deletedFields): string
    {
        $migrationName = 'modify_' . $table->name . '_table_' . date('Y_m_d_His');
        $className = Str::studly($migrationName);
        
        $migrationPath = base_path("Modules/{$table->moduleProject->name}/database/migrations");
        
        if (!File::exists($migrationPath)) {
            File::makeDirectory($migrationPath, 0755, true);
        }
        
        $migrationFile = $migrationPath . '/' . date('Y_m_d_His') . '_' . $migrationName . '.php';
        
        $migrationContent = $this->generateMigrationContent($className, $table, $newFields, $modifiedFields, $deletedFields);
        
        File::put($migrationFile, $migrationContent);
        
        return $migrationFile;
    }
    
    /**
     * Generate migration content for field changes
     */
    private function generateMigrationContent(string $className, ModuleTable $table, array $newFields, array $modifiedFields, array $deletedFields): string
    {
        $upOperations = [];
        $downOperations = [];
        
        // Add new fields
        foreach ($newFields as $field) {
            $upOperations[] = $this->generateAddColumnOperation($field);
            $downOperations[] = "\$table->dropColumn('{$field['name']}');";
        }
        
        // Modify existing fields
        foreach ($modifiedFields as $field) {
            $upOperations[] = $this->generateModifyColumnOperation($field);
            // For down operation, you'd need to store the original field definition
            $downOperations[] = "// TODO: Revert {$field['name']} to original definition";
        }
        
        // Drop fields
        foreach ($deletedFields as $field) {
            $upOperations[] = "\$table->dropColumn('{$field['name']}');";
            $downOperations[] = $this->generateAddColumnOperation($field);
        }
        
        $upCode = implode("\n            ", $upOperations);
        $downCode = implode("\n            ", array_reverse($downOperations));
        
        return "<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('{$table->name}', function (Blueprint \$table) {
            {$upCode}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('{$table->name}', function (Blueprint \$table) {
            {$downCode}
        });
    }
};";
    }
    
    /**
     * Generate add column operation for a field
     */
    private function generateAddColumnOperation(array $field): string
    {
        $columnType = isset($field['database_type']) ? $field['database_type'] : (isset($field['type']) ? $field['type'] : null);
        // Special handling for enum: always require enum_values
        if ($columnType === 'enum') {
            if (empty($field['enum_values'])) {
                throw new \Exception("Enum field '{$field['name']}' must have 'enum_values' defined.");
            }
            $enumValues = $field['enum_values'];
            if (is_string($enumValues)) {
                // Try to decode JSON, fallback to comma split
                $decoded = json_decode($enumValues, true);
                if (is_array($decoded)) {
                    $enumValues = $decoded;
                } else {
                    $enumValues = array_map('trim', explode(',', $enumValues));
                }
            }
            if (!is_array($enumValues) || empty($enumValues)) {
                throw new \Exception("Enum field '{$field['name']}' has invalid 'enum_values'.");
            }
            $enumValuesPhp = '[' . implode(", ", array_map(function($v) { return var_export($v, true); }, $enumValues)) . ']';
            $column = "\$table->enum('{$field['name']}', {$enumValuesPhp})";
        } else {
            $column = "\$table->{$columnType}('{$field['name']}'";
            // Add length for string fields
            if (in_array($columnType, ['string', 'char']) && !empty($field['length'])) {
                $column .= ", {$field['length']}";
            }
            // Add precision and scale for decimal fields
            if ($columnType === 'decimal' && !empty($field['precision'])) {
                $scale = $field['scale'] ?? 2;
                $column .= ", {$field['precision']}, {$scale}";
            }
            $column .= ')';
        }

        // Add modifiers
        if (!empty($field['nullable']) && $field['nullable']) {
            $column .= '->nullable()';
        }
        
        if (!empty($field['unique']) && $field['unique']) {
            $column .= '->unique()';
        }
        
        if (!empty($field['default_value'])) {
            $defaultValue = is_string($field['default_value']) 
                ? "'{$field['default_value']}'" 
                : $field['default_value'];
            $column .= "->default({$defaultValue})";
        }
        
        if (!empty($field['unsigned']) && $field['unsigned']) {
            $column .= '->unsigned()';
        }
        
        if (!empty($field['index']) && $field['index']) {
            $column .= '->index()';
        }
        
        return $column . ';';
    }
    
    /**
     * Generate modify column operation
     */
    private function generateModifyColumnOperation(array $field): string
    {
        // For Laravel, modifying columns requires the doctrine/dbal package
        return $this->generateAddColumnOperation($field) . ' // Note: This modifies existing column';
    }
    
    /**
     * Run a specific migration file
     */
    public function runMigration(string $migrationFile): bool
    {
        try {
            // Extract the migration name from the file path
            $migrationName = basename($migrationFile, '.php');
            
            // Run the specific migration
            Artisan::call('migrate', [
                '--path' => str_replace(base_path(), '', dirname($migrationFile)),
                '--force' => true
            ]);
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Migration failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate and run migration for field changes
     */
    public function handleFieldChanges(ModuleTable $table): bool
    {
        try {
            // Get current fields from database
            $currentFields = $table->fields->keyBy('name')->toArray();
            
            // Get fields from the form submission (this would be called from the resource)
            // For now, we'll just create a placeholder migration
            
            $migrationFile = $this->generateFieldMigration($table, [], [], []);
            
            return $this->runMigration($migrationFile);
        } catch (\Exception $e) {
            \Log::error('Field migration failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate a rollback migration
     */
    public function generateRollbackMigration(ModuleTable $table, array $previousFields): string
    {
        $migrationName = 'rollback_' . $table->name . '_table_' . date('Y_m_d_His');
        $className = Str::studly($migrationName);
        
        $migrationPath = base_path("Modules/{$table->moduleProject->name}/database/migrations");
        $migrationFile = $migrationPath . '/' . date('Y_m_d_His') . '_' . $migrationName . '.php';
        
        // Logic to compare current vs previous fields and generate rollback
        $migrationContent = $this->generateMigrationContent($className, $table, [], [], []);
        
        File::put($migrationFile, $migrationContent);
        
        return $migrationFile;
    }
    
    /**
     * Generate content for creating a new table
     */
    private function generateCreateTableContent(ModuleTable $table): string
    {
        $fields = $table->fields()->get();
        $fieldDefinitions = [];
        
        // Add ID field by default
        $fieldDefinitions[] = '$table->id();';
        
        // Add custom fields
        foreach ($fields as $field) {
            $fieldArray = $field->toArray();
            
            // Ensure enum_values is properly set for enum fields
            if (($field->type === 'enum' || $field->database_type === 'enum') && $field->enum_values) {
                $fieldArray['enum_values'] = $field->enum_values;
                $fieldArray['type'] = 'enum';
                $fieldArray['database_type'] = 'enum';
                
                // Debug: Log what we're passing
                \Log::info("Processing enum field '{$field->name}' with values:", [
                    'enum_values' => $field->enum_values,
                    'field_array_enum_values' => $fieldArray['enum_values']
                ]);
            }
            
            $fieldDefinitions[] = $this->generateCreateColumnOperation($fieldArray);
        }
        
        // Add timestamps by default
        $fieldDefinitions[] = '$table->timestamps();';
        
        $fieldsCode = implode("\n            ", $fieldDefinitions);
        
        return "<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('{$table->name}', function (Blueprint \$table) {
            {$fieldsCode}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('{$table->name}');
    }
};";
    }

    /**
     * Generate create column operation for a field (for new tables)
     */
    private function generateCreateColumnOperation(array $field): string
    {
        // Use database_type for the actual migration column type
        $columnType = $field['database_type'] ?? $field['type'];
        // Special handling for enum: always require enum_values
        if ($columnType === 'enum') {
            \Log::info("Processing enum column '{$field['name']}':", [
                'field' => $field,
                'enum_values' => $field['enum_values'] ?? 'NOT SET'
            ]);
            
            if (empty($field['enum_values'])) {
                throw new \Exception("Enum field '{$field['name']}' must have 'enum_values' defined.");
            }
            $enumValues = $field['enum_values'];
            if (is_string($enumValues)) {
                // Try to decode JSON, fallback to comma split
                $decoded = json_decode($enumValues, true);
                if (is_array($decoded)) {
                    $enumValues = $decoded;
                } else {
                    $enumValues = array_map('trim', explode(',', $enumValues));
                }
            }
            if (!is_array($enumValues) || empty($enumValues)) {
                throw new \Exception("Enum field '{$field['name']}' has invalid 'enum_values'.");
            }
            $enumValuesPhp = '[' . implode(", ", array_map(function($v) { return var_export($v, true); }, $enumValues)) . ']';
            $operation = "\$table->enum('{$field['name']}', {$enumValuesPhp})";
        } else {
            $operation = '$table->' . $columnType . "('" . $field['name'] . "'";
            // Add length for certain field types
            if (in_array($columnType, ['string', 'char']) && !empty($field['length'])) {
                $operation .= ', ' . $field['length'];
            }
            // Add precision and scale for decimal fields
            if (in_array($columnType, ['decimal', 'double', 'float']) && !empty($field['precision'])) {
                $operation .= ', ' . $field['precision'];
                if (!empty($field['scale'])) {
                    $operation .= ', ' . $field['scale'];
                }
            }
            $operation .= ')';
        }

        // Add modifiers
        if (!empty($field['nullable']) && $field['nullable']) {
            $operation .= '->nullable()';
        }
        
        if (!empty($field['default_value'])) {
            if (is_string($field['default_value'])) {
                $operation .= "->default('" . $field['default_value'] . "')";
            } else {
                $operation .= '->default(' . $field['default_value'] . ')';
            }
        }
        
        if (!empty($field['unique']) && $field['unique']) {
            $operation .= '->unique()';
        }
        
        if (!empty($field['index']) && $field['index']) {
            $operation .= '->index()';
        }
        
        $operation .= ';';
        
        return $operation;
    }
}

<?php

namespace Modules\ERDDesigner\app\Services;

use Modules\ERDDesigner\app\Models\ErdProject;
use Modules\ERDDesigner\app\Models\ErdTable;
use Modules\ERDDesigner\app\Models\ErdField;
use Modules\ERDDesigner\app\Models\ErdRelationship;

class SqlExportService
{
    /**
     * Export ERD project to SQL
     */
    public function exportProject(ErdProject $project): string
    {
        $sql = $this->generateHeader($project);
        $sql .= $this->generateTables($project);
        $sql .= $this->generateRelationships($project);
        $sql .= $this->generateIndexes($project);
        $sql .= $this->generateSeedData($project);
        
        return $sql;
    }

    /**
     * Generate SQL header
     */
    private function generateHeader(ErdProject $project): string
    {
        $sql = "-- =============================================\n";
        $sql .= "-- ERD Project: {$project->name}\n";
        $sql .= "-- Description: {$project->description}\n";
        $sql .= "-- Generated on: " . now()->format('Y-m-d H:i:s') . "\n";
        $sql .= "-- Database Type: {$project->database_type}\n";
        $sql .= "-- =============================================\n\n";
        
        if ($project->database_name) {
            $sql .= "-- Create database\n";
            $sql .= "CREATE DATABASE IF NOT EXISTS `{$project->database_name}`;\n";
            $sql .= "USE `{$project->database_name}`;\n\n";
        }
        
        $sql .= "-- Disable foreign key checks\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
        
        return $sql;
    }

    /**
     * Generate table creation SQL
     */
    private function generateTables(ErdProject $project): string
    {
        $sql = "-- =============================================\n";
        $sql .= "-- Table Structure\n";
        $sql .= "-- =============================================\n\n";
        
        foreach ($project->tables()->with('fields', 'indexes')->get() as $table) {
            $sql .= $this->generateTableSql($table);
            $sql .= "\n";
        }
        
        return $sql;
    }

    /**
     * Generate SQL for a single table
     */
    private function generateTableSql(ErdTable $table): string
    {
        $sql = "-- Table: {$table->name}\n";
        if ($table->description) {
            $sql .= "-- Description: {$table->description}\n";
        }
        $sql .= "DROP TABLE IF EXISTS `{$table->name}`;\n";
        $sql .= "CREATE TABLE `{$table->name}` (\n";
        
        $fieldSql = [];
        $primaryKeys = [];
        
        foreach ($table->fields()->orderBy('order')->get() as $field) {
            $fieldSql[] = "  " . $this->generateFieldSql($field);
            
            if ($field->is_primary) {
                $primaryKeys[] = $field->name;
            }
        }
        
        // Add primary key constraint
        if (!empty($primaryKeys)) {
            $fieldSql[] = "  PRIMARY KEY (`" . implode('`, `', $primaryKeys) . "`)";
        }
        
        // Add indexes
        foreach ($table->indexes as $index) {
            if ($index->type !== 'PRIMARY') {
                $fieldSql[] = "  " . $this->generateIndexSql($index);
            }
        }
        
        $sql .= implode(",\n", $fieldSql);
        $sql .= "\n)";
        
        // Add table options
        if ($table->engine) {
            $sql .= " ENGINE={$table->engine}";
        }
        
        if ($table->charset) {
            $sql .= " DEFAULT CHARSET={$table->charset}";
        }
        
        if ($table->collation) {
            $sql .= " COLLATE={$table->collation}";
        }
        
        if ($table->comment) {
            $sql .= " COMMENT='{$table->comment}'";
        }
        
        $sql .= ";\n";
        
        return $sql;
    }

    /**
     * Generate SQL for a field
     */
    private function generateFieldSql(ErdField $field): string
    {
        $sql = "`{$field->name}` " . strtoupper($field->type);
        
        // Add length/precision
        if ($field->length && in_array($field->type, ['varchar', 'char', 'varbinary', 'binary'])) {
            $sql .= "({$field->length})";
        } elseif ($field->precision && $field->scale && $field->type === 'decimal') {
            $sql .= "({$field->precision},{$field->scale})";
        } elseif ($field->precision && in_array($field->type, ['float', 'double'])) {
            $sql .= "({$field->precision}" . ($field->scale ? ",{$field->scale}" : '') . ")";
        }
        
        // Add nullable
        if (!$field->is_nullable) {
            $sql .= " NOT NULL";
        } else {
            $sql .= " NULL";
        }
        
        // Add auto increment
        if ($field->is_auto_increment) {
            $sql .= " AUTO_INCREMENT";
        }
        
        // Add default value
        if ($field->default_value !== null && $field->default_value !== '') {
            if (in_array($field->type, ['varchar', 'char', 'text', 'tinytext', 'mediumtext', 'longtext', 'enum', 'set'])) {
                $sql .= " DEFAULT '{$field->default_value}'";
            } elseif ($field->type === 'timestamp' && strtolower($field->default_value) === 'current_timestamp') {
                $sql .= " DEFAULT CURRENT_TIMESTAMP";
            } else {
                $sql .= " DEFAULT {$field->default_value}";
            }
        }
        
        // Add comment
        if ($field->comment) {
            $sql .= " COMMENT '{$field->comment}'";
        }
        
        return $sql;
    }

    /**
     * Generate SQL for an index
     */
    private function generateIndexSql($index): string
    {
        if (empty($index->fields)) {
            return '';
        }

        $fieldList = implode('`, `', $index->fields);
        
        switch ($index->type) {
            case 'UNIQUE':
                return "UNIQUE KEY `{$index->name}` (`{$fieldList}`)";
                
            case 'FULLTEXT':
                return "FULLTEXT KEY `{$index->name}` (`{$fieldList}`)";
                
            case 'SPATIAL':
                return "SPATIAL KEY `{$index->name}` (`{$fieldList}`)";
                
            default:
                return "KEY `{$index->name}` (`{$fieldList}`)";
        }
    }

    /**
     * Generate foreign key constraints
     */
    private function generateRelationships(ErdProject $project): string
    {
        $sql = "\n-- =============================================\n";
        $sql .= "-- Foreign Key Constraints\n";
        $sql .= "-- =============================================\n\n";
        
        foreach ($project->relationships()->with(['sourceTable', 'targetTable', 'sourceField', 'targetField'])->get() as $relationship) {
            if ($relationship->type === 'many_to_one' || $relationship->type === 'one_to_one') {
                $sql .= $this->generateRelationshipSql($relationship);
                $sql .= "\n";
            }
        }
        
        $sql .= "-- Enable foreign key checks\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n\n";
        
        return $sql;
    }

    /**
     * Generate SQL for a relationship
     */
    private function generateRelationshipSql(ErdRelationship $relationship): string
    {
        if (!$relationship->sourceField || !$relationship->targetField) {
            return '';
        }

        $constraintName = "fk_{$relationship->sourceTable->name}_{$relationship->sourceField->name}";
        
        $sql = "-- Relationship: {$relationship->sourceTable->name} -> {$relationship->targetTable->name}\n";
        $sql .= "ALTER TABLE `{$relationship->sourceTable->name}` ";
        $sql .= "ADD CONSTRAINT `{$constraintName}` ";
        $sql .= "FOREIGN KEY (`{$relationship->sourceField->name}`) ";
        $sql .= "REFERENCES `{$relationship->targetTable->name}` (`{$relationship->targetField->name}`)";
        
        if ($relationship->on_update && $relationship->on_update !== 'RESTRICT') {
            $sql .= " ON UPDATE {$relationship->on_update}";
        }
        
        if ($relationship->on_delete && $relationship->on_delete !== 'RESTRICT') {
            $sql .= " ON DELETE {$relationship->on_delete}";
        }
        
        $sql .= ";\n";
        
        return $sql;
    }

    /**
     * Generate additional indexes
     */
    private function generateIndexes(ErdProject $project): string
    {
        $sql = "-- =============================================\n";
        $sql .= "-- Additional Indexes\n";
        $sql .= "-- =============================================\n\n";
        
        // Add any additional indexes that weren't included in table creation
        foreach ($project->tables()->with('indexes')->get() as $table) {
            foreach ($table->indexes as $index) {
                if ($index->type !== 'PRIMARY') {
                    $sql .= "-- Index: {$index->name} on {$table->name}\n";
                    $sql .= "CREATE";
                    
                    if ($index->type === 'UNIQUE') {
                        $sql .= " UNIQUE";
                    } elseif ($index->type === 'FULLTEXT') {
                        $sql .= " FULLTEXT";
                    } elseif ($index->type === 'SPATIAL') {
                        $sql .= " SPATIAL";
                    }
                    
                    $sql .= " INDEX `{$index->name}` ON `{$table->name}` (`" . implode('`, `', $index->fields) . "`);\n\n";
                }
            }
        }
        
        return $sql;
    }

    /**
     * Generate seed data
     */
    private function generateSeedData(ErdProject $project): string
    {
        $sql = "-- =============================================\n";
        $sql .= "-- Sample Data (Optional)\n";
        $sql .= "-- =============================================\n\n";
        
        foreach ($project->tables()->with('seederData')->get() as $table) {
            if ($table->seederData->isNotEmpty()) {
                $sql .= "-- Sample data for {$table->name}\n";
                $sql .= $this->generateTableSeedData($table);
                $sql .= "\n";
            }
        }
        
        return $sql;
    }

    /**
     * Generate seed data for a table
     */
    private function generateTableSeedData(ErdTable $table): string
    {
        $seederData = $table->seederData()->where('is_active', true)->get();
        
        if ($seederData->isEmpty()) {
            return '';
        }
        
        $sql = "INSERT INTO `{$table->name}` (";
        $fields = $seederData->pluck('field_name')->toArray();
        $sql .= "`" . implode('`, `', $fields) . "`";
        $sql .= ") VALUES\n";
        
        $values = [];
        for ($i = 0; $i < 5; $i++) { // Generate 5 sample rows
            $rowValues = [];
            foreach ($seederData as $seeder) {
                $data = $seeder->generateData(1);
                $value = $data[0] ?? 'NULL';
                
                if (is_string($value) && $value !== 'NULL') {
                    $value = "'" . addslashes($value) . "'";
                }
                
                $rowValues[] = $value;
            }
            $values[] = "(" . implode(', ', $rowValues) . ")";
        }
        
        $sql .= implode(",\n", $values) . ";\n";
        
        return $sql;
    }
}

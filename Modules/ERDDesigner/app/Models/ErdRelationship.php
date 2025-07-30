<?php

namespace Modules\ERDDesigner\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErdRelationship extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'name',
        'type',
        'source_table_id',
        'target_table_id',
        'source_field_id',
        'target_field_id',
        'on_update',
        'on_delete',
        'is_identifying',
        'cardinality_source',
        'cardinality_target',
        'description',
        'settings'
    ];

    protected $casts = [
        'is_identifying' => 'boolean',
        'settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Relationship type constants
     */
    const RELATIONSHIP_TYPES = [
        'one_to_one' => 'One to One',
        'one_to_many' => 'One to Many',
        'many_to_one' => 'Many to One',
        'many_to_many' => 'Many to Many'
    ];

    /**
     * Laravel relationship type mapping
     */
    const LARAVEL_RELATIONSHIP_TYPES = [
        'one_to_one' => 'hasOne',
        'one_to_many' => 'hasMany',
        'many_to_one' => 'belongsTo',
        'many_to_many' => 'belongsToMany'
    ];

    /**
     * Foreign key action constants
     */
    const FK_ACTIONS = [
        'RESTRICT' => 'RESTRICT',
        'CASCADE' => 'CASCADE',
        'SET NULL' => 'SET NULL',
        'NO ACTION' => 'NO ACTION',
        'SET DEFAULT' => 'SET DEFAULT'
    ];

    /**
     * Cardinality constants
     */
    const CARDINALITIES = [
        'zero_or_one' => '0..1',
        'exactly_one' => '1',
        'zero_or_many' => '0..*',
        'one_or_many' => '1..*'
    ];

    /**
     * Get the ERD project this relationship belongs to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(ErdProject::class, 'project_id');
    }

    /**
     * Get the source table
     */
    public function sourceTable(): BelongsTo
    {
        return $this->belongsTo(ErdTable::class, 'source_table_id');
    }

    /**
     * Get the target table
     */
    public function targetTable(): BelongsTo
    {
        return $this->belongsTo(ErdTable::class, 'target_table_id');
    }

    /**
     * Get the source field
     */
    public function sourceField(): BelongsTo
    {
        return $this->belongsTo(ErdField::class, 'source_field_id');
    }

    /**
     * Get the target field
     */
    public function targetField(): BelongsTo
    {
        return $this->belongsTo(ErdField::class, 'target_field_id');
    }

    /**
     * Generate SQL foreign key constraint
     */
    public function generateSql(): string
    {
        if (!$this->sourceField || !$this->targetField) {
            return '';
        }

        $constraintName = "fk_{$this->sourceTable->name}_{$this->sourceField->name}";
        
        $sql = "ALTER TABLE `{$this->sourceTable->name}` ";
        $sql .= "ADD CONSTRAINT `{$constraintName}` ";
        $sql .= "FOREIGN KEY (`{$this->sourceField->name}`) ";
        $sql .= "REFERENCES `{$this->targetTable->name}` (`{$this->targetField->name}`)";
        
        if ($this->on_update) {
            $sql .= " ON UPDATE {$this->on_update}";
        }
        
        if ($this->on_delete) {
            $sql .= " ON DELETE {$this->on_delete}";
        }
        
        $sql .= ";";
        
        return $sql;
    }

    /**
     * Get Laravel relationship method name
     */
    public function getLaravelRelationshipName(): string
    {
        if ($this->type === 'many_to_many') {
            return str()->plural($this->targetTable->name);
        }
        
        return str()->camel($this->targetTable->name);
    }

    /**
     * Get Laravel relationship type
     */
    public function getLaravelRelationshipType(): string
    {
        return self::LARAVEL_RELATIONSHIP_TYPES[$this->type] ?? 'belongsTo';
    }

    /**
     * Generate Laravel relationship method
     */
    public function generateLaravelRelationship(): string
    {
        $relationshipName = $this->getLaravelRelationshipName();
        $relationType = $this->getLaravelRelationshipType();
        $targetModel = str()->studly(str()->singular($this->targetTable->name));
        
        $method = "public function {$relationshipName}()\n{\n";
        
        switch ($this->type) {
            case 'one_to_one':
                $method .= "    return \$this->hasOne({$targetModel}::class, '{$this->sourceField->name}');";
                break;
                
            case 'one_to_many':
                $method .= "    return \$this->hasMany({$targetModel}::class, '{$this->sourceField->name}');";
                break;
                
            case 'many_to_one':
                $method .= "    return \$this->belongsTo({$targetModel}::class, '{$this->sourceField->name}');";
                break;
                
            case 'many_to_many':
                $pivotTable = $this->generatePivotTableName();
                $method .= "    return \$this->belongsToMany({$targetModel}::class, '{$pivotTable}');";
                break;
        }
        
        $method .= "\n}";
        
        return $method;
    }

    /**
     * Generate pivot table name for many-to-many relationships
     */
    public function generatePivotTableName(): string
    {
        $tables = [
            str()->singular($this->sourceTable->name),
            str()->singular($this->targetTable->name)
        ];
        
        sort($tables);
        
        return implode('_', $tables);
    }

    /**
     * Check if this is a self-referencing relationship
     */
    public function isSelfReferencing(): bool
    {
        return $this->source_table_id === $this->target_table_id;
    }

    /**
     * Get relationship display name
     */
    public function getDisplayName(): string
    {
        return $this->name ?: "{$this->sourceTable->name} -> {$this->targetTable->name}";
    }

    /**
     * Get cardinality display
     */
    public function getCardinalityDisplay(): string
    {
        $source = self::CARDINALITIES[$this->cardinality_source] ?? '1';
        $target = self::CARDINALITIES[$this->cardinality_target] ?? '*';
        
        return "{$source} : {$target}";
    }

    /**
     * Validate relationship consistency
     */
    public function validateRelationship(): array
    {
        $errors = [];
        
        // Check if tables exist
        if (!$this->sourceTable) {
            $errors[] = 'Source table not found';
        }
        
        if (!$this->targetTable) {
            $errors[] = 'Target table not found';
        }
        
        // Check if fields exist
        if (!$this->sourceField) {
            $errors[] = 'Source field not found';
        }
        
        if (!$this->targetField) {
            $errors[] = 'Target field not found';
        }
        
        // Check field type compatibility
        if ($this->sourceField && $this->targetField) {
            if ($this->sourceField->type !== $this->targetField->type) {
                $errors[] = 'Field types do not match';
            }
        }
        
        return $errors;
    }
}

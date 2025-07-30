<?php

namespace Modules\ERDDesigner\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErdTable extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'name',
        'display_name',
        'description',
        'position_x',
        'position_y',
        'width',
        'height',
        'color',
        'engine',
        'charset',
        'collation',
        'comment',
        'settings'
    ];

    protected $casts = [
        'position_x' => 'integer',
        'position_y' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Get the ERD project this table belongs to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(ErdProject::class, 'project_id');
    }

    /**
     * Get all fields in this table
     */
    public function fields(): HasMany
    {
        return $this->hasMany(ErdField::class, 'table_id')->orderBy('order');
    }

    /**
     * Get all indexes for this table
     */
    public function indexes(): HasMany
    {
        return $this->hasMany(ErdIndex::class, 'table_id');
    }

    /**
     * Get relationships where this table is the source
     */
    public function sourceRelationships(): HasMany
    {
        return $this->hasMany(ErdRelationship::class, 'source_table_id');
    }

    /**
     * Get relationships where this table is the target
     */
    public function targetRelationships(): HasMany
    {
        return $this->hasMany(ErdRelationship::class, 'target_table_id');
    }

    /**
     * Get all relationships for this table
     */
    public function allRelationships()
    {
        return ErdRelationship::where('source_table_id', $this->id)
            ->orWhere('target_table_id', $this->id);
    }

    /**
     * Get seeder data for this table
     */
    public function seederData(): HasMany
    {
        return $this->hasMany(ErdSeederData::class, 'table_id');
    }

    /**
     * Generate SQL CREATE TABLE statement
     */
    public function generateSql(): string
    {
        $sql = "CREATE TABLE `{$this->name}` (\n";
        
        $fieldSql = [];
        foreach ($this->fields as $field) {
            $fieldSql[] = "  " . $field->generateSql();
        }
        
        // Add primary key
        $primaryKeys = $this->fields->where('is_primary', true)->pluck('name')->toArray();
        if (!empty($primaryKeys)) {
            $fieldSql[] = "  PRIMARY KEY (`" . implode('`, `', $primaryKeys) . "`)";
        }
        
        // Add indexes
        foreach ($this->indexes as $index) {
            $fieldSql[] = "  " . $index->generateSql();
        }
        
        $sql .= implode(",\n", $fieldSql);
        $sql .= "\n)";
        
        // Add table options
        if ($this->engine) {
            $sql .= " ENGINE={$this->engine}";
        }
        
        if ($this->charset) {
            $sql .= " DEFAULT CHARSET={$this->charset}";
        }
        
        if ($this->collation) {
            $sql .= " COLLATE={$this->collation}";
        }
        
        if ($this->comment) {
            $sql .= " COMMENT='{$this->comment}'";
        }
        
        $sql .= ";";
        
        return $sql;
    }

    /**
     * Get primary key fields
     */
    public function getPrimaryKeyFields()
    {
        return $this->fields()->where('is_primary', true)->get();
    }

    /**
     * Get foreign key fields
     */
    public function getForeignKeyFields()
    {
        return $this->fields()->where('is_foreign_key', true)->get();
    }

    /**
     * Check if table has auto increment field
     */
    public function hasAutoIncrement(): bool
    {
        return $this->fields()->where('is_auto_increment', true)->exists();
    }

    /**
     * Get field count
     */
    public function getFieldCountAttribute(): int
    {
        return $this->fields()->count();
    }

    /**
     * Get table size estimate based on fields
     */
    public function getEstimatedSizeAttribute(): int
    {
        $size = 0;
        foreach ($this->fields as $field) {
            $size += $field->getEstimatedSize();
        }
        return $size;
    }
}

<?php

namespace Modules\ERDDesigner\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErdIndex extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'erd_indexes';

    protected $fillable = [
        'table_id',
        'name',
        'type',
        'fields',
        'is_unique',
        'is_primary',
        'comment'
    ];

    protected $casts = [
        'fields' => 'array',
        'is_unique' => 'boolean',
        'is_primary' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Index type constants
     */
    public const INDEX_TYPES = [
        'INDEX' => 'INDEX',
        'UNIQUE' => 'UNIQUE',
        'PRIMARY' => 'PRIMARY KEY',
        'FULLTEXT' => 'FULLTEXT',
        'SPATIAL' => 'SPATIAL'
    ];

    /**
     * Get the table this index belongs to
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(ErdTable::class, 'table_id');
    }

    /**
     * Generate SQL index definition
     */
    public function generateSql(): string
    {
        if (empty($this->fields)) {
            return '';
        }

        $fieldList = implode('`, `', $this->fields);
        
        switch ($this->type) {
            case 'PRIMARY':
                return "PRIMARY KEY (`{$fieldList}`)";
                
            case 'UNIQUE':
                return "UNIQUE KEY `{$this->name}` (`{$fieldList}`)";
                
            case 'FULLTEXT':
                return "FULLTEXT KEY `{$this->name}` (`{$fieldList}`)";
                
            case 'SPATIAL':
                return "SPATIAL KEY `{$this->name}` (`{$fieldList}`)";
                
            default:
                return "KEY `{$this->name}` (`{$fieldList}`)";
        }
    }

    /**
     * Get field names as string
     */
    public function getFieldNamesAttribute(): string
    {
        return implode(', ', $this->fields ?? []);
    }
}

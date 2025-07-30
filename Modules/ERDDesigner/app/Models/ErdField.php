<?php

namespace Modules\ERDDesigner\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErdField extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'table_id',
        'name',
        'display_name',
        'type',
        'length',
        'precision',
        'scale',
        'is_nullable',
        'is_primary',
        'is_unique',
        'is_auto_increment',
        'is_foreign_key',
        'default_value',
        'comment',
        'order',
        'validation_rules',
        'form_type',
        'form_options'
    ];

    protected $casts = [
        'is_nullable' => 'boolean',
        'is_primary' => 'boolean',
        'is_unique' => 'boolean',
        'is_auto_increment' => 'boolean',
        'is_foreign_key' => 'boolean',
        'order' => 'integer',
        'length' => 'integer',
        'precision' => 'integer',
        'scale' => 'integer',
        'form_options' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Field type constants
     */
    const FIELD_TYPES = [
        // Numeric types
        'tinyint' => 'TINYINT',
        'smallint' => 'SMALLINT',
        'mediumint' => 'MEDIUMINT',
        'int' => 'INT',
        'bigint' => 'BIGINT',
        'decimal' => 'DECIMAL',
        'float' => 'FLOAT',
        'double' => 'DOUBLE',
        'bit' => 'BIT',
        
        // String types
        'char' => 'CHAR',
        'varchar' => 'VARCHAR',
        'binary' => 'BINARY',
        'varbinary' => 'VARBINARY',
        'tinyblob' => 'TINYBLOB',
        'blob' => 'BLOB',
        'mediumblob' => 'MEDIUMBLOB',
        'longblob' => 'LONGBLOB',
        'tinytext' => 'TINYTEXT',
        'text' => 'TEXT',
        'mediumtext' => 'MEDIUMTEXT',
        'longtext' => 'LONGTEXT',
        
        // Date and time types
        'date' => 'DATE',
        'time' => 'TIME',
        'datetime' => 'DATETIME',
        'timestamp' => 'TIMESTAMP',
        'year' => 'YEAR',
        
        // JSON type
        'json' => 'JSON',
        
        // Enum and Set
        'enum' => 'ENUM',
        'set' => 'SET'
    ];

    /**
     * Form type constants
     */
    const FORM_TYPES = [
        'text' => 'Text Input',
        'textarea' => 'Textarea',
        'number' => 'Number Input',
        'email' => 'Email Input',
        'password' => 'Password Input',
        'url' => 'URL Input',
        'tel' => 'Phone Input',
        'date' => 'Date Picker',
        'datetime' => 'DateTime Picker',
        'time' => 'Time Picker',
        'select' => 'Select Dropdown',
        'radio' => 'Radio Buttons',
        'checkbox' => 'Checkbox',
        'toggle' => 'Toggle Switch',
        'file' => 'File Upload',
        'image' => 'Image Upload',
        'rich_text' => 'Rich Text Editor',
        'json' => 'JSON Editor',
        'hidden' => 'Hidden Field'
    ];

    /**
     * Get the table this field belongs to
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(ErdTable::class, 'table_id');
    }

    /**
     * Get relationships where this field is the source
     */
    public function sourceRelationships()
    {
        return ErdRelationship::where('source_field_id', $this->id);
    }

    /**
     * Get relationships where this field is the target
     */
    public function targetRelationships()
    {
        return ErdRelationship::where('target_field_id', $this->id);
    }

    /**
     * Generate SQL field definition
     */
    public function generateSql(): string
    {
        $sql = "`{$this->name}` {$this->type}";
        
        // Add length/precision
        if ($this->length && in_array($this->type, ['varchar', 'char', 'varbinary', 'binary'])) {
            $sql .= "({$this->length})";
        } elseif ($this->precision && $this->scale && $this->type === 'decimal') {
            $sql .= "({$this->precision},{$this->scale})";
        } elseif ($this->precision && in_array($this->type, ['float', 'double'])) {
            $sql .= "({$this->precision}" . ($this->scale ? ",{$this->scale}" : '') . ")";
        }
        
        // Add nullable
        if (!$this->is_nullable) {
            $sql .= " NOT NULL";
        } else {
            $sql .= " NULL";
        }
        
        // Add auto increment
        if ($this->is_auto_increment) {
            $sql .= " AUTO_INCREMENT";
        }
        
        // Add default value
        if ($this->default_value !== null && $this->default_value !== '') {
            if (in_array($this->type, ['varchar', 'char', 'text', 'tinytext', 'mediumtext', 'longtext'])) {
                $sql .= " DEFAULT '{$this->default_value}'";
            } else {
                $sql .= " DEFAULT {$this->default_value}";
            }
        }
        
        // Add comment
        if ($this->comment) {
            $sql .= " COMMENT '{$this->comment}'";
        }
        
        return $sql;
    }

    /**
     * Get estimated field size in bytes
     */
    public function getEstimatedSize(): int
    {
        switch ($this->type) {
            case 'tinyint':
                return 1;
            case 'smallint':
                return 2;
            case 'mediumint':
                return 3;
            case 'int':
                return 4;
            case 'bigint':
                return 8;
            case 'float':
                return 4;
            case 'double':
                return 8;
            case 'decimal':
                return ceil(($this->precision + 2) / 2);
            case 'char':
            case 'varchar':
                return $this->length ?: 255;
            case 'text':
                return 65535;
            case 'mediumtext':
                return 16777215;
            case 'longtext':
                return 4294967295;
            case 'date':
                return 3;
            case 'time':
                return 3;
            case 'datetime':
            case 'timestamp':
                return 8;
            case 'year':
                return 1;
            default:
                return 4;
        }
    }

    /**
     * Check if field type requires length
     */
    public function requiresLength(): bool
    {
        return in_array($this->type, ['varchar', 'char', 'varbinary', 'binary']);
    }

    /**
     * Check if field type supports precision/scale
     */
    public function supportsPrecisionScale(): bool
    {
        return in_array($this->type, ['decimal', 'float', 'double']);
    }

    /**
     * Get Laravel migration type
     */
    public function getLaravelMigrationType(): string
    {
        $typeMap = [
            'tinyint' => 'tinyInteger',
            'smallint' => 'smallInteger',
            'mediumint' => 'mediumInteger',
            'int' => 'integer',
            'bigint' => 'bigInteger',
            'decimal' => 'decimal',
            'float' => 'float',
            'double' => 'double',
            'char' => 'char',
            'varchar' => 'string',
            'text' => 'text',
            'mediumtext' => 'mediumText',
            'longtext' => 'longText',
            'date' => 'date',
            'time' => 'time',
            'datetime' => 'dateTime',
            'timestamp' => 'timestamp',
            'year' => 'year',
            'json' => 'json',
            'enum' => 'enum',
            'set' => 'set'
        ];

        return $typeMap[$this->type] ?? 'string';
    }
}

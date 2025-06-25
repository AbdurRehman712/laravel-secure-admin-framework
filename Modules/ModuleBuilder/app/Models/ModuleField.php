<?php

namespace Modules\ModuleBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ModuleField extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_id',
        'name',
        'label',
        'type',
        'database_type',
        'length',
        'precision',
        'scale',
        'default_value',
        'enum_values',
        'nullable',
        'unsigned',
        'auto_increment',
        'primary_key',
        'unique',
        'index',
        'foreign_key_table',
        'foreign_key_column',
        'on_delete',
        'on_update',
        'validation_rules',
        'filament_type',
        'filament_options',
        'description',
        'sort_order',
        'is_fillable',
        'is_hidden',
        'is_searchable',
        'is_sortable',
        'is_filterable',
        'cast_type'
    ];

    protected $casts = [
        'nullable' => 'boolean',
        'unsigned' => 'boolean',
        'auto_increment' => 'boolean',
        'primary_key' => 'boolean',
        'unique' => 'boolean',
        'index' => 'boolean',
        'is_fillable' => 'boolean',
        'is_hidden' => 'boolean',
        'is_searchable' => 'boolean',
        'is_sortable' => 'boolean',
        'is_filterable' => 'boolean',
        'validation_rules' => 'array',
        'filament_options' => 'array',
        'enum_values' => 'array'
    ];

    protected $attributes = [
        'nullable' => true,
        'unsigned' => false,
        'auto_increment' => false,
        'primary_key' => false,
        'unique' => false,
        'index' => false,
        'is_fillable' => true,
        'is_hidden' => false,
        'is_searchable' => true,
        'is_sortable' => true,
        'is_filterable' => true,
        'type' => 'string',
        'database_type' => 'string',
        'filament_type' => 'text',
        'on_delete' => 'cascade',
        'on_update' => 'cascade'
    ];

    // Database field types
    public const DATABASE_TYPES = [
        'bigIncrements' => 'bigIncrements',
        'bigInteger' => 'bigInteger',
        'binary' => 'binary',
        'boolean' => 'boolean',
        'char' => 'char',
        'date' => 'date',
        'dateTime' => 'dateTime',
        'decimal' => 'decimal',
        'double' => 'double',
        'enum' => 'enum',
        'float' => 'float',
        'geometry' => 'geometry',
        'geometryCollection' => 'geometryCollection',
        'increments' => 'increments',
        'integer' => 'integer',
        'ipAddress' => 'ipAddress',
        'json' => 'json',
        'jsonb' => 'jsonb',
        'lineString' => 'lineString',
        'longText' => 'longText',
        'macAddress' => 'macAddress',
        'mediumIncrements' => 'mediumIncrements',
        'mediumInteger' => 'mediumInteger',
        'mediumText' => 'mediumText',
        'morphs' => 'morphs',
        'multiLineString' => 'multiLineString',
        'multiPoint' => 'multiPoint',
        'multiPolygon' => 'multiPolygon',
        'nullableMorphs' => 'nullableMorphs',
        'nullableTimestamps' => 'nullableTimestamps',
        'nullableUuidMorphs' => 'nullableUuidMorphs',
        'point' => 'point',
        'polygon' => 'polygon',
        'rememberToken' => 'rememberToken',
        'set' => 'set',
        'smallIncrements' => 'smallIncrements',
        'smallInteger' => 'smallInteger',
        'softDeletes' => 'softDeletes',
        'string' => 'string',
        'text' => 'text',
        'time' => 'time',
        'timestamp' => 'timestamp',
        'timestamps' => 'timestamps',
        'tinyIncrements' => 'tinyIncrements',
        'tinyInteger' => 'tinyInteger',
        'unsignedBigInteger' => 'unsignedBigInteger',
        'unsignedDecimal' => 'unsignedDecimal',
        'unsignedInteger' => 'unsignedInteger',
        'unsignedMediumInteger' => 'unsignedMediumInteger',
        'unsignedSmallInteger' => 'unsignedSmallInteger',
        'unsignedTinyInteger' => 'unsignedTinyInteger',
        'uuid' => 'uuid',
        'uuidMorphs' => 'uuidMorphs',
        'year' => 'year',
    ];

    // Filament form field types
    public const FILAMENT_TYPES = [
        'text' => 'Text Input',
        'textarea' => 'Textarea',
        'email' => 'Email',
        'password' => 'Password',
        'number' => 'Number',
        'tel' => 'Telephone',
        'url' => 'URL',
        'color' => 'Color Picker',
        'date' => 'Date Picker',
        'datetime' => 'DateTime Picker',
        'time' => 'Time Picker',
        'select' => 'Select Dropdown',
        'checkbox' => 'Checkbox',
        'radio' => 'Radio Buttons',
        'toggle' => 'Toggle Switch',
        'file_upload' => 'File Upload',
        'image' => 'Image Upload',
        'rich_editor' => 'Rich Text Editor',
        'markdown_editor' => 'Markdown Editor',
        'code_editor' => 'Code Editor',
        'json' => 'JSON Editor',
        'key_value' => 'Key-Value Pairs',
        'repeater' => 'Repeater',
        'builder' => 'Builder',
        'tags' => 'Tags Input'
    ];

    public function table(): BelongsTo
    {
        return $this->belongsTo(ModuleTable::class, 'table_id');
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = Str::snake($value);
        
        if (empty($this->attributes['label'])) {
            $this->attributes['label'] = Str::title(str_replace('_', ' ', $value));
        }
    }

    public function getStudlyNameAttribute(): string
    {
        return Str::studly($this->name);
    }

    public function getCamelNameAttribute(): string
    {
        return Str::camel($this->name);
    }

    public function getKebabNameAttribute(): string
    {
        return Str::kebab($this->name);
    }

    public function isForeignKey(): bool
    {
        return !empty($this->foreign_key_table) && !empty($this->foreign_key_column);
    }

    public function requiresLength(): bool
    {
        return in_array($this->database_type, ['string', 'char', 'decimal']);
    }

    public function supportsPrecision(): bool
    {
        return in_array($this->database_type, ['decimal', 'double', 'float']);
    }

    public function getMigrationDefinition(): string
    {
        $definition = '$table->' . $this->database_type . "('{$this->name}'";
        
        if ($this->requiresLength() && $this->length) {
            $definition .= ", {$this->length}";
        }
        
        if ($this->supportsPrecision() && $this->precision) {
            $definition .= ", {$this->precision}";
            if ($this->scale) {
                $definition .= ", {$this->scale}";
            }
        }
        
        $definition .= ')';
        
        if ($this->nullable) {
            $definition .= '->nullable()';
        }
        
        if ($this->unsigned) {
            $definition .= '->unsigned()';
        }
        
        if ($this->default_value !== null) {
            $definition .= "->default('{$this->default_value}')";
        }
        
        if ($this->unique) {
            $definition .= '->unique()';
        }
        
        if ($this->index) {
            $definition .= '->index()';
        }
        
        $definition .= ';';
        
        return $definition;
    }

    // Custom accessor to ensure enum_values are properly returned
    public function getEnumValuesAttribute($value)
    {
        if ($this->type === 'enum' || $this->database_type === 'enum') {
            if (is_string($value)) {
                return json_decode($value, true);
            }
            return $value ?: ['active', 'inactive']; // Default values if missing
        }
        return $value;
    }

    // Override toArray to ensure enum_values are included
    public function toArray()
    {
        $array = parent::toArray();
        
        // Ensure enum_values are included for enum fields
        if (($this->type === 'enum' || $this->database_type === 'enum') && $this->enum_values) {
            $array['enum_values'] = $this->enum_values;
        }
        
        return $array;
    }
}

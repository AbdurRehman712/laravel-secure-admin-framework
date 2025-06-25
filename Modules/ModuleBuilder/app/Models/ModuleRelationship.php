<?php

namespace Modules\ModuleBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleRelationship extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_table_id',
        'to_table_id',
        'type',
        'name',
        'inverse_name',
        'foreign_key',
        'local_key',
        'pivot_table',
        'pivot_foreign_key',
        'pivot_related_key',
        'morph_name',
        'description'
    ];

    // Relationship types
    public const TYPES = [
        'hasOne' => 'Has One',
        'hasMany' => 'Has Many',
        'belongsTo' => 'Belongs To',
        'belongsToMany' => 'Belongs To Many (Many-to-Many)',
        'morphTo' => 'Morph To (Polymorphic)',
        'morphOne' => 'Morph One (Polymorphic)',
        'morphMany' => 'Morph Many (Polymorphic)',
        'morphToMany' => 'Morph To Many (Polymorphic Many-to-Many)',
        'morphedByMany' => 'Morphed By Many (Polymorphic Many-to-Many)',
        'hasManyThrough' => 'Has Many Through',
        'hasOneThrough' => 'Has One Through'
    ];

    public function fromTable(): BelongsTo
    {
        return $this->belongsTo(ModuleTable::class, 'from_table_id');
    }

    public function toTable(): BelongsTo
    {
        return $this->belongsTo(ModuleTable::class, 'to_table_id');
    }

    public function requiresPivotTable(): bool
    {
        return in_array($this->type, ['belongsToMany', 'morphToMany', 'morphedByMany']);
    }

    public function requiresMorphName(): bool
    {
        return in_array($this->type, ['morphTo', 'morphOne', 'morphMany', 'morphToMany', 'morphedByMany']);
    }

    public function getMethodDefinition(): string
    {
        $fromModel = $this->fromTable->studly_singular_name;
        $toModel = $this->toTable->studly_singular_name;
        
        $definition = "public function {$this->name}()\n{\n";
        
        switch ($this->type) {
            case 'hasOne':
                $definition .= "    return \$this->hasOne({$toModel}::class";
                if ($this->foreign_key) {
                    $definition .= ", '{$this->foreign_key}'";
                }
                if ($this->local_key) {
                    $definition .= ", '{$this->local_key}'";
                }
                break;
                
            case 'hasMany':
                $definition .= "    return \$this->hasMany({$toModel}::class";
                if ($this->foreign_key) {
                    $definition .= ", '{$this->foreign_key}'";
                }
                if ($this->local_key) {
                    $definition .= ", '{$this->local_key}'";
                }
                break;
                
            case 'belongsTo':
                $definition .= "    return \$this->belongsTo({$toModel}::class";
                if ($this->foreign_key) {
                    $definition .= ", '{$this->foreign_key}'";
                }
                if ($this->local_key) {
                    $definition .= ", '{$this->local_key}'";
                }
                break;
                
            case 'belongsToMany':
                $definition .= "    return \$this->belongsToMany({$toModel}::class";
                if ($this->pivot_table) {
                    $definition .= ", '{$this->pivot_table}'";
                }
                if ($this->pivot_foreign_key) {
                    $definition .= ", '{$this->pivot_foreign_key}'";
                }
                if ($this->pivot_related_key) {
                    $definition .= ", '{$this->pivot_related_key}'";
                }
                break;
                
            case 'morphTo':
                $definition .= "    return \$this->morphTo('{$this->morph_name}'";
                break;
                
            case 'morphOne':
                $definition .= "    return \$this->morphOne({$toModel}::class, '{$this->morph_name}'";
                break;
                
            case 'morphMany':
                $definition .= "    return \$this->morphMany({$toModel}::class, '{$this->morph_name}'";
                break;
                
            default:
                $definition .= "    // TODO: Implement {$this->type} relationship";
        }
        
        $definition .= ");\n}";
        
        return $definition;
    }
}

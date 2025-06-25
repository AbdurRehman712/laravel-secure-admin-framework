<?php

namespace Modules\ModuleBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModulePermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'guard_name',
        'description',
        'group_name'
    ];

    protected $attributes = [
        'guard_name' => 'admin'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ModuleProject::class, 'project_id');
    }

    public static function getDefaultPermissionsForTable(string $tableName): array
    {
        $singular = \Illuminate\Support\Str::singular($tableName);
        $studly = \Illuminate\Support\Str::studly($singular);
        
        return [
            [
                'name' => "view_{$tableName}",
                'description' => "View {$studly}",
                'group_name' => $studly
            ],
            [
                'name' => "view_any_{$tableName}",
                'description' => "View Any {$studly}",
                'group_name' => $studly
            ],
            [
                'name' => "create_{$tableName}",
                'description' => "Create {$studly}",
                'group_name' => $studly
            ],
            [
                'name' => "update_{$tableName}",
                'description' => "Update {$studly}",
                'group_name' => $studly
            ],
            [
                'name' => "delete_{$tableName}",
                'description' => "Delete {$studly}",
                'group_name' => $studly
            ],
            [
                'name' => "delete_any_{$tableName}",
                'description' => "Delete Any {$studly}",
                'group_name' => $studly
            ],
            [
                'name' => "force_delete_{$tableName}",
                'description' => "Force Delete {$studly}",
                'group_name' => $studly
            ],
            [
                'name' => "force_delete_any_{$tableName}",
                'description' => "Force Delete Any {$studly}",
                'group_name' => $studly
            ],
            [
                'name' => "restore_{$tableName}",
                'description' => "Restore {$studly}",
                'group_name' => $studly
            ],
            [
                'name' => "restore_any_{$tableName}",
                'description' => "Restore Any {$studly}",
                'group_name' => $studly
            ],
            [
                'name' => "replicate_{$tableName}",
                'description' => "Replicate {$studly}",
                'group_name' => $studly
            ],
            [
                'name' => "reorder_{$tableName}",
                'description' => "Reorder {$studly}",
                'group_name' => $studly
            ]
        ];
    }
}

<?php

namespace Modules\ERDDesigner\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class ErdProject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'database_name',
        'database_type',
        'canvas_data',
        'settings',
        'version',
        'is_public',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'canvas_data' => 'array',
        'settings' => 'array',
        'is_public' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Get the user who created this project
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this project
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get all tables in this ERD project
     */
    public function tables(): HasMany
    {
        return $this->hasMany(ErdTable::class, 'project_id');
    }

    /**
     * Get all relationships in this ERD project
     */
    public function relationships(): HasMany
    {
        return $this->hasMany(ErdRelationship::class, 'project_id');
    }

    /**
     * Get project versions/history
     */
    public function versions(): HasMany
    {
        return $this->hasMany(ErdProjectVersion::class, 'project_id');
    }

    /**
     * Create a new version of this project
     */
    public function createVersion(string $description = null): ErdProjectVersion
    {
        return $this->versions()->create([
            'version_number' => $this->getNextVersionNumber(),
            'description' => $description,
            'canvas_data' => $this->canvas_data,
            'settings' => $this->settings,
            'created_by' => auth()->id()
        ]);
    }

    /**
     * Get the next version number
     */
    private function getNextVersionNumber(): string
    {
        $lastVersion = $this->versions()->orderBy('version_number', 'desc')->first();
        
        if (!$lastVersion) {
            return '1.0.0';
        }

        $parts = explode('.', $lastVersion->version_number);
        $parts[2] = (int)$parts[2] + 1;
        
        return implode('.', $parts);
    }

    /**
     * Export project as SQL
     */
    public function exportToSql(): string
    {
        $sql = "-- ERD Project: {$this->name}\n";
        $sql .= "-- Generated on: " . now()->format('Y-m-d H:i:s') . "\n\n";
        
        if ($this->database_name) {
            $sql .= "CREATE DATABASE IF NOT EXISTS `{$this->database_name}`;\n";
            $sql .= "USE `{$this->database_name}`;\n\n";
        }

        foreach ($this->tables as $table) {
            $sql .= $table->generateSql() . "\n\n";
        }

        foreach ($this->relationships as $relationship) {
            $sql .= $relationship->generateSql() . "\n";
        }

        return $sql;
    }

    /**
     * Get table count
     */
    public function getTableCountAttribute(): int
    {
        return $this->tables()->count();
    }

    /**
     * Get relationship count
     */
    public function getRelationshipCountAttribute(): int
    {
        return $this->relationships()->count();
    }
}

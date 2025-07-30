<?php

namespace Modules\ERDDesigner\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class ErdProjectVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'version_number',
        'description',
        'canvas_data',
        'settings',
        'created_by'
    ];

    protected $casts = [
        'canvas_data' => 'array',
        'settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the ERD project this version belongs to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(ErdProject::class, 'project_id');
    }

    /**
     * Get the user who created this version
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Restore this version to the main project
     */
    public function restore(): bool
    {
        return $this->project->update([
            'canvas_data' => $this->canvas_data,
            'settings' => $this->settings,
            'version' => $this->version_number,
            'updated_by' => auth()->id()
        ]);
    }

    /**
     * Get version display name
     */
    public function getDisplayNameAttribute(): string
    {
        return "v{$this->version_number}" . ($this->description ? " - {$this->description}" : '');
    }

    /**
     * Compare with another version
     */
    public function compareWith(ErdProjectVersion $other): array
    {
        $changes = [];
        
        // Compare canvas data
        if ($this->canvas_data !== $other->canvas_data) {
            $changes[] = 'Canvas layout changed';
        }
        
        // Compare settings
        if ($this->settings !== $other->settings) {
            $changes[] = 'Project settings changed';
        }
        
        return $changes;
    }
}

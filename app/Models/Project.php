<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\app\Models\Admin;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'created_by',
        'settings',
        'ai_context',
    ];

    protected $casts = [
        'settings' => 'array',
        'ai_context' => 'array',
    ];

    /**
     * Project statuses
     */
    const STATUS_PLANNING = 'planning';
    const STATUS_DEVELOPMENT = 'development';
    const STATUS_REVIEW = 'review';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ARCHIVED = 'archived';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PLANNING => 'Planning',
            self::STATUS_DEVELOPMENT => 'Development',
            self::STATUS_REVIEW => 'Review',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_ARCHIVED => 'Archived',
        ];
    }

    /**
     * Get the admin who created this project
     */
    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /**
     * Get all team members assigned to this project
     */
    public function teamMembers(): BelongsToMany
    {
        return $this->belongsToMany(Admin::class, 'project_team_members')
                    ->withPivot(['role', 'permissions', 'joined_at'])
                    ->withTimestamps();
    }

    /**
     * Get all AI workspace content for this project
     */
    public function workspaceContent(): HasMany
    {
        return $this->hasMany(ProjectWorkspaceContent::class);
    }

    /**
     * Get workspace content by role
     */
    public function getWorkspaceContentByRole(string $role): HasMany
    {
        return $this->workspaceContent()->where('role', $role);
    }

    /**
     * Get all generated modules for this project
     */
    public function generatedModules(): HasMany
    {
        return $this->hasMany(ProjectModule::class);
    }

    /**
     * Check if user has access to this project
     */
    public function hasAccess(Admin $admin): bool
    {
        return $this->created_by === $admin->id || 
               $this->teamMembers()->where('admin_id', $admin->id)->exists();
    }

    /**
     * Get user's role in this project
     */
    public function getUserRole(Admin $admin): ?string
    {
        if ($this->created_by === $admin->id) {
            return 'project_owner';
        }

        $member = $this->teamMembers()->where('admin_id', $admin->id)->first();
        return $member?->pivot->role;
    }

    /**
     * Add team member to project
     */
    public function addTeamMember(Admin $admin, string $role, array $permissions = []): void
    {
        $this->teamMembers()->attach($admin->id, [
            'role' => $role,
            'permissions' => json_encode($permissions),
            'joined_at' => now(),
        ]);
    }

    /**
     * Remove team member from project
     */
    public function removeTeamMember(Admin $admin): void
    {
        $this->teamMembers()->detach($admin->id);
    }

    /**
     * Get project progress based on workspace content
     */
    public function getProgress(): array
    {
        $roles = [
            'product_owner' => 'Product Owner',
            'designer' => 'Designer', 
            'database_admin' => 'Database Admin',
            'frontend_developer' => 'Frontend Developer',
            'backend_developer' => 'Backend Developer',
            'devops' => 'DevOps',
        ];

        $progress = [];
        foreach ($roles as $role => $label) {
            $contentCount = $this->getWorkspaceContentByRole($role)->count();
            $progress[$role] = [
                'label' => $label,
                'completed' => $contentCount > 0,
                'content_count' => $contentCount,
            ];
        }

        return $progress;
    }

    /**
     * Export project data for code generation
     */
    public function exportForGeneration(): array
    {
        return [
            'project' => $this->toArray(),
            'workspace_content' => $this->workspaceContent()
                ->with(['admin'])
                ->get()
                ->groupBy('role')
                ->toArray(),
            'team_members' => $this->teamMembers()
                ->with(['roles'])
                ->get()
                ->toArray(),
            'generated_modules' => $this->generatedModules()
                ->get()
                ->toArray(),
        ];
    }
}

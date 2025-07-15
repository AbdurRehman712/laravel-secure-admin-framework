<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\app\Models\Admin;

class ProjectWorkspaceContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'admin_id',
        'role',
        'content_type',
        'title',
        'content',
        'ai_prompt_used',
        'parsed_data',
        'status',
        'version',
        'parent_content_id',
    ];

    protected $casts = [
        'content' => 'array',
        'parsed_data' => 'array',
        'ai_prompt_used' => 'array',
    ];

    /**
     * Content types for different roles
     */
    const TYPE_USER_STORIES = 'user_stories';
    const TYPE_ACCEPTANCE_CRITERIA = 'acceptance_criteria';
    const TYPE_WIREFRAMES = 'wireframes';
    const TYPE_DESIGN_SYSTEM = 'design_system';
    const TYPE_DATABASE_SCHEMA = 'database_schema';
    const TYPE_API_ENDPOINTS = 'api_endpoints';
    const TYPE_FRONTEND_COMPONENTS = 'frontend_components';
    const TYPE_BACKEND_LOGIC = 'backend_logic';
    const TYPE_DEPLOYMENT_CONFIG = 'deployment_config';
    const TYPE_DOCKER_CONFIG = 'docker_config';

    /**
     * Content statuses
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_REVIEW = 'review';
    const STATUS_APPROVED = 'approved';
    const STATUS_IMPLEMENTED = 'implemented';

    public static function getContentTypes(): array
    {
        return [
            self::TYPE_USER_STORIES => 'User Stories',
            self::TYPE_ACCEPTANCE_CRITERIA => 'Acceptance Criteria',
            self::TYPE_WIREFRAMES => 'Wireframes',
            self::TYPE_DESIGN_SYSTEM => 'Design System',
            self::TYPE_DATABASE_SCHEMA => 'Database Schema',
            self::TYPE_API_ENDPOINTS => 'API Endpoints',
            self::TYPE_FRONTEND_COMPONENTS => 'Frontend Components',
            self::TYPE_BACKEND_LOGIC => 'Backend Logic',
            self::TYPE_DEPLOYMENT_CONFIG => 'Deployment Config',
            self::TYPE_DOCKER_CONFIG => 'Docker Config',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_REVIEW => 'Under Review',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_IMPLEMENTED => 'Implemented',
        ];
    }

    /**
     * Get content types by role
     */
    public static function getContentTypesByRole(): array
    {
        return [
            'product_owner' => [
                self::TYPE_USER_STORIES,
                self::TYPE_ACCEPTANCE_CRITERIA,
            ],
            'designer' => [
                self::TYPE_WIREFRAMES,
                self::TYPE_DESIGN_SYSTEM,
            ],
            'database_admin' => [
                self::TYPE_DATABASE_SCHEMA,
            ],
            'frontend_developer' => [
                self::TYPE_FRONTEND_COMPONENTS,
            ],
            'backend_developer' => [
                self::TYPE_API_ENDPOINTS,
                self::TYPE_BACKEND_LOGIC,
            ],
            'devops' => [
                self::TYPE_DEPLOYMENT_CONFIG,
                self::TYPE_DOCKER_CONFIG,
            ],
        ];
    }

    /**
     * Get the project this content belongs to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the admin who created this content
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Get parent content (for versioning)
     */
    public function parentContent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_content_id');
    }

    /**
     * Get child versions of this content
     */
    public function childVersions()
    {
        return $this->hasMany(self::class, 'parent_content_id');
    }

    /**
     * Create new version of this content
     */
    public function createVersion(array $newData): self
    {
        $newVersion = $this->replicate();
        $newVersion->parent_content_id = $this->id;
        $newVersion->version = $this->version + 1;
        $newVersion->fill($newData);
        $newVersion->save();

        return $newVersion;
    }

    /**
     * Get the latest version of this content
     */
    public function getLatestVersion(): self
    {
        return $this->childVersions()
                    ->orderBy('version', 'desc')
                    ->first() ?? $this;
    }

    /**
     * Check if this is the latest version
     */
    public function isLatestVersion(): bool
    {
        return $this->childVersions()->count() === 0;
    }

    /**
     * Parse AI response content based on content type
     */
    public function parseAiResponse(string $aiResponse): array
    {
        $parser = new \App\Services\AiResponseParser();
        return $parser->parse($aiResponse, $this->content_type, $this->role);
    }

    /**
     * Get formatted content for display
     */
    public function getFormattedContent(): array
    {
        if (empty($this->parsed_data)) {
            return $this->content ?? [];
        }

        return $this->parsed_data;
    }

    /**
     * Get content summary for notifications
     */
    public function getSummary(): string
    {
        $type = self::getContentTypes()[$this->content_type] ?? $this->content_type;
        return "{$type} - {$this->title}";
    }

    /**
     * Scope to get content by role
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope to get content by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('content_type', $type);
    }

    /**
     * Scope to get latest versions only
     */
    public function scopeLatestVersions($query)
    {
        return $query->whereNull('parent_content_id')
                    ->orWhereNotExists(function ($subQuery) {
                        $subQuery->select('id')
                                ->from('project_workspace_contents as pwc2')
                                ->whereColumn('pwc2.parent_content_id', 'project_workspace_contents.id');
                    });
    }
}

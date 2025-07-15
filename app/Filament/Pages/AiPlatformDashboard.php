<?php

namespace App\Filament\Pages;

use App\Models\Project;
use App\Models\ProjectWorkspaceContent;
use App\Models\ProjectModule;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class AiPlatformDashboard extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-sparkles';

    protected static \UnitEnum|string|null $navigationGroup = 'AI Platform';

    protected static ?int $navigationSort = 0;

    protected static ?string $title = 'AI Development Platform';

    protected string $view = 'filament.pages.ai-platform-dashboard';

    public function getStats(): array
    {
        $admin = Auth::guard('admin')->user();
        
        return [
            'total_projects' => Project::count(),
            'my_projects' => Project::where('created_by', $admin->id)->count(),
            'active_projects' => Project::whereIn('status', ['planning', 'development'])->count(),
            'completed_projects' => Project::where('status', 'completed')->count(),
            'total_workspace_content' => ProjectWorkspaceContent::count(),
            'approved_content' => ProjectWorkspaceContent::where('status', 'approved')->count(),
            'generated_modules' => ProjectModule::count(),
            'active_modules' => ProjectModule::where('status', 'active')->count(),
        ];
    }

    public function getRecentProjects(): array
    {
        return Project::with(['creator', 'teamMembers'])
            ->latest()
            ->limit(5)
            ->get()
            ->toArray();
    }

    public function getRecentContent(): array
    {
        return ProjectWorkspaceContent::with(['project', 'admin'])
            ->latest()
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function getContentByRole(): array
    {
        return ProjectWorkspaceContent::selectRaw('role, count(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role')
            ->toArray();
    }

    public function getContentByType(): array
    {
        return ProjectWorkspaceContent::selectRaw('content_type, count(*) as count')
            ->groupBy('content_type')
            ->pluck('count', 'content_type')
            ->toArray();
    }

    public function getModuleStats(): array
    {
        return [
            'by_status' => ProjectModule::selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'ai_generated' => ProjectModule::where('ai_generated', true)->count(),
            'manual_created' => ProjectModule::where('ai_generated', false)->count(),
        ];
    }

    public function getRoleLabels(): array
    {
        return [
            'product_owner' => 'Product Owner',
            'designer' => 'Designer',
            'database_admin' => 'Database Admin',
            'frontend_developer' => 'Frontend Developer',
            'backend_developer' => 'Backend Developer',
            'devops' => 'DevOps',
        ];
    }

    public function getContentTypeLabels(): array
    {
        return ProjectWorkspaceContent::getContentTypes();
    }

    public function getProjectStatusLabels(): array
    {
        return Project::getStatuses();
    }

    public function getModuleStatusLabels(): array
    {
        return ProjectModule::getStatuses();
    }
}

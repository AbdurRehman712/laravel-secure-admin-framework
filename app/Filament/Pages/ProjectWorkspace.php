<?php

namespace App\Filament\Pages;

use App\Models\Project;
use App\Models\ProjectWorkspaceContent;
use App\Services\AiModuleGenerator;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Modules\Core\app\Models\Admin;

class ProjectWorkspace extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-computer-desktop';

    protected string $view = 'filament.pages.project-workspace';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'project-workspace';

    public ?Project $project = null;
    public string $currentRole = 'product_owner';
    public array $roleProgress = [];

    protected $listeners = [
        'content-created' => 'refreshContent',
        'showContentDetails' => 'showContentDetails',
        'editContent' => 'editContent',
        'generateCode' => 'generateCode'
    ];

    public function mount(): void
    {
        // Get project parameter from request
        $project = request()->get('project');

        // Check if project parameter is provided
        if (!$project) {
            $this->redirect('/admin/projects');
            return;
        }

        // Load the project
        try {
            $this->project = Project::findOrFail($project);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Project Not Found')
                ->body('The requested project could not be found.')
                ->danger()
                ->send();

            $this->redirect('/admin/projects');
            return;
        }

        // Get the authenticated admin user
        $admin = Auth::guard('admin')->user();

        // Ensure we have an Admin model
        if (!$admin instanceof Admin) {
            $this->redirect('/admin/login');
            return;
        }

        // Super simple access control - allow super admin and ai_platform_admin always
        $hasAccess = false;

        if ($admin->hasRole('Super Admin')) {
            $hasAccess = true;
        } elseif ($admin->hasRole('ai_platform_admin')) {
            $hasAccess = true;
        } elseif ($this->project->created_by === $admin->id) {
            $hasAccess = true;
        } elseif ($this->project->hasAccess($admin)) {
            $hasAccess = true;
        }

        if (!$hasAccess) {
            Notification::make()
                ->title('Access Denied')
                ->body('You do not have access to this project.')
                ->danger()
                ->send();

            $this->redirect('/admin/projects');
            return;
        }

        // Set default role to product_owner
        $this->currentRole = 'product_owner';

        // Try to get user's role in project
        try {
            $userRole = $this->project->getUserRole($admin);
            if ($userRole && $userRole !== 'project_owner') {
                $this->currentRole = $userRole;
            }
        } catch (\Exception $e) {
            // If there's an error getting the role, just use default
        }

        // Load role progress
        try {
            $this->loadRoleProgress();
        } catch (\Exception $e) {
            // If there's an error loading progress, initialize empty array
            $this->roleProgress = [];
        }
    }

    protected function loadRoleProgress(): void
    {
        $this->roleProgress = $this->project->getProgress();
    }

    public function switchRole(string $role): void
    {
        $this->currentRole = $role;
        $this->loadRoleProgress();

        // Force re-render of the page to update Livewire components with new role
        $this->dispatch('role-switched', role: $role);
    }

    public function getRoleDisplayName(string $role): string
    {
        return match ($role) {
            'product_owner' => 'Product Owner',
            'designer' => 'Designer',
            'database_admin' => 'Database Admin',
            'frontend_developer' => 'Frontend Developer',
            'backend_developer' => 'Backend Developer',
            'devops' => 'DevOps',
            default => ucwords(str_replace('_', ' ', $role)),
        };
    }

    public function getAvailableRoles(): array
    {
        return [
            'product_owner' => 'Product Owner',
            'database_backend_developer' => 'Database & Backend Developer',
            'project_manager' => 'Project Manager',
        ];
    }

    public function getRoleDescription(string $role): string
    {
        return match ($role) {
            'product_owner' => 'Define user stories, acceptance criteria, and project requirements using AI tools.',
            'database_backend_developer' => 'Design database schemas, create Laravel models, controllers, APIs, and implement business logic.',
            'project_manager' => 'Manage project coordination, deployment configurations, and oversee development process.',
            default => 'Collaborate on the project development process.',
        };
    }

    public function getAiPromptTemplates(string $role): array
    {
        return match ($role) {
            'product_owner' => [
                'user_stories' => [
                    'title' => 'Generate User Stories',
                    'prompt' => 'Generate detailed user stories for a [PROJECT_TYPE] application with the following features: [FEATURES]. Include acceptance criteria for each story.',
                    'example' => 'Generate detailed user stories for a e-commerce application with the following features: product catalog, shopping cart, user authentication, payment processing. Include acceptance criteria for each story.',
                ],
                'acceptance_criteria' => [
                    'title' => 'Create Acceptance Criteria',
                    'prompt' => 'Create detailed acceptance criteria for the following user story: [USER_STORY]. Use Given-When-Then format.',
                    'example' => 'Create detailed acceptance criteria for the following user story: As a customer, I want to add products to my shopping cart so that I can purchase multiple items at once. Use Given-When-Then format.',
                ],
            ],
            'database_backend_developer' => [
                'database_schema' => [
                    'title' => 'Generate Database Schema',
                    'prompt' => 'Create a complete database schema for a [PROJECT_TYPE] application with the following entities: [ENTITIES]. Include relationships, indexes, and constraints.',
                    'example' => 'Create a complete database schema for a e-commerce application with the following entities: users, products, categories, orders, order_items, payments. Include relationships, indexes, and constraints.',
                ],
                'api_endpoints' => [
                    'title' => 'Generate API Endpoints',
                    'prompt' => 'Create RESTful API endpoints for [RESOURCE] management including CRUD operations, validation, and proper HTTP responses.',
                    'example' => 'Create RESTful API endpoints for product management including CRUD operations, validation, and proper HTTP responses.',
                ],
                'backend_logic' => [
                    'title' => 'Generate Backend Logic',
                    'prompt' => 'Create Laravel 11 controllers and models for [FEATURE] with proper validation, relationships, and API endpoints.',
                    'example' => 'Create Laravel 11 controllers and models for order management with proper validation, relationships, and API endpoints.',
                ],
            ],
            'project_manager' => [
                'project_planning' => [
                    'title' => 'Create Project Plan',
                    'prompt' => 'Create a comprehensive project plan for a [PROJECT_TYPE] application with phases, milestones, and risk assessment.',
                    'example' => 'Create a comprehensive project plan for an e-commerce platform with phases, milestones, and risk assessment.',
                ],
                'deployment_config' => [
                    'title' => 'Generate Deployment Configuration',
                    'prompt' => 'Create Docker configuration for a Laravel 11 application with [SERVICES]. Include Dockerfile, docker-compose.yml, and environment setup.',
                    'example' => 'Create Docker configuration for a Laravel 11 application with MySQL, Redis, and Nginx. Include Dockerfile, docker-compose.yml, and environment setup.',
                ],
            ],
            default => [],
        };
    }

    public function getWorkspaceContent(string $role): array
    {
        return $this->project->getWorkspaceContentByRole($role)
            ->with('admin')
            ->latest()
            ->get()
            ->toArray();
    }

    public function getTitle(): string
    {
        return $this->project ? "AI Workspace - {$this->project->name}" : 'AI Workspace';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate_modules')
                ->label('Generate Modules')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('success')
                ->action('generateModules')
                ->requiresConfirmation()
                ->modalHeading('Generate Laravel Modules')
                ->modalDescription('This will analyze your workspace content and automatically generate Laravel modules with models, resources, and migrations.')
                ->modalSubmitActionLabel('Generate Modules'),

            Action::make('back_to_project')
                ->label('Back to Project')
                ->icon('heroicon-o-arrow-left')
                ->url(fn (): string => route('filament.admin.resources.projects.view', ['record' => $this->project->id])),
        ];
    }

    public function generateModules(): void
    {
        try {
            $generator = new \App\Services\AiModuleGenerator($this->project);
            $results = $generator->generateAndInstallModules();

            $successCount = collect($results)->where('activated', true)->count();
            $totalCount = count($results);

            if ($successCount > 0) {
                Notification::make()
                    ->title('Modules Generated Successfully!')
                    ->body("Generated and activated {$successCount} out of {$totalCount} modules.")
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('No Modules Generated')
                    ->body('No new modules were generated. Check your workspace content and try again.')
                    ->warning()
                    ->send();
            }

            // Clear Filament cache
            \Artisan::call('filament:clear-cached-components');

        } catch (\Exception $e) {
            Notification::make()
                ->title('Module Generation Failed')
                ->body('There was an error generating modules: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function showContentDetails($contentId): void
    {
        $content = ProjectWorkspaceContent::findOrFail($contentId);

        Notification::make()
            ->title('Content Details')
            ->body("Viewing: {$content->title}")
            ->info()
            ->send();

        // TODO: Implement content details modal/page
    }

    public function editContent($contentId): void
    {
        $content = ProjectWorkspaceContent::findOrFail($contentId);

        Notification::make()
            ->title('Edit Content')
            ->body("Editing: {$content->title}")
            ->info()
            ->send();

        // TODO: Implement content editing functionality
    }

    public function generateCode($contentId): void
    {
        $content = ProjectWorkspaceContent::findOrFail($contentId);

        try {
            // Generate code from this specific content
            $generator = new AiModuleGenerator($this->project);
            $modules = $generator->generateModules();

            if (empty($modules)) {
                Notification::make()
                    ->title('No Modules Generated')
                    ->body('No new modules were generated from this content. Check the content format and try again.')
                    ->warning()
                    ->send();
            } else {
                Notification::make()
                    ->title('Code Generated Successfully!')
                    ->body('Generated ' . count($modules) . ' module(s) from: ' . $content->title)
                    ->success()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Code Generation Failed')
                ->body('Error generating code: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}

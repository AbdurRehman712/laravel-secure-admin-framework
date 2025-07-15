<?php

namespace App\Filament\Pages;

use App\Models\Project;
use App\Models\ProjectWorkspaceContent;
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
            'designer' => 'Designer',
            'database_admin' => 'Database Admin',
            'frontend_developer' => 'Frontend Developer',
            'backend_developer' => 'Backend Developer',
            'devops' => 'DevOps',
        ];
    }

    public function getRoleDescription(string $role): string
    {
        return match ($role) {
            'product_owner' => 'Define user stories, acceptance criteria, and project requirements using AI tools.',
            'designer' => 'Create wireframes, design systems, and UI/UX specifications with AI assistance.',
            'database_admin' => 'Design database schemas, relationships, and data structures using AI.',
            'frontend_developer' => 'Build Livewire components, Blade templates, and frontend interactions.',
            'backend_developer' => 'Create Laravel controllers, models, APIs, and business logic.',
            'devops' => 'Configure deployment, Docker containers, and CI/CD pipelines.',
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
            'designer' => [
                'wireframes' => [
                    'title' => 'Generate Wireframes',
                    'prompt' => 'Create detailed wireframe descriptions for [PAGE_NAME] page of a [PROJECT_TYPE] application. Include layout, components, and user interactions.',
                    'example' => 'Create detailed wireframe descriptions for product listing page of a e-commerce application. Include layout, components, and user interactions.',
                ],
                'design_system' => [
                    'title' => 'Create Design System',
                    'prompt' => 'Generate a comprehensive design system for a [PROJECT_TYPE] application including color palette, typography, spacing, and component guidelines.',
                    'example' => 'Generate a comprehensive design system for a e-commerce application including color palette, typography, spacing, and component guidelines.',
                ],
            ],
            'database_admin' => [
                'database_schema' => [
                    'title' => 'Generate Database Schema',
                    'prompt' => 'Create a complete database schema for a [PROJECT_TYPE] application with the following entities: [ENTITIES]. Include relationships, indexes, and constraints.',
                    'example' => 'Create a complete database schema for a e-commerce application with the following entities: users, products, categories, orders, order_items, payments. Include relationships, indexes, and constraints.',
                ],
            ],
            'frontend_developer' => [
                'frontend_components' => [
                    'title' => 'Generate Livewire Components',
                    'prompt' => 'Create Livewire 3 components for [FEATURE] functionality in a Laravel application. Include component class, Blade template, and Alpine.js interactions.',
                    'example' => 'Create Livewire 3 components for shopping cart functionality in a Laravel application. Include component class, Blade template, and Alpine.js interactions.',
                ],
            ],
            'backend_developer' => [
                'backend_logic' => [
                    'title' => 'Generate Laravel Controllers',
                    'prompt' => 'Create Laravel 11 controllers and models for [FEATURE] with proper validation, relationships, and API endpoints.',
                    'example' => 'Create Laravel 11 controllers and models for order management with proper validation, relationships, and API endpoints.',
                ],
                'api_endpoints' => [
                    'title' => 'Generate API Endpoints',
                    'prompt' => 'Create RESTful API endpoints for [RESOURCE] management including CRUD operations, validation, and proper HTTP responses.',
                    'example' => 'Create RESTful API endpoints for product management including CRUD operations, validation, and proper HTTP responses.',
                ],
            ],
            'devops' => [
                'docker_config' => [
                    'title' => 'Generate Docker Configuration',
                    'prompt' => 'Create Docker configuration for a Laravel 11 application with [SERVICES]. Include Dockerfile, docker-compose.yml, and environment setup.',
                    'example' => 'Create Docker configuration for a Laravel 11 application with MySQL, Redis, and Nginx. Include Dockerfile, docker-compose.yml, and environment setup.',
                ],
                'deployment_config' => [
                    'title' => 'Generate CI/CD Pipeline',
                    'prompt' => 'Create GitHub Actions workflow for deploying a Laravel application to [PLATFORM] with testing, building, and deployment stages.',
                    'example' => 'Create GitHub Actions workflow for deploying a Laravel application to AWS with testing, building, and deployment stages.',
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
}

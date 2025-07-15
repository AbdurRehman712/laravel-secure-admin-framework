<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\ProjectWorkspaceContent;
use App\Services\AiResponseParser;
use Livewire\Component;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class AiContentCreator extends Component
{
    public Project $project;
    public string $role;
    public string $contentType = '';
    public string $title = '';
    public string $aiResponse = '';
    public array $promptTemplate = [];
    public bool $showForm = false;

    protected $rules = [
        'title' => 'required|string|max:255',
        'aiResponse' => 'required|string|min:10',
        'contentType' => 'required|string',
    ];

    public function mount(Project $project, string $role)
    {
        $this->project = $project;
        $this->role = $role;
    }

    public function openForm(string $contentType, array $template)
    {
        $this->contentType = $contentType;
        $this->promptTemplate = $template;
        $this->title = '';
        $this->aiResponse = '';
        $this->showForm = true;
    }

    public function closeForm()
    {
        $this->showForm = false;
        $this->reset(['title', 'aiResponse', 'contentType', 'promptTemplate']);
    }

    public function copyPrompt()
    {
        $this->dispatch('copy-to-clipboard', text: $this->promptTemplate['prompt'] ?? '');
        
        Notification::make()
            ->title('Prompt Copied!')
            ->body('The AI prompt has been copied to your clipboard.')
            ->success()
            ->send();
    }

    public function saveContent()
    {
        $this->validate();

        try {
            // Parse the AI response
            $parser = new AiResponseParser();
            $parsedData = $parser->parse($this->aiResponse, $this->contentType, $this->role);

            // Create the workspace content
            $content = ProjectWorkspaceContent::create([
                'project_id' => $this->project->id,
                'admin_id' => Auth::guard('admin')->id(),
                'role' => $this->role,
                'content_type' => $this->contentType,
                'title' => $this->title,
                'content' => ['raw' => $this->aiResponse],
                'ai_prompt_used' => $this->promptTemplate,
                'parsed_data' => $parsedData,
                'status' => 'draft',
                'version' => 1,
            ]);

            Notification::make()
                ->title('Content Saved!')
                ->body("Your {$this->getContentTypeLabel()} has been saved successfully.")
                ->success()
                ->send();

            // Refresh the parent component
            $this->dispatch('content-created');
            
            $this->closeForm();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error Saving Content')
                ->body('There was an error saving your content. Please try again.')
                ->danger()
                ->send();
        }
    }

    public function getContentTypeLabel(): string
    {
        return ProjectWorkspaceContent::getContentTypes()[$this->contentType] ?? $this->contentType;
    }

    public function getAvailableContentTypes(): array
    {
        return ProjectWorkspaceContent::getContentTypesByRole()[$this->role] ?? [];
    }

    public function getPromptTemplates(): array
    {
        return match ($this->role) {
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

    public function render()
    {
        return view('livewire.ai-content-creator', [
            'promptTemplates' => $this->getPromptTemplates(),
            'availableContentTypes' => $this->getAvailableContentTypes(),
        ]);
    }
}

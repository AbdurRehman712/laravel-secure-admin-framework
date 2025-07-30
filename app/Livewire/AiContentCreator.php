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

    protected $listeners = [
        'role-switched' => 'updateRole',
    ];

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

    public function updateRole($role)
    {
        $this->role = $role;
        $this->reset(['contentType', 'title', 'aiResponse', 'promptTemplate', 'showForm']);
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

    public function copyPrompt($templateKey = null)
    {
        $templates = $this->getPromptTemplates();
        $prompt = $templateKey ? ($templates[$templateKey]['prompt'] ?? '') : ($this->promptTemplate['prompt'] ?? '');

        $this->dispatch('copy-to-clipboard', text: $prompt);

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
                    'sample_response' => "1. User Registration and Authentication\nAs a new customer, I want to create an account so that I can save my preferences and order history.\nGiven I am on the registration page\nWhen I enter valid email, password, and personal details\nThen I should receive a confirmation email\nAnd I should be able to log in with my credentials\n\n2. Product Browsing and Search\nAs a customer, I want to browse and search for products so that I can find items I want to purchase.\nGiven I am on the homepage\nWhen I use the search bar or browse categories\nThen I should see relevant products with images, prices, and descriptions\nAnd I should be able to filter and sort results\n\n3. Shopping Cart Management\nAs a customer, I want to add products to my cart so that I can purchase multiple items at once.\nGiven I am viewing a product\nWhen I click 'Add to Cart'\nThen the product should be added to my cart\nAnd I should see the updated cart count\nAnd I should be able to modify quantities or remove items\n\n4. Secure Checkout Process\nAs a customer, I want to complete my purchase securely so that I can receive my ordered items.\nGiven I have items in my cart\nWhen I proceed to checkout\nThen I should be able to enter shipping and payment information\nAnd I should receive an order confirmation\nAnd I should receive email notifications about order status",
                ],
                'acceptance_criteria' => [
                    'title' => 'Create Acceptance Criteria',
                    'prompt' => 'Create detailed acceptance criteria for the following user story: [USER_STORY]. Use Given-When-Then format.',
                    'example' => 'Create detailed acceptance criteria for the following user story: As a customer, I want to add products to my shopping cart so that I can purchase multiple items at once. Use Given-When-Then format.',
                    'sample_response' => "Given I am viewing a product page\nWhen I click the 'Add to Cart' button\nThen the product should be added to my shopping cart\nAnd the cart icon should show the updated item count\nAnd I should see a success notification\n\nGiven I have items in my cart\nWhen I view my shopping cart\nThen I should see all added products with their details\nAnd I should be able to modify quantities\nAnd I should be able to remove items\nAnd I should see the total price calculation\n\nGiven my cart is empty\nWhen I try to proceed to checkout\nThen I should see a message indicating the cart is empty\nAnd I should be redirected to continue shopping\n\nGiven I add the same product multiple times\nWhen I view my cart\nThen the quantity should be incremented\nAnd the price should be calculated correctly",
                ],
                'project_planning' => [
                    'title' => 'Create Project Plan',
                    'prompt' => 'Create a comprehensive project plan for [PROJECT_TYPE] application including phases, milestones, and deliverables.',
                    'example' => 'Create a comprehensive project plan for e-commerce application including phases, milestones, and deliverables.',
                    'sample_response' => "# E-commerce Application Project Plan\n\n## Phase 1: Foundation & Setup (Weeks 1-2)\n### Milestones:\n- Project setup and environment configuration\n- Database design and initial migration\n- Basic authentication system\n\n### Deliverables:\n- Laravel application setup\n- Database schema\n- User registration/login functionality\n- Admin panel setup\n\n## Phase 2: Core Features (Weeks 3-6)\n### Milestones:\n- Product catalog implementation\n- Shopping cart functionality\n- Basic order processing\n\n### Deliverables:\n- Product management system\n- Category management\n- Shopping cart with CRUD operations\n- Basic checkout process\n\n## Phase 3: Advanced Features (Weeks 7-10)\n### Milestones:\n- Payment integration\n- Order management system\n- Email notifications\n\n### Deliverables:\n- Payment gateway integration\n- Order tracking system\n- Email notification system\n- Inventory management\n\n## Phase 4: Testing & Deployment (Weeks 11-12)\n### Milestones:\n- Comprehensive testing\n- Performance optimization\n- Production deployment\n\n### Deliverables:\n- Test suite completion\n- Performance optimization\n- Production deployment\n- Documentation",
                ],
            ],
            'database_backend_developer' => [
                'database_schema' => [
                    'title' => 'Generate Database Schema',
                    'prompt' => 'Create a complete database schema for a [PROJECT_TYPE] application with the following entities: [ENTITIES]. Include relationships, indexes, and constraints.',
                    'example' => 'Create a complete database schema for a e-commerce application with the following entities: users, products, categories, orders, order_items, payments. Include relationships, indexes, and constraints.',
                    'sample_response' => "## users\nCREATE TABLE users (\n    id BIGINT PRIMARY KEY AUTO_INCREMENT,\n    name VARCHAR(255) NOT NULL,\n    email VARCHAR(255) UNIQUE NOT NULL,\n    email_verified_at TIMESTAMP NULL,\n    password VARCHAR(255) NOT NULL,\n    phone VARCHAR(20),\n    address TEXT,\n    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP\n);\n\n## categories\nCREATE TABLE categories (\n    id BIGINT PRIMARY KEY AUTO_INCREMENT,\n    name VARCHAR(255) NOT NULL,\n    slug VARCHAR(255) UNIQUE NOT NULL,\n    description TEXT,\n    parent_id BIGINT NULL,\n    is_active BOOLEAN DEFAULT TRUE,\n    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL\n);\n\n## products\nCREATE TABLE products (\n    id BIGINT PRIMARY KEY AUTO_INCREMENT,\n    name VARCHAR(255) NOT NULL,\n    slug VARCHAR(255) UNIQUE NOT NULL,\n    description TEXT,\n    price DECIMAL(10,2) NOT NULL,\n    category_id BIGINT NOT NULL,\n    stock_quantity INT DEFAULT 0,\n    is_active BOOLEAN DEFAULT TRUE,\n    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,\n    INDEX idx_category (category_id),\n    INDEX idx_active (is_active)\n);\n\n## orders\nCREATE TABLE orders (\n    id BIGINT PRIMARY KEY AUTO_INCREMENT,\n    user_id BIGINT NOT NULL,\n    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',\n    total_amount DECIMAL(10,2) NOT NULL,\n    shipping_address TEXT NOT NULL,\n    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,\n    INDEX idx_user (user_id),\n    INDEX idx_status (status)\n);",
                ],
                'api_endpoints' => [
                    'title' => 'Generate API Endpoints',
                    'prompt' => 'Create RESTful API endpoints for [RESOURCE] management including CRUD operations, validation, and proper HTTP responses.',
                    'example' => 'Create RESTful API endpoints for product management including CRUD operations, validation, and proper HTTP responses.',
                    'sample_response' => "# Product Management API Endpoints\n\n## GET /api/products\n**Description:** Retrieve all products with pagination\n**Method:** GET\n**Parameters:** page, per_page, category_id, search\n**Response:** 200 OK\n```json\n{\n  \"data\": [\n    {\n      \"id\": 1,\n      \"name\": \"Product Name\",\n      \"slug\": \"product-name\",\n      \"price\": 29.99,\n      \"category\": \"Electronics\"\n    }\n  ],\n  \"meta\": {\n    \"current_page\": 1,\n    \"total\": 100\n  }\n}\n```\n\n## POST /api/products\n**Description:** Create a new product\n**Method:** POST\n**Authentication:** Required\n**Validation:** name (required), price (required, numeric), category_id (required, exists)\n**Response:** 201 Created\n\n## GET /api/products/{id}\n**Description:** Retrieve a specific product\n**Method:** GET\n**Response:** 200 OK or 404 Not Found\n\n## PUT /api/products/{id}\n**Description:** Update a product\n**Method:** PUT\n**Authentication:** Required\n**Response:** 200 OK or 404 Not Found\n\n## DELETE /api/products/{id}\n**Description:** Delete a product\n**Method:** DELETE\n**Authentication:** Required\n**Response:** 204 No Content or 404 Not Found",
                ],
                'backend_logic' => [
                    'title' => 'Generate Laravel Controllers',
                    'prompt' => 'Create Laravel 11 controllers and models for [FEATURE] with proper validation, relationships, and API endpoints.',
                    'example' => 'Create Laravel 11 controllers and models for order management with proper validation, relationships, and API endpoints.',
                    'sample_response' => "# Order Management System\n\n## OrderController Features\n- CRUD operations for orders\n- Status management (pending, processing, shipped, delivered, cancelled)\n- User relationship handling\n- Validation and error handling\n\n## Key Methods\n1. index() - List orders with filtering\n2. store() - Create new order with validation\n3. show() - Display specific order details\n4. update() - Update order status\n5. destroy() - Cancel/delete orders\n\n## Validation Rules\n- user_id: required, must exist in users table\n- items: required array with minimum 1 item\n- product_id: required for each item, must exist\n- quantity: required integer, minimum 1\n- shipping_address: required string\n\n## Response Format\nJSON responses with proper HTTP status codes:\n- 200 OK for successful retrieval\n- 201 Created for new orders\n- 404 Not Found for missing orders\n- 422 Unprocessable Entity for validation errors",
                ],
            ],
            'project_manager' => [
                'deployment_config' => [
                    'title' => 'Generate CI/CD Pipeline',
                    'prompt' => 'Create GitHub Actions workflow for deploying a Laravel application to [PLATFORM] with testing, building, and deployment stages.',
                    'example' => 'Create GitHub Actions workflow for deploying a Laravel application to AWS with testing, building, and deployment stages.',
                    'sample_response' => "# GitHub Actions CI/CD Pipeline\n\n```yaml\nname: Laravel CI/CD Pipeline\n\non:\n  push:\n    branches: [ main, develop ]\n  pull_request:\n    branches: [ main ]\n\njobs:\n  test:\n    runs-on: ubuntu-latest\n    \n    services:\n      mysql:\n        image: mysql:8.0\n        env:\n          MYSQL_ROOT_PASSWORD: password\n          MYSQL_DATABASE: testing\n        options: --health-cmd=\"mysqladmin ping\" --health-interval=10s --health-timeout=5s --health-retries=3\n    \n    steps:\n    - uses: actions/checkout@v3\n    \n    - name: Setup PHP\n      uses: shivammathur/setup-php@v2\n      with:\n        php-version: '8.2'\n        extensions: mbstring, dom, fileinfo, mysql\n    \n    - name: Install dependencies\n      run: composer install --no-progress --prefer-dist --optimize-autoloader\n    \n    - name: Generate key\n      run: php artisan key:generate\n    \n    - name: Run tests\n      run: php artisan test\n      env:\n        DB_CONNECTION: mysql\n        DB_HOST: 127.0.0.1\n        DB_PORT: 3306\n        DB_DATABASE: testing\n        DB_USERNAME: root\n        DB_PASSWORD: password\n\n  deploy:\n    needs: test\n    runs-on: ubuntu-latest\n    if: github.ref == 'refs/heads/main'\n    \n    steps:\n    - name: Deploy to production\n      run: echo 'Deploying to AWS...'\n```",
                ],
                'project_planning' => [
                    'title' => 'Create Project Plan',
                    'prompt' => 'Create a detailed project plan for [PROJECT_TYPE] with timeline, resource allocation, and risk management.',
                    'example' => 'Create a detailed project plan for e-commerce platform with timeline, resource allocation, and risk management.',
                    'sample_response' => "# E-commerce Platform Project Plan\n\n## Project Overview\n**Duration:** 12 weeks\n**Team Size:** 5 developers\n**Budget:** $150,000\n\n## Phase 1: Foundation (Weeks 1-3)\n### Timeline: 3 weeks\n### Resources:\n- 1 Backend Developer\n- 1 Database Administrator\n- 1 Project Manager\n\n### Deliverables:\n- Project setup and environment configuration\n- Database design and initial migrations\n- User authentication system\n- Admin panel foundation\n\n### Risks:\n- Database design complexity\n- **Mitigation:** Early stakeholder review\n\n## Phase 2: Core Features (Weeks 4-8)\n### Timeline: 5 weeks\n### Resources:\n- 2 Backend Developers\n- 1 Frontend Developer\n- 1 UI/UX Designer\n\n### Deliverables:\n- Product catalog with categories\n- Shopping cart functionality\n- Order processing system\n- Payment gateway integration\n\n### Risks:\n- Payment integration complexity\n- **Mitigation:** Use established payment providers\n\n## Phase 3: Advanced Features (Weeks 9-11)\n### Timeline: 3 weeks\n### Resources:\n- 1 Backend Developer\n- 1 Frontend Developer\n- 1 QA Tester\n\n### Deliverables:\n- Inventory management\n- Order tracking system\n- Email notification system\n- Admin reporting dashboard\n\n## Phase 4: Testing & Deployment (Week 12)\n### Timeline: 1 week\n### Resources:\n- Full team\n\n### Deliverables:\n- Comprehensive testing\n- Performance optimization\n- Production deployment\n- Documentation and training\n\n### Critical Success Factors:\n- Regular stakeholder communication\n- Agile development methodology\n- Continuous integration/deployment\n- Comprehensive testing strategy",
                ],
                'testing_strategy' => [
                    'title' => 'Define Testing Strategy',
                    'prompt' => 'Define comprehensive testing strategy for [PROJECT_TYPE] including unit tests, integration tests, and deployment procedures.',
                    'example' => 'Define comprehensive testing strategy for e-commerce platform including unit tests, integration tests, and deployment procedures.',
                    'sample_response' => "# E-commerce Testing Strategy\n\n## 1. Unit Testing\n### Coverage Target: 80%\n### Tools: PHPUnit, Laravel Testing\n### Focus Areas:\n- Model relationships and validations\n- Service layer business logic\n- Helper functions and utilities\n- API endpoint responses\n\n### Example Tests:\n- User registration validation\n- Product price calculations\n- Order status transitions\n- Payment processing logic\n\n## 2. Integration Testing\n### Tools: Laravel Feature Tests, Database Testing\n### Focus Areas:\n- API endpoint workflows\n- Database transactions\n- Third-party service integrations\n- Email notification systems\n\n### Test Scenarios:\n- Complete checkout process\n- Payment gateway integration\n- Order fulfillment workflow\n- User authentication flow\n\n## 3. End-to-End Testing\n### Tools: Laravel Dusk, Selenium\n### Focus Areas:\n- Critical user journeys\n- Cross-browser compatibility\n- Mobile responsiveness\n- Performance under load\n\n### Test Cases:\n- User registration to first purchase\n- Product search and filtering\n- Cart management and checkout\n- Admin order management\n\n## 4. Performance Testing\n### Tools: Apache JMeter, Laravel Telescope\n### Metrics:\n- Page load times < 2 seconds\n- API response times < 500ms\n- Concurrent user capacity: 1000+\n- Database query optimization\n\n## 5. Security Testing\n### Focus Areas:\n- SQL injection prevention\n- XSS protection\n- CSRF token validation\n- Authentication security\n- Payment data protection\n\n## 6. Deployment Procedures\n### Pre-deployment Checklist:\n- All tests passing\n- Code review completed\n- Database migrations tested\n- Environment variables configured\n- Backup procedures verified\n\n### Deployment Steps:\n1. Run full test suite\n2. Deploy to staging environment\n3. Smoke testing on staging\n4. Production deployment\n5. Post-deployment verification\n6. Monitor system performance",
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

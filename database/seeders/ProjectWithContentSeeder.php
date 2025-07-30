<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\ProjectWorkspaceContent;
use App\Services\AiResponseParser;
use Modules\Core\app\Models\Admin;

class ProjectWithContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clean up existing data
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        ProjectWorkspaceContent::truncate();
        Project::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Get the first admin user
        $admin = Admin::first();

        if (!$admin) {
            $this->command->error('No admin users found. Please run the admin seeder first.');
            return;
        }

        $this->command->info('Creating projects with comprehensive AI-generated content...');

        // Create E-commerce Platform with full content
        $this->createEcommerceProject($admin);

        // Create Task Management System with full content
        $this->createTaskManagementProject($admin);

        // Create Blog Platform with full content
        $this->createBlogProject($admin);

        // Create CRM System with full content
        $this->createCrmProject($admin);

        // Create Learning Management System with full content
        $this->createLmsProject($admin);

        // Create Real Estate Platform with full content
        $this->createRealEstateProject($admin);

        // Create Restaurant Management System with full content
        $this->createRestaurantProject($admin);

        $this->command->info('✅ Created 7 comprehensive projects with AI content for module generation!');
    }

    private function createEcommerceProject($admin)
    {
        $project = Project::create([
            'name' => 'E-commerce Platform',
            'slug' => 'ecommerce-platform',
            'description' => 'A comprehensive e-commerce platform with product catalog, shopping cart, payment processing, and order management.',
            'status' => 'development',
            'created_by' => $admin->id,
            'settings' => [
                'framework' => 'Laravel 11',
                'frontend' => 'Livewire 3 + Alpine.js',
                'styling' => 'Tailwind CSS',
                'database' => 'MySQL',
            ],
            'ai_context' => [
                'project_type' => 'E-commerce',
                'target_audience' => 'Small to medium businesses',
                'key_features' => ['Product catalog', 'Shopping cart', 'Payment processing', 'Order management'],
            ],
        ]);

        // Product Owner Content
        $this->createWorkspaceContent($project, $admin, 'product_owner', 'user_stories', 'E-commerce User Stories', 
            "1. User Registration and Authentication\nAs a new customer, I want to create an account so that I can save my preferences and order history.\nGiven I am on the registration page\nWhen I enter valid email, password, and personal details\nThen I should receive a confirmation email\nAnd I should be able to log in with my credentials\n\n2. Product Browsing and Search\nAs a customer, I want to browse and search for products so that I can find items I want to purchase.\nGiven I am on the homepage\nWhen I use the search bar or browse categories\nThen I should see relevant products with images, prices, and descriptions\nAnd I should be able to filter and sort results\n\n3. Shopping Cart Management\nAs a customer, I want to add products to my cart so that I can purchase multiple items at once.\nGiven I am viewing a product\nWhen I click 'Add to Cart'\nThen the product should be added to my cart\nAnd I should see the updated cart count\nAnd I should be able to modify quantities or remove items\n\n4. Secure Checkout Process\nAs a customer, I want to complete my purchase securely so that I can receive my ordered items.\nGiven I have items in my cart\nWhen I proceed to checkout\nThen I should be able to enter shipping and payment information\nAnd I should receive an order confirmation\nAnd I should receive email notifications about order status"
        );

        // Database & Backend Developer Content
        $this->createWorkspaceContent($project, $admin, 'database_backend_developer', 'database_schema', 'E-commerce Database Schema',
            "## users\nCREATE TABLE users (\n    id BIGINT PRIMARY KEY AUTO_INCREMENT,\n    name VARCHAR(255) NOT NULL,\n    email VARCHAR(255) UNIQUE NOT NULL,\n    email_verified_at TIMESTAMP NULL,\n    password VARCHAR(255) NOT NULL,\n    phone VARCHAR(20),\n    address TEXT,\n    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP\n);\n\n## categories\nCREATE TABLE categories (\n    id BIGINT PRIMARY KEY AUTO_INCREMENT,\n    name VARCHAR(255) NOT NULL,\n    slug VARCHAR(255) UNIQUE NOT NULL,\n    description TEXT,\n    parent_id BIGINT NULL,\n    is_active BOOLEAN DEFAULT TRUE,\n    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL\n);\n\n## products\nCREATE TABLE products (\n    id BIGINT PRIMARY KEY AUTO_INCREMENT,\n    name VARCHAR(255) NOT NULL,\n    slug VARCHAR(255) UNIQUE NOT NULL,\n    description TEXT,\n    price DECIMAL(10,2) NOT NULL,\n    category_id BIGINT NOT NULL,\n    stock_quantity INT DEFAULT 0,\n    is_active BOOLEAN DEFAULT TRUE,\n    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,\n    INDEX idx_category (category_id),\n    INDEX idx_active (is_active)\n);\n\n## orders\nCREATE TABLE orders (\n    id BIGINT PRIMARY KEY AUTO_INCREMENT,\n    user_id BIGINT NOT NULL,\n    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',\n    total_amount DECIMAL(10,2) NOT NULL,\n    shipping_address TEXT NOT NULL,\n    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,\n    INDEX idx_user (user_id),\n    INDEX idx_status (status)\n);"
        );

        // Project Manager Content
        $this->createWorkspaceContent($project, $admin, 'project_manager', 'project_planning', 'E-commerce Project Plan',
            "# E-commerce Platform Project Plan\n\n## Project Overview\n**Duration:** 12 weeks\n**Team Size:** 5 developers\n**Budget:** $150,000\n\n## Phase 1: Foundation (Weeks 1-3)\n### Timeline: 3 weeks\n### Resources:\n- 1 Backend Developer\n- 1 Database Administrator\n- 1 Project Manager\n\n### Deliverables:\n- Project setup and environment configuration\n- Database design and initial migrations\n- User authentication system\n- Admin panel foundation\n\n### Risks:\n- Database design complexity\n- **Mitigation:** Early stakeholder review\n\n## Phase 2: Core Features (Weeks 4-8)\n### Timeline: 5 weeks\n### Resources:\n- 2 Backend Developers\n- 1 Frontend Developer\n- 1 UI/UX Designer\n\n### Deliverables:\n- Product catalog with categories\n- Shopping cart functionality\n- Order processing system\n- Payment gateway integration\n\n### Risks:\n- Payment integration complexity\n- **Mitigation:** Use established payment providers\n\n## Phase 3: Advanced Features (Weeks 9-11)\n### Timeline: 3 weeks\n### Resources:\n- 1 Backend Developer\n- 1 Frontend Developer\n- 1 QA Tester\n\n### Deliverables:\n- Inventory management\n- Order tracking system\n- Email notification system\n- Admin reporting dashboard\n\n## Phase 4: Testing & Deployment (Week 12)\n### Timeline: 1 week\n### Resources:\n- Full team\n\n### Deliverables:\n- Comprehensive testing\n- Performance optimization\n- Production deployment\n- Documentation and training\n\n### Critical Success Factors:\n- Regular stakeholder communication\n- Agile development methodology\n- Continuous integration/deployment\n- Comprehensive testing strategy"
        );

        $this->command->info("✅ Created E-commerce Platform with comprehensive content");
    }

    private function createTaskManagementProject($admin)
    {
        $project = Project::create([
            'name' => 'Task Management System',
            'slug' => 'task-management-system',
            'description' => 'A collaborative task management system with project organization, team collaboration, and progress tracking.',
            'status' => 'development',
            'created_by' => $admin->id,
            'settings' => [
                'framework' => 'Laravel 11',
                'frontend' => 'Livewire 3 + Alpine.js',
                'styling' => 'Tailwind CSS',
                'database' => 'MySQL',
            ],
            'ai_context' => [
                'project_type' => 'Task Management',
                'target_audience' => 'Teams and organizations',
                'key_features' => ['Project management', 'Task tracking', 'Team collaboration', 'Progress reporting'],
            ],
        ]);

        // Add comprehensive content for task management
        $this->createWorkspaceContent($project, $admin, 'product_owner', 'user_stories', 'Task Management User Stories',
            "1. Project Creation and Management\nAs a project manager, I want to create and organize projects so that I can manage multiple initiatives effectively.\nGiven I am logged into the system\nWhen I create a new project with name, description, and team members\nThen the project should be created with proper permissions\nAnd team members should be notified\nAnd I should be able to manage project settings\n\n2. Task Creation and Assignment\nAs a team member, I want to create and assign tasks so that work can be distributed effectively.\nGiven I am in a project workspace\nWhen I create a task with title, description, assignee, and due date\nThen the task should be created and assigned\nAnd the assignee should receive a notification\nAnd the task should appear in relevant dashboards\n\n3. Progress Tracking and Updates\nAs a team member, I want to update task progress so that everyone knows the current status.\nGiven I have assigned tasks\nWhen I update task status, add comments, or upload files\nThen the changes should be saved and logged\nAnd relevant team members should be notified\nAnd progress should be reflected in project dashboards"
        );

        $this->createWorkspaceContent($project, $admin, 'database_backend_developer', 'database_schema', 'Task Management Database Schema',
            "## projects\nCREATE TABLE projects (\n    id BIGINT PRIMARY KEY AUTO_INCREMENT,\n    name VARCHAR(255) NOT NULL,\n    slug VARCHAR(255) UNIQUE NOT NULL,\n    description TEXT,\n    status ENUM('planning', 'active', 'completed', 'archived') DEFAULT 'planning',\n    owner_id BIGINT NOT NULL,\n    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE\n);\n\n## tasks\nCREATE TABLE tasks (\n    id BIGINT PRIMARY KEY AUTO_INCREMENT,\n    title VARCHAR(255) NOT NULL,\n    description TEXT,\n    project_id BIGINT NOT NULL,\n    assigned_to BIGINT NULL,\n    created_by BIGINT NOT NULL,\n    status ENUM('todo', 'in_progress', 'review', 'completed') DEFAULT 'todo',\n    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',\n    due_date DATE NULL,\n    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,\n    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,\n    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE\n);\n\n## project_members\nCREATE TABLE project_members (\n    id BIGINT PRIMARY KEY AUTO_INCREMENT,\n    project_id BIGINT NOT NULL,\n    user_id BIGINT NOT NULL,\n    role ENUM('owner', 'admin', 'member', 'viewer') DEFAULT 'member',\n    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,\n    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,\n    UNIQUE KEY unique_project_user (project_id, user_id)\n);"
        );

        $this->command->info("✅ Created Task Management System with comprehensive content");
    }

    private function createBlogProject($admin)
    {
        $project = Project::create([
            'name' => 'Blog Platform',
            'slug' => 'blog-platform',
            'description' => 'A modern blog platform with content management, user engagement, and SEO optimization.',
            'status' => 'development',
            'created_by' => $admin->id,
            'settings' => [
                'framework' => 'Laravel 11',
                'frontend' => 'Livewire 3 + Alpine.js',
                'styling' => 'Tailwind CSS',
                'database' => 'MySQL',
            ],
            'ai_context' => [
                'project_type' => 'Blog Platform',
                'target_audience' => 'Content creators and bloggers',
                'key_features' => ['Content management', 'User engagement', 'SEO optimization', 'Social sharing'],
            ],
        ]);

        // Add blog platform content
        $this->createWorkspaceContent($project, $admin, 'product_owner', 'user_stories', 'Blog Platform User Stories',
            "1. Content Creation and Publishing\nAs a blogger, I want to create and publish articles so that I can share my content with readers.\nGiven I am logged into the admin panel\nWhen I create a new post with title, content, categories, and tags\nThen the post should be saved as draft or published\nAnd readers should be able to view published posts\nAnd SEO metadata should be automatically generated\n\n2. Reader Engagement\nAs a reader, I want to interact with blog content so that I can engage with the community.\nGiven I am viewing a blog post\nWhen I leave comments, like posts, or share content\nThen my interactions should be recorded\nAnd the author should be notified of comments\nAnd social sharing should work correctly\n\n3. Content Management\nAs a blog administrator, I want to manage all content so that I can maintain quality and organization.\nGiven I have admin privileges\nWhen I moderate comments, organize categories, or manage users\nThen changes should be applied immediately\nAnd appropriate notifications should be sent\nAnd content should remain well-organized"
        );

        $this->createWorkspaceContent($project, $admin, 'database_backend_developer', 'database_schema', 'Blog Platform Database Schema',
            "## posts\nCREATE TABLE posts (\n    id BIGINT PRIMARY KEY AUTO_INCREMENT,\n    title VARCHAR(255) NOT NULL,\n    slug VARCHAR(255) UNIQUE NOT NULL,\n    content LONGTEXT NOT NULL,\n    excerpt TEXT,\n    author_id BIGINT NOT NULL,\n    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',\n    published_at TIMESTAMP NULL,\n    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,\n    INDEX idx_status (status),\n    INDEX idx_published (published_at)\n);\n\n## categories\nCREATE TABLE categories (\n    id BIGINT PRIMARY KEY AUTO_INCREMENT,\n    name VARCHAR(255) NOT NULL,\n    slug VARCHAR(255) UNIQUE NOT NULL,\n    description TEXT,\n    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP\n);\n\n## comments\nCREATE TABLE comments (\n    id BIGINT PRIMARY KEY AUTO_INCREMENT,\n    post_id BIGINT NOT NULL,\n    author_name VARCHAR(255) NOT NULL,\n    author_email VARCHAR(255) NOT NULL,\n    content TEXT NOT NULL,\n    status ENUM('pending', 'approved', 'spam') DEFAULT 'pending',\n    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,\n    INDEX idx_status (status)\n);"
        );

        $this->command->info("✅ Created Blog Platform with comprehensive content");
    }

    private function createWorkspaceContent($project, $admin, $role, $contentType, $title, $aiResponse)
    {
        $parser = new AiResponseParser();
        $parsedData = $parser->parse($aiResponse, $contentType, $role);

        ProjectWorkspaceContent::create([
            'project_id' => $project->id,
            'admin_id' => $admin->id,
            'role' => $role,
            'content_type' => $contentType,
            'title' => $title,
            'content' => ['raw' => $aiResponse],
            'ai_prompt_used' => [
                'title' => "Generated {$title}",
                'prompt' => "Generated comprehensive {$contentType} for {$project->name}",
            ],
            'parsed_data' => $parsedData,
            'status' => 'approved',
            'version' => 1,
        ]);
    }

    private function createCrmProject($admin)
    {
        $project = Project::create([
            'name' => 'CRM System',
            'slug' => 'crm-system',
            'description' => 'A comprehensive Customer Relationship Management system with lead tracking, sales pipeline, and customer management.',
            'status' => 'development',
            'created_by' => $admin->id,
            'settings' => [
                'framework' => 'Laravel 11',
                'frontend' => 'Livewire 3 + Alpine.js',
                'styling' => 'Tailwind CSS',
                'database' => 'MySQL',
            ],
            'ai_context' => [
                'project_type' => 'CRM',
                'target_audience' => 'Sales teams and businesses',
                'key_features' => ['Lead management', 'Sales pipeline', 'Customer tracking', 'Reporting'],
            ],
        ]);

        // Database Backend Developer content
        $this->createWorkspaceContent($project, $admin, 'database_backend_developer', 'database_schema', 'CRM Database Schema', '
CREATE TABLE companies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    website VARCHAR(255),
    address TEXT,
    industry VARCHAR(100),
    size ENUM(\'startup\', \'small\', \'medium\', \'large\', \'enterprise\') DEFAULT \'small\',
    status ENUM(\'active\', \'inactive\', \'prospect\') DEFAULT \'prospect\',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_industry (industry),
    INDEX idx_status (status)
);

CREATE TABLE contacts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE,
    phone VARCHAR(50),
    position VARCHAR(100),
    department VARCHAR(100),
    is_primary BOOLEAN DEFAULT FALSE,
    status ENUM(\'active\', \'inactive\') DEFAULT \'active\',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_company (company_id),
    INDEX idx_email (email)
);

CREATE TABLE leads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED,
    contact_id BIGINT UNSIGNED,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    source ENUM(\'website\', \'referral\', \'social_media\', \'email\', \'phone\', \'event\') DEFAULT \'website\',
    status ENUM(\'new\', \'contacted\', \'qualified\', \'proposal\', \'negotiation\', \'closed_won\', \'closed_lost\') DEFAULT \'new\',
    value DECIMAL(12,2),
    probability INT DEFAULT 0,
    expected_close_date DATE,
    assigned_to BIGINT UNSIGNED,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_assigned (assigned_to),
    INDEX idx_close_date (expected_close_date)
);

CREATE TABLE opportunities (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id BIGINT UNSIGNED,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    amount DECIMAL(12,2) NOT NULL,
    stage ENUM(\'prospecting\', \'qualification\', \'proposal\', \'negotiation\', \'closed_won\', \'closed_lost\') DEFAULT \'prospecting\',
    probability INT DEFAULT 0,
    close_date DATE,
    assigned_to BIGINT UNSIGNED,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    INDEX idx_stage (stage),
    INDEX idx_close_date (close_date)
);
        ');
    }

    private function createLmsProject($admin)
    {
        $project = Project::create([
            'name' => 'Learning Management System',
            'slug' => 'learning-management-system',
            'description' => 'A comprehensive Learning Management System with courses, students, assessments, and progress tracking.',
            'status' => 'development',
            'created_by' => $admin->id,
            'settings' => [
                'framework' => 'Laravel 11',
                'frontend' => 'Livewire 3 + Alpine.js',
                'styling' => 'Tailwind CSS',
                'database' => 'MySQL',
            ],
            'ai_context' => [
                'project_type' => 'LMS',
                'target_audience' => 'Educational institutions and corporate training',
                'key_features' => ['Course management', 'Student enrollment', 'Assessments', 'Progress tracking'],
            ],
        ]);

        // Database Backend Developer content
        $this->createWorkspaceContent($project, $admin, 'database_backend_developer', 'database_schema', 'LMS Database Schema', '
CREATE TABLE instructors (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(50),
    bio TEXT,
    expertise TEXT,
    status ENUM(\'active\', \'inactive\') DEFAULT \'active\',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_status (status)
);

CREATE TABLE courses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    instructor_id BIGINT UNSIGNED,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    description TEXT,
    objectives TEXT,
    duration_hours INT,
    difficulty_level ENUM(\'beginner\', \'intermediate\', \'advanced\') DEFAULT \'beginner\',
    price DECIMAL(8,2),
    is_published BOOLEAN DEFAULT FALSE,
    max_students INT,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (instructor_id) REFERENCES instructors(id) ON DELETE SET NULL,
    INDEX idx_instructor (instructor_id),
    INDEX idx_published (is_published),
    INDEX idx_difficulty (difficulty_level)
);

CREATE TABLE students (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(50),
    date_of_birth DATE,
    enrollment_date DATE,
    status ENUM(\'active\', \'inactive\', \'graduated\', \'suspended\') DEFAULT \'active\',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_status (status)
);

CREATE TABLE enrollments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED,
    course_id BIGINT UNSIGNED,
    enrollment_date DATE,
    completion_date DATE NULL,
    progress_percentage INT DEFAULT 0,
    status ENUM(\'enrolled\', \'in_progress\', \'completed\', \'dropped\') DEFAULT \'enrolled\',
    grade DECIMAL(5,2) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (student_id, course_id),
    INDEX idx_status (status),
    INDEX idx_progress (progress_percentage)
);
        ');
    }

    private function createRealEstateProject($admin)
    {
        $project = Project::create([
            'name' => 'Real Estate Platform',
            'slug' => 'real-estate-platform',
            'description' => 'A comprehensive real estate platform with property listings, agent management, and client inquiries.',
            'status' => 'development',
            'created_by' => $admin->id,
            'settings' => [
                'framework' => 'Laravel 11',
                'frontend' => 'Livewire 3 + Alpine.js',
                'styling' => 'Tailwind CSS',
                'database' => 'MySQL',
            ],
            'ai_context' => [
                'project_type' => 'Real Estate',
                'target_audience' => 'Real estate agencies and property managers',
                'key_features' => ['Property listings', 'Agent management', 'Client inquiries', 'Virtual tours'],
            ],
        ]);

        // Database Backend Developer content
        $this->createWorkspaceContent($project, $admin, 'database_backend_developer', 'database_schema', 'Real Estate Database Schema', '
CREATE TABLE agents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(50),
    license_number VARCHAR(100),
    bio TEXT,
    photo VARCHAR(255),
    commission_rate DECIMAL(5,2) DEFAULT 3.00,
    status ENUM(\'active\', \'inactive\') DEFAULT \'active\',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_license (license_number)
);

CREATE TABLE properties (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    agent_id BIGINT UNSIGNED,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    property_type ENUM(\'house\', \'apartment\', \'condo\', \'townhouse\', \'land\', \'commercial\') NOT NULL,
    listing_type ENUM(\'sale\', \'rent\') NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    bedrooms INT,
    bathrooms DECIMAL(3,1),
    square_feet INT,
    lot_size DECIMAL(10,2),
    address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(50) NOT NULL,
    zip_code VARCHAR(20) NOT NULL,
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    status ENUM(\'available\', \'pending\', \'sold\', \'rented\', \'off_market\') DEFAULT \'available\',
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE SET NULL,
    INDEX idx_agent (agent_id),
    INDEX idx_type (property_type),
    INDEX idx_listing_type (listing_type),
    INDEX idx_price (price),
    INDEX idx_location (city, state),
    INDEX idx_status (status)
);

CREATE TABLE clients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(50),
    client_type ENUM(\'buyer\', \'seller\', \'renter\', \'landlord\') NOT NULL,
    budget_min DECIMAL(12,2),
    budget_max DECIMAL(12,2),
    preferred_locations TEXT,
    status ENUM(\'active\', \'inactive\') DEFAULT \'active\',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_type (client_type),
    INDEX idx_budget (budget_min, budget_max)
);

CREATE TABLE inquiries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id BIGINT UNSIGNED,
    client_id BIGINT UNSIGNED,
    agent_id BIGINT UNSIGNED,
    message TEXT,
    inquiry_type ENUM(\'viewing\', \'information\', \'offer\', \'general\') DEFAULT \'general\',
    status ENUM(\'new\', \'contacted\', \'scheduled\', \'completed\', \'closed\') DEFAULT \'new\',
    scheduled_date DATETIME NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE SET NULL,
    INDEX idx_property (property_id),
    INDEX idx_client (client_id),
    INDEX idx_status (status)
);
        ');
    }

    private function createRestaurantProject($admin)
    {
        $project = Project::create([
            'name' => 'Restaurant Management System',
            'slug' => 'restaurant-management-system',
            'description' => 'A comprehensive restaurant management system with menu management, orders, reservations, and inventory tracking.',
            'status' => 'development',
            'created_by' => $admin->id,
            'settings' => [
                'framework' => 'Laravel 11',
                'frontend' => 'Livewire 3 + Alpine.js',
                'styling' => 'Tailwind CSS',
                'database' => 'MySQL',
            ],
            'ai_context' => [
                'project_type' => 'Restaurant Management',
                'target_audience' => 'Restaurant owners and managers',
                'key_features' => ['Menu management', 'Order processing', 'Reservations', 'Inventory tracking'],
            ],
        ]);

        // Database Backend Developer content
        $this->createWorkspaceContent($project, $admin, 'database_backend_developer', 'database_schema', 'Restaurant Database Schema', '
CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_sort (sort_order),
    INDEX idx_active (is_active)
);

CREATE TABLE menu_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(8,2) NOT NULL,
    cost DECIMAL(8,2),
    preparation_time INT,
    calories INT,
    allergens TEXT,
    is_vegetarian BOOLEAN DEFAULT FALSE,
    is_vegan BOOLEAN DEFAULT FALSE,
    is_gluten_free BOOLEAN DEFAULT FALSE,
    is_available BOOLEAN DEFAULT TRUE,
    image VARCHAR(255),
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_category (category_id),
    INDEX idx_price (price),
    INDEX idx_available (is_available)
);

CREATE TABLE tables (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    table_number VARCHAR(20) NOT NULL UNIQUE,
    capacity INT NOT NULL,
    location ENUM(\'indoor\', \'outdoor\', \'private\', \'bar\') DEFAULT \'indoor\',
    status ENUM(\'available\', \'occupied\', \'reserved\', \'maintenance\') DEFAULT \'available\',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_capacity (capacity),
    INDEX idx_status (status)
);

CREATE TABLE reservations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    table_id BIGINT UNSIGNED,
    customer_name VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(50),
    customer_email VARCHAR(255),
    party_size INT NOT NULL,
    reservation_date DATE NOT NULL,
    reservation_time TIME NOT NULL,
    status ENUM(\'confirmed\', \'seated\', \'completed\', \'cancelled\', \'no_show\') DEFAULT \'confirmed\',
    special_requests TEXT,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE SET NULL,
    INDEX idx_table (table_id),
    INDEX idx_date_time (reservation_date, reservation_time),
    INDEX idx_status (status)
);

CREATE TABLE orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    table_id BIGINT UNSIGNED,
    reservation_id BIGINT UNSIGNED NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    customer_name VARCHAR(255),
    order_type ENUM(\'dine_in\', \'takeout\', \'delivery\') DEFAULT \'dine_in\',
    status ENUM(\'pending\', \'confirmed\', \'preparing\', \'ready\', \'served\', \'completed\', \'cancelled\') DEFAULT \'pending\',
    subtotal DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) NOT NULL,
    tip_amount DECIMAL(10,2) DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM(\'cash\', \'card\', \'mobile\') DEFAULT \'cash\',
    payment_status ENUM(\'pending\', \'paid\', \'refunded\') DEFAULT \'pending\',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE SET NULL,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE SET NULL,
    INDEX idx_table (table_id),
    INDEX idx_order_number (order_number),
    INDEX idx_status (status),
    INDEX idx_payment_status (payment_status)
);
        ');
    }
}

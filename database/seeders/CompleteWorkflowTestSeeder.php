<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\ProjectWorkspaceContent;
use Modules\Core\app\Models\Admin;

class CompleteWorkflowTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get team members
        $superAdmin = Admin::where('email', 'admin@admin.com')->first();
        $productOwner = Admin::where('email', 'product.owner@test.com')->first();
        $designer = Admin::where('email', 'designer@test.com')->first();
        $databaseAdmin = Admin::where('email', 'database.admin@test.com')->first();
        $frontendDev = Admin::where('email', 'frontend.dev@test.com')->first();
        $backendDev = Admin::where('email', 'backend.dev@test.com')->first();
        $devops = Admin::where('email', 'devops@test.com')->first();

        if (!$superAdmin) {
            $this->command->error('Super Admin not found. Please run the admin seeder first.');
            return;
        }

        // Create a comprehensive test project
        $project = Project::create([
            'name' => 'AI-Powered E-commerce Platform',
            'slug' => 'ai-ecommerce-platform',
            'description' => 'A complete e-commerce platform with AI-powered recommendations, real-time inventory management, and advanced analytics. This project demonstrates the full AI Development Platform workflow.',
            'status' => 'development',
            'created_by' => $superAdmin->id,
            'settings' => [
                'framework' => 'Laravel 11',
                'frontend' => 'Livewire 3 + Alpine.js',
                'styling' => 'Tailwind CSS',
                'database' => 'MySQL',
                'cache' => 'Redis',
                'queue' => 'Redis',
                'ai_features' => [
                    'product_recommendations',
                    'inventory_prediction',
                    'customer_analytics',
                    'automated_pricing'
                ]
            ],
            'ai_context' => [
                'project_type' => 'E-commerce with AI',
                'target_audience' => 'Medium to large businesses',
                'key_features' => [
                    'Product catalog with AI recommendations',
                    'Smart inventory management',
                    'Customer behavior analytics',
                    'Automated pricing optimization',
                    'Real-time order tracking',
                    'Multi-vendor marketplace',
                    'Advanced reporting dashboard'
                ],
                'technical_requirements' => [
                    'High performance',
                    'Scalable architecture',
                    'Real-time updates',
                    'API-first design',
                    'Mobile responsive',
                    'SEO optimized'
                ]
            ],
        ]);

        // Add team members to the project
        if ($productOwner) $project->addTeamMember($productOwner, 'product_owner');
        if ($designer) $project->addTeamMember($designer, 'designer');
        if ($databaseAdmin) $project->addTeamMember($databaseAdmin, 'database_admin');
        if ($frontendDev) $project->addTeamMember($frontendDev, 'frontend_developer');
        if ($backendDev) $project->addTeamMember($backendDev, 'backend_developer');
        if ($devops) $project->addTeamMember($devops, 'devops');

        // Create comprehensive workspace content for each role

        // 1. Product Owner Content - User Stories
        if ($productOwner) {
            ProjectWorkspaceContent::create([
                'project_id' => $project->id,
                'admin_id' => $productOwner->id,
                'role' => 'product_owner',
                'content_type' => 'user_stories',
                'title' => 'Core E-commerce User Stories',
                'content' => [
                    'raw' => 'Here are the comprehensive user stories for the AI-powered e-commerce platform:

1. **User Registration & Authentication**
As a new customer, I want to create an account quickly so that I can start shopping and track my orders.
- Given I am on the registration page
- When I enter valid email, password, and basic information
- Then I should receive a verification email and be able to log in

2. **AI-Powered Product Discovery**
As a customer, I want to see personalized product recommendations so that I can discover items I might like.
- Given I am browsing the website
- When I view products or add items to cart
- Then I should see relevant recommendations based on my behavior and preferences

3. **Smart Search & Filtering**
As a customer, I want to search for products with intelligent filters so that I can find exactly what I need.
- Given I am on the product catalog page
- When I enter search terms or apply filters
- Then I should see relevant results with AI-enhanced suggestions

4. **Shopping Cart & Checkout**
As a customer, I want a seamless checkout experience so that I can complete my purchase quickly.
- Given I have items in my cart
- When I proceed to checkout
- Then I should be able to complete payment with minimal steps

5. **Order Tracking & Management**
As a customer, I want to track my orders in real-time so that I know when to expect delivery.
- Given I have placed an order
- When I check my order status
- Then I should see real-time updates and delivery estimates

6. **Vendor Management**
As a vendor, I want to manage my products and inventory so that I can run my business effectively.
- Given I am a registered vendor
- When I access my dashboard
- Then I should be able to add products, manage inventory, and view sales analytics

7. **Admin Analytics Dashboard**
As an admin, I want comprehensive analytics so that I can make data-driven business decisions.
- Given I am logged in as an admin
- When I access the analytics dashboard
- Then I should see sales metrics, customer insights, and AI-generated recommendations'
                ],
                'ai_prompt_used' => [
                    'title' => 'Generate User Stories',
                    'prompt' => 'Generate detailed user stories for an AI-powered e-commerce platform with features: product recommendations, inventory management, multi-vendor support, analytics dashboard. Include acceptance criteria for each story.',
                ],
                'parsed_data' => [
                    'stories' => [
                        [
                            'title' => 'User Registration & Authentication',
                            'description' => 'As a new customer, I want to create an account quickly so that I can start shopping and track my orders.',
                            'acceptance_criteria' => [
                                'Given I am on the registration page',
                                'When I enter valid email, password, and basic information',
                                'Then I should receive a verification email and be able to log in'
                            ],
                            'priority' => 'high',
                            'story_points' => 5
                        ],
                        [
                            'title' => 'AI-Powered Product Discovery',
                            'description' => 'As a customer, I want to see personalized product recommendations so that I can discover items I might like.',
                            'acceptance_criteria' => [
                                'Given I am browsing the website',
                                'When I view products or add items to cart',
                                'Then I should see relevant recommendations based on my behavior and preferences'
                            ],
                            'priority' => 'high',
                            'story_points' => 13
                        ],
                        [
                            'title' => 'Smart Search & Filtering',
                            'description' => 'As a customer, I want to search for products with intelligent filters so that I can find exactly what I need.',
                            'acceptance_criteria' => [
                                'Given I am on the product catalog page',
                                'When I enter search terms or apply filters',
                                'Then I should see relevant results with AI-enhanced suggestions'
                            ],
                            'priority' => 'medium',
                            'story_points' => 8
                        ],
                        [
                            'title' => 'Shopping Cart & Checkout',
                            'description' => 'As a customer, I want a seamless checkout experience so that I can complete my purchase quickly.',
                            'acceptance_criteria' => [
                                'Given I have items in my cart',
                                'When I proceed to checkout',
                                'Then I should be able to complete payment with minimal steps'
                            ],
                            'priority' => 'high',
                            'story_points' => 8
                        ],
                        [
                            'title' => 'Order Tracking & Management',
                            'description' => 'As a customer, I want to track my orders in real-time so that I know when to expect delivery.',
                            'acceptance_criteria' => [
                                'Given I have placed an order',
                                'When I check my order status',
                                'Then I should see real-time updates and delivery estimates'
                            ],
                            'priority' => 'medium',
                            'story_points' => 5
                        ],
                        [
                            'title' => 'Vendor Management',
                            'description' => 'As a vendor, I want to manage my products and inventory so that I can run my business effectively.',
                            'acceptance_criteria' => [
                                'Given I am a registered vendor',
                                'When I access my dashboard',
                                'Then I should be able to add products, manage inventory, and view sales analytics'
                            ],
                            'priority' => 'medium',
                            'story_points' => 13
                        ],
                        [
                            'title' => 'Admin Analytics Dashboard',
                            'description' => 'As an admin, I want comprehensive analytics so that I can make data-driven business decisions.',
                            'acceptance_criteria' => [
                                'Given I am logged in as an admin',
                                'When I access the analytics dashboard',
                                'Then I should see sales metrics, customer insights, and AI-generated recommendations'
                            ],
                            'priority' => 'low',
                            'story_points' => 8
                        ]
                    ],
                    'total_count' => 7,
                    'total_story_points' => 60,
                    'parsed_at' => now()->toISOString()
                ],
                'status' => 'approved',
            ]);
        }

        $this->command->info("Created comprehensive test project: {$project->name}");
        $this->command->info("Added team members and workspace content");
        $this->command->info("Project ID: {$project->id}");
        $this->command->info("Project URL: http://127.0.0.1:8000/admin/project-workspace?project={$project->id}");
        $this->command->info('Complete workflow test seeder completed successfully!');
    }
}

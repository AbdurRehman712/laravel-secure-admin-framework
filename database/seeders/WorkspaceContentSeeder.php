<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\ProjectWorkspaceContent;
use Modules\Core\app\Models\Admin;

class WorkspaceContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Admin::first();
        $project = Project::first();
        
        if (!$admin || !$project) {
            $this->command->error('No admin users or projects found. Please run the admin and project seeders first.');
            return;
        }

        // Sample User Stories for Product Owner
        $userStoriesContent = ProjectWorkspaceContent::create([
            'project_id' => $project->id,
            'admin_id' => $admin->id,
            'role' => 'product_owner',
            'content_type' => 'user_stories',
            'title' => 'E-commerce Core User Stories',
            'content' => [
                'raw' => 'Here are the user stories for the e-commerce platform:

1. User Registration and Authentication
As a new customer, I want to create an account so that I can save my preferences and order history.
- Given I am on the registration page
- When I enter valid email, password, and personal details
- Then I should receive a confirmation email and be able to log in

2. Product Browsing
As a customer, I want to browse products by category so that I can find items I\'m interested in.
- Given I am on the homepage
- When I click on a product category
- Then I should see all products in that category with filters and sorting options

3. Shopping Cart Management
As a customer, I want to add products to my cart so that I can purchase multiple items at once.
- Given I am viewing a product
- When I click "Add to Cart"
- Then the product should be added to my cart and the cart count should update

4. Checkout Process
As a customer, I want to complete my purchase securely so that I can receive my ordered items.
- Given I have items in my cart
- When I proceed to checkout and enter payment details
- Then I should receive an order confirmation and email receipt'
            ],
            'ai_prompt_used' => [
                'title' => 'Generate User Stories',
                'prompt' => 'Generate detailed user stories for a e-commerce application with the following features: product catalog, shopping cart, user authentication, payment processing. Include acceptance criteria for each story.',
            ],
            'parsed_data' => [
                'stories' => [
                    [
                        'title' => 'User Registration and Authentication',
                        'description' => 'As a new customer, I want to create an account so that I can save my preferences and order history.',
                        'acceptance_criteria' => [
                            'Given I am on the registration page',
                            'When I enter valid email, password, and personal details',
                            'Then I should receive a confirmation email and be able to log in'
                        ],
                        'priority' => 'high',
                        'story_points' => 5
                    ],
                    [
                        'title' => 'Product Browsing',
                        'description' => 'As a customer, I want to browse products by category so that I can find items I\'m interested in.',
                        'acceptance_criteria' => [
                            'Given I am on the homepage',
                            'When I click on a product category',
                            'Then I should see all products in that category with filters and sorting options'
                        ],
                        'priority' => 'high',
                        'story_points' => 8
                    ],
                    [
                        'title' => 'Shopping Cart Management',
                        'description' => 'As a customer, I want to add products to my cart so that I can purchase multiple items at once.',
                        'acceptance_criteria' => [
                            'Given I am viewing a product',
                            'When I click "Add to Cart"',
                            'Then the product should be added to my cart and the cart count should update'
                        ],
                        'priority' => 'high',
                        'story_points' => 5
                    ],
                    [
                        'title' => 'Checkout Process',
                        'description' => 'As a customer, I want to complete my purchase securely so that I can receive my ordered items.',
                        'acceptance_criteria' => [
                            'Given I have items in my cart',
                            'When I proceed to checkout and enter payment details',
                            'Then I should receive an order confirmation and email receipt'
                        ],
                        'priority' => 'high',
                        'story_points' => 13
                    ]
                ],
                'total_count' => 4,
                'parsed_at' => now()->toISOString()
            ],
            'status' => 'approved',
        ]);

        // Sample Database Schema for Database Admin
        $databaseSchemaContent = ProjectWorkspaceContent::create([
            'project_id' => $project->id,
            'admin_id' => $admin->id,
            'role' => 'database_admin',
            'content_type' => 'database_schema',
            'title' => 'E-commerce Database Schema',
            'content' => [
                'raw' => 'Here\'s the complete database schema for the e-commerce application:

## Users Table
- id (primary key)
- name (varchar 255)
- email (varchar 255, unique)
- email_verified_at (timestamp, nullable)
- password (varchar 255)
- phone (varchar 20, nullable)
- address (text, nullable)
- created_at (timestamp)
- updated_at (timestamp)

## Categories Table
- id (primary key)
- name (varchar 255)
- slug (varchar 255, unique)
- description (text, nullable)
- image (varchar 255, nullable)
- parent_id (foreign key to categories.id, nullable)
- created_at (timestamp)
- updated_at (timestamp)

## Products Table
- id (primary key)
- name (varchar 255)
- slug (varchar 255, unique)
- description (text)
- price (decimal 10,2)
- sale_price (decimal 10,2, nullable)
- sku (varchar 100, unique)
- stock_quantity (integer, default 0)
- category_id (foreign key to categories.id)
- featured_image (varchar 255, nullable)
- gallery (json, nullable)
- status (enum: active, inactive, draft)
- created_at (timestamp)
- updated_at (timestamp)

## Orders Table
- id (primary key)
- user_id (foreign key to users.id)
- order_number (varchar 50, unique)
- status (enum: pending, processing, shipped, delivered, cancelled)
- total_amount (decimal 10,2)
- shipping_address (json)
- billing_address (json)
- payment_status (enum: pending, paid, failed, refunded)
- payment_method (varchar 50)
- notes (text, nullable)
- created_at (timestamp)
- updated_at (timestamp)

## Order Items Table
- id (primary key)
- order_id (foreign key to orders.id)
- product_id (foreign key to products.id)
- quantity (integer)
- price (decimal 10,2)
- total (decimal 10,2)
- created_at (timestamp)
- updated_at (timestamp)

## Shopping Cart Table
- id (primary key)
- user_id (foreign key to users.id)
- product_id (foreign key to products.id)
- quantity (integer)
- created_at (timestamp)
- updated_at (timestamp)

Indexes:
- products: category_id, status, sku
- orders: user_id, status, order_number
- order_items: order_id, product_id
- shopping_cart: user_id, product_id'
            ],
            'ai_prompt_used' => [
                'title' => 'Generate Database Schema',
                'prompt' => 'Create a complete database schema for a e-commerce application with the following entities: users, products, categories, orders, order_items, payments. Include relationships, indexes, and constraints.',
            ],
            'parsed_data' => [
                'tables' => [
                    [
                        'name' => 'users',
                        'fields' => [
                            ['name' => 'id', 'type' => 'bigint', 'primary' => true],
                            ['name' => 'name', 'type' => 'varchar', 'length' => '255'],
                            ['name' => 'email', 'type' => 'varchar', 'length' => '255', 'unique' => true],
                            ['name' => 'password', 'type' => 'varchar', 'length' => '255'],
                            ['name' => 'phone', 'type' => 'varchar', 'length' => '20', 'nullable' => true],
                            ['name' => 'address', 'type' => 'text', 'nullable' => true],
                        ],
                        'relationships' => [],
                        'indexes' => ['email']
                    ],
                    [
                        'name' => 'categories',
                        'fields' => [
                            ['name' => 'id', 'type' => 'bigint', 'primary' => true],
                            ['name' => 'name', 'type' => 'varchar', 'length' => '255'],
                            ['name' => 'slug', 'type' => 'varchar', 'length' => '255', 'unique' => true],
                            ['name' => 'description', 'type' => 'text', 'nullable' => true],
                            ['name' => 'parent_id', 'type' => 'bigint', 'nullable' => true],
                        ],
                        'relationships' => [
                            ['type' => 'belongsTo', 'related_table' => 'categories', 'foreign_key' => 'parent_id']
                        ],
                        'indexes' => ['slug', 'parent_id']
                    ],
                    [
                        'name' => 'products',
                        'fields' => [
                            ['name' => 'id', 'type' => 'bigint', 'primary' => true],
                            ['name' => 'name', 'type' => 'varchar', 'length' => '255'],
                            ['name' => 'slug', 'type' => 'varchar', 'length' => '255', 'unique' => true],
                            ['name' => 'price', 'type' => 'decimal', 'precision' => '10,2'],
                            ['name' => 'category_id', 'type' => 'bigint'],
                            ['name' => 'stock_quantity', 'type' => 'integer', 'default' => 0],
                        ],
                        'relationships' => [
                            ['type' => 'belongsTo', 'related_table' => 'categories', 'foreign_key' => 'category_id']
                        ],
                        'indexes' => ['category_id', 'slug']
                    ]
                ],
                'total_tables' => 3,
                'parsed_at' => now()->toISOString()
            ],
            'status' => 'approved',
        ]);

        $this->command->info('Created sample workspace content:');
        $this->command->info("- {$userStoriesContent->title}");
        $this->command->info("- {$databaseSchemaContent->title}");
        $this->command->info('Workspace content seeder completed successfully!');
    }
}

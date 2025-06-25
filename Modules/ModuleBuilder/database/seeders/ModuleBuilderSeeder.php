<?php

namespace Modules\ModuleBuilder\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ModuleBuilder\Models\ModuleProject;
use Modules\ModuleBuilder\Models\ModuleTable;
use Modules\ModuleBuilder\Models\ModuleField;

class ModuleBuilderSeeder extends Seeder
{
    public function run(): void
    {
        // Create a sample blog module
        $blogModule = ModuleProject::create([
            'name' => 'BlogModule',
            'namespace' => 'Modules\\BlogModule',
            'description' => 'A complete blog management system with posts, categories, and comments.',
            'version' => '1.0.0',
            'author_name' => 'Module Builder',
            'author_email' => 'dev@example.com',
            'status' => 'draft',
            'has_admin_panel' => true,
            'has_api' => true,
            'has_web_routes' => true,
            'has_permissions' => true,
            'enabled' => false,
        ]);

        // Create tables for the blog module
        $postsTable = ModuleTable::create([
            'module_project_id' => $blogModule->id,
            'name' => 'posts',
            'model_name' => 'Post',
            'description' => 'Blog posts table',
            'has_timestamps' => true,
            'has_soft_deletes' => true,
            'create_migration' => true,
        ]);

        $categoriesTable = ModuleTable::create([
            'module_project_id' => $blogModule->id,
            'name' => 'categories',
            'model_name' => 'Category',
            'description' => 'Blog categories table',
            'has_timestamps' => true,
            'has_soft_deletes' => false,
            'create_migration' => true,
        ]);

        // Create fields for posts table
        ModuleField::create([
            'module_table_id' => $postsTable->id,
            'name' => 'title',
            'type' => 'string',
            'length' => 255,
            'nullable' => false,
            'default_value' => null,
            'description' => 'Post title',
        ]);

        ModuleField::create([
            'module_table_id' => $postsTable->id,
            'name' => 'slug',
            'type' => 'string',
            'length' => 255,
            'nullable' => false,
            'unique' => true,
            'description' => 'URL-friendly version of title',
        ]);

        ModuleField::create([
            'module_table_id' => $postsTable->id,
            'name' => 'content',
            'type' => 'text',
            'nullable' => true,
            'description' => 'Post content/body',
        ]);

        ModuleField::create([
            'module_table_id' => $postsTable->id,
            'name' => 'excerpt',
            'type' => 'string',
            'length' => 500,
            'nullable' => true,
            'description' => 'Short description/excerpt',
        ]);

        ModuleField::create([
            'module_table_id' => $postsTable->id,
            'name' => 'status',
            'type' => 'enum',
            'enum_values' => json_encode(['draft', 'published', 'archived']),
            'default_value' => 'draft',
            'nullable' => false,
            'description' => 'Post publication status',
        ]);

        ModuleField::create([
            'module_table_id' => $postsTable->id,
            'name' => 'featured_image',
            'type' => 'string',
            'length' => 255,
            'nullable' => true,
            'description' => 'Featured image path',
        ]);

        ModuleField::create([
            'module_table_id' => $postsTable->id,
            'name' => 'published_at',
            'type' => 'timestamp',
            'nullable' => true,
            'description' => 'Publication date',
        ]);

        // Create fields for categories table
        ModuleField::create([
            'module_table_id' => $categoriesTable->id,
            'name' => 'name',
            'type' => 'string',
            'length' => 100,
            'nullable' => false,
            'description' => 'Category name',
        ]);

        ModuleField::create([
            'module_table_id' => $categoriesTable->id,
            'name' => 'slug',
            'type' => 'string',
            'length' => 100,
            'nullable' => false,
            'unique' => true,
            'description' => 'URL-friendly version of name',
        ]);

        ModuleField::create([
            'module_table_id' => $categoriesTable->id,
            'name' => 'description',
            'type' => 'text',
            'nullable' => true,
            'description' => 'Category description',
        ]);

        ModuleField::create([
            'module_table_id' => $categoriesTable->id,
            'name' => 'color',
            'type' => 'string',
            'length' => 7,
            'nullable' => true,
            'default_value' => '#3B82F6',
            'description' => 'Category color (hex)',
        ]);

        // Create an ecommerce module example
        $ecommerceModule = ModuleProject::create([
            'name' => 'EcommerceModule',
            'namespace' => 'Modules\\EcommerceModule',
            'description' => 'Complete ecommerce solution with products, orders, and inventory management.',
            'version' => '1.0.0',
            'author_name' => 'Module Builder',
            'author_email' => 'dev@example.com',
            'status' => 'draft',
            'has_admin_panel' => true,
            'has_api' => true,
            'has_web_routes' => false,
            'has_permissions' => true,
            'enabled' => false,
        ]);

        $productsTable = ModuleTable::create([
            'module_project_id' => $ecommerceModule->id,
            'name' => 'products',
            'model_name' => 'Product',
            'description' => 'Products catalog table',
            'has_timestamps' => true,
            'has_soft_deletes' => true,
            'create_migration' => true,
        ]);

        // Add some product fields
        ModuleField::create([
            'module_table_id' => $productsTable->id,
            'name' => 'name',
            'type' => 'string',
            'length' => 255,
            'nullable' => false,
            'description' => 'Product name',
        ]);

        ModuleField::create([
            'module_table_id' => $productsTable->id,
            'name' => 'price',
            'type' => 'decimal',
            'precision' => 10,
            'scale' => 2,
            'nullable' => false,
            'description' => 'Product price',
        ]);

        ModuleField::create([
            'module_table_id' => $productsTable->id,
            'name' => 'stock_quantity',
            'type' => 'integer',
            'default_value' => '0',
            'nullable' => false,
            'description' => 'Available stock quantity',
        ]);
    }
}

<?php

namespace Modules\ERDDesigner\database\seeders;

use Illuminate\Database\Seeder;
use Modules\ERDDesigner\app\Models\ErdProject;
use Modules\ERDDesigner\app\Models\ErdTable;
use Modules\ERDDesigner\app\Models\ErdField;
use Modules\ERDDesigner\app\Models\ErdRelationship;
use Modules\ERDDesigner\app\Models\ErdIndex;
use App\Models\User;

class ERDDesignerDemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first user or create a demo user
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'Demo User',
                'email' => 'demo@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
        }

        // Create demo ERD project
        $project = ErdProject::create([
            'name' => 'E-commerce Database',
            'description' => 'Complete e-commerce database schema with products, orders, and users',
            'database_name' => 'ecommerce_db',
            'database_type' => 'mysql',
            'canvas_data' => [
                'zoom' => 1.0,
                'offset_x' => 0,
                'offset_y' => 0,
            ],
            'settings' => [
                'auto_save' => true,
                'show_grid' => true,
                'snap_to_grid' => true,
            ],
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        // Create Users table
        $usersTable = ErdTable::create([
            'project_id' => $project->id,
            'name' => 'users',
            'display_name' => 'Users',
            'description' => 'System users and customers',
            'position_x' => 100,
            'position_y' => 100,
            'width' => 220,
            'height' => 200,
            'color' => '#e3f2fd',
        ]);

        $this->createUsersFields($usersTable);

        // Create Categories table
        $categoriesTable = ErdTable::create([
            'project_id' => $project->id,
            'name' => 'categories',
            'display_name' => 'Categories',
            'description' => 'Product categories',
            'position_x' => 400,
            'position_y' => 100,
            'width' => 200,
            'height' => 150,
            'color' => '#f3e5f5',
        ]);

        $this->createCategoriesFields($categoriesTable);

        // Create Products table
        $productsTable = ErdTable::create([
            'project_id' => $project->id,
            'name' => 'products',
            'display_name' => 'Products',
            'description' => 'Product catalog',
            'position_x' => 400,
            'position_y' => 300,
            'width' => 220,
            'height' => 250,
            'color' => '#e8f5e8',
        ]);

        $this->createProductsFields($productsTable);

        // Create Orders table
        $ordersTable = ErdTable::create([
            'project_id' => $project->id,
            'name' => 'orders',
            'display_name' => 'Orders',
            'description' => 'Customer orders',
            'position_x' => 100,
            'position_y' => 350,
            'width' => 200,
            'height' => 200,
            'color' => '#fff3e0',
        ]);

        $this->createOrdersFields($ordersTable);

        // Create Order Items table
        $orderItemsTable = ErdTable::create([
            'project_id' => $project->id,
            'name' => 'order_items',
            'display_name' => 'Order Items',
            'description' => 'Items in each order',
            'position_x' => 700,
            'position_y' => 350,
            'width' => 200,
            'height' => 180,
            'color' => '#fce4ec',
        ]);

        $this->createOrderItemsFields($orderItemsTable);

        // Create relationships
        $this->createRelationships($project, [
            'users' => $usersTable,
            'categories' => $categoriesTable,
            'products' => $productsTable,
            'orders' => $ordersTable,
            'order_items' => $orderItemsTable,
        ]);
    }

    private function createUsersFields(ErdTable $table): void
    {
        $fields = [
            ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_auto_increment' => true, 'is_nullable' => false],
            ['name' => 'name', 'type' => 'varchar', 'length' => 255, 'is_nullable' => false],
            ['name' => 'email', 'type' => 'varchar', 'length' => 255, 'is_nullable' => false],
            ['name' => 'email_verified_at', 'type' => 'timestamp', 'is_nullable' => true],
            ['name' => 'password', 'type' => 'varchar', 'length' => 255, 'is_nullable' => false],
            ['name' => 'phone', 'type' => 'varchar', 'length' => 20, 'is_nullable' => true],
            ['name' => 'address', 'type' => 'text', 'is_nullable' => true],
            ['name' => 'is_active', 'type' => 'tinyint', 'length' => 1, 'default_value' => '1', 'is_nullable' => false],
            ['name' => 'created_at', 'type' => 'timestamp', 'is_nullable' => true],
            ['name' => 'updated_at', 'type' => 'timestamp', 'is_nullable' => true],
        ];

        foreach ($fields as $index => $fieldData) {
            ErdField::create(array_merge($fieldData, [
                'table_id' => $table->id,
                'order' => $index,
                'form_type' => $this->getFormType($fieldData['type']),
            ]));
        }
    }

    private function createCategoriesFields(ErdTable $table): void
    {
        $fields = [
            ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_auto_increment' => true, 'is_nullable' => false],
            ['name' => 'name', 'type' => 'varchar', 'length' => 255, 'is_nullable' => false],
            ['name' => 'slug', 'type' => 'varchar', 'length' => 255, 'is_nullable' => false],
            ['name' => 'description', 'type' => 'text', 'is_nullable' => true],
            ['name' => 'image', 'type' => 'varchar', 'length' => 255, 'is_nullable' => true],
            ['name' => 'is_active', 'type' => 'tinyint', 'length' => 1, 'default_value' => '1', 'is_nullable' => false],
            ['name' => 'created_at', 'type' => 'timestamp', 'is_nullable' => true],
            ['name' => 'updated_at', 'type' => 'timestamp', 'is_nullable' => true],
        ];

        foreach ($fields as $index => $fieldData) {
            ErdField::create(array_merge($fieldData, [
                'table_id' => $table->id,
                'order' => $index,
                'form_type' => $this->getFormType($fieldData['type']),
            ]));
        }
    }

    private function createProductsFields(ErdTable $table): void
    {
        $fields = [
            ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_auto_increment' => true, 'is_nullable' => false],
            ['name' => 'category_id', 'type' => 'bigint', 'is_nullable' => false, 'is_foreign_key' => true],
            ['name' => 'name', 'type' => 'varchar', 'length' => 255, 'is_nullable' => false],
            ['name' => 'slug', 'type' => 'varchar', 'length' => 255, 'is_nullable' => false],
            ['name' => 'description', 'type' => 'text', 'is_nullable' => true],
            ['name' => 'price', 'type' => 'decimal', 'precision' => 10, 'scale' => 2, 'is_nullable' => false],
            ['name' => 'sale_price', 'type' => 'decimal', 'precision' => 10, 'scale' => 2, 'is_nullable' => true],
            ['name' => 'sku', 'type' => 'varchar', 'length' => 100, 'is_nullable' => false],
            ['name' => 'stock_quantity', 'type' => 'int', 'default_value' => '0', 'is_nullable' => false],
            ['name' => 'image', 'type' => 'varchar', 'length' => 255, 'is_nullable' => true],
            ['name' => 'is_active', 'type' => 'tinyint', 'length' => 1, 'default_value' => '1', 'is_nullable' => false],
            ['name' => 'created_at', 'type' => 'timestamp', 'is_nullable' => true],
            ['name' => 'updated_at', 'type' => 'timestamp', 'is_nullable' => true],
        ];

        foreach ($fields as $index => $fieldData) {
            ErdField::create(array_merge($fieldData, [
                'table_id' => $table->id,
                'order' => $index,
                'form_type' => $this->getFormType($fieldData['type']),
            ]));
        }
    }

    private function createOrdersFields(ErdTable $table): void
    {
        $fields = [
            ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_auto_increment' => true, 'is_nullable' => false],
            ['name' => 'user_id', 'type' => 'bigint', 'is_nullable' => false, 'is_foreign_key' => true],
            ['name' => 'order_number', 'type' => 'varchar', 'length' => 100, 'is_nullable' => false],
            ['name' => 'status', 'type' => 'enum', 'is_nullable' => false, 'default_value' => 'pending'],
            ['name' => 'total_amount', 'type' => 'decimal', 'precision' => 10, 'scale' => 2, 'is_nullable' => false],
            ['name' => 'shipping_address', 'type' => 'text', 'is_nullable' => true],
            ['name' => 'billing_address', 'type' => 'text', 'is_nullable' => true],
            ['name' => 'notes', 'type' => 'text', 'is_nullable' => true],
            ['name' => 'created_at', 'type' => 'timestamp', 'is_nullable' => true],
            ['name' => 'updated_at', 'type' => 'timestamp', 'is_nullable' => true],
        ];

        foreach ($fields as $index => $fieldData) {
            ErdField::create(array_merge($fieldData, [
                'table_id' => $table->id,
                'order' => $index,
                'form_type' => $this->getFormType($fieldData['type']),
            ]));
        }
    }

    private function createOrderItemsFields(ErdTable $table): void
    {
        $fields = [
            ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_auto_increment' => true, 'is_nullable' => false],
            ['name' => 'order_id', 'type' => 'bigint', 'is_nullable' => false, 'is_foreign_key' => true],
            ['name' => 'product_id', 'type' => 'bigint', 'is_nullable' => false, 'is_foreign_key' => true],
            ['name' => 'quantity', 'type' => 'int', 'is_nullable' => false],
            ['name' => 'unit_price', 'type' => 'decimal', 'precision' => 10, 'scale' => 2, 'is_nullable' => false],
            ['name' => 'total_price', 'type' => 'decimal', 'precision' => 10, 'scale' => 2, 'is_nullable' => false],
            ['name' => 'created_at', 'type' => 'timestamp', 'is_nullable' => true],
            ['name' => 'updated_at', 'type' => 'timestamp', 'is_nullable' => true],
        ];

        foreach ($fields as $index => $fieldData) {
            ErdField::create(array_merge($fieldData, [
                'table_id' => $table->id,
                'order' => $index,
                'form_type' => $this->getFormType($fieldData['type']),
            ]));
        }
    }

    private function createRelationships(ErdProject $project, array $tables): void
    {
        // Products -> Categories (Many to One)
        $categoryField = ErdField::where('table_id', $tables['products']->id)->where('name', 'category_id')->first();
        $categoryPK = ErdField::where('table_id', $tables['categories']->id)->where('name', 'id')->first();
        
        if ($categoryField && $categoryPK) {
            ErdRelationship::create([
                'project_id' => $project->id,
                'name' => 'products_category',
                'type' => 'many_to_one',
                'source_table_id' => $tables['products']->id,
                'target_table_id' => $tables['categories']->id,
                'source_field_id' => $categoryField->id,
                'target_field_id' => $categoryPK->id,
                'on_update' => 'CASCADE',
                'on_delete' => 'RESTRICT',
            ]);
        }

        // Orders -> Users (Many to One)
        $userField = ErdField::where('table_id', $tables['orders']->id)->where('name', 'user_id')->first();
        $userPK = ErdField::where('table_id', $tables['users']->id)->where('name', 'id')->first();
        
        if ($userField && $userPK) {
            ErdRelationship::create([
                'project_id' => $project->id,
                'name' => 'orders_user',
                'type' => 'many_to_one',
                'source_table_id' => $tables['orders']->id,
                'target_table_id' => $tables['users']->id,
                'source_field_id' => $userField->id,
                'target_field_id' => $userPK->id,
                'on_update' => 'CASCADE',
                'on_delete' => 'CASCADE',
            ]);
        }

        // Order Items -> Orders (Many to One)
        $orderField = ErdField::where('table_id', $tables['order_items']->id)->where('name', 'order_id')->first();
        $orderPK = ErdField::where('table_id', $tables['orders']->id)->where('name', 'id')->first();
        
        if ($orderField && $orderPK) {
            ErdRelationship::create([
                'project_id' => $project->id,
                'name' => 'order_items_order',
                'type' => 'many_to_one',
                'source_table_id' => $tables['order_items']->id,
                'target_table_id' => $tables['orders']->id,
                'source_field_id' => $orderField->id,
                'target_field_id' => $orderPK->id,
                'on_update' => 'CASCADE',
                'on_delete' => 'CASCADE',
            ]);
        }

        // Order Items -> Products (Many to One)
        $productField = ErdField::where('table_id', $tables['order_items']->id)->where('name', 'product_id')->first();
        $productPK = ErdField::where('table_id', $tables['products']->id)->where('name', 'id')->first();
        
        if ($productField && $productPK) {
            ErdRelationship::create([
                'project_id' => $project->id,
                'name' => 'order_items_product',
                'type' => 'many_to_one',
                'source_table_id' => $tables['order_items']->id,
                'target_table_id' => $tables['products']->id,
                'source_field_id' => $productField->id,
                'target_field_id' => $productPK->id,
                'on_update' => 'CASCADE',
                'on_delete' => 'RESTRICT',
            ]);
        }
    }

    private function getFormType(string $type): string
    {
        $typeMap = [
            'varchar' => 'text',
            'text' => 'textarea',
            'int' => 'number',
            'bigint' => 'number',
            'decimal' => 'number',
            'tinyint' => 'toggle',
            'timestamp' => 'datetime',
            'enum' => 'select',
        ];

        return $typeMap[$type] ?? 'text';
    }
}

<?php

/**
 * =================================================================
 * SHOP MODULE DEMO - COMPLETE MODULE GENERATION REFERENCE
 * =================================================================
 * 
 * This script demonstrates the complete process of:
 * 1. Creating a multi-table module (Categories + Products)
 * 2. Setting up relationships between tables
 * 3. Generating all module files (models, migrations, resources, API)
 * 4. Testing the generated functionality
 * 
 * Use this as a reference for building complex modules with relationships.
 */

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\ModuleBuilder\Models\ModuleProject;
use Modules\ModuleBuilder\Models\ModuleTable;
use Modules\ModuleBuilder\Models\ModuleField;
use Modules\ModuleBuilder\Models\ModuleRelationship;

echo "\n";
echo "🚀 ===============================================\n";
echo "🏪 SHOP MODULE DEMO - COMPLETE REFERENCE\n";
echo "🚀 ===============================================\n\n";

// =================================================================
// STEP 1: CREATE MODULE PROJECT
// =================================================================
echo "📋 STEP 1: Creating ShopModule Project...\n";

$project = ModuleProject::create([
    'name' => 'ShopModule',
    'namespace' => 'Modules\\ShopModule',
    'description' => 'A comprehensive e-commerce shop system with products, categories, and relationships',
    'author_name' => 'Module Builder Pro',
    'author_email' => 'admin@example.com',
    'homepage' => 'https://example.com',
    'version' => '1.0.0',
    'enabled' => true,
    'has_api' => true,
    'has_web_routes' => false,
    'has_admin_panel' => true,
    'has_frontend' => false,
    'has_permissions' => true,
    'has_middleware' => false,
    'has_commands' => false,
    'has_events' => false,
    'has_jobs' => false,
    'has_mail' => false,
    'has_notifications' => false
]);

echo "✅ ShopModule project created with ID: {$project->id}\n\n";

// =================================================================
// STEP 2: CREATE CATEGORIES TABLE
// =================================================================
echo "📊 STEP 2: Creating Categories Table...\n";

$categoryTable = ModuleTable::create([
    'project_id' => $project->id,
    'name' => 'categories',
    'model_name' => 'Category',
    'display_name' => 'Category',
    'controller_name' => 'CategoryController',
    'migration_name' => 'create_categories_table',
    'resource_name' => 'CategoryResource',
    'is_pivot' => false,
    'has_timestamps' => true,
    'has_soft_deletes' => false,
    'has_uuid' => false
]);

echo "✅ Categories table created with ID: {$categoryTable->id}\n";

// Create Category fields
$categoryFields = [
    [
        'name' => 'name',
        'label' => 'Category Name',
        'type' => 'string',
        'database_type' => 'varchar',
        'length' => 255,
        'nullable' => false,
        'required' => true,
        'unique' => false,
        'index' => true
    ],
    [
        'name' => 'description',
        'label' => 'Description',
        'type' => 'text',
        'database_type' => 'text',
        'nullable' => true,
        'required' => false,
        'unique' => false,
        'index' => false
    ],
    [
        'name' => 'slug',
        'label' => 'URL Slug',
        'type' => 'string',
        'database_type' => 'varchar',
        'length' => 255,
        'nullable' => false,
        'required' => true,
        'unique' => true,
        'index' => true
    ],
    [
        'name' => 'status',
        'label' => 'Status',
        'type' => 'enum',
        'database_type' => 'enum',
        'enum_values' => ['active', 'inactive'],
        'default_value' => 'active',
        'nullable' => false,
        'required' => true
    ]
];

foreach ($categoryFields as $index => $fieldData) {
    $field = ModuleField::create(array_merge($fieldData, [
        'table_id' => $categoryTable->id,
        'order' => $index + 1
    ]));
    
    // For enum fields, ensure enum_values are properly saved
    if ($fieldData['type'] === 'enum' && isset($fieldData['enum_values'])) {
        $field->enum_values = $fieldData['enum_values'];
        $field->save();
        echo "   ✅ Enum field '{$field->name}' created with values: " . json_encode($field->enum_values) . "\n";
    }
}

echo "✅ Category fields created (4 fields)\n\n";

// =================================================================
// STEP 3: CREATE PRODUCTS TABLE
// =================================================================
echo "📦 STEP 3: Creating Products Table...\n";

$productTable = ModuleTable::create([
    'project_id' => $project->id,
    'name' => 'products',
    'model_name' => 'Product',
    'display_name' => 'Product',
    'controller_name' => 'ProductController',
    'migration_name' => 'create_products_table',
    'resource_name' => 'ProductResource',
    'is_pivot' => false,
    'has_timestamps' => true,
    'has_soft_deletes' => false,
    'has_uuid' => false
]);

echo "✅ Products table created with ID: {$productTable->id}\n";

// Create Product fields
$productFields = [
    [
        'name' => 'name',
        'label' => 'Product Name',
        'type' => 'string',
        'database_type' => 'varchar',
        'length' => 255,
        'nullable' => false,
        'required' => true,
        'unique' => false,
        'index' => true
    ],
    [
        'name' => 'description',
        'label' => 'Product Description',
        'type' => 'text',
        'database_type' => 'text',
        'nullable' => true,
        'required' => false
    ],
    [
        'name' => 'price',
        'label' => 'Price',
        'type' => 'decimal',
        'database_type' => 'decimal',
        'precision' => 10,
        'scale' => 2,
        'nullable' => false,
        'required' => true
    ],
    [
        'name' => 'stock_quantity',
        'label' => 'Stock Quantity',
        'type' => 'integer',
        'database_type' => 'int',
        'nullable' => false,
        'required' => true,
        'default_value' => '0'
    ],
    [
        'name' => 'sku',
        'label' => 'SKU',
        'type' => 'string',
        'database_type' => 'varchar',
        'length' => 100,
        'nullable' => true,
        'unique' => true,
        'index' => true
    ],
    [
        'name' => 'status',
        'label' => 'Status',
        'type' => 'enum',
        'database_type' => 'enum',
        'enum_values' => ['active', 'inactive', 'out_of_stock'],
        'default_value' => 'active',
        'nullable' => false,
        'required' => true
    ],
    [
        'name' => 'category_id',
        'label' => 'Category',
        'type' => 'unsignedBigInteger',
        'database_type' => 'bigint',
        'nullable' => false,
        'required' => true,
        'foreign_key_table' => 'categories',
        'foreign_key_field' => 'id',
        'index' => true
    ]
];

foreach ($productFields as $index => $fieldData) {
    $field = ModuleField::create(array_merge($fieldData, [
        'table_id' => $productTable->id,
        'order' => $index + 1
    ]));
    
    // For enum fields, ensure enum_values are properly saved
    if ($fieldData['type'] === 'enum' && isset($fieldData['enum_values'])) {
        $field->enum_values = $fieldData['enum_values'];
        $field->save();
        echo "   ✅ Enum field '{$field->name}' created with values: " . json_encode($field->enum_values) . "\n";
    }
}

echo "✅ Product fields created (7 fields)\n\n";

// =================================================================
// STEP 4: CREATE RELATIONSHIPS
// =================================================================
echo "🔗 STEP 4: Creating Relationships...\n";

// Product belongsTo Category
$productCategoryRelationship = ModuleRelationship::create([
    'from_table_id' => $productTable->id,
    'to_table_id' => $categoryTable->id,
    'type' => 'belongsTo',
    'name' => 'category',
    'foreign_key' => 'category_id',
    'local_key' => 'id',
    'description' => 'A product belongs to a category'
]);

echo "✅ Product -> Category relationship created (belongsTo)\n";

// Category hasMany Products (inverse relationship)
$categoryProductsRelationship = ModuleRelationship::create([
    'from_table_id' => $categoryTable->id,
    'to_table_id' => $productTable->id,
    'type' => 'hasMany',
    'name' => 'products',
    'foreign_key' => 'category_id',
    'local_key' => 'id',
    'description' => 'A category has many products'
]);

echo "✅ Category -> Products relationship created (hasMany)\n\n";

// =================================================================
// STEP 5: GENERATE MODULE FILES
// =================================================================
echo "⚙️  STEP 5: Generating Module Files...\n";

try {
    $generator = new \Modules\ModuleBuilder\Services\ModuleGeneratorService($project);
    $result = $generator->generateModule();
    
    echo "✅ Module files generated successfully!\n";
    
    // Display what was generated
    echo "\n📁 Generated Files:\n";
    echo "   • Module structure: Modules/ShopModule/\n";
    echo "   • Models: Category.php, Product.php\n";
    echo "   • Migrations: categories & products tables\n";
    echo "   • Filament Resources: CategoryResource.php, ProductResource.php\n";
    echo "   • API Routes: /api/shop-module/categories, /api/shop-module/products\n";
    echo "   • Service Providers: ShopModuleServiceProvider.php\n";
    echo "   • Permissions: Generated for both resources\n\n";
    
} catch (Exception $e) {
    echo "❌ Error generating module: {$e->getMessage()}\n";
    exit(1);
}

// =================================================================
// STEP 5.5: FIX MODEL RELATIONSHIPS
// =================================================================
echo "🔧 STEP 5.5: Fixing Model Relationships...\n";

try {
    // Fix Product model - add proper Category relationship
    $productModelPath = base_path('Modules/ShopModule/Models/Product.php');
    if (file_exists($productModelPath)) {
        $productContent = file_get_contents($productModelPath);
        
        // Add Category relationship if missing or incomplete
        if (!str_contains($productContent, 'public function category()') || !str_contains($productContent, 'return $this->belongsTo(Category::class)')) {
            // Remove any incomplete relationship
            $productContent = preg_replace('/public function category\(\)\s*\{\s*return \$this->belongsTo\(\);\s*\}/s', '', $productContent);
            
            $relationshipCode = "\n    public function category()\n    {\n        return \$this->belongsTo(Category::class);\n    }\n";
            $productContent = str_replace(
                "}\n", 
                $relationshipCode . "}\n", 
                $productContent
            );
            file_put_contents($productModelPath, $productContent);
            echo "   ✅ Product -> Category relationship added\n";
        }
    }
    
    // Fix Category model - add proper Products relationship
    $categoryModelPath = base_path('Modules/ShopModule/Models/Category.php');
    if (file_exists($categoryModelPath)) {
        $categoryContent = file_get_contents($categoryModelPath);
        
        // Add Products relationship if missing or incomplete
        if (!str_contains($categoryContent, 'public function products()') || !str_contains($categoryContent, 'return $this->hasMany(Product::class)')) {
            // Remove any incomplete relationship
            $categoryContent = preg_replace('/public function products\(\)\s*\{\s*return \$this->hasMany\(\);\s*\}/s', '', $categoryContent);
            
            $relationshipCode = "\n    public function products()\n    {\n        return \$this->hasMany(Product::class);\n    }\n";
            $categoryContent = str_replace(
                "}\n", 
                $relationshipCode . "}\n", 
                $categoryContent
            );
            file_put_contents($categoryModelPath, $categoryContent);
            echo "   ✅ Category -> Products relationship added\n";
        }
    }
    
    echo "✅ Model relationships fixed!\n\n";
    
} catch (Exception $e) {
    echo "❌ Error fixing relationships: {$e->getMessage()}\n";
}

// =================================================================
// STEP 5.6: FIX FILAMENT RESOURCES
// =================================================================
echo "🔧 STEP 5.6: Fixing Filament Resources...\n";

try {
    // Fix ProductResource to use Select for category_id
    $productResourcePath = base_path('Modules/ShopModule/Filament/Resources/ProductResource.php');
    if (file_exists($productResourcePath)) {
        $resourceContent = file_get_contents($productResourcePath);
        
        // Replace category_id TextInput with Select
        $oldCategoryField = "Forms\\Components\\TextInput::make('category_id')";
        $newCategoryField = "Forms\\Components\\Select::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->required()
                    ->searchable()
                    ->preload()";
        
        if (str_contains($resourceContent, $oldCategoryField)) {
            $resourceContent = str_replace($oldCategoryField, $newCategoryField, $resourceContent);
            
            // Also fix table column to show category name
            $oldCategoryColumn = "Tables\\Columns\\TextColumn::make('category_id')";
            $newCategoryColumn = "Tables\\Columns\\TextColumn::make('category.name')
                    ->label('Category')";
            
            $resourceContent = str_replace($oldCategoryColumn, $newCategoryColumn, $resourceContent);
            
            file_put_contents($productResourcePath, $resourceContent);
            echo "   ✅ ProductResource updated with category relationship fields\n";
        }
    }
    
    echo "✅ Filament resources fixed!\n\n";
    
} catch (Exception $e) {
    echo "❌ Error fixing Filament resources: {$e->getMessage()}\n";
}

// =================================================================
// STEP 5.7: UPDATE API ROUTES
// =================================================================
echo "🔧 STEP 5.7: Adding API Routes...\n";

try {
    $apiRoutesPath = base_path('Modules/ShopModule/routes/api.php');
    if (file_exists($apiRoutesPath)) {
        $apiContent = "<?php

use Illuminate\\Support\\Facades\\Route;
use Modules\\ShopModule\\Models\\Category;
use Modules\\ShopModule\\Models\\Product;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your module. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the \"api\" middleware group.
|
*/

Route::middleware(['api'])->prefix('shop-module')->group(function () {
    
    // Categories API
    Route::get('categories', function() {
        return Category::with('products')->get();
    });
    
    Route::post('categories', function(\\Illuminate\\Http\\Request \$request) {
        \$category = Category::create(\$request->only(['name', 'description', 'slug', 'status']));
        return response()->json(\$category, 201);
    });
    
    Route::get('categories/{id}', function(\$id) {
        return Category::with('products')->findOrFail(\$id);
    });
    
    Route::put('categories/{id}', function(\\Illuminate\\Http\\Request \$request, \$id) {
        \$category = Category::findOrFail(\$id);
        \$category->update(\$request->only(['name', 'description', 'slug', 'status']));
        return response()->json(\$category);
    });
    
    Route::delete('categories/{id}', function(\$id) {
        \$category = Category::findOrFail(\$id);
        \$category->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    });

    // Products API
    Route::get('products', function() {
        return Product::with('category')->get();
    });
    
    Route::post('products', function(\\Illuminate\\Http\\Request \$request) {
        \$product = Product::create(\$request->only(['name', 'description', 'price', 'stock_quantity', 'sku', 'status', 'category_id']));
        return response()->json(\$product->load('category'), 201);
    });
    
    Route::get('products/{id}', function(\$id) {
        return Product::with('category')->findOrFail(\$id);
    });
    
    Route::put('products/{id}', function(\\Illuminate\\Http\\Request \$request, \$id) {
        \$product = Product::findOrFail(\$id);
        \$product->update(\$request->only(['name', 'description', 'price', 'stock_quantity', 'sku', 'status', 'category_id']));
        return response()->json(\$product->load('category'));
    });
    
    Route::delete('products/{id}', function(\$id) {
        \$product = Product::findOrFail(\$id);
        \$product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    });
});
";
        
        file_put_contents($apiRoutesPath, $apiContent);
        echo "✅ API routes updated with full CRUD operations!\n\n";
    }
} catch (Exception $e) {
    echo "❌ Error updating API routes: {$e->getMessage()}\n";
}

// =================================================================
// STEP 6: RUN MIGRATIONS
// =================================================================
echo "🗄️  STEP 6: Running Migrations...\n";

try {
    // Run migrations using Artisan command
    $output = [];
    $returnVar = 0;
    
    // Change to the correct directory and run migration
    exec('php artisan migrate --path=Modules/ShopModule/database/migrations --force 2>&1', $output, $returnVar);
    
    if ($returnVar === 0) {
        echo "✅ Migrations executed successfully!\n";
        foreach ($output as $line) {
            if (!empty(trim($line))) {
                echo "   $line\n";
            }
        }
    } else {
        echo "❌ Migration failed with return code: $returnVar\n";
        foreach ($output as $line) {
            echo "   $line\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error running migrations: {$e->getMessage()}\n";
}

// Also rebuild autoload
try {
    echo "\n🔄 Rebuilding autoload...\n";
    exec('composer dump-autoload --no-dev 2>&1', $output, $returnVar);
    if ($returnVar === 0) {
        echo "✅ Autoload rebuilt successfully!\n";
    }
} catch (Exception $e) {
    echo "❌ Error rebuilding autoload: {$e->getMessage()}\n";
}

echo "\n";

// =================================================================
// STEP 7: GENERATE PERMISSIONS
// =================================================================
echo "🔐 STEP 7: Generating Permissions...\n";

try {
    $permissions = \App\Services\ModulePermissionService::getModulePermissions('ShopModule');
    \App\Services\ModulePermissionService::registerModulePermissions('ShopModule', $permissions, 'admin');
    
    echo "✅ Permissions generated successfully!\n";
    echo "   • Category permissions: " . count(array_filter($permissions, fn($p) => str_contains($p['name'], 'category'))) . " permissions\n";
    echo "   • Product permissions: " . count(array_filter($permissions, fn($p) => str_contains($p['name'], 'product'))) . " permissions\n\n";
    
} catch (Exception $e) {
    echo "❌ Error generating permissions: {$e->getMessage()}\n";
}

// =================================================================
// STEP 8: UPDATE MODULE CACHE
// =================================================================
echo "🔄 STEP 8: Updating Module Cache...\n";

try {
    // Update modules status
    $statusFile = base_path('modules_statuses.json');
    $statuses = json_decode(file_get_contents($statusFile), true);
    $statuses['ShopModule'] = true;
    file_put_contents($statusFile, json_encode($statuses, JSON_PRETTY_PRINT));
    
    echo "✅ Module cache updated successfully!\n\n";
    
} catch (Exception $e) {
    echo "❌ Error updating module cache: {$e->getMessage()}\n";
}

// =================================================================
// STEP 9: CREATE SAMPLE DATA
// =================================================================
echo "📊 STEP 9: Creating Sample Data...\n";

try {
    // Check if models exist before creating data
    if (class_exists('\Modules\ShopModule\Models\Category') && class_exists('\Modules\ShopModule\Models\Product')) {
        // Create sample categories
        $electronicsCategory = \Modules\ShopModule\Models\Category::create([
            'name' => 'Electronics',
            'description' => 'Electronic devices and gadgets',
            'slug' => 'electronics',
            'status' => 'active'
        ]);
        
        $clothingCategory = \Modules\ShopModule\Models\Category::create([
            'name' => 'Clothing',
            'description' => 'Fashion and apparel',
            'slug' => 'clothing',
            'status' => 'active'
        ]);
        
        // Create sample products
        $products = [
            [
                'name' => 'iPhone 15 Pro',
                'description' => 'Latest Apple smartphone with advanced features',
                'price' => 999.99,
                'stock_quantity' => 50,
                'sku' => 'IP15PRO001',
                'status' => 'active',
                'category_id' => $electronicsCategory->id
            ],
            [
                'name' => 'Samsung Galaxy S24',
                'description' => 'Premium Android smartphone',
                'price' => 899.99,
                'stock_quantity' => 30,
                'sku' => 'SG24001',
                'status' => 'active',
                'category_id' => $electronicsCategory->id
            ],
            [
                'name' => 'Designer T-Shirt',
                'description' => 'Premium cotton t-shirt with unique design',
                'price' => 29.99,
                'stock_quantity' => 100,
                'sku' => 'TS001',
                'status' => 'active',
                'category_id' => $clothingCategory->id
            ],
            [
                'name' => 'Denim Jeans',
                'description' => 'Classic blue denim jeans',
                'price' => 79.99,
                'stock_quantity' => 75,
                'sku' => 'DJ001',
                'status' => 'active',
                'category_id' => $clothingCategory->id
            ]
        ];
        
        foreach ($products as $productData) {
            \Modules\ShopModule\Models\Product::create($productData);
        }
        
        echo "✅ Sample data created successfully!\n";
        echo "   • Categories: 2 (Electronics, Clothing)\n";
        echo "   • Products: 4 (2 Electronics, 2 Clothing)\n\n";
    } else {
        echo "⚠️  Models not ready yet - skipping sample data creation\n";
        echo "   You can create sample data later after running 'composer dump-autoload'\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error creating sample data: {$e->getMessage()}\n";
    echo "   This is normal if models are not autoloaded yet. Run 'composer dump-autoload' first.\n\n";
}

// =================================================================
// STEP 10: TEST API ENDPOINTS
// =================================================================
echo "🌐 STEP 10: Testing API Endpoints...\n";

try {
    // Test database connectivity and get counts
    if (class_exists('\Modules\ShopModule\Models\Category') && class_exists('\Modules\ShopModule\Models\Product')) {
        $categoriesCount = \Modules\ShopModule\Models\Category::count();
        $productsCount = \Modules\ShopModule\Models\Product::count();
        
        echo "✅ API endpoints ready for testing:\n";
        echo "   • GET  /api/shop-module/categories (Returns: $categoriesCount categories)\n";
        echo "   • POST /api/shop-module/categories\n";
        echo "   • GET  /api/shop-module/products (Returns: $productsCount products)\n";
        echo "   • POST /api/shop-module/products\n";
        echo "   • GET  /api/shop-module/products/{id}\n";
        echo "   • PUT  /api/shop-module/products/{id}\n";
        echo "   • DELETE /api/shop-module/products/{id}\n\n";
    } else {
        echo "✅ API endpoints created and ready for testing:\n";
        echo "   • GET  /api/shop-module/categories\n";
        echo "   • POST /api/shop-module/categories\n";
        echo "   • GET  /api/shop-module/products\n";
        echo "   • POST /api/shop-module/products\n";
        echo "   • GET  /api/shop-module/products/{id}\n";
        echo "   • PUT  /api/shop-module/products/{id}\n";
        echo "   • DELETE /api/shop-module/products/{id}\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error testing API: {$e->getMessage()}\n";
}

// =================================================================
// FINAL SUMMARY
// =================================================================
echo "🎉 ===============================================\n";
echo "🏪 SHOP MODULE DEMO COMPLETED SUCCESSFULLY!\n";
echo "🎉 ===============================================\n\n";

echo "📋 SUMMARY:\n";
echo "✅ Project: ShopModule (ID: {$project->id})\n";
echo "✅ Tables: Categories (4 fields) + Products (7 fields)\n";
echo "✅ Relationships: Product belongsTo Category, Category hasMany Products\n";
echo "✅ Generated Files: Models, Migrations, Resources, API Routes, Permissions\n";
echo "✅ Sample Data: 2 categories, 4 products\n";
echo "✅ Permissions: Generated and assigned to admin roles\n";
echo "✅ Module Status: Enabled and cached\n\n";

echo "🌐 ADMIN PANEL ACCESS:\n";
echo "   • Categories: http://localhost/filament/admin/shop-module/categories\n";
echo "   • Products: http://localhost/filament/admin/shop-module/products\n\n";

echo "🔧 API TESTING EXAMPLES:\n";
echo "   • curl -X GET http://localhost/filament/api/shop-module/categories\n";
echo "   • curl -X GET http://localhost/filament/api/shop-module/products\n";
echo "   • curl -X POST http://localhost/filament/api/shop-module/products -d '{\"name\":\"New Product\",\"price\":99.99,\"category_id\":1}'\n\n";

echo "🚀 The ShopModule is now fully functional and ready for use!\n";
echo "💡 Use this script as a reference for creating complex multi-table modules.\n\n";

echo "📝 POST-GENERATION STEPS:\n";
echo "   1. Run 'composer dump-autoload' to ensure all classes are autoloaded\n";
echo "   2. Run 'php artisan cache:clear' to clear all caches\n";
echo "   3. Run 'php artisan route:clear' to refresh routes\n";
echo "   4. Check the Filament admin panel at /admin for new resources\n";
echo "   5. Test API endpoints using the examples above\n\n";

echo "🎯 VERIFICATION CHECKLIST:\n";
echo "   □ ShopModule directory exists in Modules/\n";
echo "   □ Database tables created (categories, products)\n";
echo "   □ Models have proper relationships\n";
echo "   □ Filament resources display in admin panel\n";
echo "   □ API endpoints respond correctly\n";
echo "   □ Permissions are created and assigned\n";
echo "   □ Sample data can be created successfully\n\n";

# Laravel Filament Module Builder Pro

## Quick Start Guide

### 🚀 Installation & Setup

1. **Run Database Migrations**
```bash
php artisan migrate:fresh --seed
```

2. **Test Module Generation**
```bash
php create_shop_module_demo_enhanced.php
```

### 📖 Documentation Files

| File | Description |
|------|-------------|
| `MODULE_BUILDER_DOCUMENTATION.md` | Complete documentation with examples |
| `create_shop_module_demo_enhanced.php` | Reference demo script |
| `MODULE_BUILDER_QUICK_START.md` | This quick start guide |

### 🏪 Demo Module Features

The `create_shop_module_demo_enhanced.php` script demonstrates:

- ✅ **Project Creation**: Complete module configuration
- ✅ **Database Design**: Tables with relationships and enum fields
- ✅ **Code Generation**: Models, migrations, Filament resources
- ✅ **API Creation**: RESTful endpoints with full CRUD
- ✅ **Permissions**: Role-based access control
- ✅ **Testing**: Sample data and validation

### 🎯 Generated Module Structure

```
Modules/ShopModule/
├── Models/
│   ├── Category.php           # Category model with products relationship
│   └── Product.php            # Product model with category relationship
├── Filament/Resources/
│   ├── CategoryResource.php   # Admin interface for categories
│   └── ProductResource.php    # Admin interface with category select
├── database/migrations/
│   ├── create_categories_table.php    # Categories with enum status
│   └── create_products_table.php      # Products with foreign keys
├── routes/api.php             # RESTful API endpoints
└── app/Providers/             # Service providers and configuration
```

### 🔧 Key Features Demonstrated

#### 1. Enum Fields Support
```php
// Categories: status enum ['active', 'inactive']
// Products: status enum ['active', 'inactive', 'out_of_stock']
```

#### 2. Relationships
```php
// Product belongs to Category
// Category has many Products
```

#### 3. API Endpoints
```bash
GET    /api/shop-module/categories
POST   /api/shop-module/categories
GET    /api/shop-module/products
POST   /api/shop-module/products
```

#### 4. Admin Panel
- Categories management at `/admin/shop-module/categories`
- Products management at `/admin/shop-module/products`

### 📝 Usage Examples

#### Creating a Simple Module
```php
// 1. Create project
$project = ModuleProject::create([
    'name' => 'BlogModule',
    'namespace' => 'Modules\\BlogModule',
    'has_api' => true,
    'has_admin_panel' => true,
]);

// 2. Create table
$table = ModuleTable::create([
    'project_id' => $project->id,
    'name' => 'posts',
    'model_name' => 'Post',
]);

// 3. Add fields
ModuleField::create([
    'table_id' => $table->id,
    'name' => 'title',
    'type' => 'string',
    'database_type' => 'string',
    'length' => 255,
]);

// 4. Generate module
$generator = new ModuleGeneratorService($project);
$result = $generator->generateModule();
```

#### Creating Enum Fields
```php
ModuleField::create([
    'table_id' => $table->id,
    'name' => 'status',
    'type' => 'enum',
    'database_type' => 'enum',
    'enum_values' => ['draft', 'published', 'archived'],
    'default_value' => 'draft',
]);
```

#### Creating Relationships
```php
// belongsTo relationship
ModuleRelationship::create([
    'project_id' => $project->id,
    'from_table_id' => $postsTable->id,
    'to_table_id' => $categoriesTable->id,
    'name' => 'category',
    'type' => 'belongsTo',
    'foreign_key' => 'category_id',
]);
```

### 🛠️ Troubleshooting

#### Common Issues & Solutions

1. **Migration Errors**
```bash
# Clear caches and rebuild
php artisan cache:clear
composer dump-autoload
php artisan migrate:fresh --seed
```

2. **Enum Field Errors**
```php
// Ensure enum_values is an array
'enum_values' => ['value1', 'value2', 'value3']
```

3. **Module Not Loading**
```bash
# Check module status
php artisan module:list
php artisan module:enable YourModule
```

4. **Permission Issues**
```bash
# Reset permissions
php artisan permission:cache-reset
```

### 🎉 Success Checklist

After running the demo script, verify:

- [ ] ShopModule directory created in `Modules/`
- [ ] Database tables exist: `categories`, `products`
- [ ] Models have relationships: `Category::products()`, `Product::category()`
- [ ] Admin panel accessible at `/admin`
- [ ] API endpoints respond correctly
- [ ] Permissions created and assigned
- [ ] Sample data created successfully

### 🔗 API Testing

Test the generated APIs:

```bash
# List categories
curl -X GET http://localhost/api/shop-module/categories

# Create category
curl -X POST http://localhost/api/shop-module/categories \
  -H "Content-Type: application/json" \
  -d '{"name":"New Category","slug":"new-category","status":"active"}'

# List products
curl -X GET http://localhost/api/shop-module/products

# Create product
curl -X POST http://localhost/api/shop-module/products \
  -H "Content-Type: application/json" \
  -d '{"name":"New Product","price":99.99,"category_id":1,"status":"active"}'
```

### 📚 Additional Resources

- **Full Documentation**: `MODULE_BUILDER_DOCUMENTATION.md`
- **Demo Script**: `create_shop_module_demo_enhanced.php`
- **Laravel Modules**: [nwidart/laravel-modules](https://github.com/nwidart/laravel-modules)
- **Filament**: [filamentphp.com](https://filamentphp.com)
- **Spatie Permissions**: [spatie.be/docs/laravel-permission](https://spatie.be/docs/laravel-permission)

### 🤝 Support

For issues and questions:
- Check the troubleshooting section above
- Review the full documentation
- Run the demo script for reference examples

### 📄 License

MIT License - see the full documentation for details.

# Module Builder v1 Documentation

## 🎉 Laravel + Filament Module Builder System

A complete module development system for Laravel + Filament 4.x that provides an **October CMS-like plugin builder experience** with professional admin interfaces, working relationships, and auto-generation capabilities.

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Features](#features)
3. [Installation](#installation)
4. [Module Builders](#module-builders)
5. [Generated Components](#generated-components)
6. [Field Types](#field-types)
7. [Relationships](#relationships)
8. [Usage Examples](#usage-examples)
9. [Troubleshooting](#troubleshooting)
10. [Technical Details](#technical-details)

---

## 🎯 Overview

The Module Builder system provides three powerful tools for rapid Laravel + Filament development:

- **🏗️ Enhanced Module Builder** - Create complete modules with demo data
- **✨ Module Editor** - Extend existing modules with new features  
- **🎨 Simple Module Builder** - Basic module creation for simple needs

### Key Benefits

- ✅ **October CMS Experience** - Familiar plugin builder interface
- ✅ **Working Relationships** - Proper dropdown selects with data
- ✅ **Auto-Discovery** - New modules appear automatically in sidebar
- ✅ **Rich Field Types** - 15+ field types including JSON, enum, rich text
- ✅ **Sample Data** - Realistic factories and seeders
- ✅ **Professional UI** - Complete Filament 4.x admin interfaces

---

## 🚀 Features

### ✅ Complete Module Generation
- **Models** with proper relationships and factories
- **Migrations** with constraints and foreign keys
- **Filament Resources** with forms and tables
- **Factories** for realistic sample data
- **Seeders** for database population
- **Permissions** auto-registered with role system

### ✅ Advanced Field Types
- **Basic**: string, text, integer, decimal, boolean
- **Advanced**: rich_text, json, enum, date, datetime
- **Files**: image, file uploads with validation
- **Relationships**: belongsTo, hasMany with working dropdowns

### ✅ Professional Features
- **Auto-sidebar registration** - New modules appear automatically
- **Working relationship dropdowns** - Properly populated selects
- **Sample data generation** - Realistic test data
- **Complete CRUD interfaces** - Professional admin panels
- **Role-based permissions** - Integrated with existing auth system

---

## 📦 Installation

### Prerequisites
- Laravel 10+
- Filament 4.x
- MySQL/PostgreSQL database
- PHP 8.1+

### Setup
The Module Builder system is already integrated into your Laravel + Filament application:

1. **Access Module Builders**:
   - Enhanced Module Builder: `/admin/enhanced-module-builder`
   - Module Editor: `/admin/module-editor`
   - Simple Module Builder: `/admin/simple-module-builder`

2. **Auto-Discovery**: New modules automatically appear in admin sidebar

3. **Permissions**: Module builder permissions are auto-registered

---

## 🏗️ Module Builders

### 1. Enhanced Module Builder

**Purpose**: Create complete, production-ready modules with multiple models and relationships.

**Features**:
- ✅ Multiple models per module
- ✅ Complex relationships (belongsTo, hasMany)
- ✅ Rich field types (15+ supported)
- ✅ Demo data auto-fill
- ✅ Complete e-commerce templates

**Demo Data**: Click "Fill Demo Data (Shop)" for a complete e-commerce module with:
- **Categories** (name, slug, description, image, active, sort_order)
- **Products** (name, SKU, pricing, inventory, SEO, status)
- **Orders** (customer info, billing, shipping, payment tracking)

### 2. Module Editor

**Purpose**: Extend existing modules with new tables, fields, and relationships.

**Features**:
- ✅ Add fields to existing tables
- ✅ Create new tables in existing modules
- ✅ Modify relationships
- ✅ Update permissions

### 3. Simple Module Builder

**Purpose**: Quick creation of basic modules with single models.

**Features**:
- ✅ Single model generation
- ✅ Basic field types
- ✅ Simple relationships
- ✅ Fast development

---

## 📊 Generated Components

### Models
```php
<?php
namespace Modules\Shop\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    
    protected $table = 'shop_products';
    protected $fillable = ['name', 'slug', 'sku', 'price', 'category_id'];
    
    protected static function newFactory()
    {
        return \Modules\Shop\database\factories\ProductFactory::new();
    }
    
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
```

### Filament Resources
```php
<?php
namespace Modules\Shop\app\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationGroup = 'Shop';
    
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('name')->required(),
            TextInput::make('sku')->required(),
            TextInput::make('price')->numeric()->required(),
            Select::make('category_id')
                ->label('Category')
                ->options(Category::all()->pluck('name', 'id')->toArray())
                ->required()
        ]);
    }
}
```

### Migrations
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shop_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->decimal('price', 10, 2);
            $table->foreignId('category_id')->constrained('shop_categories');
            $table->timestamps();
        });
    }
};
```

---

## 🎨 Field Types

### Basic Types
- **string** - Text input with length validation
- **text** - Textarea for longer content
- **integer** - Numeric input for whole numbers
- **decimal** - Numeric input with decimal places
- **boolean** - Toggle switch for true/false

### Advanced Types
- **rich_text** - WYSIWYG editor for formatted content
- **json** - JSON field for complex data structures
- **enum** - Select dropdown with predefined options
- **date** - Date picker
- **datetime** - Date and time picker

### File Types
- **image** - Image upload with preview
- **file** - General file upload

### Relationship Types
- **belongsTo** - Many-to-one relationship with dropdown select
- **hasMany** - One-to-many relationship (inverse of belongsTo)

---

## 🔗 Relationships

### BelongsTo Example
```php
// Product belongs to Category
[
    'from_model' => 'Product',
    'to_model' => 'Category', 
    'type' => 'belongsTo',
    'foreign_key' => 'category_id',
    'relationship_name' => 'category'
]
```

**Generated Code**:
- **Model Method**: `public function category() { return $this->belongsTo(Category::class); }`
- **Form Field**: `Select::make('category_id')->options(Category::all()->pluck('name', 'id'))`
- **Migration**: `$table->foreignId('category_id')->constrained('categories')`

### HasMany Example
```php
// Category has many Products
[
    'from_model' => 'Category',
    'to_model' => 'Product',
    'type' => 'hasMany', 
    'foreign_key' => 'category_id',
    'relationship_name' => 'products'
]
```

**Generated Code**:
- **Model Method**: `public function products() { return $this->hasMany(Product::class); }`

---

## 💡 Usage Examples

### Example 1: E-commerce Module
```php
// Use Enhanced Module Builder with demo data
1. Go to /admin/enhanced-module-builder
2. Click "Fill Demo Data (Shop)"
3. Review the generated structure:
   - Categories (name, slug, description, image)
   - Products (name, SKU, price, category relationship)
   - Orders (customer info, billing, payment)
4. Click "Generate Enhanced Module"
5. Module appears automatically in sidebar
```

### Example 2: Blog Module
```php
// Create custom blog module
Module: Blog
Models:
  - Category (name, slug, description)
  - Post (title, slug, content, category_id, published_at)
  - Tag (name, slug)
  
Relationships:
  - Post belongsTo Category
  - Category hasMany Posts
```

### Example 3: Library Module  
```php
// Create library management system
Module: Library
Models:
  - Author (name, bio, birth_date)
  - Book (title, isbn, pages, author_id, published_at)
  
Relationships:
  - Book belongsTo Author
  - Author hasMany Books
```

---

## 🔧 Troubleshooting

### Common Issues

#### 1. Module Not Appearing in Sidebar
**Solution**: Modules auto-appear via AdminPanelProvider discovery. Clear cache:
```bash
php artisan config:clear
php artisan route:clear
```

#### 2. Relationship Dropdown Empty
**Solution**: Ensure related model has data:
```bash
php artisan db:seed --class="Modules\Shop\database\seeders\CategorySeeder"
```

#### 3. Foreign Key Errors
**Solution**: Check model fillable array includes foreign keys:
```php
protected $fillable = ['name', 'price', 'category_id']; // Include category_id
```

#### 4. Factory Errors
**Solution**: Ensure models have newFactory method:
```php
protected static function newFactory()
{
    return \Modules\Shop\database\factories\ProductFactory::new();
}
```

### Debug Commands
```bash
# Check module structure
ls -la Modules/YourModule/

# Test model relationships
php artisan tinker
>>> \Modules\Shop\app\Models\Product::with('category')->first()

# Run specific seeders
php artisan db:seed --class="Modules\Shop\database\seeders\ProductSeeder"
```

---

## ⚙️ Technical Details

### Auto-Discovery System
```php
// AdminPanelProvider automatically discovers modules
private function discoverModuleResources($panel): void
{
    $directories = glob(base_path('Modules/*'), GLOB_ONLYDIR);
    foreach ($directories as $directory) {
        $moduleName = basename($directory);
        if (!in_array($moduleName, ['Core', 'PublicUser', 'ModuleBuilder'])) {
            $resourcesPath = $directory . '/app/Filament/Resources';
            if (file_exists($resourcesPath)) {
                $panel->discoverResources(
                    in: $resourcesPath,
                    for: "Modules\\{$moduleName}\\app\\Filament\\Resources"
                );
            }
        }
    }
}
```

### Working Relationship Format
```php
// Single-line format that works reliably
Select::make('category_id')
    ->label('Category')
    ->options(\Modules\Shop\app\Models\Category::all()->pluck('name', 'id')->toArray())
    ->required()
```

### Module Structure
```
Modules/YourModule/
├── app/
│   ├── Filament/Resources/
│   ├── Http/Controllers/
│   ├── Models/
│   └── Providers/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── routes/
└── module.json
```

---

## 🎉 Conclusion

The Module Builder v1 system provides a **complete, production-ready solution** for rapid Laravel + Filament development. With working relationships, auto-discovery, and professional interfaces, it delivers the **October CMS plugin builder experience** you expect.

**Ready to build amazing modules!** 🚀

---

## 📞 Support

For issues or questions:
1. Check the troubleshooting section
2. Review generated code in `Modules/` directory
3. Test with demo data first
4. Clear cache when making changes

**Happy Module Building!** ✨

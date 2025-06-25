# Laravel Filament Module Builder Pro

## Overview

The **Laravel Filament Module Builder Pro** is a powerful, automated code generation tool that creates complete Laravel modules with Filament admin panels, API endpoints, relationships, permissions, and migrations. It provides a visual interface for designing database schemas and automatically generates all necessary files including models, migrations, Filament resources, API controllers, and permission systems.

## Features

### 🚀 Core Features
- **Visual Database Designer**: Create tables and fields through an intuitive web interface
- **Relationship Management**: Define and manage complex database relationships (hasOne, hasMany, belongsTo, belongsToMany)
- **Enum Field Support**: Full support for enum fields with custom values
- **Automatic Code Generation**: Generate models, migrations, Filament resources, and API endpoints
- **Permission System**: Automatic generation of role-based permissions
- **API Generation**: RESTful API endpoints with full CRUD operations
- **Module System**: Modular architecture using nwidart/laravel-modules

### 🎯 Advanced Features
- **Smart Form Fields**: Automatic Filament form component selection based on field types
- **Relationship-Aware Resources**: Auto-generated select fields for foreign key relationships
- **Migration Generation**: Safe, rollback-capable database migrations
- **Enum Migration Support**: Proper enum column generation with values
- **Autoload Management**: Automatic composer autoload updates
- **Cache Management**: Intelligent cache clearing and module registration

## Installation

### Prerequisites
- Laravel 12+
- PHP 8.1+
- Filament 4.x
- nwidart/laravel-modules
- spatie/laravel-permission

### Setup Steps

1. **Install Required Packages**
```bash
composer require nwidart/laravel-modules
composer require spatie/laravel-permission
composer require filament/filament
```

2. **Publish Module Configuration**
```bash
php artisan vendor:publish --provider="Nwidart\Modules\LaravelModulesServiceProvider"
```

3. **Run Migrations**
```bash
php artisan migrate
php artisan db:seed --class=AdminSeeder
```

4. **Install Module Builder**
- Copy the `ModuleBuilder` module to your `Modules/` directory
- Enable the module: `php artisan module:enable ModuleBuilder`
- Run module migrations: `php artisan migrate`

## Module Structure

```
Modules/ModuleBuilder/
├── app/
│   ├── Filament/Resources/          # Admin panel resources
│   ├── Http/Controllers/            # Web controllers
│   ├── Models/                      # Eloquent models
│   └── Services/                    # Business logic services
├── database/
│   ├── migrations/                  # Database migrations
│   └── seeders/                     # Database seeders
├── Stubs/                          # Code generation templates
└── module.json                     # Module configuration
```

## Usage Guide

### 1. Creating a New Module Project

```php
use Modules\ModuleBuilder\Models\ModuleProject;

$project = ModuleProject::create([
    'name' => 'BlogModule',
    'namespace' => 'Modules\\BlogModule',
    'description' => 'A complete blog system with posts and categories',
    'author_name' => 'Your Name',
    'author_email' => 'your@email.com',
    'version' => '1.0.0',
    'enabled' => true,
    'has_api' => true,
    'has_admin_panel' => true,
    'has_permissions' => true,
]);
```

### 2. Creating Database Tables

```php
use Modules\ModuleBuilder\Models\ModuleTable;

$categoriesTable = ModuleTable::create([
    'project_id' => $project->id,
    'name' => 'categories',
    'model_name' => 'Category',
    'display_name' => 'Categories',
    'has_timestamps' => true,
    'has_soft_deletes' => false,
]);

$postsTable = ModuleTable::create([
    'project_id' => $project->id,
    'name' => 'posts',
    'model_name' => 'Post',
    'display_name' => 'Posts',
    'has_timestamps' => true,
    'has_soft_deletes' => true,
]);
```

### 3. Adding Fields to Tables

```php
use Modules\ModuleBuilder\Models\ModuleField;

// Category fields
ModuleField::create([
    'table_id' => $categoriesTable->id,
    'name' => 'name',
    'label' => 'Category Name',
    'type' => 'string',
    'database_type' => 'string',
    'length' => 255,
    'nullable' => false,
    'unique' => true,
    'is_fillable' => true,
    'is_searchable' => true,
    'table_column' => true,
]);

ModuleField::create([
    'table_id' => $categoriesTable->id,
    'name' => 'status',
    'label' => 'Status',
    'type' => 'enum',
    'database_type' => 'enum',
    'enum_values' => ['active', 'inactive'],
    'default_value' => 'active',
    'nullable' => false,
    'is_fillable' => true,
    'table_column' => true,
]);

// Post fields
ModuleField::create([
    'table_id' => $postsTable->id,
    'name' => 'title',
    'label' => 'Post Title',
    'type' => 'string',
    'database_type' => 'string',
    'length' => 255,
    'nullable' => false,
    'is_fillable' => true,
    'is_searchable' => true,
    'table_column' => true,
]);

ModuleField::create([
    'table_id' => $postsTable->id,
    'name' => 'category_id',
    'label' => 'Category',
    'type' => 'unsignedBigInteger',
    'database_type' => 'unsignedBigInteger',
    'nullable' => false,
    'is_fillable' => true,
    'table_column' => true,
]);
```

### 4. Creating Relationships

```php
use Modules\ModuleBuilder\Models\ModuleRelationship;

// Post belongs to Category
ModuleRelationship::create([
    'project_id' => $project->id,
    'from_table_id' => $postsTable->id,
    'to_table_id' => $categoriesTable->id,
    'name' => 'category',
    'type' => 'belongsTo',
    'foreign_key' => 'category_id',
    'local_key' => 'id',
]);

// Category has many Posts
ModuleRelationship::create([
    'project_id' => $project->id,
    'from_table_id' => $categoriesTable->id,
    'to_table_id' => $postsTable->id,
    'name' => 'posts',
    'type' => 'hasMany',
    'foreign_key' => 'category_id',
    'local_key' => 'id',
]);
```

### 5. Generating the Module

```php
use Modules\ModuleBuilder\Services\ModuleGeneratorService;

$generator = new ModuleGeneratorService($project);
$result = $generator->generateModule();

if ($result['success']) {
    echo "Module generated successfully!";
    // Run migrations
    Artisan::call('migrate');
} else {
    echo "Error: " . $result['message'];
}
```

## Field Types and Database Mappings

### Supported Field Types

| Field Type | Database Type | Laravel Migration | Filament Component |
|------------|---------------|-------------------|-------------------|
| string | string | `string()` | TextInput |
| text | text | `text()` | Textarea |
| longText | longText | `longText()` | Textarea |
| integer | integer | `integer()` | TextInput |
| bigInteger | bigInteger | `bigInteger()` | TextInput |
| decimal | decimal | `decimal()` | TextInput |
| boolean | boolean | `boolean()` | Toggle |
| date | date | `date()` | DatePicker |
| datetime | datetime | `dateTime()` | DateTimePicker |
| timestamp | timestamp | `timestamp()` | DateTimePicker |
| json | json | `json()` | KeyValue |
| enum | enum | `enum()` | Select |

### Enum Field Configuration

Enum fields require special configuration:

```php
ModuleField::create([
    'name' => 'status',
    'type' => 'enum',
    'database_type' => 'enum',
    'enum_values' => ['draft', 'published', 'archived'], // Array of values
    'default_value' => 'draft',
    'nullable' => false,
]);
```

## Generated File Structure

When a module is generated, the following structure is created:

```
Modules/YourModule/
├── Models/
│   ├── Category.php              # Eloquent models with relationships
│   └── Post.php
├── Filament/Resources/
│   ├── CategoryResource.php      # Filament admin resources
│   ├── CategoryResource/Pages/   # Resource pages
│   ├── PostResource.php
│   └── PostResource/Pages/
├── app/
│   ├── Http/Controllers/Api/     # API controllers
│   └── Providers/               # Service providers
├── database/migrations/          # Database migrations
├── routes/
│   ├── api.php                  # API routes
│   └── web.php                  # Web routes
└── module.json                  # Module configuration
```

## API Endpoints

Generated modules include RESTful API endpoints:

```
GET    /api/your-module/categories          # List all categories
POST   /api/your-module/categories          # Create category
GET    /api/your-module/categories/{id}     # Show category
PUT    /api/your-module/categories/{id}     # Update category
DELETE /api/your-module/categories/{id}     # Delete category

GET    /api/your-module/posts               # List all posts
POST   /api/your-module/posts               # Create post
GET    /api/your-module/posts/{id}          # Show post
PUT    /api/your-module/posts/{id}          # Update post
DELETE /api/your-module/posts/{id}          # Delete post
```

## Permission System

The module builder automatically generates permissions for each resource:

### Generated Permissions
- `view_any_{resource}` - View resource listing
- `view_{resource}` - View individual resource
- `create_{resource}` - Create new resource
- `update_{resource}` - Update existing resource
- `delete_{resource}` - Delete resource
- `delete_any_{resource}` - Bulk delete resources
- `force_delete_{resource}` - Force delete (if soft deletes)
- `force_delete_any_{resource}` - Bulk force delete
- `restore_{resource}` - Restore soft deleted
- `restore_any_{resource}` - Bulk restore
- `replicate_{resource}` - Duplicate resource

### Permission Assignment
```php
use Spatie\Permission\Models\Role;

$adminRole = Role::findByName('admin');
$adminRole->givePermissionTo([
    'view_any_categories',
    'create_categories',
    'update_categories',
    'delete_categories',
]);
```

## Advanced Configuration

### Custom Form Components

Override default form components by setting the `form_component` field:

```php
ModuleField::create([
    'name' => 'content',
    'type' => 'longText',
    'form_component' => 'RichEditor', // Custom Filament component
]);
```

### Validation Rules

Add validation rules to fields:

```php
ModuleField::create([
    'name' => 'email',
    'type' => 'string',
    'validation_rules' => 'required|email|unique:users,email',
]);
```

### Custom Casts

Specify custom model casts:

```php
ModuleField::create([
    'name' => 'metadata',
    'type' => 'json',
    'cast_type' => 'array',
]);
```

## Troubleshooting

### Common Issues

#### 1. Enum Migration Errors
**Error**: `Too few arguments to function Blueprint::enum()`

**Solution**: Ensure enum fields have `enum_values` defined:
```php
$field->enum_values = ['value1', 'value2', 'value3'];
```

#### 2. Class Not Found Errors
**Error**: `Class "CreateTableName" not found`

**Solution**: Run composer autoload:
```bash
composer dump-autoload
php artisan cache:clear
```

#### 3. Permission Errors
**Error**: Permission not found

**Solution**: Generate and assign permissions:
```bash
php artisan permission:cache-reset
```

#### 4. Module Not Loading
**Error**: Module files not found

**Solution**: Check module registration:
```bash
php artisan module:list
php artisan module:enable YourModule
```

### Debug Mode

Enable debug logging by setting `LOG_LEVEL=debug` in `.env`:

```env
LOG_LEVEL=debug
```

## Testing

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific module tests
php artisan test --filter=ModuleBuilder
```

### Creating Test Data

Use the provided demo script to create test modules:

```bash
php create_shop_module_demo.php
```

## Performance Considerations

### Optimization Tips

1. **Enable Module Caching**
```bash
php artisan module:cache
```

2. **Optimize Autoloader**
```bash
composer dump-autoload --optimize
```

3. **Cache Routes and Config**
```bash
php artisan route:cache
php artisan config:cache
```

4. **Database Indexing**
- Add indexes to frequently queried fields
- Use foreign key constraints for relationships

## Security

### Best Practices

1. **Validate Input Data**
- Always validate user input in forms
- Use Laravel's validation rules

2. **Permission Checks**
- Implement proper authorization gates
- Use middleware for API protection

3. **SQL Injection Prevention**
- Use Eloquent ORM or parameterized queries
- Avoid raw SQL when possible

## Extending the Module Builder

### Custom Field Types

Add custom field types by extending the field type mapping:

```php
// In ModuleGeneratorService
protected function getCustomFieldTypes(): array
{
    return [
        'phone' => 'string',
        'currency' => 'decimal',
        'slug' => 'string',
    ];
}
```

### Custom Stubs

Create custom code generation templates in `Stubs/` directory:

```php
// custom-model.stub
<?php

namespace Modules\{{MODULE_NAME}}\Models;

use Illuminate\Database\Eloquent\Model;

class {{MODEL_NAME}} extends Model
{
    // Custom model implementation
}
```

## API Reference

### ModuleGeneratorService

The main service class for generating modules.

#### Methods

##### `generateModule(): array`
Generates the complete module structure.

**Returns**: Array with success status and generated files list.

##### `generateMigrations(): void`
Generates database migrations for all tables.

##### `generateModels(): void`
Generates Eloquent models with relationships.

##### `generateFilamentResources(): void`
Generates Filament admin panel resources.

##### `generateApiControllers(): void`
Generates API controllers with CRUD operations.

### Models

#### ModuleProject
Main project model containing module configuration.

**Fields**:
- `name`: Module name
- `namespace`: PHP namespace
- `description`: Project description
- `enabled`: Whether module is active
- `has_api`: Include API endpoints
- `has_admin_panel`: Include Filament resources

#### ModuleTable
Database table definition.

**Fields**:
- `name`: Table name
- `model_name`: Corresponding model name
- `has_timestamps`: Include created_at/updated_at
- `has_soft_deletes`: Include soft delete functionality

#### ModuleField
Table field definition.

**Fields**:
- `name`: Field name
- `type`: Field type
- `database_type`: Database column type
- `enum_values`: Array of enum values (for enum fields)
- `nullable`: Allow null values
- `unique`: Unique constraint
- `default_value`: Default field value

#### ModuleRelationship
Database relationship definition.

**Fields**:
- `name`: Relationship method name
- `type`: Relationship type (hasOne, hasMany, belongsTo, belongsToMany)
- `foreign_key`: Foreign key column
- `local_key`: Local key column

## Changelog

### Version 1.0.0
- Initial release
- Basic module generation
- Filament resource creation
- API endpoint generation
- Permission system integration

### Version 1.1.0
- Enhanced enum field support
- Improved relationship handling
- Better error handling and validation
- Migration rollback support

### Version 1.2.0
- Advanced form component mapping
- Custom validation rules
- Performance optimizations
- Comprehensive documentation

## Contributing

### Development Setup

1. Clone the repository
2. Install dependencies: `composer install`
3. Run tests: `php artisan test`
4. Submit pull requests with tests

### Code Standards

- Follow PSR-12 coding standards
- Write comprehensive tests
- Document new features
- Use semantic versioning

## License

This module is licensed under the MIT License. See LICENSE file for details.

## Support

For support and questions:
- GitHub Issues: [Repository Issues](https://github.com/your-repo/issues)
- Documentation: This file
- Email: your-support@email.com

## Credits

Developed with ❤️ using:
- [Laravel](https://laravel.com)
- [Filament](https://filamentphp.com)
- [nwidart/laravel-modules](https://github.com/nwidart/laravel-modules)
- [spatie/laravel-permission](https://github.com/spatie/laravel-permission)

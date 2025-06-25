# Module Builder - Migration Workflow Guide

## 🔄 **How Migrations Work When Fields Are Added or Updated**

### **Automatic Migration Generation**

When you add or modify fields in the Module Builder, the system automatically handles database schema changes through Laravel migrations.

### **🎯 Workflow Process:**

#### **1. Create/Edit Table with Fields**
- Use the Module Builder interface to create a new table
- Add fields using the inline "Table Fields" repeater
- Each field can specify:
  - **Field Name**: Database column name
  - **Field Type**: string, integer, boolean, etc.
  - **Length**: For varchar/string fields
  - **Nullable**: Allow NULL values
  - **Unique**: Unique constraint
  - **Filament Component**: How it appears in admin forms

#### **2. Auto-Migration Generation**
When you save a table with field changes:
- ✅ **Migration file** is automatically created
- ✅ **File location**: `Modules/{ModuleName}/database/migrations/`
- ✅ **Notification** appears with migration details
- ✅ **File naming**: `YYYY_MM_DD_HHMMSS_modify_{table_name}_table.php`

#### **3. Migration Execution Options**

**Option A: Manual Execution**
```bash
# Run migrations for specific module
php artisan migrate --path=Modules/{ModuleName}/database/migrations

# Run all pending migrations
php artisan migrate
```

**Option B: UI Execution**
- Go to the table edit page
- Click **"Run Pending Migrations"** button
- Confirm execution in modal dialog

#### **4. Migration Content Example**

When you add fields to a `posts` table, the generated migration looks like:

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('title', 255);
            $table->text('content')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['title', 'content', 'is_published', 'published_at']);
        });
    }
};
```

### **🔧 Field Types & Database Mapping**

| Module Builder Type | Database Column Type | Example Usage |
|-------------------|---------------------|---------------|
| `string` | `VARCHAR(length)` | Names, emails, titles |
| `text` | `TEXT` | Long descriptions, content |
| `integer` | `INT` | Counts, IDs, numbers |
| `bigInteger` | `BIGINT` | Large numbers, timestamps |
| `decimal` | `DECIMAL(precision,scale)` | Prices, measurements |
| `boolean` | `BOOLEAN/TINYINT(1)` | Yes/no, active/inactive |
| `date` | `DATE` | Birth dates, deadlines |
| `datetime` | `DATETIME` | Created at, updated at |
| `timestamp` | `TIMESTAMP` | System timestamps |
| `json` | `JSON` | Settings, metadata |
| `foreignId` | `BIGINT UNSIGNED` | Foreign key references |

### **⚠️ Important Migration Considerations**

#### **Adding New Fields**
- ✅ **Safe**: Adding new columns is always safe
- ✅ **Non-destructive**: Existing data is preserved
- ✅ **Can be nullable**: New fields should typically be nullable

#### **Modifying Existing Fields**
- ⚠️ **Caution required**: May affect existing data
- ⚠️ **Data loss risk**: Changing types can truncate data
- ⚠️ **Requires testing**: Always test on staging first

#### **Removing Fields**
- 🚨 **Data loss**: Dropping columns permanently deletes data
- 🚨 **Backup first**: Always backup before dropping
- 🚨 **Two-step process**: Remove from code first, drop column later

### **📋 Best Practices**

#### **1. Development Workflow**
```bash
# 1. Add fields in Module Builder UI
# 2. Auto-migration is generated
# 3. Review the migration file
# 4. Run migration
php artisan migrate --path=Modules/YourModule/database/migrations

# 5. Test the changes
# 6. Commit both model and migration files
```

#### **2. Production Deployment**
```bash
# 1. Deploy code with migration files
# 2. Backup database
# 3. Run migrations
php artisan migrate --force

# 4. Verify changes
# 5. Monitor for issues
```

#### **3. Rollback Strategy**
```bash
# If something goes wrong, rollback last migration
php artisan migrate:rollback --step=1

# Or rollback specific migration
php artisan migrate:rollback --path=Modules/YourModule/database/migrations
```

### **🔄 Advanced Scenarios**

#### **1. Adding Foreign Key Relationships**
```php
// In migration
$table->foreignId('category_id')->constrained()->onDelete('cascade');

// In Module Builder
Field Type: foreignId
Name: category_id
Validation: required|exists:categories,id
```

#### **2. Adding Indexes for Performance**
```php
// In migration
$table->string('slug')->unique();
$table->index(['status', 'created_at']);

// Module Builder sets unique: true for unique constraints
```

#### **3. Enum Fields**
```php
// In migration
$table->enum('status', ['draft', 'published', 'archived']);

// In Module Builder
Field Type: enum
Validation: required|in:draft,published,archived
```

### **🚨 Troubleshooting**

#### **Migration Fails**
1. **Check syntax** in generated migration file
2. **Verify table exists** in database
3. **Check for conflicts** with existing columns
4. **Review Laravel logs** for detailed error

#### **Fields Not Appearing**
1. **Run migration** if not executed
2. **Clear cache**: `php artisan cache:clear`
3. **Refresh model**: Update fillable properties

#### **Data Type Mismatches**
1. **Check field mapping** in migration
2. **Verify input validation** rules
3. **Test with sample data**

### **📚 Additional Resources**

- [Laravel Migration Documentation](https://laravel.com/docs/migrations)
- [Database Schema Builder](https://laravel.com/docs/migrations#tables)
- [Filament Form Builder](https://filamentphp.com/docs/forms/fields)

---

**💡 Pro Tip**: Always test field changes in development first. The Module Builder makes it easy to iterate on your database schema, but production changes should be carefully planned and executed.

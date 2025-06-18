# Laravel Secure Admin Framework

<div align="center">

![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Filament](https://img.shields.io/badge/Filament-4.x-F59E0B?style=for-the-badge&logo=filame## 🛡️ Module Permission System

This project features a **sophisticated module-aware permission system** that automatically manages permissions based on your application's modular structure. It provides a more advanced alternative to Filament Shield, specifically designed for modular Laravel applications.

### 🎯 **Key Features:**

- **🔍 Auto-Discovery**: Automatically discovers Filament resources across all modules
- **📦 Module Organization**: Groups permissions by modules (Core, PublicUser, System)
- **🛡️ Comprehensive CRUD**: Generates all standard permissions (view, create, update, delete, etc.)
- **🔄 Dual Guard Support**: Seamlessly works with both `admin` and `web` guards
- **🎨 Intuitive Interface**: Module-grouped permission management in Filament admin
- **⚡ CLI Management**: Powerful artisan commands for permission setup and management
- **🔄 Dynamic Registration**: New modules and permissions are automatically detected=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)

**A comprehensive Laravel framework featuring modular architecture with Filament admin panel, role-based permissions, and secure dual authentication system for enterprise and government applications.**

[🚀 Live Demo](https://your-demo-link.com) • [📖 Documentation](https://github.com/AbdurRehman712/laravel-secure-admin-framework/wiki) • [🐛 Report Bug](https://github.com/AbdurRehman712/laravel-secure-admin-framework/issues) • [💡 Request Feature](https://github.com/AbdurRehman712/laravel-secure-admin-framework/issues)

</div>

## 🌟 Overview

Laravel Secure Admin Framework is a production-ready, enterprise-grade Laravel template designed for organizations requiring robust admin management systems with secure authentication and granular permission controls. Built with modern web technologies and best practices, it provides a solid foundation for building scalable administrative applications.

**Perfect for:** Government agencies, enterprise organizations, educational institutions, healthcare systems, and any organization requiring secure, role-based admin panels.

## ✨ Key Features

### 🏗️ **Modular Architecture**
- Clean separation of concerns using `nwidart/laravel-modules`
- Plug-and-play module system for easy extensibility
- Scalable structure for large-scale applications

### 🔐 **Advanced Security**
- Dual authentication guards (Admin & Public Users)
- Role-Based Access Control (RBAC) with `spatie/laravel-permission`
- Secure session management and CSRF protection
- Password hashing with bcrypt

### 🎨 **Modern Admin Interface**
- Beautiful, responsive admin panel powered by Filament v4
- Intuitive user management with CRUD operations
- Advanced filtering and search capabilities
- Real-time form validation

### 👥 **User Management**
- Separate admin and public user systems
- Comprehensive role and permission management
- User profile management with email verification
- Bulk user operations

### 🔧 **Developer Experience**
- PSR-4 autoloading compliance
- Comprehensive documentation
- Easy module creation and management
- Built-in testing structure

## �️ Technology Stack

### Backend
- **[Laravel 11.x](https://laravel.com/)** - PHP web application framework
- **[Filament 4.x](https://filamentphp.com/)** - Modern admin panel for Laravel
- **[Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)** - Role and permission management
- **[nWidart Laravel Modules](https://nwidart.com/laravel-modules/)** - Modular application architecture

### Frontend
- **[Tailwind CSS](https://tailwindcss.com/)** - Utility-first CSS framework
- **[Alpine.js](https://alpinejs.dev/)** - Lightweight JavaScript framework
- **[Livewire](https://laravel-livewire.com/)** - Full-stack framework for Laravel

### Database
- **MySQL 8.0+** / **PostgreSQL 13+** - Primary database options
- **Redis** (Optional) - Caching and session storage

### Development Tools
- **Vite** - Fast build tool and development server
- **Composer** - PHP dependency manager
- **npm** - Node.js package manager

## 🏗️ Project Structure

```
app/
├── Models/
│   ├── Admin.php           # Admin user model (moved to Core module)
│   └── User.php            # Public user model
├── Providers/
│   └── Filament/
│       └── AdminPanelProvider.php  # Filament admin panel configuration
Modules/
├── Core/                   # Core admin functionality
│   ├── app/
│   │   ├── Models/
│   │   │   └── Admin.php   # Admin model with roles
│   │   ├── Filament/
│   │   │   └── Resources/
│   │   │       └── AdminResource.php  # Admin management resource
│   │   └── Providers/
│   │       └── CoreServiceProvider.php  # Permission registration
│   └── database/
│       └── migrations/     # Admin-related migrations
└── PublicUser/            # Public user functionality
    ├── app/
    │   ├── Http/
    │   │   └── Controllers/
    │   │       ├── Auth/
    │   │       │   └── LoginController.php  # Public auth
    │   │       └── PublicUserController.php
    │   └── Providers/
    └── routes/
        └── web.php        # Public user routes
```

## � Quick Start

### Prerequisites
- PHP 8.2 or higher
- Composer 2.0+
- Node.js 18+ & npm
- MySQL 8.0+ or PostgreSQL 13+

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/AbdurRehman712/laravel-secure-admin-framework.git
   cd laravel-secure-admin-framework
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database**
   ```bash
   # Update .env file with your database credentials
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run migrations and seeders**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

   **Note:** If you encounter permission/role conflicts, use the safe reset command:
   ```bash
   php artisan db:reset-seed --force
   ```

6. **Build assets**
   ```bash
   npm run build
   ```

7. **Start development server**
   ```bash
   php artisan serve
   ```

### 🎯 Default Access Credentials

**Admin Panel** (Access: `/admin`)
- **Super Admin**
  - Email: `admin@admin.com`
  - Password: `password`

- **Regular Admin**
  - Email: `user@admin.com`
  - Password: `password`

**Public Users** (Various test accounts with different roles)
- **User**: `john@example.com` / `password`
- **Premium User**: `jane@example.com` / `password`
- **Moderator**: `alice@example.com` / `password`

## 🔐 Authentication Guards

### Admin Guard (`admin`)
- **Model**: `Modules\Core\app\Models\Admin`
- **Guard Name**: `admin`
- **Access**: `/admin` routes
- **Filament Panel**: Configured to use admin guard

### Web Guard (`web`)
- **Model**: `App\Models\User`
- **Guard Name**: `web`
- **Access**: Public user routes (`/user/*`)

## 📝 How to Add a New Module

1. **Create the module**
   ```bash
   php artisan module:make ModuleName
   ```

2. **Fix namespace issues** (Update these files after creation):
   - `Modules/ModuleName/module.json` - Update provider namespace
   - `Modules/ModuleName/app/Providers/*ServiceProvider.php` - Fix namespaces

3. **Register permissions** in the module's service provider:
   ```php
   protected function registerPermissions(): void
   {
       $permissions = [
           'view_items',
           'create_items',
           'edit_items',
           'delete_items',
       ];

       foreach ($permissions as $permission) {
           Permission::findOrCreate($permission, 'admin'); // or 'web'
       }
   }
   ```

4. **Create Filament Resources** (if needed):
   ```bash
   # Inside your module directory
   mkdir -p app/Filament/Resources
   ```

5. **Update composer autoloader**:
   ```bash
   composer dump-autoload
   ```

## 🛠️ How to Add Filament Resources

1. **Create the resource** in your module:
   ```php
   // Modules/YourModule/app/Filament/Resources/YourResource.php
   namespace Modules\YourModule\app\Filament\Resources;

   use Filament\Resources\Resource;
   // ... other imports

   class YourResource extends Resource
   {
       protected static ?string $model = YourModel::class;
       // ... resource configuration
   }
   ```

2. **Register the resource** in AdminPanelProvider:
   ```php
   ->discoverResources(in: base_path('Modules/YourModule/app/Filament/Resources'), for: 'Modules\YourModule\app\Filament\Resources')
   ```

## �️ Custom Shield-Like Permission System

This project includes a **custom-built module permission system** that provides **Filament Shield-like functionality** but is specifically designed for modular Laravel applications.

### 🎯 **Key Features:**

- **🔍 Auto-Discovery**: Automatically discovers Filament resources in all modules
- **📦 Module-Aware**: Groups permissions by modules for better organization
- **🛡️ Full CRUD Control**: Generates all standard permissions (view, create, update, delete, etc.)
- **� Dual Guard Support**: Works with both `admin` and `web` guards
- **🎨 Clean Interface**: Beautiful role management interface in Filament admin
- **⚡ CLI Management**: Powerful commands for permission setup and management

### 🚀 **How It Works:**

The system automatically scans your application's modular structure and discovers Filament resources, then generates appropriate permissions for each resource organized by their respective modules.

#### **1. Module Structure Detection**
```
Modules/
├── Core/                   # Core admin functionality
│   └── app/Filament/Resources/
│       └── AdminResource.php → Generates Core module permissions
├── PublicUser/            # Public user management
│   └── app/Filament/Resources/
│       └── UserResource.php → Generates PublicUser module permissions
└── System/                # Application-level permissions
    └── ModuleRoleResource → Generates System module permissions
```

#### **2. Permission Generation**
For each discovered resource (e.g., `AdminResource`), the system automatically creates:

```php
// Core Module Permissions (from AdminResource)
'view_any_admin'     // View admin listing
'view_admin'         // View specific admin
'create_admin'       // Create new admin
'update_admin'       // Edit existing admin
'delete_admin'       // Delete admin
'delete_any_admin'   // Bulk delete admins
'force_delete_admin' // Permanent delete
'restore_admin'      // Restore soft-deleted
'replicate_admin'    // Duplicate admin
```

#### **3. Module-Aware Organization**
Permissions are grouped by their source modules:

- **Core Module**: Admin management, system core functions
- **PublicUser Module**: Public user CRUD operations  
- **System Module**: Application-level features (module roles, settings)

### 🚀 **Quick Setup:**

#### **1. Initial Setup (One-time)**
```bash
# Complete setup with permissions registration and demo roles
php artisan permissions:setup --guard=admin

# Or register permissions only (without demo roles)
php artisan permissions:register --guard=admin -v
```

#### **2. Access Module Roles Management**
1. Visit `/admin` → **Module Roles**
2. Create/edit roles with module-specific permissions
3. Assign roles to users through Admin/User resources

#### **3. Add Permission Authorization (Optional)**
For automatic permission checking in resources:
```php
<?php
// In your Filament Resources (e.g., AdminResource.php)

class AdminResource extends Resource
{
    // Add authorization methods
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_admin') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_admin') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_admin') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_admin') ?? false;
    }
    
    // ...rest of your resource
}
```

### 📋 **Module Permission Structure**

The system organizes permissions into three main categories:

#### **Core Module Permissions**
```php
// Admin Management (from AdminResource)
'view_any_admin', 'view_admin', 'create_admin', 'update_admin', 
'delete_admin', 'delete_any_admin', 'force_delete_admin', 
'force_delete_any_admin', 'restore_admin', 'restore_any_admin', 
'replicate_admin'
```

#### **PublicUser Module Permissions**  
```php
// Public User Management (from UserResource)
'view_any_user', 'view_user', 'create_user', 'update_user',
'delete_user', 'delete_any_user', 'force_delete_user',
'force_delete_any_user', 'restore_user', 'restore_any_user',
'replicate_user'
```

#### **System Module Permissions**
```php
// Application-level permissions (from ModuleRoleResource)
'view_any_module_role', 'view_module_role', 'create_module_role',
'update_module_role', 'delete_module_role', 'delete_any_module_role',
'force_delete_module_role', 'force_delete_any_module_role',
'restore_module_role', 'restore_any_module_role', 'replicate_module_role'
```

### 🎯 **Default Roles**

The system comes with pre-configured roles:

- **Super Admin**: Full access to all modules and permissions
- **Admin**: Core admin functionality with limited system access
- **User Manager**: Public user management permissions only

### ⚡ **Available CLI Commands**

```bash
# Register all module permissions
php artisan permissions:register --guard=admin

# Setup permissions and assign to Super Admin
php artisan permissions:setup --guard=admin

# Register permissions for specific module
php artisan permissions:register --module=Core --guard=admin

# Reset database and reseed (development only)
php artisan db:reset-seed --force
```

### 🎯 **Managing Roles and Permissions**

#### **1. Access Module Roles Interface**
- Navigate to Admin Panel → **Module Roles** 
- View all roles with permission counts
- Filter by guard type (Admin/Web)

#### **2. Create New Role**
- Click "Create" button
- Enter role name and select guard
- Choose permissions organized by modules:
  - 📦 **Core Module Permissions** (Admin management)
  - 📦 **PublicUser Module Permissions** (User management)  
  - 📦 **System Module Permissions** (Module roles, settings)
- Use "Select all" toggles for quick selection

#### **3. Edit Existing Roles**
- Click "Edit" on any role
- Modify permissions with module-grouped interface
- Save changes to update role permissions

#### **4. Assign Roles to Users**
- Use **Admin Resource** for admin users
- Use **User Resource** for public users
- Select roles during user creation/editing

### 🔧 **Advanced Usage**

#### **Service Class Integration**
```php
use App\Services\ModulePermissionService;

// Get all modules with their permissions
$modules = ModulePermissionService::getModulesWithPermissions();

// Get permissions for specific module
$corePermissions = ModulePermissionService::getModulePermissions('Core');

// Register permissions programmatically
ModulePermissionService::registerAllPermissions('admin');

// Get permission structure for role management
$grouped = ModulePermissionService::getPermissionsGroupedByModule('admin');
```

#### **Permission Checking in Code**
```php
// Check specific permissions
if (auth()->user()->can('view_any_admin')) {
    // User can view admin listing
}

// Check role-based access
if (auth()->user()->hasRole('Super Admin')) {
    // User has super admin access
}

// Check multiple permissions
if (auth()->user()->canAny(['create_admin', 'update_admin'])) {
    // User can create or update admins
}
```

### 🔍 **Permission Structure Example**

For a project with `Core` and `PublicUser` modules:

```
📦 Core Module
├── AdminResource
│   ├── view_any_admin
│   ├── view_admin
│   ├── create_admin
│   ├── update_admin
│   ├── delete_admin
│   └── ... (other CRUD permissions)

📦 PublicUser Module
├── UserResource
│   ├── view_any_user
│   ├── view_user
│   ├── create_user
│   ├── update_user
│   ├── delete_user
│   └── ... (other CRUD permissions)
```

### 🚀 **Adding New Modules**

When you create new modules, the permission system automatically adapts:

#### **1. Create New Module**
```bash
php artisan module:make YourModule
```

#### **2. Create Filament Resources**
```php
// Modules/YourModule/app/Filament/Resources/ItemResource.php
namespace Modules\YourModule\app\Filament\Resources;

use Filament\Resources\Resource;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;
    
    // Add authorization methods for automatic permission checking
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_item') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_item') ?? false;
    }
    
    // ... rest of resource configuration
}
```

#### **3. Register New Permissions**
```bash
# Auto-discover and register permissions for the new module
php artisan permissions:register --module=YourModule --guard=admin
```

#### **4. Update Module Roles**
- Visit **Module Roles** in admin panel
- Edit existing roles to include new module permissions
- Create specialized roles for the new module

The system will automatically:
- ✅ Detect the new module and its resources
- ✅ Generate appropriate CRUD permissions
- ✅ Group them under "YourModule Module Permissions"
- ✅ Make them available in the role management interface

### 🔄 **Migration from Filament Shield**

If you're migrating from Filament Shield:

1. **Remove Shield Package**:
   ```bash
   composer remove bezhansalleh/filament-shield
   ```

2. **Replace Authorization Methods**:
   ```php
   // Instead of Shield trait, use direct permission methods
   public static function canViewAny(): bool
   {
       return auth()->user()?->can('view_any_resource') ?? false;
   }
   ```

3. **Register Module Permissions**:
   ```bash
   php artisan permissions:register --guard=admin
   ```

4. **Update Role Management**:
   - Use **Module Roles** instead of Shield's role management
   - Reassign permissions using the new module-grouped interface

### 💡 **Benefits Over Traditional Systems**

- ✅ **Module Organization**: Permissions grouped by logical modules
- ✅ **Auto-Discovery**: No manual permission registration required
- ✅ **Scalable**: Easily supports new modules and resources
- ✅ **User-Friendly**: Intuitive interface for role management  
- ✅ **Guard Flexibility**: Seamless dual-guard support
- ✅ **CLI Support**: Powerful commands for automation
- ✅ **No Dependencies**: Self-contained permission system

## 🌐 Routes Structure

### Admin Routes
```php
// Handled by Filament - /admin/*
// Access via AdminPanelProvider configuration
```

### Public User Routes
```php
// Modules/PublicUser/routes/web.php
Route::prefix('user')->name('publicuser.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'showLoginForm']);
        Route::post('login', [LoginController::class, 'login']);
    });
    
    Route::middleware(['auth:web'])->group(function () {
        Route::get('dashboard', [PublicUserController::class, 'dashboard']);
        Route::post('logout', [LoginController::class, 'logout']);
    });
});
```

## 🧩 Module Development Guidelines

1. **Namespace Convention**: `Modules\{ModuleName}\app\...`
2. **Service Provider**: Always register permissions in the boot method
3. **Models**: Include appropriate guard names for permissions
4. **Resources**: Follow Filament v4 patterns
5. **Routes**: Use appropriate middleware for guards

## 🚦 Development Status

- ✅ **MILESTONE 1**: Package installation (Laravel Modules, Spatie Permission)
- ✅ **MILESTONE 2**: Dual authentication guards configuration
- ✅ **MILESTONE 3**: Core module creation with admin management
- ✅ **MILESTONE 4**: Permission system registration
- ✅ **MILESTONE 5**: PublicUser module creation
- ✅ **MILESTONE 6**: Auth routes and guard separation
- ✅ **MILESTONE 7**: Default roles and admin user seeding
- ✅ **MILESTONE 8**: Project template and documentation

## 🔄 Next Steps for Development

1. ~~**Complete Filament Resources**: Fix AdminResource for Filament v4 compatibility~~ ✅ **COMPLETED**
2. **Add More Modules**: Create additional business logic modules
3. **Frontend Views**: Add public user interface views
4. **API Integration**: Add API routes and controllers
5. **Testing**: Implement comprehensive test suite
6. **Deployment**: Add deployment configurations

## 🧪 System Verification

To verify the system is working correctly:

```bash
# 1. Start the development server
php artisan serve

# 2. Access the admin panel
# Visit: http://localhost:8000/admin
# Login: admin@admin.com / password

# 3. Check database integrity
php artisan tinker --execute="
echo 'Admin Users: ' . Modules\Core\app\Models\Admin::count();
echo '\nPublic Users: ' . App\Models\User::count();
echo '\nAdmin Roles: ' . Spatie\Permission\Models\Role::where('guard_name', 'admin')->count();
echo '\nPublic Roles: ' . Spatie\Permission\Models\Role::where('guard_name', 'web')->count();
"

# 4. Reset system if needed
php artisan db:reset-seed --force
```

## 🐛 Known Issues

- ~~AdminResource needs Filament v4 compatibility fixes~~ ✅ **FIXED**
- ~~Some autoloader warnings for module structure (non-critical)~~ ✅ **FIXED**
- ~~Filament Shield references without Shield package installed~~ ✅ **FIXED**
- ~~Duplicate role/permission assignments in database~~ ✅ **FIXED**

## ✅ System Status

- **Admin Panel**: ✅ Working at `/admin`
- **Dual Guards**: ✅ Admin (`admin`) and Public (`web`) authentication
- **Role Management**: ✅ Spatie Permission system with proper guards
- **Filament Resources**: ✅ AdminResource and UserResource functional
- **Database Seeding**: ✅ Idempotent seeders prevent duplicates
- **Custom Branding**: ✅ H. Sol logo and professional styling
- **Module System**: ✅ Laravel Modules with proper namespace resolution
- **🛡️ Module Permissions**: ✅ Custom Shield-like system implemented
- **📋 Permission Management**: ✅ Module Roles resource with GUI interface
- **⚡ CLI Commands**: ✅ `permissions:setup` and `permissions:register` commands
- **🎯 Demo Roles**: ✅ Module Manager, Core Admin, User Manager roles created

## 🤝 Contributing

We welcome contributions from the community! Here's how you can help:

1. **Fork the repository** on GitHub
2. **Create a feature branch** (`git checkout -b feature/amazing-feature`)
3. **Make your changes** and add tests if applicable
4. **Commit your changes** (`git commit -m 'Add amazing feature'`)
5. **Push to the branch** (`git push origin feature/amazing-feature`)
6. **Open a Pull Request**

### Development Guidelines
- Follow PSR-12 coding standards
- Write meaningful commit messages
- Add tests for new features
- Update documentation as needed

## 📈 Roadmap

- [ ] **API Module**: RESTful API with authentication
- [ ] **Notification System**: Real-time notifications
- [ ] **Activity Logs**: Comprehensive audit trails
- [ ] **Multi-tenancy**: Support for multiple organizations
- [ ] **Docker Support**: Containerization for easy deployment
- [ ] **Advanced Reporting**: Analytics and reporting dashboard

## 📄 License

This project is open-sourced software licensed under the [MIT License](LICENSE).

## 👨‍� Creator

<div align="center">

### **Abdur Rehman Majeed**
*Senior Frontend Developer | Full-Stack Advocate*

[![LinkedIn](https://img.shields.io/badge/LinkedIn-0077B5?style=for-the-badge&logo=linkedin&logoColor=white)](https://www.linkedin.com/in/abdurrehman712)
[![GitHub](https://img.shields.io/badge/GitHub-100000?style=for-the-badge&logo=github&logoColor=white)](https://github.com/AbdurRehman712)

**H. Sol (Hereafter Solutions)**  
*Building Tomorrow's Solutions Today*

---

*"Code with conviction. Design with da'wah. Serve with sincerity."*

Crafted by Abdur Rehman Majeed, a seasoned Senior Frontend Developer turned full-stack advocate with a proven track record. ARM is passionate about leveraging technology for da'wah and social impact, creating solutions that serve both worldly and spiritual needs.

</div>

## 🌟 Support & Community

- **Documentation**: [Wiki](https://github.com/AbdurRehman712/laravel-secure-admin-framework/wiki)
- **Issues**: [GitHub Issues](https://github.com/AbdurRehman712/laravel-secure-admin-framework/issues)
- **Discussions**: [GitHub Discussions](https://github.com/AbdurRehman712/laravel-secure-admin-framework/discussions)

## 🙏 Acknowledgments

- **Laravel Team** - For the amazing framework
- **Filament Team** - For the beautiful admin panel
- **Spatie** - For the excellent permission package
- **nWidart** - For the modular architecture package

---

<div align="center">

**If this project helped you, please consider giving it a ⭐ star!**

*Built with ❤️ for the open-source community*

</div>

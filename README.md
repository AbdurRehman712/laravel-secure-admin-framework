# Laravel Modular Admin Template

A comprehensive Laravel template featuring modular architecture with Filament admin panel, role-based permissions, and separate authentication guards for admin and public users.

## 🚀 Features

- **Modular Architecture**: Clean separation of concerns using `nwidart/laravel-modules`
- **Admin Panel**: Beautiful admin interface powered by Filament v4
- **Role-Based Permissions**: Complete RBAC system using `spatie/laravel-permission`
- **Dual Authentication**: Separate guards for admin and public users
- **Permission Management**: Auto-generated permissions per module
- **Scalable Structure**: Easy to add new modules and extend functionality

## 📦 Installed Packages

- **nwidart/laravel-modules**: Modular application architecture
- **spatie/laravel-permission**: Role and permission management
- **filament/filament**: Modern admin panel interface

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

## 🔧 Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd laravel-modular-admin-template
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Build assets**
   ```bash
   npm run build
   ```

6. **Start the development server**
   ```bash
   php artisan serve
   ```

## 👤 Default Users

After seeding, you can access the admin panel at `/admin` with:

- **Super Admin**: 
  - Email: `admin@admin.com`
  - Password: `password`
  - Permissions: All admin permissions

- **Regular Admin**: 
  - Email: `user@admin.com`
  - Password: `password`
  - Permissions: Limited admin permissions

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

## 🔑 Permission System

### Permission Naming Convention
- `view_{resource}` - View/list resources
- `create_{resource}` - Create new resources
- `edit_{resource}` - Edit existing resources
- `delete_{resource}` - Delete resources

### Role Management
- **Super Admin**: Has all permissions
- **Admin**: Has limited permissions (customizable per module)

### Guards
- Admin permissions use `admin` guard
- Public user permissions use `web` guard

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

1. **Complete Filament Resources**: Fix AdminResource for Filament v4 compatibility
2. **Add More Modules**: Create additional business logic modules
3. **Frontend Views**: Add public user interface views
4. **API Integration**: Add API routes and controllers
5. **Testing**: Implement comprehensive test suite
6. **Deployment**: Add deployment configurations

## 🐛 Known Issues

- AdminResource needs Filament v4 compatibility fixes
- Some autoloader warnings for module structure (non-critical)

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## 📞 Support

For questions and support, please open an issue in the repository.

---

**Happy Coding! 🎉**

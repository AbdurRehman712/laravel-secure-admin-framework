# Laravel Secure Admin Framework

<div align="center">

![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Filament](https://img.shields.io/badge/Filament-4.x-F59E0B?style=for-the-badge&logo=filament&logoColor=white)
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

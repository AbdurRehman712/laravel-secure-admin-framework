# Quick Installation Guide

## Prerequisites

- PHP >= 8.1
- Composer
- Node.js & NPM
- MySQL/PostgreSQL database

## Installation Steps

1. **Clone and Setup**
   ```bash
   git clone <your-repo-url> laravel-modular-admin
   cd laravel-modular-admin
   composer install
   npm install
   ```

2. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Configuration**
   Edit `.env` file:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

4. **Run Migrations and Seeders**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Build Assets**
   ```bash
   npm run build
   ```

6. **Start Development Server**
   ```bash
   php artisan serve
   ```

## Access Points

- **Admin Panel**: http://localhost:8000/admin
  - Super Admin: admin@admin.com / password
  - Regular Admin: user@admin.com / password

- **Public Routes**: http://localhost:8000/user/*
  - Login: http://localhost:8000/user/login

## Quick Test

1. Visit http://localhost:8000/admin
2. Login with admin@admin.com / password
3. You should see the Filament admin dashboard

That's it! Your modular Laravel application with Filament admin panel is ready! 🎉

## Module Permission System

This application includes a sophisticated module-aware permission system that automatically manages permissions based on your application's modular structure.

### How It Works

The system automatically discovers and organizes permissions by modules:

- **Core Module**: Admin management, user roles, system settings
- **PublicUser Module**: Public user management, user-related operations  
- **System Module**: Application-level permissions like module role management

### Key Features

1. **Auto-Discovery**: Permissions are automatically discovered from Filament resources in each module
2. **Module Grouping**: Permissions are organized by module for better management
3. **Dynamic Registration**: New modules and their permissions are automatically detected
4. **Role-Based Access**: Assign permissions to roles with module-aware interface

### Permission Structure

Each permission follows the pattern: `{action}_{resource}` where:
- `action`: view_any, view, create, update, delete, etc.
- `resource`: admin, user, role, etc.

Examples:
- `view_any_admin` - View any admin user
- `create_user` - Create public users
- `update_module_role` - Edit module roles

### Managing Permissions

1. **Access Module Roles**: Go to Admin Panel → Module Roles
2. **Create/Edit Roles**: Permissions are grouped by module for easy selection
3. **Assign to Users**: Users inherit permissions through their assigned roles

### CLI Commands

```bash
# Register all module permissions
php artisan permissions:register

# Setup module permissions (register + assign to Super Admin)
php artisan permissions:setup

# Reset database and reseed (development only)
php artisan db:reset-seed --force
```

### Default Roles

- **Super Admin**: Has all permissions across all modules
- **Admin**: Limited admin permissions
- **User Manager**: Can manage public users only

### Adding New Modules

When you add new modules with Filament resources:

1. Place resources in `Modules/{ModuleName}/app/Filament/Resources/`
2. Run `php artisan permissions:register` to discover new permissions
3. Assign permissions to roles via the Module Roles interface

The system will automatically detect and organize permissions by your new module.

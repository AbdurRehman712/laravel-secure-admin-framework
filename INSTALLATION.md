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

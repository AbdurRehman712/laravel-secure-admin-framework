# SecureAdmin Framework - Fresh Installation Guide

This guide addresses the common database-related errors during fresh installation and provides multiple installation methods.

## 🚨 Common Installation Issues

### Issue 1: "Table 'cache' doesn't exist" Error
**Cause**: Laravel is configured to use database for cache/sessions before migrations run.

### Issue 2: "Table 'permissions' doesn't exist" Error  
**Cause**: Service providers try to register permissions during boot before database setup.

### Issue 3: "SQLSTATE[42S02]: Base table or view not found"
**Cause**: Application tries to access database tables that haven't been created yet.

## ✅ Solutions Implemented

The framework now includes several fixes:

1. **Smart Service Providers**: All service providers now check if database tables exist before accessing them
2. **Installation Scripts**: Automated scripts handle the installation process correctly
3. **Database Checks**: Built-in checks prevent database access during migrations
4. **Graceful Fallbacks**: Services fail silently during installation instead of throwing errors

## 🚀 Installation Methods

### Method 1: Automated Installation Script (Recommended)

```bash
# 1. Clone and install dependencies
git clone <repository-url>
cd filament
composer install

# 2. Configure environment
cp .env.example .env
# Edit .env with your database credentials

# 3. Generate app key
php artisan key:generate

# 4. Run automated installation
php install.php
```

### Method 2: Artisan Setup Command

```bash
# Follow steps 1-3 from Method 1, then:
php artisan setup:install

# For fresh installation (resets everything):
php artisan setup:install --fresh
```

### Method 3: Manual Installation (Step by Step)

```bash
# 1. Basic setup
composer install
cp .env.example .env
php artisan key:generate

# 2. Configure .env for installation
# Temporarily set these values:
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# 3. Clear caches and run migrations
php artisan config:clear
php artisan cache:clear
php artisan migrate

# 4. Seed database
php artisan db:seed

# 5. Restore production settings in .env:
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database

# 6. Build assets
npm install
npm run build

# 7. Final optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🔧 Environment Configuration

### Database Settings
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Cache Settings (Production)
```env
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

### Cache Settings (Development)
```env
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

## 🛠️ Troubleshooting

### If Installation Still Fails

1. **Check Database Connection**
   ```bash
   php artisan tinker
   DB::connection()->getPdo();
   ```

2. **Clear Everything and Start Fresh**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   rm -rf bootstrap/cache/*.php
   ```

3. **Reset Database**
   ```bash
   php artisan migrate:fresh --seed
   ```

4. **Check File Permissions**
   ```bash
   chmod -R 755 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

### Common Error Solutions

**Error**: "Class 'Spatie\Permission\Models\Permission' not found"
**Solution**: Run `composer install` and ensure all dependencies are installed.

**Error**: "No application encryption key has been specified"
**Solution**: Run `php artisan key:generate`

**Error**: "Access denied for user"
**Solution**: Check database credentials in `.env` file.

## 📋 Post-Installation Checklist

- [ ] Application loads without errors at `/admin`
- [ ] Can login with `admin@admin.com` / `password`
- [ ] Module Builder is accessible at `/admin/enhanced-module-builder`
- [ ] Module Editor is accessible at `/admin/module-editor`
- [ ] No console errors in browser
- [ ] Database tables are created
- [ ] Permissions are working

## 🔐 Security Steps

1. **Change Default Password**
   - Login to `/admin`
   - Go to Admin Users
   - Edit admin user and set strong password

2. **Update Environment**
   ```env
   APP_DEBUG=false  # For production
   APP_ENV=production  # For production
   ```

3. **Set Proper Permissions**
   ```bash
   chmod -R 755 storage bootstrap/cache
   ```

## 🎯 Quick Test

1. Visit `http://localhost/admin`
2. Login with:
   - Email: `admin@admin.com`
   - Password: `password`
3. Navigate to "Enhanced Module Builder"
4. Try creating a test module

## 📚 Next Steps

After successful installation:

1. **Read Documentation**: Check `MODULE_BUILDER_V1_DOCUMENTATION.md`
2. **Create First Module**: Use the Enhanced Module Builder
3. **Configure Permissions**: Set up user roles
4. **Customize Branding**: Update logos and colors

## 🆘 Getting Help

If you still encounter issues:

1. Check `storage/logs/laravel.log` for detailed errors
2. Verify all requirements are met
3. Try the fresh installation option
4. Ensure database server is running and accessible

---

**The framework is now designed to handle fresh installations gracefully! 🎉**

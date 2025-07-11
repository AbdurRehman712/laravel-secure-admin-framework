#!/usr/bin/env php
<?php

/**
 * SecureAdmin Framework Installation Script
 * 
 * This script handles the initial setup of the SecureAdmin Framework
 * to avoid database-related errors during fresh installation.
 */

echo "🚀 SecureAdmin Framework Installation Script\n";
echo "============================================\n\n";

// Check if we're in the right directory
if (!file_exists('artisan')) {
    echo "❌ Error: This script must be run from the Laravel project root directory.\n";
    exit(1);
}

// Check if .env file exists
if (!file_exists('.env')) {
    echo "❌ Error: .env file not found. Please copy .env.example to .env and configure it first.\n";
    exit(1);
}

echo "📋 Step 1: Checking environment configuration...\n";

// Load environment variables
$envContent = file_get_contents('.env');
$dbConnection = getEnvValue($envContent, 'DB_CONNECTION');
$dbHost = getEnvValue($envContent, 'DB_HOST');
$dbDatabase = getEnvValue($envContent, 'DB_DATABASE');

echo "   Database: {$dbConnection}\n";
echo "   Host: {$dbHost}\n";
echo "   Database: {$dbDatabase}\n\n";

echo "🔧 Step 2: Optimizing configuration for fresh installation...\n";

// Check current cache configuration
$currentCacheStore = getEnvValue($envContent, 'CACHE_STORE');
$needsOptimization = in_array($currentCacheStore, ['database', 'redis']);

if ($needsOptimization) {
    // Temporarily modify .env to use file-based cache/sessions during installation
    $tempEnvContent = $envContent;
    $tempEnvContent = setEnvValue($tempEnvContent, 'CACHE_STORE', 'file');
    $tempEnvContent = setEnvValue($tempEnvContent, 'SESSION_DRIVER', 'file');
    $tempEnvContent = setEnvValue($tempEnvContent, 'QUEUE_CONNECTION', 'sync');

    // Backup original .env
    copy('.env', '.env.backup');
    file_put_contents('.env', $tempEnvContent);

    echo "   ✅ Temporarily switched to file-based cache/sessions\n";
    echo "   ✅ Backed up original .env to .env.backup\n\n";
} else {
    echo "   ✅ Configuration already optimized for installation\n\n";
}

echo "🗄️  Step 3: Setting up database...\n";

// Clear any cached config
runCommand('php artisan config:clear');
runCommand('php artisan cache:clear');

echo "   ✅ Cleared configuration cache\n";

// Run migrations
echo "   📦 Running database migrations...\n";
runCommand('php artisan migrate --force');

echo "   ✅ Database migrations completed\n\n";

echo "🔐 Step 4: Setting up permissions and roles...\n";

// Run seeders
echo "   👤 Creating admin users and roles...\n";
runCommand('php artisan db:seed --force');

echo "   ✅ Admin users and roles created\n\n";

echo "⚙️  Step 5: Restoring production configuration...\n";

// Restore original .env if backup exists
if (file_exists('.env.backup')) {
    copy('.env.backup', '.env');
    unlink('.env.backup');
    echo "   ✅ Restored original .env configuration\n";
} else {
    echo "   ✅ No configuration changes to restore\n";
}

// Clear cache again with new configuration
runCommand('php artisan config:clear');
runCommand('php artisan cache:clear');

echo "   ✅ Cleared cache with production settings\n\n";

echo "🎨 Step 6: Building frontend assets...\n";

if (file_exists('package.json')) {
    echo "   📦 Installing npm dependencies...\n";
    runCommand('npm install');
    
    echo "   🏗️  Building production assets...\n";
    runCommand('npm run build');
    
    echo "   ✅ Frontend assets built successfully\n\n";
} else {
    echo "   ⚠️  No package.json found, skipping frontend build\n\n";
}

echo "🔧 Step 7: Final optimizations...\n";

// Optimize for production
runCommand('php artisan config:cache');
runCommand('php artisan route:cache');
runCommand('php artisan view:cache');

echo "   ✅ Application optimized\n\n";

echo "🎉 Installation completed successfully!\n\n";

echo "📋 Next Steps:\n";
echo "   1. Visit your application: " . getEnvValue($envContent, 'APP_URL') . "/admin\n";
echo "   2. Login with:\n";
echo "      Email: admin@admin.com\n";
echo "      Password: password\n\n";

echo "📚 Documentation:\n";
echo "   - Module Builder: /admin/enhanced-module-builder\n";
echo "   - Module Editor: /admin/module-editor\n";
echo "   - README.md for detailed information\n\n";

echo "✨ Happy coding with SecureAdmin Framework!\n";

/**
 * Helper functions
 */
function runCommand($command) {
    echo "   Running: {$command}\n";
    $output = [];
    $returnCode = 0;
    exec($command . ' 2>&1', $output, $returnCode);
    
    if ($returnCode !== 0) {
        echo "   ❌ Command failed: " . implode("\n", $output) . "\n";
        exit(1);
    }
    
    return $output;
}

function getEnvValue($envContent, $key) {
    if (preg_match("/^{$key}=(.*)$/m", $envContent, $matches)) {
        return trim($matches[1], '"\'');
    }
    return '';
}

function setEnvValue($envContent, $key, $value) {
    $pattern = "/^{$key}=.*$/m";
    $replacement = "{$key}={$value}";
    
    if (preg_match($pattern, $envContent)) {
        return preg_replace($pattern, $replacement, $envContent);
    } else {
        return $envContent . "\n{$replacement}";
    }
}

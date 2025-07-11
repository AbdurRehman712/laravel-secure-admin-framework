<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class SetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'setup:install {--fresh : Run fresh installation}';

    /**
     * The console command description.
     */
    protected $description = 'Setup SecureAdmin Framework for first-time installation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 SecureAdmin Framework Setup');
        $this->info('================================');
        $this->newLine();

        // Check if .env exists
        if (!File::exists(base_path('.env'))) {
            $this->error('❌ .env file not found. Please copy .env.example to .env and configure it first.');
            return 1;
        }

        $fresh = $this->option('fresh');

        if ($fresh) {
            $this->warn('⚠️  Fresh installation will reset all data!');
            if (!$this->confirm('Are you sure you want to continue?')) {
                $this->info('Installation cancelled.');
                return 0;
            }
        }

        $this->info('📋 Step 1: Preparing environment...');
        $this->optimizeForInstallation();

        $this->info('🗄️  Step 2: Setting up database...');
        $this->setupDatabase($fresh);

        $this->info('🔐 Step 3: Setting up permissions and roles...');
        $this->setupPermissions();

        $this->info('⚙️  Step 4: Restoring configuration...');
        $this->restoreConfiguration();

        $this->info('🎨 Step 5: Building assets...');
        $this->buildAssets();

        $this->info('🔧 Step 6: Final optimizations...');
        $this->finalOptimizations();

        $this->newLine();
        $this->info('🎉 Setup completed successfully!');
        $this->newLine();

        $this->displayNextSteps();

        return 0;
    }

    private function optimizeForInstallation()
    {
        // Clear caches
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        
        $this->line('   ✅ Cleared application caches');
    }

    private function setupDatabase($fresh = false)
    {
        try {
            if ($fresh) {
                $this->line('   🔄 Running fresh migrations...');
                Artisan::call('migrate:fresh', ['--force' => true]);
            } else {
                $this->line('   📦 Running migrations...');
                Artisan::call('migrate', ['--force' => true]);
            }
            
            $this->line('   ✅ Database migrations completed');
        } catch (\Exception $e) {
            $this->error('   ❌ Migration failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function setupPermissions()
    {
        try {
            $this->line('   👤 Creating admin users and roles...');
            Artisan::call('db:seed', ['--force' => true]);
            
            $this->line('   ✅ Permissions and roles configured');
        } catch (\Exception $e) {
            $this->error('   ❌ Seeding failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function restoreConfiguration()
    {
        // Clear cache with production settings
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        
        $this->line('   ✅ Configuration restored');
    }

    private function buildAssets()
    {
        if (File::exists(base_path('package.json'))) {
            $this->line('   📦 Installing npm dependencies...');
            $this->runCommand('npm install');
            
            $this->line('   🏗️  Building assets...');
            $this->runCommand('npm run build');
            
            $this->line('   ✅ Assets built successfully');
        } else {
            $this->line('   ⚠️  No package.json found, skipping asset build');
        }
    }

    private function finalOptimizations()
    {
        if (app()->environment('production')) {
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
            
            $this->line('   ✅ Production optimizations applied');
        } else {
            $this->line('   ✅ Development environment ready');
        }
    }

    private function runCommand($command)
    {
        $process = proc_open(
            $command,
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            base_path()
        );

        if (is_resource($process)) {
            fclose($pipes[0]);
            $output = stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            
            $returnCode = proc_close($process);
            
            if ($returnCode !== 0) {
                throw new \Exception("Command failed: {$command}\nError: {$error}");
            }
        }
    }

    private function displayNextSteps()
    {
        $appUrl = config('app.url', 'http://localhost');
        
        $this->info('📋 Next Steps:');
        $this->line("   1. Visit your application: {$appUrl}/admin");
        $this->line('   2. Login with:');
        $this->line('      Email: admin@admin.com');
        $this->line('      Password: password');
        $this->newLine();
        
        $this->info('📚 Available Features:');
        $this->line('   - Enhanced Module Builder: /admin/enhanced-module-builder');
        $this->line('   - Module Editor: /admin/module-editor');
        $this->line('   - User Management: /admin/admins');
        $this->newLine();
        
        $this->info('📖 Documentation:');
        $this->line('   - README.md for detailed information');
        $this->line('   - MODULE_BUILDER_V1_DOCUMENTATION.md for module builder guide');
        $this->newLine();
        
        $this->info('✨ Happy coding with SecureAdmin Framework!');
    }
}

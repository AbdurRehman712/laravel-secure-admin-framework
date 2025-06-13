<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class ResetAndSeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:reset-seed {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely reset and reseed the database with proper cleanup';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will clear all role-permission and user-role data. Continue?')) {
                $this->info('Operation cancelled.');
                return;
            }
        }

        $this->info('🧹 Clearing role-permission assignments...');
        DB::table('role_has_permissions')->truncate();
        
        $this->info('🧹 Clearing user-role assignments...');
        DB::table('model_has_roles')->truncate();
        
        $this->info('🧹 Clearing permission cache...');
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        $this->info('🌱 Running database seeder...');
        Artisan::call('db:seed', [], $this->getOutput());
        
        $this->info('✅ Database reset and seeded successfully!');
    }
}

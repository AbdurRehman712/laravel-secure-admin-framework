<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');

        // Create default test user
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
            ]
        );
        $this->command->info('✅ Test user created');

        // Clear any existing permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $this->command->info('🧹 Permission cache cleared');

        // Seed admin users and roles
        $this->command->info('👤 Seeding admin users and roles...');
        $this->call(AdminSeeder::class);
        
        // Seed public users and roles
        $this->command->info('👥 Seeding public users and roles...');
        $this->call(PublicUserSeeder::class);

        // Seed projects with comprehensive AI content
        $this->command->info('📋 Seeding projects with AI-generated content...');
        $this->call(ProjectWithContentSeeder::class);

        $this->command->info('🎉 Database seeding completed successfully!');
    }
}

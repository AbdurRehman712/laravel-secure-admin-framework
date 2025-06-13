<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PublicUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles for public users
        $userRole = Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);
        $premiumRole = Role::firstOrCreate(['name' => 'Premium User', 'guard_name' => 'web']);
        $moderatorRole = Role::firstOrCreate(['name' => 'Moderator', 'guard_name' => 'web']);

        // Create permissions for public users
        $permissions = [
            'view posts',
            'create posts',
            'edit own posts',
            'delete own posts',
            'moderate posts',
            'access premium content',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Assign permissions to roles
        $userRole->givePermissionTo(['view posts', 'create posts', 'edit own posts', 'delete own posts']);
        $premiumRole->givePermissionTo(['view posts', 'create posts', 'edit own posts', 'delete own posts', 'access premium content']);
        $moderatorRole->givePermissionTo(['view posts', 'create posts', 'edit own posts', 'delete own posts', 'moderate posts']);

        // Create sample users
        $users = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'User'
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'Premium User'
            ],
            [
                'name' => 'Bob Johnson',
                'email' => 'bob@example.com',
                'email_verified_at' => null,
                'password' => Hash::make('password'),
                'role' => 'User'
            ],
            [
                'name' => 'Alice Brown',
                'email' => 'alice@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'Moderator'
            ],
            [
                'name' => 'Charlie Wilson',
                'email' => 'charlie@example.com',
                'email_verified_at' => null,
                'password' => Hash::make('password'),
                'role' => 'Premium User'
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'email_verified_at' => $userData['email_verified_at'],
                    'password' => $userData['password'],
                ]
            );

            // Assign role to user
            $user->assignRole($userData['role']);
        }
    }
}

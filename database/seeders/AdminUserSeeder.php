<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user if doesn't exist
        $adminUser = User::where('email', 'admin@gmail.com')->first();

        if (!$adminUser) {
            $adminUser = User::create([
                'name' => 'Administrator',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('password'), // Default password
                'email_verified_at' => now(),
                'allowed_status' => json_encode([
                    '-1',
                    '0',
                    '1',
                    '2',
                    '3',
                    '4',
                    '5',
                    '6',
                    '8',
                    '9',
                    '10',
                    '11',
                    '12',
                    '15',
                    '16',
                    '17',
                    '18',
                    '19',
                    '20'
                ]),
            ]);

            $this->command->info('Created admin user: admin@gmail.com');
        } else {
            $this->command->info('Admin user already exists: admin@gmail.com');
        }

        // Ensure Admin role exists and assign it
        $adminRole = Role::where('name', 'Admin')->first();

        if ($adminRole && !$adminUser->hasRole('Admin')) {
            $adminUser->assignRole('Admin');
            $this->command->info('Assigned Admin role to admin@gmail.com');
        } elseif (!$adminRole) {
            $this->command->warn('Admin role not found. Please run RolePermissionSeeder first.');
        } else {
            $this->command->info('Admin user already has Admin role');
        }

        // Also assign Super Admin role for full access
        $superAdminRole = Role::where('name', 'Super Admin')->first();

        if ($superAdminRole && !$adminUser->hasRole('Super Admin')) {
            $adminUser->assignRole('Super Admin');
            $this->command->info('Assigned Super Admin role to admin@gmail.com');
        } elseif (!$superAdminRole) {
            $this->command->warn('Super Admin role not found. Please run RolePermissionSeeder first.');
        } else {
            $this->command->info('Admin user already has Super Admin role');
        }

        $this->command->line('');
        $this->command->info('Admin User Details:');
        $this->command->line('Email: admin@gmail.com');
        $this->command->line('Password: password');
        $this->command->line('Roles: ' . implode(', ', $adminUser->getRoleNames()->toArray()));
    }
}

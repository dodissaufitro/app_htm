<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateSampleUsers extends Command
{
    protected $signature = 'test:create-sample-users';
    protected $description = 'Create sample users for testing access control';

    public function handle()
    {
        $this->info('Creating sample users for testing...');

        // Sample users dengan berbagai role dan akses
        $users = [
            [
                'name' => 'Viewer User',
                'email' => 'viewer@example.com',
                'password' => Hash::make('password123'),
                'role' => 'Viewer',
                'allowed_status' => ['COMPLETED'] // Hanya bisa lihat yang selesai
            ],
            [
                'name' => 'Operator User',
                'email' => 'operator@example.com',
                'password' => Hash::make('password123'),
                'role' => 'Operator',
                'allowed_status' => ['DRAFT', 'PROSES'] // Draft dan proses
            ],
            [
                'name' => 'Verifikator User',
                'email' => 'verifikator@example.com',
                'password' => Hash::make('password123'),
                'role' => 'Verifikator',
                'allowed_status' => ['DRAFT', 'PROSES', 'APPROVED'] // Draft, proses, approved
            ],
            [
                'name' => 'Limited Admin',
                'email' => 'limited@example.com',
                'password' => Hash::make('password123'),
                'role' => 'Admin',
                'allowed_status' => ['REJECTED'] // Hanya rejected
            ]
        ];

        foreach ($users as $userData) {
            // Delete if exists
            User::where('email', $userData['email'])->delete();

            // Create user
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => $userData['password'],
                'allowed_status' => $userData['allowed_status']
            ]);

            // Assign role
            $role = Role::findByName($userData['role']);
            $user->assignRole($role);

            $this->info("✅ Created: {$userData['name']} ({$userData['email']}) - Role: {$userData['role']} - Status: " . implode(', ', $userData['allowed_status']));
        }

        $this->info("\n🎉 Sample users created successfully!");
        $this->info("Password for all users: password123");
    }
}
